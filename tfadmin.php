#!/usr/bin/env php
<?php
/**
 * tfadmin.php - command-line interface for Typeframe administration
 *
 * Syntax: tfadmin.php [command] [arguments...]
 *
 * Commands are implemented through scripts in source/cli/tfadmin, where the
 * name of the script is [command].inc.php. Commands can only contain letters.
 */

if(!isset($_SERVER['SHELL'])){
	header('Location: admin/tools');
	die('<a href="admin/tools">Please click here to go to the tools administration</a>');
}

require_once('typeframe.config.php');
require_once(TYPEF_SOURCE_DIR . '/import.php');

$cmd = basename($argv[0]);

if (empty($argv[1])) {
	die("Enter '{$cmd} help' for a list of commands.\n");
}
if (preg_match('/[^a-z\-]/i', $argv[1])) {
	die("Illegal command.\n");
}

if (file_exists(TYPEF_SOURCE_DIR . "/cli/tfadmin/{$argv[1]}.inc.php")) {
	include(TYPEF_SOURCE_DIR . "/cli/tfadmin/{$argv[1]}.inc.php");
} else {
	die("Command '{$argv[1]}' not recognized.\nUse '{$cmd} help' to see a list of commands.\n");
}
