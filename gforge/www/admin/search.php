<?php
/**
  *
  * Site Admin generic user/group search page
  *
  * This is the single page for searching/selection of users/groups for
  * Site Admin. Currently, it supports querying by (sub)string match in
  * string user/group properties (names, fullnames, email) and status.
  * If new search criteria will be required, they should be added here,
  * not any other (new) page.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

site_admin_header(array('title'=>'Admin Search Results'));

function format_name($name, $status) {
	if ($status == 'D') {
		return "<b><strike>$name</strike></b>";
	} else if ($status == 'S') {
		return "<b><u>$name</u></b>";
	} else if ($status == 'H') {
		return "<b><u>$name</u></b>";
	} else if ($status == 'P') {
		return "<b><i>$name</i></b>";
	} else if ($status == 'I') {
		return "<b><i>$name</i></b>";
	}

	return $name;
}

/*
	Main code
*/

if ($search == "") {

  exit_error("Refusing to display whole DB","That would display whole DB.  Please use a CLI query if you wish to do this.");

}

if ($substr) {
	$search = "%$search%";
}


if ($usersearch) {

	$result = db_query("
	    SELECT DISTINCT * 
	    FROM users
	    WHERE user_id ILIKE '$search'
	    OR user_name ILIKE '%$search%'
	    OR email ILIKE '%$search%'
	    OR realname ILIKE '%$search%'
	"); 

	print '<p><b>User search with criteria "<i>'.$search.'</i>": '
	      .db_numrows($result).' matches.</b></p>';

	if (db_numrows($result) < 1) {
		echo db_error();
	} else {


		$title=array();
		$title[]='ID';
		$title[]='Username';
		$title[]='Real Name';
		$title[]='Email';
		$title[]='Member since';
		$title[]='Status (Web/Unix)';
					 
		echo html_build_list_table_top($title);

		while ($row = db_fetch_array($result)) {
			print '
				<tr bgcolor="'.html_get_alt_row_color($i++).'">
				<td><a href="useredit.php?user_id='.$row['user_id'].'">'.$row['user_id'].'</a></td>
				<td>'.format_name($row['user_name'], $row['status']).'</td>
				<td>'.$row['realname'].'</td>
				<td>'.$row['email'].'</td>
				<td>'.date($sys_datefmt, $row['add_date']).'</td>
				<td align="center">'.format_name($row['status'].'/'.$row['unix_status'], $row['status']).'</td>
				</tr>
			'; 
		}
		print "</table>";

	} 
} // end if ($usersearch)


if ($groupsearch) {

	if ($status) {
		$crit_sql  .= " AND status='$status'";
		$crit_desc .= " status=$status";
	}
	if (isset($is_public)) {
		$crit_sql  .= " AND is_public='$is_public'";
		$crit_desc .= " is_public=$is_public";
	}

	$result = db_query("
		SELECT DISTINCT *
		FROM groups
		WHERE (group_id ILIKE '%$search%'
		OR unix_group_name ILIKE '%$search%'
		OR group_name ILIKE '%$search%')
		$crit_sql
	"); 

	if ($crit_desc) {
		$crit_desc = "($crit_desc )";
	}
	print '<p><b>Group search with criteria "<i>'.$search.'</i>" '.$crit_desc.': '
	      .db_numrows($result).' matches.</b></p>';

	if (db_numrows($result) < 1) {
		echo db_error();
	} else {

		$title=array();
		$title[]='ID';
		$title[]='Unix Name';
		$title[]='Full Name';
		$title[]='Registered';
		$title[]='Status';

		echo html_build_list_table_top($title);

		while ($row = db_fetch_array($result)) {

			$extra_status = "";
			if (!$row['is_public']) {
				$extra_status = "/PRV";
			}
			
			print '
				<tr bgcolor="'.html_get_alt_row_color($i++).'">
				<td><a href="groupedit.php?group_id='.$row['group_id'].'">'.$row['group_id'].'</a></td>
				<td>'.format_name($row['unix_group_name'], $row['status']).'</td>
				<td>'.$row['group_name'].'</td>
				<td>'.date($sys_datefmt, $row['register_time']).'</td>
				<td align="center">'.format_name($row['status'].$extra_status, $row['status']).'</td>
				</tr>
			';
					
		}
		
		print "</table>";

	} 


} //end if($groupsearch)

site_admin_footer(array());

?>
