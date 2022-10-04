<?php
/**
 * FRS HTML Utilities
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2013-2014, Franck Villaume - TrivialDev
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

/**
 * frs_show_status_popup - pop-up select box of supported frs statuses
 *
 * @param	string	$name		default value 'status_id'
 * @param	string	$checked_val	default value 'xzxz'
 * @return	string	html code
 */
function frs_show_status_popup($name = 'status_id', $checked_val = 'xzxz') {
	global $FRS_STATUS_RES;
	if (!isset($FRS_STATUS_RES)) {
		$FRS_STATUS_RES = db_query_params('SELECT * FROM frs_status', array());
	}
	return html_build_select_box($FRS_STATUS_RES, $name, $checked_val, false);
}

/**
 * frs_show_filetype_popup - pop-up select box of supported frs filetypes
 *
 * @param	string	$name		default value 'type_id'
 * @param	string	$checked_val	default value 'xzxz'
 * @return	string	html code
 */
function frs_show_filetype_popup ($name = 'type_id', $checked_val = 'xzxz') {
	global $FRS_FILETYPE_RES;
	if (!isset($FRS_FILETYPE_RES)) {
		$FRS_FILETYPE_RES = db_query_params('SELECT * FROM frs_filetype ORDER BY type_id', array());
	}
	return html_build_select_box($FRS_FILETYPE_RES, $name, $checked_val, false);
}

/**
 * frs_show_processor_popup - pop-up select box of supported frs processor options
 *
 * @param	string	$name		default value 'processor_id'
 * @param	string	$checked_val	default value 'xzxz'
 * @return	string html code
 */
function frs_show_processor_popup($name = 'processor_id', $checked_val = 'xzxz') {
	global $FRS_PROCESSOR_RES;
	if (!isset($FRS_PROCESSOR_RES)) {
		$FRS_PROCESSOR_RES = db_query_params('SELECT * FROM frs_processor ORDER BY processor_id', array());
	}
	return html_build_select_box($FRS_PROCESSOR_RES, $name, $checked_val, false);
}

/**
 * frs_show_release_popup - pop-up select box of packages:releases for this group
 *
 * @param	int	$group_id	the project id
 * @param	string	$name		default value 'processor_id'
 * @param	string	$checked_val	default value 'xzxz'
 * @return	string	html code
 */
function frs_show_release_popup($group_id, $name = 'release_id', $checked_val = 'xzxz') {
	global $FRS_RELEASE_RES;

	if (!$group_id) {
		return _('Error: group id required');
	}
	if (!isset($FRS_RELEASE_RES)) {
		$FRS_RELEASE_RES = db_query_params("SELECT frs_release.release_id,(frs_package.name || ' : ' || frs_release.name) FROM frs_release, frs_package
							WHERE frs_package.group_id = $1
							AND frs_release.package_id = frs_package.package_id",
							array($group_id));
		echo db_error();
	}
	return html_build_select_box($FRS_RELEASE_RES, $name, $checked_val, false);
}

/**
 * frs_show_package_popup - pop-up select box of packages for this group
 *
 * @param	int	$group_id	the project id
 * @param	string	$name		default value 'processor_id'
 * @param	string	$checked_val	default value 'xzxz'
 * @return	string	html code
 */
function frs_show_package_popup($group_id, $name = 'package_id', $checked_val = 'xzxz') {
	global $FRS_PACKAGE_RES;
	if (!$group_id) {
		return _('Error: group id required');
	}
	if (!isset($FRS_PACKAGE_RES)) {
		$FRS_PACKAGE_RES=db_query_params('SELECT package_id,name
						FROM frs_package WHERE group_id = $1',
						array($group_id));
		echo db_error();
	}
	return html_build_select_box($FRS_PACKAGE_RES, $name, $checked_val, false);
}

/**
 * frs_add_file_from_form - helper to add a file from the qrs form which allows multiple possibilities to add a file.
 *
 * @param	object		$release		the release object to which the file belongs
 * @param	int		$type_id		the file type
 * @param	int		$processor_id		the processor type
 * @param	string		$release_date		the release date
 * @param	array		$userfile		a new uploaded file
 * @param	string		$ftp_filename		a already uploaded file using ftp
 * @param	string		$manual_filename	a already uploaded file using manual upload
 * @param	int		$docman_fileid		a doc_id of a already uploaded file using docman
 * @return	bool|string	true on success or string message on error
 */
function frs_add_file_from_form($release, $type_id, $processor_id, $release_date,
				 $userfile, $ftp_filename, $manual_filename, $docman_fileid) {

	$group_unix_name = $release->getFRSPackage()->getGroup()->getUnixName();
	$incoming = forge_get_config('groupdir_prefix').'/'.$group_unix_name.'/incoming';
	$upload_dir = forge_get_config('ftp_upload_dir').'/'.$group_unix_name;

	$filechecks = false;

	$filenamecheck  = util_is_valid_filename($userfile['name']);

	if ($userfile && is_uploaded_file($userfile['tmp_name']) && $filenamecheck) {
		$infile = $userfile['tmp_name'];
		$fname = $userfile['name'];
		$move = true;
		$filechecks = true;
	} elseif ($userfile && $userfile['error'] != UPLOAD_ERR_OK && $userfile['error'] != UPLOAD_ERR_NO_FILE) {
		switch ($userfile['error']) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return _('The uploaded file exceeds the maximum file size. Contact to the site admin to upload this big file, or use an alternate upload method (if available).');
			break;
			case UPLOAD_ERR_PARTIAL:
				return _('The uploaded file was only partially uploaded.');
			break;
			default:
				return _('Unknown file upload error.').' => ';
			break;
		}
	} elseif ($release->getFRSPackage()->getGroup()->usesFTP() && $ftp_filename && util_is_valid_filename($ftp_filename) && is_file($upload_dir.'/'.$ftp_filename)) {
		$infile = $upload_dir.'/'.$ftp_filename;
		$fname = $ftp_filename;
		$move = false;
		$filechecks = true;
	} elseif (forge_get_config('use_manual_uploads') && $manual_filename && util_is_valid_filename($manual_filename) && is_file($incoming.'/'.$manual_filename)) {
		$infile = $incoming.'/'.$manual_filename;
		$fname = $manual_filename;
		$move = false;
		$filechecks = true;
	} elseif ($release->getFRSPackage()->getGroup()->usesDocman() && $docman_fileid) {
		$doc = new Document($release->getFRSPackage()->getGroup(), $docman_fileid);
		$fname = $doc->getFileName();
		$infile = DocumentStorage::instance()->get($doc->getSerialIDVersion());
		$move = false;
		$filechecks = true;
	} elseif ($userfile && $userfile['error'] == UPLOAD_ERR_NO_FILE) {
		return _('Must select a file.');
	} elseif ($userfile && $filenamecheck) {
		return _('Invalid characters in file name.');
	}

	if ($filechecks) {
		if (strlen($fname) < 3)
			return _('Name is too short. It must be at least 3 characters.');
		if (!$move) {
			$tmp = tempnam('', '');
			copy($infile, $tmp);
			$infile = $tmp;
		}
		$frsf = new FRSFile($release);
		if (!$frsf || !is_object($frsf)) {
			return _('Could Not Get FRSFile');
		} elseif ($frsf->isError()) {
			return $frsf->getErrorMessage();
		} else {
			if (!$frsf->create($fname, $infile, $type_id, $processor_id, $release_date)) {
				return $frsf->getErrorMessage();
			}
			return true;
		}
	} else {
		return _('Unknown file upload error.');
	}
}

/**
 * frs_filterfiles - filter utils.php:&ls() output for additional constraints from FRS
 *
 * @param	array	$in	the output of the &ls() function from utils.php
 * @return	array	the filtered array
 */
function frs_filterfiles($in) {
	$out = array();
	for ($i = 0; $i < count($in); $i++) {
		if (strlen($in[$i]) < 3)
			continue;
		$out[] = $in[$i];
	}
	return $out;
}
