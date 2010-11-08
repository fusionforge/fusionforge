<?php
/**
 * Sitewide Statistics
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'stats/site_stats_utils.php';

session_require_global_perm ('forge_stats', 'read') ;

$HTML->header(array('title'=>sprintf(_('%1$s Sitewide Statistics Graphs'), forge_get_config ('forge_name'))));
print '<h1>'._('Sitewide Statistics Graphs').'</h1>' . "\n";
?>

<hr />

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr align="center">
<td><a href="index.php"><?php echo _('OVERVIEW STATS'); ?></a></td>
<td><a href="projects.php"><?php echo _('PROJECT STATS'); ?></a></td>
<td><strong><?php echo _('SITE GRAPHS'); ?></strong></td>
</tr>
</table>

<hr />

<p style="text-align: center;">
<img src="views_graph.php?monthly=1" alt="" />
</p>
<p style="text-align: center;">
<img src="users_graph.php" alt="" />
</p>

<?php
$HTML->footer( array() );
?>
