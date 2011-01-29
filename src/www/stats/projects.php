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

$report=getStringFromRequest('report');
$orderby=getStringFromRequest('orderby'); 
$projects=getIntFromRequest('projects'); 
$trovecatid=getIntFromRequest('trovecatid');

session_require_global_perm ('forge_stats', 'read') ;

$HTML->header(array('title'=>sprintf(_('%1$s Site Project Statistical Comparisons'), forge_get_config ('forge_name'))));

?>

<hr />

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr align="center">
<td><a href="index.php"><?php echo _('OVERVIEW STATS'); ?></a></td>
<td><strong><?php echo _('PROJECT STATS'); ?></strong></td>
<td><a href="graphs.php"><?php echo _('SITE GRAPHS'); ?></a></td>
</tr>
</table>

<hr />
	<div align="center">
	<br /><br />
	<?php
	stats_site_projects( $report, $orderby, $projects, $trovecatid );
	?>
	<br /><br />
	</div>
	<?php
$HTML->footer( array() );
?>
