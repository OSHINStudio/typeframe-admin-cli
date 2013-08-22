<?php
/**
 * Repair problems in a Typeframe installation.
 * This command will configure BaseModel schemas and fix writeable directory
 * permissions.
 */

function fixPermissions($dir) {
    static $ftp = null;
	static $depth = 0;
	$depth++;
    if (is_null($ftp)) {
        $ftp = new Typeframe_File();
    }
	if (!file_exists(TYPEF_DIR . '/files' . $dir)) {
		$ftp->mkdir('/files' . $dir);
	}
	if (substr(sprintf('%o', fileperms(TYPEF_DIR . '/files/' . $dir)), -4) !== '0777') {
		$ftp->chmod(0777, '/files' . $dir);
	}
    $dh = opendir(TYPEF_DIR . '/files' . $dir);
    while (($file = readdir($dh)) !== false) {
        if (substr($file, 0, 1) != '.') {
            if (is_dir(TYPEF_DIR . '/files' . $dir . '/' . $file)) {
                fixPermissions($dir . '/' . $file);
            } else {
				$perm = substr(decoct(fileperms(TYPEF_DIR . '/files/' . $dir . '/' . $file)), 1);
				if ($perm !== '0666' && $perm !== '0777') {
					$ftp->chmod(0666, '/files/' . $dir . '/' . $file);
				}
            }
        }
    }
	$depth--;
	if ($depth == 0) {
		$ftp->close();
		$ftp = null;
	}
}

// Configure the schemas
$files = scandir('source/classes/BaseModel');
foreach($files as $file) {
	if (substr($file, 0, 1) != '.') {
		$pathinfo = pathinfo($file);
		if ($pathinfo['extension'] == 'php') {
			$cls = "BaseModel_{$pathinfo['filename']}";
			$mod = new $cls();
			$src = Dbi_Source::GetModelSource($mod);
			$src->configureSchema($mod);
		}
	}
}

// Fix the file permissions
$files = scandir(TYPEF_SOURCE_DIR . '/packages');
$ftp = new Typeframe_File();
foreach ($files as $file) {
	if (substr($file, 0, 1) != '.');
	$pathinfo = pathinfo($file);
	if ($pathinfo['extension'] == 'xml') {
		$xml = Pagemill_SimpleXmlElement::LoadFile(TYPEF_SOURCE_DIR . "/packages/{$file}");
		if (!$xml) {
			die("Could not parse {$file}\n");
		}
		foreach ($xml->updir as $u) {
			$dir = trim($u);
			$parts = explode('/', $dir);
			$thisDir = '';
			foreach ($parts as $part) {
				if ($part) {
					$thisDir .= "/{$part}";
					if (!file_exists(TYPEF_DIR . $thisDir)) {
						$ftp->mkdir($thisDir);
						$ftp->chmod(0777, $thisDir);
					}
				}
			}
		}
	}
}
fixPermissions('/public');
fixPermissions('/secure');
fixPermissions('/cache');
