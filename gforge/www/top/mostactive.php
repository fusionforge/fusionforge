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

require ('pre.php');    
require_once ('common/include/Stats.class');    

$offset = getIntFromRequest('offset');
$type = getStringFromRequest('type');
$limit = getStringFromRequest('limit');

if (!$offset || $offset < 0) {
	$offset=0;
}

if ($type == 'week') {
	$title = $Language->getText('top','active_weekly');
} else {
	$title = $Language->getText('top','active_all_time');
}

$HTML->header(array('title'=>$title));

print '<p><h3>'.$title.'</h3>
<br /><em>('.$Language->getText('top_mostactive','updated_daily').')</em>

<p><a href="/top/">['.$Language->getText('top','view_other_top_category').']</a>';

$arr=array($Language->getText('top_mostactive','rank'),$Language->getText('top_mostactive','project_name'),$Language->getText('top_mostactive','percentile'));

echo $HTML->listTableTop($arr);

$stats = new Stats();
$res_top = $stats->getMostActiveStats($type, $offset);
$rows=db_numrows($res_top);

while ($row_top = db_fetch_array($res_top)) {
	$i++;
	print '<tr '. $HTML->boxGetAltRowStyle($i) .'><td>&nbsp;&nbsp;'.$i
		.'</td><td><a href="/projects/'. strtolower($row_top['unix_group_name']) .'/">'
		.$row_top['group_name']."</a>"
		.'</td><td align="right">'.substr($row_top['percentile'],0,5).'%</td></tr>';
}

if ($i<$rows) {
print '<tr class="tablegetmore"><td>'.(($offset>0)?'<a href="mostactive.php?type='.$type.'&offset='.($offset-$LIMIT).'"><strong><-- '.$Language->getText('general','more').'</a>':'&nbsp;').'</td>
	<td>&nbsp;</td>
	<td align="RIGHT"><a href="mostactive.php?type='.$type.'&offset='.($offset+$LIMIT).'"><strong>'.$Language->getText('general','more').' --></strong></a></td></tr>';
}

echo $HTML->listTableBottom();

$HTML->footer(array());
?>
