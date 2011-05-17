<?php
/**
 * FRS Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

$no_gz_buffer=true;

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';
require_once $gfcommon.'frs/FRSFile.class.php';

$arr=explode('/',getStringFromServer('REQUEST_URI'));
$file_id=$arr[3];

$res=db_query_params ('SELECT frs_file.filename,frs_package.is_public,frs_package.package_id,
	frs_file.file_id,groups.unix_group_name,groups.group_id,frs_release.release_id
	FROM frs_package,frs_release,frs_file,groups
	WHERE frs_release.release_id=frs_file.release_id
	AND groups.group_id=frs_package.group_id
	AND frs_release.package_id=frs_package.package_id
	AND frs_file.file_id=$1',
			array($file_id));

if (db_numrows($res) < 1) {
	header("HTTP/1.0 404 Not Found");
	require_once $gfwww.'404.php';
	exit;
}

$group_id = db_result($res,0,'group_id');
$package_id = db_result($res,0,'package_id');
$release_id = db_result($res,0,'release_id');

$Group =& group_get_object($group_id);
if (!$Group || !is_object($Group) || $Group->isError()) {
	exit_no_group();
}

$Package = new FRSPackage($Group,$package_id);
if (!$Package || !is_object($Package)) {
	exit_error(_('Error Getting Package'),'frs');
} else if ($Package->isError()) {
	exit_error($Package->getErrorMessage(),'frs');
}
$is_public = $Package->isPublic();

$Release = new FRSRelease($Package,$release_id);
if (!$Release || !is_object($Release) || $Release->isError()) {
	exit_error(_('Error Getting Release'),'frs');
}

$File = new FRSFile($Release,$file_id);
if (!$File || !is_object($File) || $File->isError()) {
	exit_error(_('Error Getting File'),'frs');
}
$filename = $File->getName();


//  Members of projects can see all packages
//  Non-members can only see public packages
if(!$is_public) {
	if (!session_loggedin() || !user_ismember($group_id)) {
		exit_permission_denied();
	}
}
if ($GLOBALS['sys_block_anonymous_downloads']) {
	if (!session_loggedin()) {
		exit_permission_denied();
	}
}

$filepath=forge_get_config('upload_dir').'/'.$Group->getUnixName().'/'.$Package->getFileName().'/'.$Release->getFileName().'/'.$File->getName();
if (file_exists($filepath)) {
	Header('Content-disposition: attachment; filename="'.str_replace('"', '', $filename).'"');
	Header("Content-type: application/binary");
	$length = filesize($filepath);
	Header("Content-length: $length");

	readfile_chunked($filepath);

	if (session_loggedin()) {
		$s =& session_get_user();
		$us=$s->getID();
	} else {
		$us=100;
	}

	$ip=getStringFromServer('REMOTE_ADDR');
	$res=db_query_params("INSERT INTO frs_dlstats_file (ip_address,file_id,month,day,user_id) 
		VALUES ($1, $2, $3, $4, $5)", array($ip,$file_id,date('Ym'),date('d'),$us));
} else {
	header("HTTP/1.0 404 Not Found");
	require_once $gfwww.'404.php';
}

?>
