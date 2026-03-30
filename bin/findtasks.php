#!/usr/bin/env php
<?php

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(__DIR__ . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * Recursively find task lines inside data/pages/ and output a CSV.
 *
 * No arguments: generates todo.csv, wip.csv and done.csv at once.
 *
 * Modes:
 *   todo  - searches for [_] and [ ] (DokuWiki + Markdown pending tasks)
 *   wip   - searches for [>]
 *   done  - searches for [x]
 */
class FindTasksCLI extends CLI
{
    /** @var array Map of mode => patterns */
    private static $modes = [
        'todo' => ['[_]', '[ ]'],
        'wip'  => ['[>]'],
        'done' => ['[x]'],
    ];

    /** @var array Default output filename per mode */
    private static $defaultOutput = [
        'todo' => 'todo.csv',
        'wip'  => 'wip.csv',
        'done' => 'done.csv',
    ];

    protected function setup(Options $options)
    {
        $options->setHelp(
            'Recursively searches data/pages/ for task lines and outputs a CSV.' . "\n" .
            'Each row contains the relative file path and the matching line, separated by ";".' . "\n\n" .
            'Run without arguments to generate todo.csv, wip.csv and done.csv at once.' . "\n\n" .
            'Modes:' . "\n" .
            '  todo  searches for [_] and [ ]  (pending tasks, DokuWiki + Markdown)' . "\n" .
            '  wip   searches for [>]           (work in progress)' . "\n" .
            '  done  searches for [x]           (completed tasks)' . "\n\n" .
            'Example output:' . "\n" .
            '  dokuwiki/to-do.txt;### - [_] blablabla' . "\n\n" .
            'Examples:' . "\n" .
            '  php bin/findtasks.php                        # generate all 3 CSV files at once' . "\n" .
            '  php bin/findtasks.php todo                   # print pending tasks to stdout' . "\n" .
            '  php bin/findtasks.php wip                    # print WIP tasks to stdout' . "\n" .
            '  php bin/findtasks.php done                   # print done tasks to stdout' . "\n" .
            '  php bin/findtasks.php todo -o todo.csv       # save pending tasks to todo.csv' . "\n" .
            '  php bin/findtasks.php wip  -o wip.csv        # save WIP tasks to wip.csv' . "\n" .
            '  php bin/findtasks.php done -o done.csv       # save done tasks to done.csv' . "\n" .
            '  php bin/findtasks.php todo -p "[TODO]"       # override pattern'
        );

        $options->registerArgument(
            'mode',
            'Task mode to search: todo, wip or done. Omit to generate all 3 CSV files at once.',
            false
        );

        $options->registerOption(
            'output',
            'Write CSV to this file instead of stdout.',
            'o',
            'file'
        );

        $options->registerOption(
            'pattern',
            'Override the search pattern. Ignores the mode patterns and uses this single string instead.',
            'p',
            'string'
        );
    }

    protected function main(Options $options)
    {
        global $conf;

        $args     = $options->getArgs();
        $mode     = strtolower(trim($args[0] ?? ''));
        $outfile  = $options->getOpt('output', '');
        $override = $options->getOpt('pattern', '');

        $pagesDir = $conf['datadir'];
        if (!is_dir($pagesDir)) {
            $this->fatal("Pages directory not found: $pagesDir");
        }

        // No mode given: generate all 3 CSV files at once
        if ($mode === '') {
            $totalStart = microtime(true);
            foreach (self::$modes as $m => $patterns) {
                $start = microtime(true);
                $rows  = $this->collectRows($pagesDir, $patterns);
                $file  = self::$defaultOutput[$m];
                $elapsed = round(microtime(true) - $start, 3);
                $this->writeCSV($rows, $file, $m, $elapsed);
            }
            $totalElapsed = round(microtime(true) - $totalStart, 3);
            $this->info("Total time: {$totalElapsed}s");
            return;
        }

        // Single mode
        if ($override) {
            $patterns = [$override];
        } elseif (isset(self::$modes[$mode])) {
            $patterns = self::$modes[$mode];
        } else {
            $this->fatal("Unknown mode \"$mode\". Use: todo, wip or done.");
        }

        $start = microtime(true);
        $rows  = $this->collectRows($pagesDir, $patterns);
        $elapsed = round(microtime(true) - $start, 3);

        if (!$rows) {
            $this->info("No matching lines found. ({$elapsed}s)");
            return;
        }

        if ($outfile) {
            $this->writeCSV($rows, $outfile, $mode, $elapsed);
        } else {
            echo implode("\n", $rows) . "\n";
            $this->info("Done in {$elapsed}s");
        }
    }

    /**
     * Collect all matching rows for a list of patterns, sorted by path.
     *
     * @param string $pagesDir
     * @param array  $patterns
     * @return array
     */
    private function collectRows($pagesDir, $patterns)
    {
        $rows = [];
        foreach ($patterns as $pattern) {
            $this->scanDir($pagesDir, $pagesDir, $pattern, $rows);
        }
        sort($rows);
        return $rows;
    }

    /**
     * Write rows to a CSV file, overwriting if it exists.
     *
     * @param array  $rows
     * @param string $file
     * @param string $label    Used in success/error messages
     * @param float  $elapsed  Seconds taken to collect the rows
     */
    private function writeCSV($rows, $file, $label, $elapsed)
    {
        $csv = implode("\n", $rows) . "\n";
        if (file_put_contents($file, $csv) === false) {
            $this->error("Permission error: could not write to $file ({$elapsed}s)");
            return;
        }
        $this->success(count($rows) . ' match(es) written to ' . $file . ' [' . $label . '] ' . $elapsed . 's');
    }

    /**
     * Recursively scan a directory for lines matching a single pattern.
     *
     * @param string $baseDir  Absolute path to data/pages/ (for computing relative paths)
     * @param string $dir      Current directory being scanned
     * @param string $pattern  String to search for within each line
     * @param array  &$rows    Collected CSV rows
     */
    private function scanDir($baseDir, $dir, $pattern, &$rows)
    {
        $entries = scandir($dir);
        if ($entries === false) return;

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') continue;

            $fullPath = $dir . '/' . $entry;

            if (is_dir($fullPath)) {
                $this->scanDir($baseDir, $fullPath, $pattern, $rows);
                continue;
            }

            if (substr($entry, -4) !== '.txt') continue;

            $relativePath = ltrim(str_replace($baseDir, '', $fullPath), '/\\');
            $relativePath = str_replace('\\', '/', $relativePath);

            $lines = file($fullPath, FILE_IGNORE_NEW_LINES);
            if ($lines === false) continue;

            foreach ($lines as $line) {
                if (strpos($line, $pattern) !== false) {
                    $rows[] = $relativePath . ';' . $line;
                }
            }
        }
    }
}

// Main
$cli = new FindTasksCLI();
$cli->run();
