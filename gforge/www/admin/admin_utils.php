<?php
/**
  *
  * Module of support routines for Site Admin
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


function site_admin_header($params) {
	GLOBAL $HTML;
	global $feedback;
	$HTML->header($params);
	echo html_feedback_top($feedback);
	echo '<H2>'.$GLOBALS['sys_name'].' Site Admin</H2>
	<P><A HREF="/admin/">Site Admin Home</A> |
	<A HREF="/news/admin/">Site News Admin</A> |
	<A HREF="/stats/">Site Stats</A>
	<P>';
}

function site_admin_footer($vals=0) {
	GLOBAL $HTML;
	echo html_feedback_bottom($feedback);
	$HTML->footer(array());
}

function show_group_type_box($name='group_type',$checked_val='xzxz') {
	$result=db_query("SELECT * FROM group_type");
	return html_build_select_box ($result,'group_type',$checked_val,false);
}

// Return group_id by group name. Should be in Group.class
function seek_gid($g_unixname) {

	$gname = strtolower($g_unixname);

	$res_vh = db_query("
		SELECT *
		FROM groups
		WHERE unix_group_name='$gname'
	");

	if (db_numrows($res_vh) < 1) {

	        return 0;

	} else {

		$row_db = db_fetch_array($res_vh);
		return $row_db['group_id'];

	}

}

?>
