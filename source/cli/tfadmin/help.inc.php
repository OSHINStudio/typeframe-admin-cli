<?php
/**
 * Get a list of tfadmin commands.
 */

if (!empty($argv[2])) {
	$file = TYPEF_SOURCE_DIR . "/cli/tfadmin/{$argv[2]}.inc.php";
	if (file_exists($file)) {
		$code = file_get_contents($file);
		preg_match('/\/\*\*([\w\W\s\S]*?)\*\//', $code, $matches);
		$lines = explode("\n", $matches[1]);
		foreach ($lines as $line) {
			$line = trim(substr($line, strpos($line, '*') + 1));
			if ($line) {
				echo "{$line}\n";
			}
		}
	} else {
		echo "The '{$argv[2]}' command does not exist.\nUse '{$cmd} help' to see a list of commands.\n";
	}
} else {
	echo "Syntax: {$cmd} [command] [arguments]\n";
	echo "Available commands: \n";
	$files = scandir(TYPEF_SOURCE_DIR . '/cli/tfadmin');
	foreach ($files as $file) {
		if (substr($file, 0, 1) != '.' && substr($file, -8) == '.inc.php') {
			$command = substr($file, 0, -8);
			$code = file_get_contents(TYPEF_SOURCE_DIR . '/cli/tfadmin/' . $file);
			$description = '';
			if (preg_match('/\/\*\*[\w\W]*?\*([\w\W\s\S]*?)\*/', $code, $matches)) {
				if (trim($matches[1])) {
					$description = ' - ' . trim($matches[1]);
				}
			}
			echo "  {$command}{$description}\n";
		}
	}
	echo "For more detail about a command, try '{$cmd} help [command]'.\n";
}
