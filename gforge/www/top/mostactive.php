<?php
/**
 *
 * GForge Top-Statistics: Most active Users
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version $Id$
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

require ('../env.inc.php');    
require ('pre.php');    
require_once ('common/include/Stats.class.php');    

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

print '<p><h3>'.$title.'</h3>
<br /><em>('._('Updated Daily').')</em>

<p>'.util_make_link ('/top/','['._('View Other Top Categories').']');

$arr=array(_('Rank'),_('Project name'),_('Percentile'));

echo $HTML->listTableTop($arr);

$stats = new Stats();
$res_top = $stats->getMostActiveStats($type, $offset);
$rows=db_numrows($res_top);

$i=0;
while ($row_top = db_fetch_array($res_top)) {
	$i++;
	print '
	<tr '. $HTML->boxGetAltRowStyle($i) .'>
		<td>&nbsp;&nbsp;'.$i.'
		</td>
		<td>'.util_make_link ('/projects/'. strtolower($row_top['unix_group_name']) .'/', $row_top['group_name']).'
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
