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
	people_header(array('title'=>$Language->getText('people_viewprofile','title'),'pagename'=>'people_viewprofile'));

	//for security, include group_id
	$sql="SELECT * FROM users WHERE user_id='$user_id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		echo db_error();
		$feedback .= $Language->getText('people_viewprofile','user_fetch_failed');
		echo '<h2>'.$Language->getText('people_viewprofile','no_such_user').'</h2>';
	} else {

		/*
			profile set private
		*/
		if (db_result($result,0,'people_view_skills') != 1) {
			echo '<h2>'.$Language->getText('people_viewprofile','set_private').'</h2>';
			people_footer(array());
			exit;
		}

		echo '
        <p>
		<strong>'.$Language->getText('people_viewprofile','skills_profile_for').' : </strong>'. db_result($result,0,'realname') .
        ' ('.db_result($result, 0, 'user_name') .
        ')<br /><br /></p> <table border="0" width="100%">';
        
        displayUserSkills($user_id, 0);
        		
		echo '</table>';
	}

	people_footer(array());

} else {
	/*
		Not logged in or insufficient privileges
	*/
	exit_error('Error','user_id not found.');
}

?>
