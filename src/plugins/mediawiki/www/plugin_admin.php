<?php
/**
 * MediaWiki Plugin for FusionForge
 *
 * Copyright © 2010, 2012
 *      Thorsten Glaser <t.glaser@tarent.de>
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012,2014, Franck Villaume - TrivialDev
 * All rights reserved.
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
 *
 * Admin page for the plugin
 */

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';

global $HTML;

function logo_create($file_location, $project_mw_images_dir) {
	$logofile = $project_mw_images_dir . "/.wgLogo.png";

	if (!is_file($file_location) || !file_exists($file_location)) {
		return _("Invalid file upload");
	}
	$img = getimagesize($file_location);
	if (!$img || ($img[2] != IMAGETYPE_PNG)) {
		return _("Not a valid PNG image");
	}
	if ($img[0] != 135 || $img[1] != 135) {
		return sprintf(_("Image size is %dx%d pixels, expected %dx%d instead"), $img[0], $img[1], 135, 135);
	}

	if (!is_writable($project_mw_images_dir)) {
		return sprintf( _("Cannot copy file to target directory %s"), $project_mw_images_dir);
	}

	if (file_exists($logofile) && !is_writable($logofile)) {
		return _("Cannot overwrite existing file");
	}

	$cmd = "/bin/mv " . escapeshellcmd($file_location) .
	    " " . escapeshellcmd($logofile);
	exec($cmd,$out);
	if (!file_exists($logofile)) {
		return _("Cannot move file to target location");
	}

	return _("New file installed successfully");
}

$user = session_get_user();
if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
	exit_error("Invalid User", "Cannot process your request for this user.");
}
$gid = getIntFromRequest("group_id", -1);
if ($gid == -1) {
	$group = false;
} else {
	$group = group_get_object($gid);
}
if (!$group) {
	exit_error("Invalid Project", "Nonexistent Project");
}
if (!$group->usesPlugin("mediawiki")) {
	exit_disabled();
}

$userperm = $group->getPermission();
if (!$userperm->IsMember()) {
	exit_permission_denied();
} elseif (!$userperm->IsAdmin()) {
	exit_error("Access Denied", "You are not an admin of this project");
}

$group_unix_name = $group->getUnixName();
$wgUploadDirectory = forge_get_config('projects_path', 'mediawiki') . "/".$group_unix_name . "/images";
$group_logo = $wgUploadDirectory . "/.wgLogo.png";
$group_logo_url = util_make_url("/plugins/mediawiki/wiki/" . $group_unix_name . "/images/.wgLogo.png");

$incoming = False;
if (forge_get_config('use_manual_uploads')) {
	$incoming = forge_get_config('groupdir_prefix')."/$group_unix_name/incoming";

	if ( (! is_dir($incoming)) || (! opendir($incoming)) ) {
		$error_msg = sprintf( _("Not a directory or could not access contents of %s"), $incoming);
	}
}

/* As the cronjob creates images subdirs in project data only if the uploads are enabled, there are chances the upload may fail */
if (! forge_get_config('enable_uploads', 'mediawiki')) {
	$error_msg .= _("Mediawiki plugin's configuration may require to enable uploads ('enable_uploads'). Contact your admin.");
}

if (getStringFromRequest("logo_submit")) {
	$userfile = getUploadedFile('userfile');
	$userfile_name = $userfile['name'];
	$manual_filename = getStringFromRequest('manual_filename');

	$feedback = "";

	if (getIntFromRequest("logo_nuke") == 1) {
		if (unlink($wgUploadDirectory . "/.wgLogo.png")) {
			$feedback = _("File successfully removed");
		} else {
			$feedback = _("File removal error");
		}
	} elseif ($userfile && is_uploaded_file($userfile['tmp_name']) &&
			util_is_valid_filename($userfile['name'])) {
		$infile = $userfile['tmp_name'];
		$fname = $userfile['name'];
		$move = true;
	} elseif ($userfile && $userfile['error'] != UPLOAD_ERR_OK &&
		$userfile['error'] != UPLOAD_ERR_NO_FILE) {
		switch ($userfile['error']) {
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			$feedback = _('The uploaded file exceeds the maximum file size. Contact to the site admin to upload this big file, or use an alternate upload method (if available).');
			break;
		case UPLOAD_ERR_PARTIAL:
			$feedback = _('The uploaded file was only partially uploaded.');
			break;
		default:
			$feedback = _('Unknown file upload error.');
			break;
		}
	} elseif (forge_get_config ('use_manual_uploads') && $manual_filename &&
			util_is_valid_filename($manual_filename) &&
			is_file($incoming.'/'.$manual_filename)) {
		$incoming = forge_get_config('groupdir_prefix')."/$group_unix_name/incoming";
		$infile = $incoming.'/'.$manual_filename;
		$fname = $manual_filename;
		$move = false;
	} else {
		$feedback = _('Unknown file upload error.');
	}

	if (!$feedback) {
		if (!$move) {
			$tmp = tempnam('', '');
			copy($infile, $tmp);
			$infile = $tmp;
		}
		$feedback = logo_create($infile, $wgUploadDirectory);
	}
}

site_project_header(array(
	"title" => _('MediaWiki Plugin Admin for ').$group->getPublicName(),
	"pagename" => _('MediaWiki Project Admin'),
	"sectionvals" => array($group->getPublicName()),
	"toptab" => "admin",
	"group" => $gid,
	));

echo html_e('h2', array(), _('Nightly XML dump'));
echo html_e('p', array(), sprintf(_('<a href="%s">Download</a> the nightly created XML dump (backup) here.'),
				util_make_url("/plugins/mediawiki/dumps/" . $group_unix_name . ".xml")));

echo html_e('h2', array(), _("This project's wiki logo : \$wgLogo"));
echo html_ao('div', array('style' => 'border:solid 1px black; margin:3px; padding:3px'));;
if (file_exists($group_logo)) {
	echo html_e('p', array(), _("Current logo:") . ' (<a href="' . $group_logo_url .
				'">' . _("Download") . '</a>)<br /><img alt="wgLogo.png" ' .
				'class="boxed_wgLogo" src="' . $group_logo_url . '" />');
} else {
	echo html_e('p', array(), _('No per-project logo currently installed.'));
}
echo html_ac(html_ap() -1);
echo $HTML->openForm(array('enctype' => 'multipart/form-data', 'method' => 'post', 'style' => 'border:solid 1px black; margin:3px; padding:3px;',
				'action' => getStringFromServer('PHP_SELF').'?group_id='.$gid));
echo html_e('h3', array(), _('Upload a new logo'));
echo html_e('p', array(), _('The logo must be in PNG format and precisely 135x135 pixels in size.'));
echo html_e('p', array(), _('Upload a new file').(': ').html_e('input', array('type' => 'file', 'name' => 'userfile')));
if (forge_get_config('use_manual_uploads')) {
	echo '<p>';
	printf(_('Alternatively, you can use a file you already uploaded (by SFTP or SCP) to the <a href="%2$s">project\'s incoming directory</a> (%1$s).'),
		$incoming, "sftp://" . forge_get_config('shell_host') . $incoming . "/");
	echo ' ' . _('This direct <kbd>sftp://</kbd> link only works with some browsers, such as Konqueror.') . '<br />';
	$manual_files_arr=ls($incoming,true);
	if ( count($manual_files_arr) > 0 ) {
		echo _('Choose an already uploaded file:').'<br />';
		echo html_build_select_box_from_arrays($manual_files_arr,$manual_files_arr,'manual_filename','');
	} else {
		echo '<input type="hidden" name="manual_filename" value="">';
	}
	echo '</p>';
}
echo html_e('p', array(), html_e('input', array('type' => 'checkbox', 'name' => 'logo_nuke', 'value' => 1)).
			_('… or delete the currently uploaded logo and revert to the site default'));
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'logo_submit', 'value' => _('Upload new logo'))));
echo $HTML->closeForm();
site_project_footer(array());
