<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    
session_require(array('group'=>'1','admin_flags'=>'A'));
$HTML->header(array('title'=>$GLOBALS['system_name'].': User List'));

/**
 * performAction() - Updates the indicated user status
 *
 * @param               string  $newStatus - the new user status
 * @param               string  $statusString - the status string to display
 * @param               string  $user_id - the user id to act upon
 */
function performAction($newStatus, $statusString, $user_id) {
	db_query("UPDATE users set status='".$newStatus."' WHERE user_id='".$user_id."'");
	echo "<h2>User updated to ".$statusString." status</h2>";
}

function show_users_list ($result) {
	echo '<p>Key:
		<font color="#00ff00">Active</font>
		<font color="grey">Deleted</font>
		<font color="red">Suspended</font>
		(*)Pending</p>
		<p>
		<table width="100%" cellspacing="0" cellpadding="0" border="1">';

	while ($usr = db_fetch_array($result)) {
		print "\n<tr><td bgcolor=\"";
		if ($usr[status] == 'A') print "#00ff00";
		if ($usr[status] == 'D') print "grey";
		if ($usr[status] == 'S') print "red";
		print "\"><a href=\"useredit.php?user_id=$usr[user_id]\">";
		if ($usr[status] == 'P') print "*";
		print "$usr[user_name]</a>";
		print "</td>";
		print "\n<td><a href=\"/developer/?form_dev=$usr[user_id]\">[DevProfile]</a></td>";
		print "\n<td><a href=\"userlist.php?action=activate&user_id=$usr[user_id]\">[Activate]</a></td>";
		print "\n<td><a href=\"userlist.php?action=delete&user_id=$usr[user_id]\">[Delete]</a></td>";
		print "\n<td><a href=\"userlist.php?action=suspend&user_id=$usr[user_id]\">[Suspend]</a></td>";
		print "</td>";
	}
	print "</table></p>";

}

// Administrative functions

if ($action=='delete') {
	performAction('D', "DELETED", $user_id);
} else if ($action=='activate') {
	performAction('A', "ACTIVE", $user_id);
} else if ($action=='suspend') {
	performAction('S', "SUSPENDED", $user_id);
}

/*
	Add a user to this group
*/
if ($action=='add_to_group') {
	db_query("INSERT INTO user_group (user_id,group_id) VALUES ($user_id,$group_id)");
}

/*
	Show list of users
*/
print "<p>User list for group: ";
if (!$group_id) {
	print "<strong>All Groups</strong>";
	print "\n<p>";
	
	if ($user_name_search) {
	  // [RM] LIKE is case-sensitive, and we don't want that
	  //		$result = db_query("SELECT user_name,user_id,status FROM users WHERE user_name LIKE '$user_name_search%' ORDER BY user_name");
		$result = db_query("SELECT user_name,user_id,status FROM users WHERE user_name ~* '^$user_name_search' ORDER BY user_name");
	} else {
		$result = db_query("SELECT user_name,user_id,status FROM users ORDER BY user_name");
	}
	show_users_list ($result);
} else {
	/*
		Show list for one group
	*/
	print "<strong>" . group_getname($group_id) . "</strong>";
	
	print "\n<p>";

	$result = db_query("SELECT users.user_id AS user_id,users.user_name AS user_name,users.status AS status "
		. "FROM users,user_group "
		. "WHERE users.user_id=user_group.user_id AND "
		. "user_group.group_id=$group_id ORDER BY users.user_name");
	show_users_list ($result);

	/*
        	Show a form so a user can be added to this group
	*/
	?>
	<hr />
	<p>
	<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="add_to_group">
	<input name="user_id" type="TEXT" value="">
	<p>
	Add User to Group (<?php print group_getname($group_id); ?>):
	<br />
	<input type="hidden" name="group_id" value="<?php print $group_id; ?>">
	<p>
	<input type="submit" name="Submit" value="Submit">
	</form>

	<?php	
}

$HTML->footer(array());

?>
