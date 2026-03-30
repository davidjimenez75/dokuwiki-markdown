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
 *   auto-generated/tags/todo.md     — lines containing #TODO
 *   auto-generated/tags/to-do.md    — lines containing #TO-DO
 *   auto-generated/tags/done.md     — lines containing #DONE
 *   auto-generated/tags/wip.md      — lines containing #WIP
 *   auto-generated/tags/bug.md      — lines containing #BUG
 *   auto-generated/tags/issue.md    — lines containing #ISSUE
 *   auto-generated/tags/goal.md     — lines containing #GOAL
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

    /** @var array Tag modes: name => [patterns, output page] */
    private static $tags = [
        'todo' => [
            'patterns' => ['#TODO'],
            'page'     => 'auto-generated/tags/todo',
            'title'    => '#TODO',
        ],
        'to-do' => [
            'patterns' => ['#TO-DO'],
            'page'     => 'auto-generated/tags/to-do',
            'title'    => '#TO-DO',
        ],
        'done' => [
            'patterns' => ['#DONE'],
            'page'     => 'auto-generated/tags/done',
            'title'    => '#DONE',
        ],
        'wip' => [
            'patterns' => ['#WIP'],
            'page'     => 'auto-generated/tags/wip',
            'title'    => '#WIP',
        ],
        'bug' => [
            'patterns' => ['#BUG'],
            'page'     => 'auto-generated/tags/bug',
            'title'    => '#BUG',
        ],
        'issue' => [
            'patterns' => ['#ISSUE'],
            'page'     => 'auto-generated/tags/issue',
            'title'    => '#ISSUE',
        ],
        'goal' => [
            'patterns' => ['#GOAL'],
            'page'     => 'auto-generated/tags/goal',
            'title'    => '#GOAL',
        ],
    ];

    protected function setup(Options $options)
    {
        $options->setHelp(
            'Auto-generates wiki pages under auto-generated/ based on searches across all pages.' . "\n\n" .
            'Generated pages (tasks):' . "\n" .
            '  auto-generated/tasks/to-do   lines matching [_] and [ ]' . "\n" .
            '  auto-generated/tasks/wip     lines matching [>] and [WIP]' . "\n" .
            '  auto-generated/tasks/done    lines matching [x]' . "\n\n" .
            'Generated pages (tags):' . "\n" .
            '  auto-generated/tags/todo     lines containing #TODO' . "\n" .
            '  auto-generated/tags/to-do    lines containing #TO-DO' . "\n" .
            '  auto-generated/tags/done     lines containing #DONE' . "\n" .
            '  auto-generated/tags/wip      lines containing #WIP' . "\n" .
            '  auto-generated/tags/bug      lines containing #BUG' . "\n" .
            '  auto-generated/tags/issue    lines containing #ISSUE' . "\n" .
            '  auto-generated/tags/goal     lines containing #GOAL' . "\n\n" .
            'Content inside auto-generated/ is always excluded from scanning.' . "\n\n" .
            'Examples:' . "\n" .
            '  php bin/autogenerate.php              # generate all pages' . "\n" .
            '  php bin/autogenerate.php tasks        # generate task pages only' . "\n" .
            '  php bin/autogenerate.php tasks to-do  # generate a single task page' . "\n" .
            '  php bin/autogenerate.php tags         # generate all tag pages' . "\n" .
            '  php bin/autogenerate.php tags bug     # generate a single tag page'
        );

        $options->registerArgument(
            'group',
            'Group to generate: tasks, tags. Omit to generate all groups.',
            false
        );

        $options->registerArgument(
            'name',
            'Specific page within the group (e.g. to-do, wip, done, bug). Omit to generate all in group.',
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
        }

        if ($group === '' || $group === 'tags') {
            $modes = $name ? [$name => self::$tags[$name] ?? null] : self::$tags;
            foreach ($modes as $modeName => $mode) {
                if (!$mode) {
                    $this->error("Unknown tag page \"$modeName\". Use: todo, to-do, done, wip, bug, issue, goal.");
                    continue;
                }
                $start   = microtime(true);
                $rows    = $this->collectRows($pagesDir, $mode['patterns'], $ext, true);
                $elapsed = round(microtime(true) - $start, 3);
                $this->buildPage($pagesDir, $mode, $rows, $elapsed, $dryRun, $ext);
            }
        }

        if ($group !== '' && $group !== 'tasks' && $group !== 'tags') {
            $this->fatal("Unknown group \"$group\". Available: tasks, tags");
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
     * @param bool   $tagMode   Use whole-word regex matching instead of strpos
     * @return array  [ ['page' => 'ns/page', 'line' => '...'], ... ]
     */
    private function collectRows($pagesDir, $patterns, $ext, $tagMode = false)
    {
        $rows = [];
        foreach ($patterns as $pattern) {
            $this->scanDir($pagesDir, $pagesDir, $pattern, $ext, $rows, $tagMode);
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
     * @param bool   $tagMode  Use whole-word regex matching instead of strpos
     */
    private function scanDir($baseDir, $dir, $pattern, $ext, &$rows, $tagMode = false)
    {
        $entries = scandir($dir);
        if ($entries === false) return;

        // Pre-compile regex for tag mode: match #TAG not followed by another tag character
        $regex = $tagMode ? '/' . preg_quote($pattern, '/') . '(?![A-Z0-9_-])/' : null;

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') continue;

            $fullPath = $dir . '/' . $entry;

            if (is_dir($fullPath)) {
                // Skip auto-generated directory
                $relDir = ltrim(str_replace($baseDir, '', $fullPath), '/\\');
                if ($relDir === self::EXCLUDE_DIR || str_starts_with($relDir, self::EXCLUDE_DIR . '/')) {
                    continue;
                }
                $this->scanDir($baseDir, $fullPath, $pattern, $ext, $rows, $tagMode);
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
                $matched = $tagMode
                    ? preg_match($regex, $line)
                    : strpos($line, $pattern) !== false;

                if ($matched) {
                    $rows[] = ['page' => $pageId, 'line' => trim($line)];
                }
            }
        }
    }
}

// Main
$cli = new AutoGenerateCLI();
$cli->run();
