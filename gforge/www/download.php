<?php
/**
  *
  * Fetch a multimedia data from database
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

$no_gz_buffer=true;

require_once('squal_pre.php');

$arr=explode('/',$REQUEST_URI);
$file_id=$arr[2];

$res=db_query("SELECT frs_file.filename,frs_file.file_id,groups.unix_group_name
	FROM frs_package,frs_release,frs_file,groups
	WHERE frs_release.release_id=frs_file.release_id
	AND groups.group_id=frs_package.group_id
	AND frs_release.package_id=frs_package.package_id
	AND frs_file.file_id='$file_id'");

if (db_numrows($res) < 1) {
	Header("Status: 404");
	exit;
}

$group_name=db_result($res,0,'unix_group_name');
$filename=db_result($res,0,'filename');
$release_id=db_result($res,0,'release_id');

/*
echo $group_name.'|'.$filename.'|'.$sys_upload_dir.$group_name.'/'.$filename;
if (file_exists($sys_upload_dir.$group_name.'/'.$filename)) {
	echo '<br />file exists';
	passthru($sys_upload_dir.$group_name.'/'.$filename);
}
*/
if (file_exists($sys_upload_dir.$group_name.'/'.$filename)) {
	Header("Content-disposition: filename=".$filename);
	Header("Content-type: application/binary");
	$length = filesize($sys_upload_dir.$group_name.'/'.$filename);
	Header("Content-length: $length");

	readfile($sys_upload_dir.$group_name.'/'.$filename);

	if (session_loggedin()) {
		$s =& session_get_user();
		$us=$s->getID();
	} else {
		$us=100;
	}

	$res=db_query("INSERT INTO frs_dlstats_file (ip_address,file_id,month,day,user_id) 
		VALUES ('$REMOTE_ADDR','$file_id','".date('Ym')."','".date('d')."','$us')");
} else {
	Header("Status: 404");
}

?>
