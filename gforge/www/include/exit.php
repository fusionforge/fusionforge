<?php
/**
 * Exit functions
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/**
 * exit_error() - Exit PHP with error
 *
 * @param		string	Error title
 * @param		string	Error text
 */
function exit_error($title,$text="") {
	global $HTML,$group_id, $Language;
	$HTML->header(array('title'=>_('Exiting with error'),'group'=>$group_id));
	print '<span class="error">'.$title.'</span><p>'.htmlspecialchars($text) .'</p>';
	$HTML->footer(array());
	exit;
}

/**
 * exit_permission_denied() - Exit with permission denied error
 *
 * @param		string	$reason_descr
 */
function exit_permission_denied($reason_descr='') {
	global $Language;
	if(!session_loggedin()) {
		exit_not_logged_in();
	} else {
		if (!$reason_descr) {
			$reason_descr=_('This project\'s administrator will have to grant you permission to view this page.');
		}
		exit_error(_('Permission Denied.'),$reason_descr);
	}
}

/**
 * exit_not_logged_in() - Exit with not logged in error
 */
function exit_not_logged_in() {
	//instead of a simple error page, now take them to the login page
	header ("Location: ".$GLOBALS['sys_urlprefix']."/account/login.php?return_to=".urlencode(getStringFromServer('REQUEST_URI')));
	exit;
}

/**
 * exit_no_group() - Exit with no group chosen error
 */
function exit_no_group() {
	global $Language;
	exit_error(_('ERROR - No group was chosen or you can\'t access it'),_('No group was chosen or you can\'t access it'));
}

/**
 * exit_missing_param() - Exit with missing required parameters error
 */
function exit_missing_param() {
	global $Language;
	exit_error(_('Error - missing parameters'),_('Error - missing required parameters'));
}

/**
 * exit_disabled() - Exit with disabled feature error.
 */
function exit_disabled() {
	global $Language;
	exit_error(_('Error - disabled feature.'),_('The Site Administrator has turned off this feature.'));
}

/**
 * exit_form_double_submit() - Exit with double submit error.
 */
function exit_form_double_submit() {
	global $Language;
	exit_error(_('Error - double submit'),_('You Attempted To Double-submit this item. Please avoid double-clicking.'));
}

?>
