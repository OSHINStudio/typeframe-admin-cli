<?php
/**
 * Check for customized files in packages
 * Syntax: compare-local [package]
 */

if (empty($argv[2])) {
	echo "No package specified.\n";
	exit;
}

$xml = Pagemill_SimpleXmlElement::LoadFile(TYPEF_SOURCE_DIR . '/packages/' . $argv[2] . '.xml');
foreach ($xml->file as $file) {
	if (file_exists(TYPEF_DIR . "/{$file}")) {
		$md5 = md5_file(TYPEF_DIR . "/{$file}");
		if ($md5 != "{$file['md5']}") {
			echo "Local version of {$file} is different.\n";
		}
	} else {
		echo "Local version of {$file} does not exist.\n";
	}
}
