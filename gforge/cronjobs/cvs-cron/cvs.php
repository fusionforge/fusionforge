#!/usr/bin/php
<?php

require ('squal_pre.php');

$maincvsroot = "/cvsroot/";

//the directory exists
if(is_dir($maincvsroot)) {
	addProjectRepositories();
} else {
	if(is_file($maincvsroot)) {
		print "$maincvsroot exists but is a file\n";
		exit;
	} else {
		if (mkdir($maincvsroot)) {
			//need to update group permissions using chmod
			addProjectRepositories();
		} else {
			print "unable to make $maincvsroot directory\n";
			exit;
		}	
	}
}

function addProjectRepositories() {
	$res = db_query("select unix_group_name from groups where status='A'");
	$activegroups = array();
	
	for($i = 0; $i < db_numrows($res); $i++) {
		$activegroups[] = db_result($res,$i,'unix_group_name');
	}

	global $maincvsroot;
	
	$dir = opendir($maincvsroot);
	$dirlisting = array();

	while (($file = readdir($dir)) !== false) {
		$dirlisting[] = $file;
	}  
	
	closedir($dir);

	for($i = 0; $i < count($activegroups); $i++) {
		for($k = 0; $k < count($dirlisting); $k++) {
			if($activegroups[$i] == $dirlisting[$k]) {
				continue 2;
			}	
		}

		mkdir($maincvsroot . $activegroups[$i]);
		//chmod it to something
	}
}

?>
