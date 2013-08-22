<?php
/**
 * Download a package from the repository.
 * Syntax: download [package] [--force]
 * The optional --force argument will overwrite customized files.
 */

if (empty($argv[2])) {
	echo "No package specified.\n";
	exit;
}
$force = false;
if (in_array('--force', $argv)) {
	$force = true;
}
echo "Downloading '${argv[2]}' package from " . TYPEF_PROVIDER . "\n";
Install::Download(TYPEF_PROVIDER . '/download/newest?package=' . $argv[2], $force);
