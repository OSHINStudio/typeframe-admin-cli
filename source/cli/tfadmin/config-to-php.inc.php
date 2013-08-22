<?php
/**
 * Export config names and values to PHP scripts.
 * This command will create source/scripts/define.d/[package name].php for each
 * package.
 */

$dir = scandir(TYPEF_SOURCE_DIR . '/registry');
foreach ($dir as $file) {
	if (substr($file, 0, 1) !== '.' && is_file(TYPEF_SOURCE_DIR . '/registry/' . $file)) {
		$defines = array();
		$xml = simplexml_load_file(TYPEF_SOURCE_DIR . '/registry/' . $file);
		$configs = $xml->xpath('//config');
		foreach ($configs as $config) {
			$items = $config->xpath('item');
			foreach ($items as $item) {
				if ($item['name']) {
					$defines[] = array(
						'name' => (string)$item['name'],
						'caption' => (string)$item['caption'],
						'default' => (string)$item['default']
					);
				}
			}
		}
		$package = substr($file, 0, -8);
		echo "Writing {$package}.php...\n";
		$pm = new Pagemill();
		$pm->setVariable('defines', $defines);
		$source = $pm->writeFile(TYPEF_SOURCE_DIR . '/cli/tfadmin/define.tpl', true);
		$source = str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $source);
		file_put_contents(TYPEF_SOURCE_DIR . '/scripts/define.d/' . $package . '.php', $source);
	}
}
