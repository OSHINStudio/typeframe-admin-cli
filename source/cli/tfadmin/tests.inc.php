<?php
/**
 * Download tests for all installed packages.
 */

$dir = scandir(TYPEF_SOURCE_DIR . '/packages');
foreach ($dir as $file) {
	if (substr($file, 0, 1) != '.' && pathinfo($file, PATHINFO_EXTENSION) == 'xml') {
		$package = pathinfo($file, PATHINFO_FILENAME);
		$xml = Pagemill_SimpleXmlElement::LoadFile(TYPEF_SOURCE_DIR . "/packages/{$file}");
		$version = $xml['version'];
		// Ignore packages without tests (required for backwards compatibility)
		$buffer = @file_get_contents(TYPEF_PROVIDER . '/download/' . $package . '-' . $version . '-test.tar.gz');
		if ($buffer) {
			echo "Installing tests for {$package}\n";
			file_put_contents('test.tar.gz', $buffer);
			exec('tar -zxvf test.tar.gz');
			exec('rm test.tar.gz');
		}
	}
}
