<?php
//HOWTO:
/*
php import_svn.php OLD_PROJECT_NAME NEW_PROJECT_NAME
OLD_PROJECT_NAME : Name of the project when it was exported (should be the name of the archive)
NEW_PROJECT_NAME : Name of the project where the svn should be imported
*/


$oldpjctname = $argv[1];
$newpjctname = $argv[2];

if (isset($oldpjctname) && isset($newpjctname)){
	$svnpath = "/svnroot/".$newpjctname;
	$dumppath = "/tmp/".$oldpjctname."/SCM/SVN/".$oldpjctname.".svndump";

	$shellstring = "svnadmin load ".$svnpath." < ".$dumppath." 1>/dev/null 2>/dev/null";
	
	shell_exec($shellstring);

}
?>
