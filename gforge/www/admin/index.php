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

site_admin_header(array('title'=>$GLOBALS['system_name']." Admin"));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');

?>
 
<p>Administrative Functions
<p><i><b>Warning!</b> These functions currently have minimal error checking,
if any. They are fine to play with but may not act as expected if you leave
fields blank, etc... Also, navigating the admin functions with the 
<b>back</b> button is highly unadvised.</i>

<p><B>User/Group/Category Maintenance</B>
<ul>
<li><a href="userlist.php">Display Full User List/Edit Users</a>&nbsp;&nbsp;
<li>Display Users Beginning with : 
<?php
	for ($i=0; $i < count($abc_array); $i++) {
		echo "<a href=\"userlist.php?user_name_search=$abc_array[$i]\">$abc_array[$i]</a>|";
	}
?>
<br>
Search <i>(email,username,realname,userid)</i>:
<br>
<form name="usersrch" action="search.php" method="POST">
  <input type="text" name="search">
  <input type="hidden" name="usersearch" value="1">
  <input type="submit" value="get">
</form>
<BR>&nbsp;
<li><a href="grouplist.php">Display Full Group List/Edit Groups</a>

<li>Display Groups Beginning with : 
<?php
	for ($i=0; $i < count($abc_array); $i++) {
		echo "<a href=\"grouplist.php?group_name_search=$abc_array[$i]\">$abc_array[$i]</a>|";
	}
?>
<br>
Search <i>(groupid,groupunixname,groupname)</i>:
<br>
<form name="gpsrch" action="search.php" method="POST">
  <input type="text" name="search">
  <input type="hidden" name="groupsearch" value="1">
  <input type="submit" value="get">
</form>

<p>


<LI>Groups in <a href="grouplist.php?status=I"><B>I</B> (incomplete) Status</A>
<LI>Groups in <a href="approve-pending.php"><B>P</B> (pending) Status</A> <i>(New Project Approval)</i>
<LI>Groups in <a href="grouplist.php?status=D"><B>D</B> (deleted) Status</A>
</ul>

<p><b>Trove</b>
<ul>
<li><a href="trove/trove_cat_list.php">Display Trove Map</a>
<li><a href="trove/trove_cat_add.php">Add to the Trove Map</a>
</ul>

<P><B>Statistics</B>
<ul>
<li><a href="lastlogins.php">View Most Recent Logins</A>
</ul>

<P><B>Site Utilities</B>
<UL>
<LI><A href="massmail.php">Mail Engine for SourceForge Subscribers (MESS)</A>
<LI><A HREF="add_language.php">Add Supported Language</A>
</UL>

<P><B>Site Stats</B>
<?php
	$res=db_query("SELECT count(*) AS count FROM users WHERE status='A'");
	$row = db_fetch_array($res);
	print "<P>Registered active site users: <B>$row[count]</B>";

	$res=db_query("SELECT count(*) AS count FROM groups");
	$row = db_fetch_array($res);
	print "<BR>Registered projects: <B>$row[count]</B>";

	$res=db_query("SELECT count(*) AS count FROM groups WHERE status='A'");
	$row = db_fetch_array($res);
	print "<BR>Registered/hosted projects: <B>$row[count]</B>";

	$res=db_query("SELECT count(*) AS count FROM groups WHERE status='P'");
	$row = db_fetch_array($res);
	print "<BR>Pending projects: <B>$row[count]</B>";
site_admin_footer(array());

?>
