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

$HTML->header(array('title'=>$Language->getText('top','title')));
?>

<p><strong><?php echo $Language->getText('top','top_project',$GLOBALS['sys_name']); ?></strong></P>

<p><?php echo $Language->getText('top','about_blurb',$GLOBALS['sys_name']); ?>
<ul>
<li><a href="mostactive.php?type=week"><?php echo $Language->getText('top','active_weekly'); ?></a>
<li><a href="mostactive.php"><?php echo $Language->getText('top','active_all_time'); ?></a>
<br />&nbsp;
<li><a href="toplist.php?type=downloads"><?php echo $Language->getText('top','downloads'); ?></a>
<br />&nbsp;
<li><a href="toplist.php?type=pageviews_proj">Top Project Page Views</a> 
<li><a href="toplist.php?type=forumposts_week"><?php echo $Language->getText('top','forum_posts'); ?></a>

<!--
<li><a href="toplist.php?type=downloads_week"><?php echo $Language->getText('top','downloads_7_days'); ?></a>
<li><a href="topusers.php"><?php echo $Language->getText('top','highest_ranked_users'); ?></a>
-->

</ul>

<?php
$HTML->footer(array());
?>
