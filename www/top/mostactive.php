<?php
/**
 *
 * Top-Statistics: Most active Users
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

require ('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/Stats.class.php';

$offset = getIntFromRequest('offset');
$type = getStringFromRequest('type');
$limit = getStringFromRequest('limit');

if (!$offset || $offset < 0) {
	$offset=0;
}

if ($type == 'week') {
	$title = _('Most Active This Week');
} else {
	$title = _('Most Active All Time');
}

$HTML->header(array('title'=>$title));

print '<p><em>('._('Updated Daily').')</em></p>

<p>'.util_make_link ('/top/','['._('View Other Top Categories').']').'</p>';

$arr=array(_('Rank'),_('Project name'),_('Percentile'));

echo $HTML->listTableTop($arr);

$stats = new Stats();
$res_top = $stats->getMostActiveStats($type, $offset);
$rows=db_numrows($res_top);

$i=0;
while ($row_top = db_fetch_array($res_top)) {
	if (!forge_check_perm ('project_read', $row_top['group_id'])) {
		continue ;
	}
	$i++;
	print '
	<tr '. $HTML->boxGetAltRowStyle($i) .'>
		<td>&nbsp;&nbsp;'.$row_top['ranking'].'
		</td>
		<td>'.util_make_link_g (strtolower($row_top['unix_group_name']),$row_top['group_id'],$row_top['group_name']).'
		</td>
		<td align="right">'.substr($row_top['percentile'],0,5).'%</td>
	</tr>';
}

if ($i<$rows) {
	if ($offset>0) {
		print '
	<tr class="tablegetmore">
		<td>'.util_make_link ('/top/mostactive.php?type='.$type.'&offset='.($offset-$LIMIT),'<strong><-- '._('More').'</strong>');
	} else {
		print '&nbsp;';
	}
	print '
		</td>
		<td>&nbsp;</td>
		<td align="RIGHT">'.util_make_link ('/top/mostactive.php?type='.$type.'&offset='.($offset+$LIMIT),'<strong>'._('More').' --></strong>').'
		</td>
	</tr>';
}

echo $HTML->listTableBottom();

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
