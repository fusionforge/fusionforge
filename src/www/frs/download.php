<?php
/**
 * FRS Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2013, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'frs/include/frs_utils.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';
require_once $gfcommon.'frs/FRSFile.class.php';

/* This script can work in one of the following modes:
 * - file: one specific file (given its file_id)
 * - latestzip: gets a Zip archive containing all files in the latest
 *   release of a given package (given a package_id)
 * - latestfile: the version of a file that's in the
 *   latest release of a given package (given the file name and the
 *   package_id)
 */

function send_file($filename, $filepath, $file_id = NULL, $mode = NULL) {
	if (!file_exists($filepath)) {
		session_redirect404();
	}

	header('Content-disposition: attachment; filename="'.str_replace('"', '', $filename).'"');
	sysdebug_off("Content-type: application/binary");
	$length = filesize($filepath);
	header("Content-length: $length");

	readfile_chunked($filepath);

	if (!$file_id) {
		return;
	}

	if (session_loggedin()) {
		$s =& session_get_user();
		$us=$s->getID();
	} else {
		$us=100;
	}

	$ip = getStringFromServer('REMOTE_ADDR');
	if ($mode != 'latestzip') {
		db_query_params("INSERT INTO frs_dlstats_file (ip_address,file_id,month,day,user_id) VALUES ($1, $2, $3, $4, $5)", array($ip,$file_id,date('Ym'),date('d'),$us));
	} else {
		// here $file_id is a package_id
		$Package = frspackage_get_object($file_id);
		$release = $Package->getNewestRelease();
		$files = $release->getFiles();
		foreach ($files as $fileObject) {
			db_query_params("INSERT INTO frs_dlstats_file (ip_address,file_id,month,day,user_id) VALUES ($1, $2, $3, $4, $5)", array($ip, $fileObject->getID(), date('Ym'), date('d'), $us));
		}
	}
}

$normalized_urlprefix = normalized_urlprefix();
$pathinfo = substr_replace(getStringFromServer('REQUEST_URI'), '', 0, strlen($normalized_urlprefix)-1);
$expl_pathinfo = explode('/', $pathinfo);

$mode = $expl_pathinfo[3];

// .../download.php/123/foo.tar.gz (5.1 backward compatibility)
if (ctype_digit($mode)) {
	$mode = 'file';
	$expl_pathinfo = array_merge(array_slice($expl_pathinfo, 0, 3),
				     array($mode),
				     array_slice($expl_pathinfo, 3));
}

switch ($mode) {
case 'file':
	// .../download.php/file/123/foo.tar.gz
	// 123 -> file_id
	// foo.tar.gz ignored

	$file_id = $expl_pathinfo[4];

	// Allow alternate content-type rendering by hook
	$default_content_type = 'application/binary';

	$script = 'frs_download_file';
	$content_type = util_negociate_alternate_content_types($script, $default_content_type);

	if($content_type != $default_content_type) {
		$hook_params = array();
		$hook_params['accept'] = $content_type;
		$hook_params['group_id'] = $group_id;
		$hook_params['file_id'] = $file_id;
		$hook_params['return'] = '';
		$hook_params['content_type'] = '';
		plugin_hook_by_reference('content_negociated_frs_download_file', $hook_params);
		if($hook_params['content_type'] != ''){
			header('Content-type: '. $hook_params['content_type']);
			echo $hook_params['content'];
		}
		else {
			header('HTTP/1.1 406 Not Acceptable',true,406);
		}
		exit(0);
	}

	$File = frsfile_get_object($file_id);
	if (!$File) {
		session_redirect404();
	}

	$Release = $File->FRSRelease;
	$Package = $Release->FRSPackage;
	$Group = $Package->Group;
	if ($Package->isPublic()) {
		session_require_perm('frs', $Package->Group->getID(), 'read_public');
	} else {
		session_require_perm('frs', $Package->Group->getID(), 'read_private');
	}

	$filename = $File->getName();
	$filepath = forge_get_config('upload_dir').'/'.$Group->getUnixName().'/'.$Package->getFileName().'/'.$Release->getFileName().'/'.$filename;

	send_file ($filename, $filepath, $file_id);

	break;

case 'latestzip':
	// .../download.php/latestzip/123/package-latest.zip
	// 123 -> package_id
	// package-latest.zip ignored
	$package_id = $expl_pathinfo[4];

	$Package = frspackage_get_object($package_id);
	if (!$Package || !$Package->getNewestRelease()) {
		session_redirect404();
	}

	if ($Package->isPublic()) {
		session_require_perm('frs', $Package->Group->getID(), 'read_public');
	} else {
		session_require_perm('frs', $Package->Group->getID(), 'read_private');
	}

	$filename = $Package->getNewestReleaseZipName();
	$filepath = $Package->getNewestReleaseZipPath();
	send_file ($filename, $filepath, $package_id, $mode);

	break;

case 'latestfile':
	// .../download.php/latestfile/123/foo.tar.gz
	// 123 -> package_id
	// foo.tar.gz -> file name
	$package_id = $expl_pathinfo[4];
	$file_name = $expl_pathinfo[5];

	$res = db_query_params ('SELECT f.file_id FROM frs_file f, frs_release r, frs_package p WHERE f.release_id = r.release_id AND r.package_id = p.package_id AND p.package_id = $1 AND f.filename = $2 ORDER BY f.release_id DESC',
				array($package_id, $file_name));

	if (!$res || db_numrows($res) < 1) {
		session_redirect404();
	}

	$row = db_fetch_array($res);
	$file_id = $row['file_id'];
	$File = frsfile_get_object($file_id);

	$Release = $File->FRSRelease;
	$Package = $Release->FRSPackage;
	$Group = $Package->Group;
	if ($Package->isPublic()) {
		session_require_perm('frs', $Package->Group->getID(), 'read_public');
	} else {
		session_require_perm('frs', $Package->Group->getID(), 'read_private');
	}

	$filename = $File->getName();
	$filepath = forge_get_config('upload_dir').'/'.$Group->getUnixName().'/'.$Package->getFileName().'/'.$Release->getFileName().'/'.$filename;

	send_file ($filename, $filepath, $file_id);

	break;

default:
	exit_error(_('Invalid download mode'));
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
