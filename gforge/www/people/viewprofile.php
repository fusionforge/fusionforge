<?php
/**
 *
 * Skills viewer page.
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002 (c) Silicon and Software Systems (S3)
 *
 * @version   $Id$
 *
 */

require_once('pre.php');
require_once('people_utils.php');
require_once('skills_utils.php');


if ($user_id) {

	/*
		Fill in the info to create a job
	*/
	people_header(array('title'=>'View a User Profile','pagename'=>'people_viewprofile'));

	//for security, include group_id
	$sql="SELECT * FROM users WHERE user_id='$user_id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		echo db_error();
		$feedback .= ' User fetch FAILED ';
		echo '<H2>No Such User</H2>';
	} else {

		/*
			profile set private
		*/
		if (db_result($result,0,'people_view_skills') != 1) {
			echo '<H2>This User Has Set His/Her Profile to Private</H2>';
			people_footer(array());
			exit;
		}

		echo '
        <P>
		<B>Skills profile for : </B>'. db_result($result,0,'realname') .
        ' ('.db_result($result, 0, 'user_name') .
        ')<br><br> <TABLE BORDER="0" WIDTH="100%">';
        
        displayUserSkills($user_id, 0);
        		
		echo '</TABLE>';
	}

	people_footer(array());

} else {
	/*
		Not logged in or insufficient privileges
	*/
	exit_error('Error','user_id not found.');
}

?>
