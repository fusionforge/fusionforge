<?php
/**
 * FusionForge : Exit functions
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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
 * exit_error() - Exit PHP with error
 *
 * @param	string	$text	Error text
 * @param	string	$toptab for navigation bar
 */
function exit_error($text = "", $toptab = '') {
	global $HTML, $group_id;
	$HTML->header(array('title'=>_('Exiting with error'), 'group'=>$group_id, 'toptab'=>$toptab));
	echo $HTML->error_msg(htmlspecialchars($text));
	$HTML->footer(array());
	exit;
}

/**
 * exit_permission_denied() - Exit with permission denied error
 *
 * @param	string	$reason_descr
 * @param	string	$toptab needed for navigation
 */
function exit_permission_denied($reason_descr = '', $toptab = '') {
	if(!session_loggedin()) {
		exit_not_logged_in();
	} else {
		if (!$reason_descr) {
			$reason_descr=_('Permission denied. This project\'s administrator will have to grant you permission to view this page.');
		}
		exit_error($reason_descr, $toptab);
	}
}

/**
 * exit_not_logged_in() - Exit with not logged in error
 */
function exit_not_logged_in() {
	//instead of a simple error page, now take them to the login page
	session_redirect("/account/login.php?triggered=1&return_to=" .
	    urlencode(getStringFromServer('REQUEST_URI')), false);
}

/**
 * exit_no_group() - Exit with no group chosen error
 */
function exit_no_group() {
	exit_error(_('Permission denied. No project was chosen, project does not exist or you cannot access it.'), '');
}

/**
 * exit_missing_param() - Exit with missing required parameters error
 *
 * @param	string	$url	URL, usually $_SERVER['HTTP_REFERER']
 * @param	array	$missing_params	array of missing parameters
 * @param	string	$toptab needed for navigation
 */
function exit_missing_param($url = '', $missing_params = array(), $toptab = '') {
	if (!empty($missing_params)) {
		$error = _('Missing Required Parameters')._(': ');
		foreach ($missing_params as $missing_param) {
			$error .= $missing_param.' ';
		}
	} else {
		$error = sprintf(_('Missing Required Parameters'));
	}
	$redirect_url = util_find_relative_referer($url);
	if (!empty($redirect_url)) {
		if (strpos($redirect_url,'?')) {
			session_redirect($redirect_url.'&error_msg='.urlencode($error));
		}
		session_redirect($redirect_url.'?error_msg='.urlencode($error));
	} else {
		exit_error($error, $toptab);
	}
}

/**
 * exit_disabled() - Exit with disabled feature error.
 *
 * @param	string	$toptab needed for navigation
 */
function exit_disabled($toptab = 'summary') {
	exit_error(_('The Site Administrator has turned off this feature.'), $toptab);
}

/**
 * exit_project_disabled() - Exit with disabled feature error (by project admin).
 *
 * @param	string	$toptab needed for navigation
 */
function exit_project_disabled($toptab = 'summary') {
	exit_error(_('The Project Administrator has turned off this feature.'), $toptab);
}

/**
 * exit_form_double_submit() - Exit with double submit error.
 *
 * @param	string	$toptab needed for navigation
 */
function exit_form_double_submit($toptab = '') {
	exit_error(_('You Attempted To Double-submit this item. Please avoid double-clicking.'), $toptab);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
