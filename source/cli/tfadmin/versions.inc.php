<?php
/**
 * Check versions of installed packages
 */

$dir = scandir(TYPEF_DIR . '/source/packages');
foreach ($dir as $file) {
	if (substr($file, -4) == '.xml') {
		$pathinfo = pathinfo($file);
		$xml = simplexml_load_file(TYPEF_DIR . '/source/packages/' . $file);
		echo str_pad($pathinfo['filename'], 24, '.') . "{$xml['version']}\n";
	}
}
