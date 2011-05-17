<?php
/**
 * FRS HTML Utilities
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

/*
	Standard header to be used on all /project/admin/* pages
*/

function frs_admin_header($params) {
	global $group_id;

	/*
		Are they logged in?
	*/
	if (!session_loggedin()) {
		exit_not_logged_in();
	}

	$project = group_get_object($group_id);
	if (!$project || !is_object($project)) {
		return;
	}

	session_require_perm ('frs', $group_id, 'write') ;

	frs_header($params);
}

function frs_admin_footer() {
	site_project_footer(array());
}

function frs_header($params) {
	global $group_id,$HTML;

	/*
		Does this site use FRS?
	*/
	if (!forge_get_config('use_frs')) {
		exit_disabled('home');
	}

	$project = group_get_object($group_id);
	if (!$project || !is_object($project)) {
		exit_no_group();
	}

	$params['toptab']='frs';
	$params['group']=$group_id;

	if (forge_check_perm ('frs', $group_id, 'write')) {
		$params['submenu'] = $HTML->subMenu(
			array(
				_('View File Releases'),
				_('Reporting'),
				_('Administration')
				),
			array(
				'/frs/?group_id='.$group_id,
				'/frs/reporting/downloads.php?group_id='.$group_id,
				'/frs/admin/?group_id='.$group_id
				)
			);
	}
	site_project_header($params);
}

function frs_footer() {
	site_project_footer(array());
}


/*
	The following functions are for the FRS (File Release System)
*/


/*
	pop-up box of supported frs statuses
*/

function frs_show_status_popup ($name='status_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of statuses
	*/
	global $FRS_STATUS_RES;
	if (!isset($FRS_STATUS_RES)) {
		$FRS_STATUS_RES=db_query_params ('SELECT * FROM frs_status',
			array());
	}
	return html_build_select_box ($FRS_STATUS_RES,$name,$checked_val,false);
}

/*
	pop-up box of supported frs filetypes
*/

function frs_show_filetype_popup ($name='type_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of the available filetypes
	*/
	global $FRS_FILETYPE_RES;
	if (!isset($FRS_FILETYPE_RES)) {
		$FRS_FILETYPE_RES=db_query_params ('SELECT * FROM frs_filetype ORDER BY type_id',
			array());
	}
	return html_build_select_box ($FRS_FILETYPE_RES,$name,$checked_val,true,_('Must Choose One'));
}

/*
	pop-up box of supported frs processor options
*/

function frs_show_processor_popup ($name='processor_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of the available processors 
	*/
	global $FRS_PROCESSOR_RES;
	if (!isset($FRS_PROCESSOR_RES)) {
		$FRS_PROCESSOR_RES=db_query_params ('SELECT * FROM frs_processor ORDER BY processor_id',
			array());
	}
	return html_build_select_box ($FRS_PROCESSOR_RES,$name,$checked_val,true,_('Must Choose One'));
}

/*
	pop-up box of packages:releases for this group
*/


function frs_show_release_popup ($group_id, $name='release_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of releases for the project
	*/
	global $FRS_RELEASE_RES;

	if (!$group_id) {
		return 'ERROR - GROUP ID REQUIRED';
	} else {
		if (!isset($FRS_RELEASE_RES)) {
			$FRS_RELEASE_RES = db_query_params("SELECT frs_release.release_id,(frs_package.name || ' : ' || frs_release.name) FROM frs_release,frs_package 
WHERE frs_package.group_id=$1 
AND frs_release.package_id=frs_package.package_id", 
							   array($group_id));
			echo db_error();
		}
		return html_build_select_box($FRS_RELEASE_RES,$name,$checked_val,false);
	}
}

/*
	pop-up box of packages for this group
*/

function frs_show_package_popup ($group_id, $name='package_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of packages for this project
	*/
	global $FRS_PACKAGE_RES;
	if (!$group_id) {
		return 'ERROR - GROUP ID REQUIRED';
	} else {
		if (!isset($FRS_PACKAGE_RES)) {
			$FRS_PACKAGE_RES=db_query_params ('SELECT package_id,name 
				FROM frs_package WHERE group_id=$1',
			array($group_id));
			echo db_error();
		}
		return html_build_select_box ($FRS_PACKAGE_RES,$name,$checked_val,false);
	}
}

function frs_add_file_from_form ($release, $type_id, $processor_id, $release_date,
				 $userfile, $ftp_filename, $manual_filename) {


	$group_unix_name = $release->getFRSPackage()->getGroup()->getUnixName() ;
	$incoming = "forge_get_config('groupdir_prefix')/$group_unix_name/incoming" ;

	$filechecks = false ;

	if ($userfile && is_uploaded_file($userfile['tmp_name']) && util_is_valid_filename($userfile['name'])) {
		$infile = $userfile['tmp_name'] ;
		$fname = $userfile['name'] ;
		$move = true ;
		$filechecks = true ;
	} elseif ($userfile && $userfile['error'] != UPLOAD_ERR_OK && $userfile['error'] != UPLOAD_ERR_NO_FILE) {
		switch ($userfile['error']) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return _('The uploaded file exceeds the maximum file size. Contact to the site admin to upload this big file, or use an alternate upload method (if available).') ;
			break;
			case UPLOAD_ERR_PARTIAL:
				return _('The uploaded file was only partially uploaded.') ;
			break;
			default:
				return _('Unknown file upload error.') ;
			break;
		}
	} elseif (forge_get_config('use_ftp_uploads') && $ftp_filename && util_is_valid_filename($ftp_filename) && is_file($upload_dir.'/'.$ftp_filename)) {
		$infile = $upload_dir.'/'.$ftp_filename;
		$fname = $ftp_filename ;
		$move = false ;
		$filechecks = true ;
	} elseif (forge_get_config('use_manual_uploads') && $manual_filename && util_is_valid_filename($manual_filename) && is_file($incoming.'/'.$manual_filename)) {
		$infile = $incoming.'/'.$manual_filename ;
		$fname = $manual_filename ;
		$move = false ;
		$filechecks = true ;
	} elseif ($userfile && $userfile['error'] == UPLOAD_ERR_NO_FILE) {
		return _('Must select a file.') ;
	}

	if ($filechecks) {
		if (!$move) {
			$tmp = tempnam ('', '') ;
			copy ($infile, $tmp) ;
			$infile = $tmp ;
		}
		$frsf = new FRSFile($release);
		if (!$frsf || !is_object($frsf)) {
			exit_error(_('Could Not Get FRSFile'),'frs');
		} elseif ($frsf->isError()) {
			exit_error($frsf->getErrorMessage(),'frs');
		} else {
			if (!$frsf->create($fname,$infile,$type_id,$processor_id,$release_date)) {
				db_rollback();
				exit_error($frsf->getErrorMessage(),'frs');
			}
			return true ;
		}
	} else {
		return _('Unknown file upload error.') ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
