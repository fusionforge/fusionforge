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

<P><B>Top GForge Projects</B></P>

<P>We track many project usage statistics on GForge, and display here
the top ranked projects in several categories.

<UL>
<LI><A href="mostactive.php?type=week">Most Active This Week</A>
<LI><A href="mostactive.php">Most Active All Time</A>
<BR>&nbsp;
<LI><A href="toplist.php?type=downloads">Top Downloads</A>
<LI><A href="toplist.php?type=downloads_week">Top Downloads (Past 7 Days)</A>
<BR>&nbsp;
<LI><A href="toplist.php?type=pageviews_proj">Top Project Pageviews</A> -
Measured by impressions of the GForge 'button' logo
<BR>&nbsp;
<LI><A href="toplist.php?type=forumposts_week">Top Forum Post Counts</A>
<BR>&nbsp;
<LI><A href="topusers.php">Highest Ranked Users</A>
</UL>

<?php
$HTML->footer(array());
?>
