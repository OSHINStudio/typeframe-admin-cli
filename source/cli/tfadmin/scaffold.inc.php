<?php

/**
 * Create Scaffolding
 * This can only be run after generating basemodels 
 */

echo "
  _____                __                     
 |_   _|  _ _ __  ___ / _|_ _ __ _ _ __  ___  
   | || || | '_ \/ -_)  _| '_/ _` | '  \/ -_) 
   |_| \_, | .__/\___|_| |_| \__,_|_|_|_\___| 
  ___  |__/|_|  __  __     _    _ _           
 / __| __ __ _ / _|/ _|___| |__| (_)_ _  __ _ 
 \__ \/ _/ _` |  _|  _/ _ \ / _` | | ' \/ _` |
 |___/\__\__,_|_| |_| \___/_\__,_|_|_||_\__, |
                                        |___/ 
\n\n";

//$model = new Model_User();
//var_dump($model->fields()); die;
$valid = false;
while(!$valid){
	echo "Application Name: ";
	$applicationname = trim(fgets(STDIN));	
	
	if(!preg_match('/[^a-z]*$/', $applicationname)){
		echo "Application name may only include lower-case english letters and no spaces\n";
	} else {
		$valid = true;
	}

	if(is_dir(TYPEF_SOURCE_DIR . "/controllers/admin/{$applicationname}")){
		echo "An application already exists with that name.  Overwrite? (y/n): ";
		$overwritevalid = false;
		while(!$overwritevalid){
			$response = trim(fgets(STDIN));
			if($response == "n"){
				$valid = false;
				$overwritevalid = true;
			} elseif($response == "y"){
				$overwritevalid = true;
			} 
		}
	}
}

$valid = false;
while (!$valid) {
	echo "Model Name: ";
	$modelname = trim(fgets(STDIN));
	if(class_exists($modelname)){
		$valid = true;
	} else {
		echo "Model doesn't exist\n";
	}
}

echo "Nickname for model (ie News Post, Gallery Album): ";
$modelnick = trim(fgets(STDIN));


$model = new $modelname();

//required?
$fields = array();
foreach($model->fields() as $fieldname => $field){
	$valid = false;
	while(!$valid){
		echo "Is {$fieldname} required?(y/n): ";
		$required = trim(fgets(STDIN));
		if($required == 'y'){
			$fields[$fieldname]['required'] = true;
			$valid = true;
		} elseif ($required == 'n'){
			$fields[$fieldname]['required'] = false;
			$valid = true;
		} 
	}
}

foreach($model->fields() as $fieldname => $field){
	if($field->type() == 'int'){
		foreach($field->arguments() as $a){
			if($a == 'auto_increment'){
				$primarykey = $fieldname;
				$fields[$fieldname]['type'] = 'hidden';
			}
		}
		if(!isset($fields[$fieldname]['type'])){
			$fields[$fieldname]['type'] = 'text';
		}
	} elseif($field->type() == 'datetime'){
		$fields[$fieldname]['type'] = 'calendar';
	} elseif($field->type() == 'text'){
		$fields[$fieldname]['type'] = 'textarea';
	} else {
		$fields[$fieldname]['type'] = 'text';
	}
}

if(!isset($primarykey)){
	echo "The model you selected has no primary key and this scaffolding requires a primary key to work.  Please start over\n"; die;
}

$innerjoins = $model->components();
foreach($innerjoins['innerJoins'] as $innerjoin){
	$linked = $innerjoin['model'];
	$foreignkey = $linked->index('primary');
	$linkedmodelname = get_class($linked);
	
	if(count($foreignkey['fields']) > 1){
		echo "{$linkedmodelname} has more than one field for primary key, and this scaffolding currently doesn't support multiple-field primary keys.\n";
	} else {
		$valid = false;
		while(!$valid){
			$foreignkey = $foreignkey['fields'][0];
			echo "Add linked {$linkedmodelname} with foreign key ({$foreignkey}) to fields? (y/n): ";

			$response = trim(fgets(STDIN));
			if($response == 'y'){
				$fields[$foreignkey]['required'] = true;
				$fields[$foreignkey]['type'] = 'model';
				$fields[$foreignkey]['model'] = $linkedmodelname;
				
				echo "Select model label (the value that shows up in the select drop-down):\n";
				$i = 0;
				foreach($linked->fields() as $fieldname => $field){
					echo "[{$i}] {$fieldname}\n";
					++$i;
				}
				$validlabel=false;
				while(!$validlabel){
					echo 'Select model label:';
					$label = trim(fgets(STDIN));
					if(is_numeric($label) && ($label >= 0 &&   $label <  count($linked->fields()))){
						$linkedmodelfields = array_keys($linked->fields());
						$fields[$foreignkey]['label'] = $linkedmodelfields[$label];
						$validlabel = true;
					}
				}
				
				echo "Nickname for {$linkedmodelname} (blank defaults to {$foreignkey}): ";
				$modelnickname = trim(fgets(STDIN));
				if($modelnickname == ''){
					$fields[$foreignkey]['nickname'] = $foreignkey;
				} else {
					$fields[$foreignkey]['nickname'] = $modelnickname;
				}
				$valid = true;
			} elseif ($response == 'n') {
				$valid = true;
			}
		}
	}
}

echo "\nPreparing to create scaffolding with the following data:\n\n";
echo "Application Name: {$applicationname}\n";
echo "Model: {$modelname}\n";
echo "Model Primary Key: {$primarykey}\n";
echo "Model Nickname: {$modelnick}\n";
echo "Fields: \n";
foreach($fields as $key => $field){
	echo "	{$key}:\n";
	echo "		* Required: {$field['required']}\n";
	echo "		* Type: {$field['type']}\n";
}

$valid = false;
while(!$valid){
	echo "\nCreate scaffolding? (y/n)";
	$letsdothis = trim(fgets(STDIN));
	if($letsdothis == 'n'){
		echo "Alright... :( bye bye\n";
		die;
	} elseif($letsdothis == 'y'){
		$valid = true;
	}
}

/*
$modelnick = "Test Model";
$fields = array();
$fields['id'] = array('required' => 'false', 'type' => 'hidden');
$fields['name'] = array('required' => 'false', 'type' => 'text');
$fields['value'] = array('required' => 'false', 'type' => 'textarea');
$fields['idate'] = array('required' => 'false', 'type' => 'calendar');
*/

$pm = new Pagemill();
$pm->setVariable('fields', $fields);
$pm->setVariable('model', $modelname);
$pm->setVariable('primarykey', $primarykey);
$pm->setVariable('applicationname', $applicationname);
$pm->setVariable('modelnick', $modelnick);

if(!is_dir(TYPEF_SOURCE_DIR . "/controllers/admin/{$applicationname}/")){
	mkdir(TYPEF_SOURCE_DIR . "/controllers/admin/{$applicationname}/");
}
if(!is_dir(TYPEF_SOURCE_DIR . "/templates/admin/{$applicationname}/")){
	mkdir(TYPEF_SOURCE_DIR . "/templates/admin/{$applicationname}/");
}

file_put_contents(TYPEF_SOURCE_DIR . "/controllers/admin/{$applicationname}/add.php", str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $pm->writeFile(__DIR__ . '/scaffold/controllers/admin/add.tpl', true)));
file_put_contents(TYPEF_SOURCE_DIR . "/controllers/admin/{$applicationname}/edit.php", str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $pm->writeFile(__DIR__ . '/scaffold/controllers/admin/edit.tpl', true)));
file_put_contents(TYPEF_SOURCE_DIR . "/controllers/admin/{$applicationname}/delete.php", str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $pm->writeFile(__DIR__ . '/scaffold/controllers/admin/delete.tpl', true)));
file_put_contents(TYPEF_SOURCE_DIR . "/controllers/admin/{$applicationname}/index.php", str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $pm->writeFile(__DIR__ . '/scaffold/controllers/admin/index.tpl', true)));
file_put_contents(TYPEF_SOURCE_DIR . "/templates/admin/{$applicationname}/add.html", str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $pm->writeFile(__DIR__ . '/scaffold/templates/admin/add.html', true)));
file_put_contents(TYPEF_SOURCE_DIR . "/templates/admin/{$applicationname}/update.inc.html", str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $pm->writeFile(__DIR__ . '/scaffold/templates/admin/update.inc.html', true)));
file_put_contents(TYPEF_SOURCE_DIR . "/templates/admin/{$applicationname}/index.html", str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $pm->writeFile(__DIR__ . '/scaffold/templates/admin/index.html', true)));
file_put_contents(TYPEF_SOURCE_DIR . "/templates/admin/{$applicationname}/edit.html", str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $pm->writeFile(__DIR__ . '/scaffold/templates/admin/edit.html', true)));
file_put_contents(TYPEF_SOURCE_DIR . "/registry/{$applicationname}.reg.xml", str_replace(array('&lt;', '&gt;', '&quot;', '&apos;'), array('<', '>', '"', "'"), $pm->writeFile(__DIR__ . '/scaffold/registry/registry.xml', true)));

Typeframe::Registry()->purgeRegistryCache();