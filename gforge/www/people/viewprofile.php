<?php
/**
  *
  * SourceForge Jobs (aka Help Wanted) Board 
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/people/people_utils.php');

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
		<TABLE BORDER="0" WIDTH="100%">
		<TR><TD>
			<B>User Name:</B><BR>
			'. db_result($result,0,'user_name') .'
		</TD></TR>
		<TR><TD>
			<B>Resume:</B><BR>
			'. nl2br(db_result($result,0,'people_resume')) .'
		</TD></TR>
		<TR><TD>
		<H2>Skill Inventory</H2>';

		//now show the list of desired skills
		echo '<P>'.people_show_skill_inventory($user_id);
		echo '</TD></TR></TABLE>';
	}

	people_footer(array());

} else {
	/*
		Not logged in or insufficient privileges
	*/
	exit_error('Error','user_id not found.');
}

?>
