<?php
/**
 * Export models to the database.
 * Syntax: dbexport [model] [model...]
 * The model arguments can be the names of any valid Dbi_Model (or Dbi_Schema).
 * If no model arguments are specified, all BaseModel classes will be
 * exported.
 */

$classes = array();
$args = array_slice($argv, 2);
if (count($args)) {
	$classes = array();
	foreach ($args as $cls) {
		if ( (class_exists($cls)) && (is_subclass_of($cls, 'Dbi_Schema')) ) {
			$classes[] = $cls;
		} else {
			die("ERROR: The {$cls} does not exist or is not a schema/model class.\n");
		}
	}
} else {
	$files = scandir(TYPEF_SOURCE_DIR . '/classes/BaseModel');
	if (substr($file, 0, 1) != '.') {
		foreach ($files as $file) {
			if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
				$cls = 'BaseModel_' . pathinfo($file, PATHINFO_FILENAME);
				if ( (class_exists($cls)) && (is_subclass_of($cls, 'Dbi_Schema')) ) {
					$classes[] = $cls;
				} else {
					die("ERROR: The {$cls} does not exist or is not a schema/model class.\n");
				}
			}
		}
	}
}
foreach ($classes as $cls) {
	echo "Exporting {$cls}...\n";
	$mod = new $cls();
	$src = Dbi_Source::GetModelSource($mod);
	$src->configureSchema($mod);
}
