<?php
/**
 * Sitewide Statistics
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'stats/site_stats_utils.php';

session_require_global_perm ('forge_stats', 'read') ;

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotpluginhighlighter();

$HTML->header(array('title'=>sprintf(_('%s Sitewide Statistics Graphs'), forge_get_config ('forge_name'))));
?>

<hr />

<table class="fullwidth">
<tr class="align-center">
<td><a href="index.php"><?php echo _('OVERVIEW STATS'); ?></a></td>
<td><a href="projects.php"><?php echo _('PROJECT STATS'); ?></a></td>
<td><strong><?php echo _('SITE GRAPHS'); ?></strong></td>
</tr>
</table>

<hr />
<p class="information" ><?php echo _('Displayed data: only last 24 months.'); ?></p>
<p class="align-center">
<?php views_graph(1); ?>
</p>
<p class="align-center">
<?php users_graph(); ?>
</p>

<?php
$HTML->footer( array() );
