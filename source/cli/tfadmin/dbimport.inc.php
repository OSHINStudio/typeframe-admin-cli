<?php
/**
 * Generate BaseModel classes from the database.
 */

// Legacy database access
$db = Typeframe::Database();

// First make sure there won't be any name conflicts.
$named = array();
$tables = Typeframe::Database()->execute('SHOW TABLES');
foreach ($tables as $row) {
	$array = $row->getArray();
	$tableName = array_pop($array);
	if (substr($tableName, 0, strlen(DBI_PREFIX)) == DBI_PREFIX) {
		$shortName = substr($tableName, strlen(DBI_PREFIX));
	} else {
		$shortName = $tableName;
	}
	$className = className($shortName);
	if (isset($named[$className])) {
		die("WARNING: The '{$tableName}' and '{$named[$className]}' tables both resolve to a class named 'BaseModel_{$className}'\n");
	}
	$named[$className] = $tableName;
}

function className($name) {
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
	return $cls;
}

function defineColumn($column) {
	$def = array();
	$def['name'] = $column['Field'];
	$type = $column['Type'];
	if (!preg_match('/([a-z]*)/i', $type, $matches)) {
		throw new Exception("Unable to parse column type {$type}");
	}
	$def['type'] = $matches[1];
	if (preg_match('/\(([\w\W\s\S]*)\)/i', $type, $matches)) {
		$json = str_replace("''", '""', $matches[1]);
		$json = '[' . preg_replace("/([^\\\])'/", '$1"', ' ' . $json) . ' ]';
		$arguments = json_decode($json);
		if (preg_match('/unsigned$/i', $column['Type'])) {
			$arguments[] = 'unsigned';
		}
		if ($column['Extra']) {
			$arguments[] = $column['Extra'];
		}
		$def['arguments'] = $arguments;
		/*if ($def['type'] == 'enum') {
			echo json_encode(array(1,2,'three')) . "\n";
			echo $matches[1] . "\n";
			echo $json . "\n";
			print_r($arguments);
			exit;
		}*/
	}
	if ($column['Null'] == 'YES') {
		$def['allowNull'] = false;
	}
	$def['defaultValue'] = $column['Default'];
	return $def;
}
if (!empty($argv[2])) {
	$tables = Typeframe::Database()->execute('SHOW TABLES LIKE \'' . mysql_real_escape_string($argv[2]) . '\'');
} else {
	$tables = Typeframe::Database()->execute('SHOW TABLES');
}
foreach ($tables as $rowTable) {
	$autoIncrement = false;
	$array = $rowTable->getArray();
	$tableName = array_pop($array);
	if (substr($tableName, 0, strlen(DBI_PREFIX)) == DBI_PREFIX) {
		$shortName = substr($tableName, strlen(DBI_PREFIX));
		$prefix = 'DBI_PREFIX';
	} else {
		$shortName = $tableName;
		$prefix = "''";
	}
	$className = className($shortName);
	$columns = array();
	$table = array('name' => $tableName);
	$rsColumns = Typeframe::Database()->execute('SHOW COLUMNS IN ' . $tableName);
	foreach ($rsColumns as $column) {
		//$columns[] = $column;
		$columns[] = defineColumn($column);
		continue;
		if ($column['Extra'] == 'auto_increment') {
			$autoIncrement = true;
		}
		$xlate = array();
		$xlate['name'] = $column['Field'];
		/*if (substr($column['Type'], 0, 3) == 'int') {
			// integer
		} else if (substr($column['Type'], 0, 7) == 'varchar') {
			// varchar
		}*/
		$xlate['type'] = addslashes($column['Type']);
		$xlate['allownull'] = ($column['Null'] == 'NO' ? false : true);
		$xlate['defaultvalue'] = $column['Default'];
		$columns[] = $xlate;
	}
	$rsIndexes = Typeframe::Database()->execute('SHOW INDEX IN ' . $tableName);
	$indexes = array();
	foreach ($rsIndexes as $index) {
		$name = strtolower($index['Key_name']);
		if (!isset($indexes[$name])) {
			$indexes[$name] = array();
			$indexes[$name]['name'] = $name;
			$indexes[$name]['unique'] = ($index['Non_unique'] == 0 ? true : false);
			$indexes[$name]['columns'] = array();
		}
		$indexes[$name]['columns'][] = array('name' => $index['Column_name']);
	}
	$classMill = new Pagemill();
	$classMill->setVariable('class', $className);
	$classMill->setVariable('table', $shortName);
	$classMill->setVariable('prefix', $prefix);
	$classMill->setVariable('columns', $columns);
	$classMill->setVariable('indexes', array_values($indexes));
	//$source = html_entity_decode($classMill->writeFile(dirname(__FILE__) . '/model.tmpl'));
	$source = $classMill->writeFile(dirname(__FILE__) . '/model.tmpl', true);
	//$source = html_entity_decode($stream->peek(), ENT_QUOTES | ENT_XML1);
	$source = str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $source);
	echo "Writing {$className}.php for {$tableName}...\n";
	$fh = fopen(TYPEF_SOURCE_DIR . '/classes/BaseModel/' . $className . '.php', 'w');
	fwrite($fh, $source . "\n");
	fclose($fh);
}
