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
 
<p><i><b>Warning!</b> These functions currently have minimal error checking,
if any. They are fine to play with but may not act as expected if you leave
fields blank, etc... Also, navigating the admin functions with the 
<b>back</b> button is highly unadvised.</i>

<p><B>User/Group Maintenance</B>
<ul>
<li><a href="userlist.php">Display Full User List/Edit Users</a>&nbsp;&nbsp;
<li>Display Users Beginning with : 
<?php
	for ($i=0; $i < count($abc_array); $i++) {
		echo "<a href=\"search.php?usersearch=1&search=$abc_array[$i]%\">$abc_array[$i]</a>|";
	}
?>
<br>
<form name="usersrch" action="search.php" method="POST">
Search <i>(userid, username, realname, email)</i>:
  <input type="text" name="search">
  <input type="hidden" name="substr" value="1">
  <input type="hidden" name="usersearch" value="1">
  <input type="submit" value="get">
</form>
<BR>&nbsp;
<li><a href="grouplist.php">Display Full Group List/Edit Groups</a>

<li>Display Groups Beginning with : 
<?php
	for ($i=0; $i < count($abc_array); $i++) {
		echo "<a href=\"search.php?groupsearch=1&search=$abc_array[$i]%\">$abc_array[$i]</a>|";
	}
?>
<form name="gpsrch" action="search.php" method="POST">
Search <i>(groupid, group unix name, full name)</i>:
  <input type="text" name="search">
  <input type="hidden" name="substr" value="1">
  <input type="hidden" name="groupsearch" value="1">
  <input type="submit" value="get">
</form>

<p>


<LI>Groups with <a href="approve-pending.php"><B>P</B> (pending) Status</A> <i>(New Project Approval)</i>
<LI>Groups with <a href="search.php?groupsearch=1&search=%&status=I"><B>I</B> (incomplete) Status</A>
<LI>Groups with <a href="search.php?groupsearch=1&search=%&status=D"><B>D</B> (deleted) Status</A>
<LI><a href="search.php?groupsearch=1&search=%&is_public=0">Private Groups </A>
</ul>

<p><b><A HREF="/register/">Register New Project</A></b>
<p><b>Trove</b>
<ul>
<li><a href="trove/trove_cat_list.php">Display Trove Map</a>
<li><a href="trove/trove_cat_add.php">Add to the Trove Map</a>
</ul>

<P><B>Site Utilities</B>
<UL>
<LI><A href="massmail.php">Mail Engine for <?php echo $GLOBALS['sys_name']; ?> Subscribers</A>
<LI><A href="unsubscribe.php"><?php echo $GLOBALS['sys_name']; ?> Site Mailings Maintenance</A>
<LI><A HREF="edit_supported_languages.php">Add, Delete, or Edit Supported Languages</A>
<LI><A HREF="edit_frs_filetype.php">Add, Delete, or Edit File Types</A>
<LI><A HREF="edit_frs_processor.php">Add, Delete, or Edit Processors</A>
<LI><A HREF="edit_frs_theme.php">Add, Delete, or Edit Themes</A>
<LI><A HREF="loadtabfiles.php">Translation file tool</A>
</UL>

<P><B>Global Admin Tools / Mass Insert Tools</B>
<UL>
<LI><A HREF="vhost.php">Virtual Host Administration Tool</A>
<LI><A HREF="database.php">Project Database Administration</A>
</UL>

<P><B>Quick Site Statistics</B></P>

<?php

$res=db_query("SELECT count(*) AS count FROM users WHERE status='A'");
$row = db_fetch_array($res);
print "<P>Active site users: <B>$row[count]</B>";

$res=db_query("SELECT count(*) AS count FROM groups");
$row = db_fetch_array($res);
print "<BR>Registered projects: <B>$row[count]</B>";

$res=db_query("SELECT count(*) AS count FROM groups WHERE status='A'");
$row = db_fetch_array($res);
print "<BR>Active projects: <B>$row[count]</B>";

$res=db_query("SELECT count(*) AS count FROM groups WHERE status='P'");
$row = db_fetch_array($res);
print "<BR>Pending projects: <B>$row[count]</B>";

site_admin_footer(array());

?>
