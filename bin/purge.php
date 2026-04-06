#!/usr/bin/env php
<?php

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(__DIR__ . '/../') . '/');
define('NOSESSION', 1);
require_once(DOKU_INC . 'inc/init.php');

/**
 * Purges DokuWiki regenerable and disposable data.
 *
 * NEVER touches: data/pages/ nor data/media/
 *
 * Tiers:
 *   --safe    cache, tmp, locks          (zero data loss, fully regenerable)
 *   --full    safe + index, meta, media_meta  (metadata is rebuilt on next visit)
 *   --nuclear full + attic, media_attic  (DESTRUCTIVE: page and media history lost)
 *
 * Add --dry-run to any tier to preview without deleting anything.
 */
class PurgeCLI extends CLI
{
	/** Targets never allowed — protected under any tier */
	private const PROTECTED = ['pages', 'media'];

	/**
	 * Tier definitions: each tier lists its own targets (cumulative via merging in main).
	 * Keys match subdirectory names under data/.
	 */
	private const TIERS = [
		'safe' => [
			'cache'  => 'Rendered HTML cache and parsed page cache',
			'tmp'    => 'Temporary upload and processing files',
			'locks'  => 'Stale edit lock files',
		],
		'full' => [
			'index'       => 'Full-text search index (rebuilt on next visit/indexer run)',
			'meta'        => 'Page metadata: dates, contributors, backlinks',
			'media_meta'  => 'Media metadata: dimensions, EXIF, thumbnails',
		],
		'nuclear' => [
			'attic'       => 'Page revision history (ALL old versions permanently deleted)',
			'media_attic' => 'Media revision history (ALL old media versions permanently deleted)',
		],
	];

	protected function setup(Options $options)
	{
		$options->setHelp(
			'Purges DokuWiki regenerable/disposable data. Never touches data/pages/ or data/media/.' . "\n\n" .
			'Tiers (cumulative — each includes the previous):' . "\n" .
			'  --safe    cache, tmp, locks                      (zero data loss)' . "\n" .
			'  --full    safe + index, meta, media_meta         (metadata rebuilt on next visit)' . "\n" .
			'  --nuclear full + attic, media_attic              (DESTRUCTIVE: history lost)' . "\n\n" .
			'Examples:' . "\n" .
			'  php bin/purge.php --safe              # clean cache/tmp/locks' . "\n" .
			'  php bin/purge.php --full              # clean cache/tmp/locks + index/meta' . "\n" .
			'  php bin/purge.php --nuclear           # everything (history gone)' . "\n" .
			'  php bin/purge.php --safe --dry-run    # preview safe targets' . "\n" .
			'  php bin/purge.php --nuclear --dry-run # preview everything without deleting'
		);

		$options->registerOption('safe',    'Delete cache, tmp, and lock files (zero data loss).', 's');
		$options->registerOption('full',    'safe + search index, page metadata, media metadata.', 'f');
		$options->registerOption('nuclear', 'full + page history (attic) and media history. DESTRUCTIVE.', 'n');
		$options->registerOption('dry-run', 'Show what would be deleted without removing anything.', 'd');
	}

	protected function main(Options $options)
	{
		global $conf;

		$dryRun  = $options->getOpt('dry-run');
		$safe    = $options->getOpt('safe');
		$full    = $options->getOpt('full');
		$nuclear = $options->getOpt('nuclear');

		if (!$safe && !$full && !$nuclear) {
			echo $options->help();
			exit(0);
		}

		// Build cumulative target list
		$targets = [];
		if ($safe || $full || $nuclear) {
			$targets = array_merge($targets, self::TIERS['safe']);
		}
		if ($full || $nuclear) {
			$targets = array_merge($targets, self::TIERS['full']);
		}
		if ($nuclear) {
			$targets = array_merge($targets, self::TIERS['nuclear']);
		}

		$dataDir = rtrim($conf['datadir'] ?? (DOKU_INC . 'data/pages'), '/pages');
		// Resolve actual data/ root (one level up from datadir which points to data/pages)
		$dataDir = dirname(rtrim($conf['datadir'], '/'));

		if ($dryRun) {
			$this->info('DRY RUN — no files will be deleted.');
		}

		if ($nuclear && !$dryRun) {
			$this->warning('NUCLEAR tier: page and media revision history will be permanently deleted.');
		}

		$totalFiles = 0;
		$totalBytes = 0;

		foreach ($targets as $dirName => $description) {
			// Safety check — should never happen given the constants, but belt-and-suspenders
			if (in_array($dirName, self::PROTECTED, true)) {
				$this->error("Refusing to touch protected directory: $dirName");
				continue;
			}

			$dirPath = $dataDir . '/' . $dirName;

			if (!is_dir($dirPath)) {
				$this->info("Skipping (not found): data/$dirName/");
				continue;
			}

			[$files, $bytes] = $this->measureDir($dirPath);
			$human = $this->humanSize($bytes);

			if ($dryRun) {
				$this->success(sprintf(
					'[DRY-RUN] data/%-15s  %5d files  %s  — %s',
					$dirName . '/',
					$files,
					str_pad($human, 8),
					$description
				));
			} else {
				$this->purgeDir($dirPath);
				$this->success(sprintf(
					'Purged    data/%-15s  %5d files  %s  — %s',
					$dirName . '/',
					$files,
					str_pad($human, 8),
					$description
				));
			}

			$totalFiles += $files;
			$totalBytes += $bytes;
		}

		$action = $dryRun ? 'Would free' : 'Freed';
		$this->info(sprintf('%s: %d files, %s', $action, $totalFiles, $this->humanSize($totalBytes)));
	}

	/**
	 * Recursively count files and sum byte sizes in a directory.
	 *
	 * @param string $dir
	 * @return array [int $fileCount, int $byteCount]
	 */
	private function measureDir(string $dir): array
	{
		$files = 0;
		$bytes = 0;

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $item) {
			if ($item->isFile()) {
				$files++;
				$bytes += $item->getSize();
			}
		}

		return [$files, $bytes];
	}

	/**
	 * Recursively delete all contents of a directory, keeping the directory itself.
	 *
	 * @param string $dir
	 */
	private function purgeDir(string $dir): void
	{
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $item) {
			if ($item->isDir()) {
				@rmdir($item->getRealPath());
			} else {
				@unlink($item->getRealPath());
			}
		}
	}

	/**
	 * Format bytes as human-readable string.
	 *
	 * @param int $bytes
	 * @return string
	 */
	private function humanSize(int $bytes): string
	{
		if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
		if ($bytes >= 1048576)    return round($bytes / 1048576, 1)    . ' MB';
		if ($bytes >= 1024)       return round($bytes / 1024, 1)       . ' KB';
		return $bytes . ' B';
	}
}

$cli = new PurgeCLI();
$cli->run();
