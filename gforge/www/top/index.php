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

<P><B>Top <?php echo $GLOBALS['sys_name']; ?> Projects</B></P>

<P>
<?php echo $Language->getText('top','about_blurb', $GLOBALS[sys_name]); ?>

<UL>
<LI><A href="mostactive.php?type=week"><?php echo $Language->getText('top','active_weekly'); ?></A>
<LI><A href="mostactive.php"><?php echo $Language->getText('top','active_all_time'); ?></A>
<BR>&nbsp;
<LI><A href="toplist.php?type=downloads"><?php echo $Language->getText('top','downloads'); ?></A>
<LI><A href="toplist.php?type=downloads_week"><?php echo $Language->getText('top','download_7_days'); ?></A>
<BR>&nbsp;
<LI><A href="toplist.php?type=pageviews_proj"><?php echo $Language->getText('top','pageviews', $GLOBALS[sys_name]); ?>
<BR>&nbsp;
<LI><A href="toplist.php?type=forumposts_week"><?php echo $Language->getText('top','forum_posts'); ?></A>
<BR>&nbsp;
<LI><A href="topusers.php"><?php echo $Language->getText('top','highest_ranked_users'); ?></A>
</UL>

<?php
$HTML->footer(array());
?>
