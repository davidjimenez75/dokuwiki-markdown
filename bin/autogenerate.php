#!/usr/bin/env php
<?php

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(__DIR__ . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * Auto-generates wiki pages under data/pages/auto-generated/
 * based on searches across the entire wiki.
 *
 * Generated pages:
 *   auto-generated/tasks/to-do.md   — lines matching [_] and [ ]
 *   auto-generated/tasks/wip.md     — lines matching [>] and [WIP]
 *   auto-generated/tasks/done.md    — lines matching [x]
 *
 * Fork detection: counts .md vs .txt files in data/pages/ root.
 *   Majority .md → dokuwiki-markdown fork
 *   Majority .txt → standard DokuWiki
 */
class AutoGenerateCLI extends CLI
{
    /** Directory (relative to datadir) that is always excluded from scanning */
    const EXCLUDE_DIR = 'auto-generated';

    /** Page IDs (DokuWiki format) to skip during scanning */
    private static $ignoredPages = [
        'conf/entities.local.conf',
    ];

    /** @var array Task modes: name => [patterns, output page] */
    private static $tasks = [
        'to-do' => [
            'patterns' => ['[_]', '[ ]'],
            'page'     => 'auto-generated/tasks/to-do',
            'title'    => 'Pending Tasks',
        ],
        'wip' => [
            'patterns' => ['[>]', '[WIP]'],
            'page'     => 'auto-generated/tasks/wip',
            'title'    => 'Work In Progress',
        ],
        'done' => [
            'patterns' => ['[x]'],
            'page'     => 'auto-generated/tasks/done',
            'title'    => 'Done Tasks',
        ],
    ];

    protected function setup(Options $options)
    {
        $options->setHelp(
            'Auto-generates wiki pages under auto-generated/ based on searches across all pages.' . "\n\n" .
            'Generated pages:' . "\n" .
            '  auto-generated/tasks/to-do   lines matching [_] and [ ]' . "\n" .
            '  auto-generated/tasks/wip     lines matching [>] and [WIP]' . "\n" .
            '  auto-generated/tasks/done    lines matching [x]' . "\n\n" .
            'Content inside auto-generated/ is always excluded from scanning.' . "\n\n" .
            'Examples:' . "\n" .
            '  php bin/autogenerate.php              # generate all pages' . "\n" .
            '  php bin/autogenerate.php tasks        # generate task pages only' . "\n" .
            '  php bin/autogenerate.php tasks to-do  # generate a single task page'
        );

        $options->registerArgument(
            'group',
            'Group to generate: tasks. Omit to generate all groups.',
            false
        );

        $options->registerArgument(
            'name',
            'Specific page within the group (e.g. to-do, wip, done). Omit to generate all in group.',
            false
        );

        $options->registerOption(
            'dry-run',
            'Show what would be generated without writing any files.',
            'd'
        );
    }

    /**
     * Detect whether this is the dokuwiki-markdown fork by counting
     * .md vs .txt files recursively in data/pages/ (up to 100 files sampled).
     *
     * @param string $pagesDir
     * @return bool
     */
    private function isMarkdownFork($pagesDir)
    {
        $md  = 0;
        $txt = 0;
        $checked = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($pagesDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;
            if (str_ends_with($file->getFilename(), '.md'))  $md++;
            if (str_ends_with($file->getFilename(), '.txt')) $txt++;
            if (++$checked >= 100) break;
        }

        return $md > $txt;
    }

    protected function main(Options $options)
    {
        global $conf;

        $args    = $options->getArgs();
        $group   = $args[0] ?? '';
        $name    = $args[1] ?? '';
        $dryRun  = $options->getOpt('dry-run');

        $pagesDir = $conf['datadir'];
        if (!is_dir($pagesDir)) {
            $this->fatal("Pages directory not found: $pagesDir");
        }

        $isMarkdown = $this->isMarkdownFork($pagesDir);
        $ext        = $isMarkdown ? '.md' : '.txt';
        if ($isMarkdown) {
            $this->info("Detected: dokuwiki-markdown fork (page extension: .md)");
        }

        $totalStart = microtime(true);

        if ($group === '' || $group === 'tasks') {
            $modes = $name ? [$name => self::$tasks[$name] ?? null] : self::$tasks;
            foreach ($modes as $modeName => $mode) {
                if (!$mode) {
                    $this->error("Unknown task page \"$modeName\". Use: to-do, wip, done.");
                    continue;
                }
                $start   = microtime(true);
                $rows    = $this->collectRows($pagesDir, $mode['patterns'], $ext);
                $elapsed = round(microtime(true) - $start, 3);
                $this->buildPage($pagesDir, $mode, $rows, $elapsed, $dryRun, $ext);
            }
        } else {
            $this->fatal("Unknown group \"$group\". Available: tasks");
        }

        $total = round(microtime(true) - $totalStart, 3);
        $this->info("Total time: {$total}s");
    }

    /**
     * Collect all rows matching the given patterns, excluding auto-generated/.
     *
     * @param string $pagesDir
     * @param array  $patterns
     * @param string $ext       Page file extension: '.md' or '.txt'
     * @return array  [ ['page' => 'ns/page', 'line' => '...'], ... ]
     */
    private function collectRows($pagesDir, $patterns, $ext)
    {
        $rows = [];
        foreach ($patterns as $pattern) {
            $this->scanDir($pagesDir, $pagesDir, $pattern, $ext, $rows);
        }
        // Sort by page path
        usort($rows, fn($a, $b) => strcmp($a['page'], $b['page']));
        return $rows;
    }

    /**
     * Build and write (or preview) a wiki page with an embedded CSV block.
     *
     * @param string $pagesDir
     * @param array  $mode      Task mode config
     * @param array  $rows      Collected rows
     * @param float  $elapsed   Scan time in seconds
     * @param bool   $dryRun
     * @param string $ext       Page file extension: '.md' or '.txt'
     */
    private function buildPage($pagesDir, $mode, $rows, $elapsed, $dryRun, $ext)
    {
        $patterns = implode('`, `', $mode['patterns']);
        $now      = date('Y-m-d H:i:s');
        $count    = count($rows);

        // Build CSV content
        $csv  = "file,content\n";
        foreach ($rows as $row) {
            $pageLink = '[[' . $row['page'] . ']]';
            // Escape double quotes in content
            $content  = str_replace('"', '""', $row['line']);
            $csv     .= $pageLink . ',"' . $content . '"' . "\n";
        }

        // Build page content
        $content  = "# {$mode['title']}\n\n";
        $content .= "⚠️ **Auto-generated page** — do not edit manually.\n\n";
        $content .= "Last updated: {$now} | {$count} result(s) | Patterns: `{$patterns}` | Scan time: {$elapsed}s\n\n";

        if ($rows) {
            $content .= "<csv>\n{$csv}</csv>\n";
        } else {
            $content .= "_No results found._\n";
        }

        // Resolve output file path
        $relPath  = str_replace('/', DIRECTORY_SEPARATOR, $mode['page']) . $ext;
        $filePath = $pagesDir . DIRECTORY_SEPARATOR . $relPath;
        $fileDir  = dirname($filePath);

        if ($dryRun) {
            $this->info("[DRY-RUN] Would write {$count} row(s) to {$mode['page']} ({$elapsed}s)");
            return;
        }

        if (!is_dir($fileDir) && !mkdir($fileDir, 0755, true)) {
            $this->error("Could not create directory: $fileDir");
            return;
        }

        if (file_put_contents($filePath, $content) === false) {
            $this->error("Permission error: could not write to $filePath");
            return;
        }

        $this->success("{$count} row(s) written to {$mode['page']} ({$elapsed}s)");
    }

    /**
     * Recursively scan a directory for lines matching a pattern.
     * Skips the auto-generated/ directory entirely.
     *
     * @param string $baseDir
     * @param string $dir
     * @param string $pattern
     * @param string $ext      Page file extension: '.md' or '.txt'
     * @param array  &$rows
     */
    private function scanDir($baseDir, $dir, $pattern, $ext, &$rows)
    {
        $entries = scandir($dir);
        if ($entries === false) return;

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') continue;

            $fullPath = $dir . '/' . $entry;

            if (is_dir($fullPath)) {
                // Skip auto-generated directory
                $relDir = ltrim(str_replace($baseDir, '', $fullPath), '/\\');
                if ($relDir === self::EXCLUDE_DIR || str_starts_with($relDir, self::EXCLUDE_DIR . '/')) {
                    continue;
                }
                $this->scanDir($baseDir, $fullPath, $pattern, $ext, $rows);
                continue;
            }

            if (!str_ends_with($entry, $ext)) continue;

            $relativePath = ltrim(str_replace($baseDir, '', $fullPath), '/\\');
            $relativePath = str_replace('\\', '/', $relativePath);
            // Strip extension for wiki page ID
            $pageId = preg_replace('/\.(md|txt)$/', '', $relativePath);

            if (in_array($pageId, self::$ignoredPages, true)) continue;

            $lines = file($fullPath, FILE_IGNORE_NEW_LINES);
            if ($lines === false) continue;

            foreach ($lines as $line) {
                if (strpos($line, $pattern) !== false) {
                    $rows[] = ['page' => $pageId, 'line' => trim($line)];
                }
            }
        }
    }
}

// Main
$cli = new AutoGenerateCLI();
$cli->run();
