<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');	 
require ('vars.php');
require ('account.php');
require ('proj_email.php');
require ('canned_responses.php');
require($DOCUMENT_ROOT.'/admin/admin_utils.php');
require($DOCUMENT_ROOT.'/project/admin/project_admin_utils.php');
global $feedback;

session_require(array('group'=>'1','admin_flags'=>'A'));

function activate_group($group_id) {
	global $feedback;
echo("activate_group($group_id)<br>");	

	if (sf_ldap_create_group($group_id,0)) {
		db_query("UPDATE groups ".
		"SET status='A' ".
		"WHERE group_id=$group_id");

		/*
			Make founding admin be an active member of the project
		*/
		 
		$admin_res=db_query("SELECT * ".
			"FROM users,user_group ".
			"WHERE user_group.group_id=$group_id ".
			"AND user_group.admin_flags='A' ".
			"AND users.user_id=user_group.user_id ");

		if (db_numrows($admin_res) > 0) {
			$group=&group_get_object($group_id);

//
//	user_get_object should really have a valid user_id passed in
//	or you are defeating the purpose of the object pooling
//
			$admin=&user_get_object(db_result($admin_res,0,'user_id'),$admin_res);

			if ($group->addUser($admin->getUnixName())) {
				/*
					Now send the project approval emails
				*/
				group_add_history ('approved','x',$group_id);
				send_new_project_email($group_id);
				usleep(250000); // TODO: This is dirty. If sendmail required pause, let send_new... handle it
			} else {
				$feedback=$group->getErrorMessage();
			}
		} else {
			echo db_error();
		}
	} else {
		/* There was error creating LDAP entry */
		group_add_history ('ldap:',sf_ldap_get_error_msg(),$group_id);
	}
}

// group public choice
if ($action=='activate') {
	/*
		update the project flag to active
	*/

	$groups=explode(',',$list_of_groups);
	array_walk($groups,'activate_group');

} else if ($action=='delete') {
	group_add_history ('deleted','x',$group_id);
	db_query("UPDATE groups ".
		 "SET status='D' ".
		 "WHERE group_id='$group_id'");

	// Determine whether to send a canned or custom rejection letter and send it
	if( $response_id == 100 ) {
		send_project_rejection($group_id, 0, $response_text);

		if( $add_to_can ) {
			add_canned_response($response_title, $response_text);
		}
	} else {
		send_project_rejection($group_id, $response_id);
	}
}


site_admin_header(array('title'=>'Approving Pending Projects'));

// get current information
$res_grp = db_query("SELECT * FROM groups WHERE status='P'");

if (db_numrows($res_grp) < 1) {
	print "<h1>None Found</h1>";
	print "<p>No Pending Projects to Approve</p>";
	site_admin_footer(array());
	exit;
}

while ($row_grp = db_fetch_array($res_grp)) {

	?>
	<H2><?php echo $row_grp['group_name']; ?></H2>

	<p>
	<A href="/admin/groupedit.php?group_id=<?php echo $row_grp['group_id']; ?>"><H3>[Edit Project Details]</H3></A>

	<p>
	<A href="/project/admin/?group_id=<?php echo $row_grp['group_id']; ?>"><H3>[Project Admin]</H3></A>

	<P>
	<A href="userlist.php?group_id=<?php print $row_grp['group_id']; ?>"><H3>[View/Edit Project Members]</H3></A>

	<p>
	<table><tr><td>
	<FORM action="<?php echo $PHP_SELF; ?>" method="POST">
	<INPUT TYPE="HIDDEN" NAME="action" VALUE="activate">
	<INPUT TYPE="HIDDEN" NAME="list_of_groups" VALUE="<?php print $row_grp['group_id']; ?>">
	<INPUT type="submit" name="submit" value="Approve">
	</FORM>
	</td></tr>
	<tr><td>
	<FORM action="<?php echo $PHP_SELF; ?>" method="POST">
	<INPUT TYPE="HIDDEN" NAME="action" VALUE="delete">
	<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="<?php print $row_grp['group_id']; ?>">
	Canned responses<br>
<?php print get_canned_responses(); ?>
	<br><br>
	Custom response tilte and text<br>
	<input type="text" name="response_title" size="30" max="25"><br>
	<textarea name="response_text" rows="10" cols="50"></textarea>
	<input type="checkbox" name="add_to_can" value="yes">Add this custom response to to canned responses
	<br>
	<INPUT type="submit" name="submit" value="Delete">
	</FORM>
	</td></tr>
	</table>

	<P>
	<B>License: <?php echo $row_grp['license']; ?></B>

	<BR><B>Home Box: <?php print $row_grp['unix_box']; ?></B>
	<BR><B>HTTP Domain: <?php print $row_grp['http_domain']; ?></B>

	<br>
	&nbsp;
	<?php
	$res_cat = db_query("SELECT category.category_id AS category_id,"
		. "category.category_name AS category_name FROM category,group_category "
		. "WHERE category.category_id=group_category.category_id AND "
		. "group_category.group_id=$row_grp[group_id]");
	while ($row_cat = db_fetch_array($res_cat)) {
		print "<br>$row_cat[category_name] "
		. "<A href=\"groupedit.php?group_id=$row_grp[group_id]&group_idrm=$row_grp[group_id]&form_catrm=$row_cat[category_id]\">"
		. "[Remove from Category]</A>";
	}

	// ########################## OTHER INFO

	print "<P><B>Other Information</B>";
	print "<P>Unix Group Name: $row_grp[unix_group_name]";

	print "<P>Submitted Description:<blockquote>$row_grp[register_purpose]</blockquote>";

	if ($row_grp[license]=="other") {
		print "<P>License Other: <blockquote>$row_grp[license_other]</blockquote>";
	}
	
	if ($row_grp[status_comment]) {
		print "<P>Pending reason: <font color=red>$row_grp[status_comment]</font>";
	}

	echo "<P><HR><P>";

}

//list of group_id's of pending projects
$arr=result_column_to_array($res_grp,0);
$group_list=implode($arr,',');

echo '
	<CENTER>
	<FORM action="'.$PHP_SELF.'" method="POST">
	<INPUT TYPE="HIDDEN" NAME="action" VALUE="activate">
	<INPUT TYPE="HIDDEN" NAME="list_of_groups" VALUE="'.$group_list.'">
	<INPUT type="submit" name="submit" value="Approve All On This Page">
	</FORM>
	';
	
site_admin_footer(array());

?>
