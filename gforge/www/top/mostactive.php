<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');    

if (!$offset || $offset < 0) {
	$offset=0;
}

if ($type == 'week') {
	$sql="SELECT groups.group_name,groups.unix_group_name,groups.group_id,project_weekly_metric.ranking,project_weekly_metric.percentile ".
		"FROM groups,project_weekly_metric ".
		"WHERE groups.group_id=project_weekly_metric.group_id AND ".
		"groups.is_public=1 ".
		"ORDER BY ranking ASC";
	$title = $Language->getText('top','active_weekly');
} else {
	$sql="SELECT groups.group_name,groups.unix_group_name,groups.group_id,project_metric.ranking,project_metric.percentile ".
		"FROM groups,project_metric ".
		"WHERE groups.group_id=project_metric.group_id AND ".
		"groups.is_public=1 ".
		"ORDER BY ranking ASC";
	$title = $Language->getText('top','active_all_time');
}


$HTML->header(array('title'=>$title));

print '<p><strong><FONT size="+1">'.$title.'</FONT></strong>
<br /><em>('.$Language->getText('top_mostactive','updated_daily').')</em>

<p><a href="/top/">['.$Language->getText('top','view_other_top_category').']</a>';

$arr=array($Language->getText('top_mostactive','rank'),$Language->getText('top_mostactive','project_name'),$Language->getText('top_mostactive','percentile'));

echo $HTML->listTableTop($arr);

$res_top = db_query($sql, $LIMIT, $offset);
$rows=db_numrows($res_top);

while ($row_top = db_fetch_array($res_top)) {
	$i++;
	print '<tr '. $HTML->boxGetAltRowStyle($i) .'><td>&nbsp;&nbsp;'.$row_top['ranking']
		.'</td><td><a href="/projects/'. strtolower($row_top['unix_group_name']) .'/">'
		.$row_top['group_name']."</a>"
		.'</td><td align="right">'.$row_top['percentile'].'</td></tr>';
}

if ($i<$rows) {
print '<tr bgcolor="'.$HTML->COLOR_LTBACK2.'"><td>'.(($offset>0)?'<a href="mostactive.php?type='.$type.'&offset='.($offset-$LIMIT).'"><strong><-- '.$Language->getText('general','more').'</strong></a>':'&nbsp;').'</td>
	<td>&nbsp;</td>
	<td align="RIGHT"><a href="mostactive.php?type='.$type.'&offset='.($offset+$LIMIT).'"><strong>'.$Language->getText('general','more').' --></strong></a></td></tr>';
}

echo $HTML->listTableBottom();

$HTML->footer(array());
?>
