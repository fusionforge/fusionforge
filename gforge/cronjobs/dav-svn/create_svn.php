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

//	Where is the SVN repository?
$svn='/var/svn';

//	Whether to separate directories by first letter like /m/mygroup /a/apple
$first_letter = false;

/*
	This script create the gforge dav/svn/docman repositories
*/

echo "Creating Groups at ". $svn."\n";

$res = db_query("SELECT is_public,enable_anonscm,unix_group_name 
	FROM groups WHERE status != 'P';");

if (!$res) {
	echo "Error!\n";
}

system("[ ! -d ".$svn." ] && mkdir $svn"); 

while ( $row =& db_fetch_array($res) ) {
	echo "Name:".$row["unix_group_name"]." \n";
	if ($first_letter) {
		//
		//	Create the docman repository for versioning of docs
		//
		system ("[ ! -d $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]." ] && mkdir -p $svn/".$row["unix_group_name"][0]."/ && $svn_path/svnadmin create $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
	} else {
		system ("[ ! -d $svn/".$row["unix_group_name"]." ] &&  $svn_path/svnadmin create $svn/".$row["unix_group_name"]);
	}
}

system("chown $file_owner -R $svn");
system("cd $svn/ ; find -type d -exec chmod 700 {} \;");
system("cd $svn/ ; find -type f -exec chmod 600 {} \;");

?>
