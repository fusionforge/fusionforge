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
	global $maincvsroot;

	$res = db_query("select group_id,unix_group_name from groups where status='A'");
	
	for($i = 0; $i < db_numrows($res); $i++) {
		/*
			Simply call cvscreate.sh
		*/
		if (is_dir("$maincvsroot/".db_result($res,$i,'unix_group_name'))) {

			//already exists

		} else(is_file("$maincvsroot/".db_result($res,$i,'unix_group_name'))) {

			echo "$maincvsroot/".db_result($res,$i,'unix_group_name')." Already Exists As A File";

		} else {

			system("cvscreate.sh ".db_result($res,$i,'unix_group_name')." ".(db_result($res,$i,'group_id')+50000));

		}
	}
}

?>
