<?php
/**
  *
  * Site Admin page for approving/rejecting new projects
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


// Show no more pending projects per page than specified here
$LIMIT = 50;

require_once('pre.php');	 
require_once('common/include/vars.php');
require_once('common/include/account.php');
require_once('www/include/proj_email.php');
require_once('www/include/canned_responses.php');
require_once('www/admin/admin_utils.php');
require_once('www/project/admin/project_admin_utils.php');
require_once('common/tracker/ArtifactTypes.class');
require_once('common/forum/Forum.class');

session_require(array('group'=>'1','admin_flags'=>'A'));

function activate_group($group_id) {
	global $feedback;

	$group =& group_get_object($group_id);

	if (!$group || !is_object($group)) {
		$feedback .= 'Error creating group object<br> ';
		return false;
	} else if ($group->isError()) {
		$feedback .= $group->getErrorMessage();
		return false;
	}

	$feedback .= '<BR>Approving Group: '.$group->getUnixName().' ';

	if (!$group->approve(session_get_user())) {
		$feedback .= $group->getErrorMessage();
		return false;
	}
	
	return true;
}

if ($action=='activate') {

	$groups=explode(',', $list_of_groups);
	array_walk($groups, 'activate_group');

} else if ($action=='delete') {

	$group =& group_get_object($group_id);
	exit_assert_object($group, 'Group');

	if (!$group->setStatus(session_get_user(), 'D')) {
		exit_error(
			'Error during group rejection',
			$this->getErrorMessage()
		);
	}

	$group->addHistory('rejected', 'x');

	// Determine whether to send a canned or custom rejection letter and send it
	if( $response_id == 100 ) {

                $group->sendRejectionEmail(0, $response_text);

		if( $add_to_can ) {
			add_canned_response($response_title, $response_text);
		}
	} else {
		$group->sendRejectionEmail($response_id);
	}
}


site_admin_header(array('title'=>'Approving Pending Projects'));

// get current information
$res_grp = db_query("SELECT * FROM groups WHERE status='P'", $LIMIT);

$rows = db_numrows($res_grp);

if ($rows < 1) {
	print "<h1>None Found</h1>";
	print "<p>No Pending Projects to Approve</p>";
	site_admin_footer(array());
	exit;
}

if ($rows > $LIMIT) {
	print "<p>Pending projects: $LIMIT+ ($LIMIT shown)</p>";
} else {
	print "<p>Pending projects: $rows</p>";
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
	<?php print get_canned_responses(); ?> <a href="responses_admin.php">(manage responses)</a>
	<br><br>
	Custom response title and text<br>
	<input type="text" name="response_title" size="30" max="25"><br>
	<textarea name="response_text" rows="10" cols="50"></textarea>
	<input type="checkbox" name="add_to_can" value="yes">Add this custom response to canned responses
	<br>
	<INPUT type="submit" name="submit" value="Reject">
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
