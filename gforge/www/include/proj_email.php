<?php
/**
 * proj_email.php - Misc project email functions.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/**
 * send_new_project_email() - Send the email whena new project has been approved.
 *
 * @param		int		The group ID
 */
function send_new_project_email($group_id) {

	$res_grp = db_query("SELECT * FROM groups WHERE group_id='$group_id'");

	if (db_numrows($res_grp) < 1) {
		echo ("Group [ $group_id ] does not exist.");
	}

	$row_grp = db_fetch_array($res_grp);

	$res_admins = db_query("SELECT users.user_name,users.email FROM users,user_group WHERE "
		. "users.user_id=user_group.user_id AND user_group.group_id='$group_id' AND "
		. "user_group.admin_flags='A'");

	if (db_numrows($res_admins) < 1) {
		echo ("Group [ $group_id ] does not seem to have any administrators.");
	}

	// send one email per admin
while ($row_admins = db_fetch_array($res_admins)) {
	$message = 
'Your project registration for SourceForge has been approved. 

Project Full Name:  '.$row_grp['group_name'].'
Project Unix Name:  '.$row_grp['unix_group_name'].'
CVS Server:         cvs.'.$row_grp['unix_group_name'].'.'.$GLOBALS['sys_default_domain'].'
Shell/Web Server:   '.$row_grp['unix_group_name'].'.'.$GLOBALS['sys_default_domain'].'

Your DNS will take up to a day to become active on our site.
While waiting for your DNS to resolve, you may try shelling into 
'. $GLOBALS['sys_shell_host']. ' and pointing CVS to '. $GLOBALS['sys_cvs_host'].'.

If after 6 hours your shell/CVS accounts still do not work, please
open a support ticket so that we may take a look at the problem.
Please note that all shell/CVS accounts are closed to telnet and 
work with both SSH1 and SSH2.

Your web site is accessible through your shell account. Please read
site documentation (see link below) about intended usage, available 
services, and directory layout of the account.

Please take some time to read the site documentation about project
administration (http://'.$GLOBALS['sys_default_domain'].'/docs/site/). If you visit your 
own project page in SourceForge while logged in, you will find 
additional menu functions to your left labeled \'Project Admin\'. 

We highly suggest that you now visit SourceForge and create a public
description for your project. This can be done by visiting your project
page while logged in, and selecting \'Project Admin\' from the menus
on the left (or by visiting https://'.$GLOBALS['sys_default_domain'].'/project/admin/?group_id='.$group_id.'
after login).

Your project will also not appear in the Trove Software Map (primary
list of projects hosted on SourceForge which offers great flexibility in
browsing and search) until you categorize it in the project administration 
screens. So that people can find your project, you should do this now. 
Visit your project while logged in, and select \'Project Admin\' from the 
menus on the left.

Enjoy the system, and please tell others about SourceForge. Let us know
if there is anything we can do to help you.

 -- the SourceForge crew';
	
	mail($row_admins['email'],"SourceForge Project Approved",$message,"From: noreply@$GLOBALS[HTTP_HOST]");

}

}

/**
 * send_project_rejection() - This function sends out a rejection message to a user who registers a project
 *
 * @param		int		The group ID
 * @param		int		The response ID
 * @param		string	The message to send
 */
function send_project_rejection($group_id, $response_id, $message="zxcv")
{
	// Get the email addr of the user who wants to register the project.
	$email = db_result(db_query("SELECT u.email AS email FROM users u, user_group ug WHERE ug.group_id='$group_id' AND u.user_id=ug.user_id;"),0,"email");
	
	// Check to see if they want to send a custom rejection response
	if( $response_id == 0 ) {
		$response = $message;
	} else {
		$response = db_result(db_query("SELECT response_text FROM canned_responses WHERE response_id='$response_id'"),0,"response_text");
	}

	mail($email, "SourceForge Project Denied", $response, "From: noreply@sourceforge.net");

	return true;
}

?>
