<?php
/**
  * SourceForge Site Admin main page
  *
  * This pages lists all global administration facilities for the
  * site, including user/group properties editing, maintanance of
  * site meta-information (Trove maps, metadata for file releases),
  * etc.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  */


require_once('pre.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

site_admin_header(array('title'=>"Site Admin"));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');


?>
 
<p><strong>User Maintenance</strong>
<ul>
	<li><a href="userlist.php">Display Full User List/Edit Users</a>&nbsp;&nbsp;
	<li>Display Users Beginning with : 
<?php
	for ($i=0; $i < count($abc_array); $i++) {
		echo "<a href=\"search.php?usersearch=1&search=$abc_array[$i]%\">$abc_array[$i]</a>|";
	}
?>
<br />
<form name="usersrch" action="search.php" method="POST">
Search <em>(userid, username, realname, email)</em>:
  <input type="text" name="search">
  <input type="hidden" name="substr" value="1">
  <input type="hidden" name="usersearch" value="1">
  <input type="submit" value="get">
</form>
</ul>
<p>
<strong>Group Maintenance</strong>
<p>
<ul>
	<li><a href="grouplist.php">Display Full Group List/Edit Groups</a>

<li>Display Groups Beginning with : 
<?php
	for ($i=0; $i < count($abc_array); $i++) {
		echo "<a href=\"search.php?groupsearch=1&search=$abc_array[$i]%\">$abc_array[$i]</a>|";
	}
?>
<form name="gpsrch" action="search.php" method="POST">
Search <em>(groupid, group unix name, full name)</em>:
  <input type="text" name="search">
  <input type="hidden" name="substr" value="1">
  <input type="hidden" name="groupsearch" value="1">
  <input type="submit" value="get">
</form>

<p>


<li><a href="/register/">Register New Project</a>
<li>Groups with <a href="approve-pending.php"><strong>P</strong> (pending) Status</a> <em>(New Project Approval)</em>
<li>Groups with <a href="search.php?groupsearch=1&search=%&status=D"><strong>D</strong> (deleted) Status</a>
<li><a href="search.php?groupsearch=1&search=%&is_public=0">Private Groups </a>
</ul>

<p>
<strong>News</strong>
<p>
<ul>
	<li><a href="/news/admin/">Approve/Reject</a> Front-page news
</ul>

<p>
<strong>Stats</strong>
<p>
<ul>
	<li><a href="/stats/">Site-Wide Stats</a>
</ul>

<p>
<strong>Trove Project Tree</strong>
<ul>
	<li><a href="trove/trove_cat_list.php">Display Trove Map</a>
	<li><a href="trove/trove_cat_add.php">Add to the Trove Map</a>
</ul>

<p><strong>Site Utilities</strong>
<ul>
	<li><a href="massmail.php">Mail Engine for <?php echo $GLOBALS['sys_name']; ?> Subscribers</a>
	<li><a href="unsubscribe.php"><?php echo $GLOBALS['sys_name']; ?> Site Mailings Maintenance</a>
	<li><a href="edit_supported_languages.php">Add, Delete, or Edit Supported Languages</a>
	<li><a href="edit_frs_filetype.php">Add, Delete, or Edit File Types</a>
	<li><a href="edit_frs_processor.php">Add, Delete, or Edit Processors</a>
	<li><a href="edit_frs_theme.php">Add, Delete, or Edit Themes</a>
	<li><a href="loadtabfiles.php">Translation file tool</a>
</ul>

<p>
<strong>Global Admin Tools / Mass Insert Tools</strong>
<ul>
	<li><a href="vhost.php">Virtual Host Administration Tool</a>
	<li><a href="database.php">Project Database Administration</a>
</ul>

<p><strong>Quick Site Statistics</strong></p>

<?php

$res=db_query("SELECT count(*) AS count FROM users WHERE status='A'");
$row = db_fetch_array($res);
print "<p>Active site users: <strong>$row[count]</strong>";

$res=db_query("SELECT count(*) AS count FROM groups");
$row = db_fetch_array($res);
print "<br />Registered projects: <strong>$row[count]</strong>";

$res=db_query("SELECT count(*) AS count FROM groups WHERE status='A'");
$row = db_fetch_array($res);
print "<br />Active projects: <strong>$row[count]</strong>";

$res=db_query("SELECT count(*) AS count FROM groups WHERE status='P'");
$row = db_fetch_array($res);
print "<br />Pending projects: <strong>$row[count]</strong>";

site_admin_footer(array());

?>
