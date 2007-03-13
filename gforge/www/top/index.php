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

$HTML->header(array('title'=>_('Top Projects')));
?>

<p><strong><?php printf(_('Top %1$s project'), $GLOBALS['sys_name']); ?></strong></P>

<p><?php printf(_('We track many project usage statistics on %1$s, and display here the top ranked projects in several categories.'), $GLOBALS['sys_name']); ?>
<ul>
<li><a href="mostactive.php?type=week"><?php echo _('Most Active This Week'); ?></a>
<li><a href="mostactive.php"><?php echo _('Most Active All Time'); ?></a>
<br />&nbsp;
<li><a href="toplist.php?type=downloads"><?php echo _('Top Downloads'); ?></a>
<br />&nbsp;
<li><a href="toplist.php?type=pageviews_proj"><?php echo _('Top Project Pageviews'); ?></a> 
<li><a href="toplist.php?type=forumposts_week"><?php echo _('Top Forum Post Counts'); ?></a>

<!--
<li><a href="toplist.php?type=downloads_week"><?php echo _('Top Downloads (Past 7 Days)'); ?></a>
<li><a href="topusers.php"><?php echo _('Highest Ranked Users'); ?></a>
-->

</ul>

<?php
$HTML->footer(array());
?>
