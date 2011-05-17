<?php
/**
 * Top-Statistics: Main page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$HTML->header(array('title'=>sprintf(_('Top %1$s Projects'), forge_get_config ('forge_name'))));
?>

<p><?php printf(_('We track many project usage statistics on %1$s, and display here the top ranked projects in several categories.'), forge_get_config ('forge_name')); ?></p>
<ul>
<li><a href="mostactive.php?type=week"><?php echo _('Most Active This Week'); ?></a></li>
<li><a href="mostactive.php"><?php echo _('Most Active All Time'); ?></a></li>
</ul>
<ul>
<li><a href="toplist.php?type=downloads"><?php echo _('Top Downloads'); ?></a></li>
</ul>
<ul>
<li><a href="toplist.php?type=pageviews_proj"><?php echo _('Top Project Pageviews'); ?></a></li> 
<li><a href="toplist.php?type=forumposts_week"><?php echo _('Top Forum Post Counts'); ?></a></li>

<!--
<li><a href="toplist.php?type=downloads_week"><?php echo _('Top Downloads (Past 7 Days)'); ?></a></li>
<li><a href="topusers.php"><?php echo _('Highest Ranked Users'); ?></a></li>
-->

</ul>

<?php
$HTML->footer(array());
?>
