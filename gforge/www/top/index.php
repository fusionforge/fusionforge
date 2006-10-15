<?php
/**
 * GForge Top-Statistics: Main page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
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
<li><a href="toplist.php?type=pageviews_proj"><?php echo $Language->getText('top','pageviews'); ?></a> 
<li><a href="toplist.php?type=forumposts_week"><?php echo $Language->getText('top','forum_posts'); ?></a>

<!--
<li><a href="toplist.php?type=downloads_week"><?php echo $Language->getText('top','downloads_7_days'); ?></a>
<li><a href="topusers.php"><?php echo $Language->getText('top','highest_ranked_users'); ?></a>
-->

</ul>

<?php
$HTML->footer(array());
?>
