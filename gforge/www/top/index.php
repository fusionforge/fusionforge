<?php
/**
  *
  * SourceForge Top-Statistics main page
  *
  * This page gives links to pages which show project/users
  * highest ranked by defferent criteria, like doenloads, rating, etc.
  * Most of these pages show dynamics of changes also.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

$HTML->header(array('title'=>'Top Project Listings'));
?>

<p><strong>Top <?php echo $GLOBALS['sys_name']?>  Projects</strong></P>

<p>We track many project usage statistics on <?php echo $GLOBALS['sys_name']?>, and display here
the top ranked projects in several categories.

<ul>
<li><a href="mostactive.php?type=week">Most Active This Week</a>
<li><a href="mostactive.php">Most Active All Time</a>
<br />&nbsp;
<li><a href="toplist.php?type=downloads">Top Downloads</a>
<li><a href="toplist.php?type=downloads_week">Top Downloads (Past 7 Days)</a>
<br />&nbsp;
<li><a href="toplist.php?type=pageviews_proj">Top Project Pageviews</a> -
Measured by impressions of the <?php echo $GLOBALS['sys_name']?> 'button' logo
<br />&nbsp;
<li><a href="toplist.php?type=forumposts_week">Top Forum Post Counts</a>
<br />&nbsp;
<li><a href="topusers.php">Highest Ranked Users</a>
</ul>

<?php
$HTML->footer(array());
?>
