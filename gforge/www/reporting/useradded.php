<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
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

require_once('pre.php');
require_once('common/reporting/report_utils.php');
require_once('common/reporting/Report.class');

$report=new Report();
if ($report->isError()) {
	exit_error('Error',$report->getErrorMessage());
}

if (!$start) {
	$z =& $report->getMonthStartArr();
	$start = $z[count($z)-1];
}

session_require( array('group'=>$sys_stats_group) );

echo report_header('Users Added');

?>
<h3>Users Added</h3>
<p>
<form action="<?php echo $PHP_SELF; ?>" method="get">
<table><tr>
<td><strong>Type:</strong><br /><?php echo report_span_box('SPAN',$SPAN); ?></td>
<td><strong>Start:</strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong>End:</strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
<td><input type="submit" name="submit" value="Refresh"></td>
</tr></table>
</form>
<p>
<img src="useradded_graph.php?<?php echo "SPAN=$SPAN&start=$start&end=$end"; ?>" width="640" height="480">
<p>
<?php

echo report_footer();

?>
