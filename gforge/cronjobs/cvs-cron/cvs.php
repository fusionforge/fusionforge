#! /usr/bin/php4 -f
<?php

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$maincvsroot = "/cvsroot/";

//the directory exists
if(is_dir($maincvsroot)) {
	addProjectRepositories();
} else {
	if(is_file($maincvsroot)) {
		$err .= "$maincvsroot exists but is a file\n";
		exit;
	} else {
		if (mkdir($maincvsroot)) {
			//need to update group permissions using chmod
			addProjectRepositories();
		} else {
			$err .= "unable to make $maincvsroot directory\n";
			exit;
		}	
	}
}

function writeFile($filePath, $content) {
	$file = fopen($filePath, 'a');
	flock($file, LOCK_EX);
	ftruncate($file, 0);
	rewind($file);
	if(!empty($content)) {
		fwrite($file, $content);
	}
	flock($file, LOCK_UN);
	fclose($file);
}

function addProjectRepositories() {
	global $maincvsroot;

	$res = db_query("select group_id,unix_group_name,enable_anonscm,enable_pserver from groups where status='A' AND group_id NOT IN (2,3,4)");
	
	for($i = 0; $i < db_numrows($res); $i++) {
		/*
			Simply call cvscreate.sh
		*/
		$repositoryPath = $maincvsroot.db_result($res,$i,'unix_group_name');
		if (is_dir($repositoryPath)) {
			$writersContent = '';
			$readersContent = '';
			$passwdContent = '';
			if(db_result($res,$i,'enable_anonscm')) {
				$repositoryMode = 02775;
				if (db_result($res,$i,'enable_pserver')) {
					$readersContent = 'anonymous';
					$passwdContent = 'anonymous:8Z8wlZezt48mY';
				}
			} else {
				$repositoryMode = 02770;
			}
			chmod($repositoryPath, $repositoryMode);
			writeFile($repositoryPath.'/CVSROOT/writers', $writersContent);
			writeFile($repositoryPath.'/CVSROOT/readers', $readersContent);
			writeFile($repositoryPath.'/CVSROOT/passwd', $passwdContent);
		} elseif (is_file($repositoryPath)) {
			$err .= $repositoryPath.' already exists as a file';
		} else {
			system('./cvscreate.sh '.db_result($res,$i,'unix_group_name').' '.(db_result($res,$i,'group_id')+50000).' '.db_result($res,$i,'enable_anonscm').' '.db_result($res,$i,'enable_pserver'));
		}
	}
}

cron_entry(13,$err);

?>
