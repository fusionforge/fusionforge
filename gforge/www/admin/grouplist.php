<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";
require($DOCUMENT_ROOT.'/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

site_admin_header(array('title'=>$GLOBALS['system_name'].$Language->getText('admin_grouplist','group_list')));

// start from root if root not passed in
if (!$form_catroot) {
	$form_catroot = 1;
}

//CB removed from 2.6 and 2.5 was link to a page saying to use new project
//print "<br /><a href=\"groupedit-add.php\">[Add Group]</a>";
print "<p>".$GLOBALS['system_name'].$Language->getText('admin_grouplist','group_list_for_category');

$sortorder = $_GET['sortorder'];
if ($sortorder == null) {
	$sortorder = "group_name";
}
if ($form_catroot == 1) {

	if (isset($group_name_search)) {
		print "<strong>" .$Language->getText('admin_grouplist','groups_that_begin_with'). "$group_name_search</strong>\n";
		$res = db_query("SELECT group_name,unix_group_name,group_id,is_public,status,license "
			. "FROM groups WHERE group_name ~* '^$group_name_search%' "
			. ($form_pending?"AND WHERE status='P' ":"")
			. " ORDER BY group_name");
	} else {
		print "<strong>All Categories</strong>\n";
		$res = db_query("SELECT group_name,unix_group_name,group_id,is_public,status,license "
			. "FROM groups "
			. ($status?"WHERE status='$status' ":"")
			. "ORDER BY $sortorder");
	}
} else {
	print "<strong>" . category_fullname($form_catroot) . "</strong>\n";

	$res = db_query("SELECT groups.group_name,groups.unix_group_name,groups.group_id,"
		. "groups.is_public,"
		. "groups.license,"
		. "groups.status "
		. "FROM groups,group_category "
		. "WHERE groups.group_id=group_category.group_id AND "
		. "group_category.category_id=$GLOBALS[form_catroot] "
		. "ORDER BY $sortorder");
}
?>
</p>
<table width="100%" border="1">
<tr>
<td><strong>
<a href="?sortorder=group_name">
<?php echo $Language->getText('admin_grouplist','group_name_click_to_edit'); ?>
</a>
</strong></td>
<td><strong>
<a href="?sortorder=unix_group_name">
<?php echo $Language->getText('admin_grouplist','unix_name'); ?>
</a>
</strong></td>
<td><strong>
<a href="?sortorder=status">
<?php echo $Language->getText('admin_grouplist','status'); ?>
</a>
</strong></td>
<td><strong>
<a href="?sortorder=is_public">
<?php echo $Language->getText('admin_grouplist','public'); ?>
</a>
</strong></td>
<td><strong>
<a href="?sortorder=license">
<?php echo $Language->getText('admin_grouplist','license'); ?>
</a>
</strong></td>
<td><strong>
<?php echo $Language->getText('admin_grouplist','members'); ?>
</strong></td>
</tr>

<?php
while ($grp = db_fetch_array($res)) {
	print "<tr>";
	print "<td><a href=\"groupedit.php?group_id=$grp[group_id]\">$grp[group_name]</a></td>";
	print "<td>$grp[unix_group_name]</td>";
	print "<td>$grp[status]</td>";
	print "<td>$grp[is_public]</td>";
	print "<td>$grp[license]</td>";

	// members
	$res_count = db_query("SELECT user_id FROM user_group WHERE group_id=$grp[group_id]");
	print "<td>" . db_numrows($res_count) . "</td>";

	print "</tr>\n";
}
?>

</table>

<?php
site_admin_footer(array());

?>
