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
	$title = 'Most Active This Week';
} else {
	$sql="SELECT groups.group_name,groups.unix_group_name,groups.group_id,project_metric.ranking,project_metric.percentile ".
		"FROM groups,project_metric ".
		"WHERE groups.group_id=project_metric.group_id AND ".
		"groups.is_public=1 ".
		"ORDER BY ranking ASC";
	$title = 'Most Active All Time';
}


$HTML->header(array('title'=>$title));

print '<P><B><FONT size="+1">'.$title.'</FONT></B>
<BR><I>(Updated Daily)</I>

<P><A href="/top/">[View Other Top Categories]</A>

<?php
$arr=array('Rank','Project Name','Percentile');

echo $HTML->listTableTop($arr);

$res_top = db_query($sql, $LIMIT, $offset);
$rows=db_numrows($res_top);

while ($row_top = db_fetch_array($res_top)) {
	$i++;
	print '<TR '. $HTML->boxGetAltRowStyle($i) .'><TD>&nbsp;&nbsp;'.$row_top['ranking']
		.'</TD><TD><A href="/projects/'. strtolower($row_top['unix_group_name']) .'/">'
		.$row_top['group_name']."</A>"
		.'</TD><TD align="right">'.$row_top['percentile'].'</TD></TR>';
}

if ($i<$rows) {
print '<TR BGCOLOR="'.$HTML->COLOR_LTBACK2.'"><TD>'.(($offset>0)?'<A HREF="mostactive.php?type='.$type.'&offset='.($offset-$LIMIT).'"><B><-- More</B></A>':'&nbsp;').'</TD>
	<TD>&nbsp;</TD>
	<TD ALIGN="RIGHT"><A HREF="mostactive.php?type='.$type.'&offset='.($offset+$LIMIT).'"><B>More --></B></A></TD></TR>';
}

echo $HTML->listTableBottom();

$HTML->footer(array());
?>
