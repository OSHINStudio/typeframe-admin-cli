<?php
/**
 * List differences between BaseModel classes and the database schema.
 * This command will only explain the differences. Use dbimport and dbexport
 * to modify the classes and the tables respectively.
 */

function className($name) {
	if (substr($name, 0, strlen(DBI_PREFIX)) == DBI_PREFIX) {
		$name = substr($name, strlen(DBI_PREFIX));
	}
	$cls = '';
	for ($i = 0; $i < strlen($name); $i++) {
		$char = substr($name, $i, 1);
		if ($i == 0) {
			$cls .= strtoupper($char);
		} else if ($char == '_') {
			$i++;
			$char = substr($name, $i, 1);
			$cls .= strtoupper($char);
		} else {
			$cls .= $char;
		}
	}
	return "BaseModel_{$cls}";
}

$diffs = 0;
$models = array();
$files = scandir(TYPEF_SOURCE_DIR . '/classes/BaseModel');
foreach ($files as $file) {
	if (substr($file, 0, 1) != '.') {
		$pathinfo = pathinfo($file);
		if ($pathinfo['extension'] == 'php') {
			$models[] = 'BaseModel_' . $pathinfo['filename'];
		}
	}
}
$tables = array();
$result = Typeframe::Database()->execute('SHOW TABLES');
foreach ($result as $row) {
	$row = $row->getArray();
	//var_dump($row);
	$tableName = array_pop($row);
	$columns = array();
	//$rsCol = Typeframe::Database()->prepare('SHOW COLUMNS IN `' . $tableName . '`');
	//$rsCol->execute();
	$cols = Typeframe::Database()->execute('SHOW COLUMNS IN `' . $tableName . '`');
	//while ($col = $rsCol->fetch_array()) {
	foreach ($cols as $col) {
		$columns[] = $col['Field'];
	}
	$tables[$tableName] = $columns;
}
foreach (array_keys($tables) as $table) {
	$cls = className($table);
	if (!in_array($cls, $models)) {
		echo "The {$table} table does not have a BaseModel class.\n";
		$diffs++;
	} else {
		$mod = new $cls();
		foreach ($tables[$table] as $fld) {
			if (!$mod->field($fld)) {
				echo "The {$cls} BaseModel does not have the {$fld} field from the {$table} table.\n";
				$diffs++;
			}
		}
	}
}
foreach ($models as $cls) {
	$mod = new $cls();
	$tbl = $mod->prefix() . $mod->name();
	if (!in_array($tbl, array_keys($tables))) {
		echo "The {$cls} BaseModel's {$tbl} table does not exist.\n";
		$diffs++;
	} else {
		foreach (array_keys($mod->fields()) as $fld) {
			if (!in_array($fld, $tables[$tbl])) {
				echo "The {$tbl} table does not have the {$fld} field defined in {$cls}.\n";
				$diffs++;
			}
		}
	}
}
echo "{$diffs} difference" . ($diffs != 1 ? 's' : '') . " found.\n";
