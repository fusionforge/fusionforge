#! /usr/bin/php4 -f
<?php
/**
 * create_docman.php 
 *
 * Francisco Gimeno <kikov@fco-gimeno.com>
 *
 * @version   $Id
 */

require ('squal_pre.php');

//	/path/to/svn/bin/
$svn_path='/usr/local/svn/bin';

//	Owner of files - apache
$file_owner='nobody:nogroup';

//	Where is the docman SVN repository?
$docman='/var/docman';

//	Whether to separate directories by first letter like /m/mygroup /a/apple
$first_letter = false;

/*
	This script create the gforge dav/svn/docman repositories
*/

echo "Creating Groups at ". $docman."\n";

$res = db_query("SELECT is_public,enable_anoncvs,unix_group_name 
	FROM groups WHERE status != 'P';");

if (!$res) {
	echo "Error!\n";
}

system("[ ! -d ".$docman." ] && mkdir $docman"); 

while ( $row =& db_fetch_array($res) ) {
	echo "Name:".$row["unix_group_name"]." \n";
	if ($first_letter) {
		//
		//	Create the docman repository for versioning of docs
		//
		system ("[ ! -d $docman/".$row["unix_group_name"][0]."/".$row["unix_group_name"]." ] && mkdir -p $docman/".$row["unix_group_name"][0]."/ && $svn_path/svnadmin create $docman/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
	} else {
		system ("[ ! -d $docman/".$row["unix_group_name"]." ] &&  $svn_path/svnadmin create $docman/".$row["unix_group_name"]);
	}
}

system("chown $file_owner -R $docman");
system("cd $docman/ ; find -type d -exec chmod 700 {} \;");
system("cd $docman/ ; find -type f -exec chmod 600 {} \;");

?>
