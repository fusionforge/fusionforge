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
function exit_error($title,$text) {
	GLOBAL $HTML,$group_id, $Language;
	$HTML->header(array('title'=>$Language->getText('exit','exiting_with_error'),'group'=>$group_id));
	print '<h2><span style="color:#FF3333">'.$title.'</span></h2><p>'.$text .'</p>';
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
	if (!$reason_descr) $reason_descr=$Language->getText('general','permexcuse');
	exit_error($Language->getText('general','permdenied'),$reason_descr);
}

/**
 * exit_not_logged_in() - Exit with not logged in error
 */
function exit_not_logged_in() {
	global $REQUEST_URI;
	//instead of a simple error page, now take them to the login page
	header ("Location: /account/login.php?return_to=".urlencode($REQUEST_URI));
	//exit_error('Not Logged In','Sorry, you have to be <a href="/account/login.php">logged in</a> to view this page.');
}

/**
 * exit_no_group() - Exit with no group chosen error
 */
function exit_no_group() {
	global $Language;
	exit_error($Language->getText('exit','choose_group_title'),$Language->getText('exit','choose_group_body'));
}

/**
 * exit_missing_param() - Exit with missing required parameters error
 */
function exit_missing_param() {
	global $Language;
	exit_error($Language->getText('exit','missing_parameters_title'),$Language->getText('exit','missing_parameters_body'));
}

/**
 *	exit_assert_object() - Assert validity of Error-derived object
 *
 *	Should be used at the beginning of the code, when
 *	instantiating object and before any HTML output.
 *
 *	@param		object	Object of subclass of Error class
 *	@param		string	Name of the class object should belong to 
 *	@return will not return if object is not valid
 */
function exit_assert_object($obj, $expected_class) {
	if (!$obj || !is_object($obj)) {
		exit_error($Language->getText('general','error'), $Language->getText('error','error_creating').$expected_class.' object');
	} else if ($obj->isError()) {
		exit_error($Language->getText('general','error'), $obj->getErrorMessage());
	}
}

?>
