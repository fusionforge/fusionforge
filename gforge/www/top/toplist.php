<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    

if ($GLOBALS[type] == 'downloads_week') {
	$rankfield = 'downloads_week';
	$title = $Language->getText('top_toplist','top_download_7_days');
	$column1 = $Language->getText('top_toplist','download');
}
else if ($GLOBALS[type] == 'pageviews_proj') {
	$rankfield = 'pageviews_proj';
	$title = $Language->getText('top_toplist','top_weekly_pagesviews',array($GLOBALS['sys_default_domain'],$GLOBALS['sys_name']));
	$column1 = $Language->getText('top_toplist','pageviews');
}
else if ($GLOBALS[type] == 'forumposts_week') {
	$rankfield = 'forumposts_week';
	$title = $Language->getText('top_toplist','top_forum_post_count');
	$column1 = $Language->getText('top_toplist','posts');
}
// default to downloads
else {
	$rankfield = 'downloads_all';
	$title = $Language->getText('top_toplist','top_download');
	$column1 = $Language->getText('top_toplist','download');
}


$HTML->header(array('title'=>$title));

print '<p><strong><FONT size="+1">'.$title.'</FONT></strong>
<br /><em>('.$Language->getText('top','updated_daily').')</em>

<p><a href="/top/">['.$Language->getText('top','view_other_top_category').']</a>';

$arr=array($Language->getText('top_toplist','rank'),$Language->getText('top_toplist','project_name'),"$column1",$Language->getText('top_toplist','last_rank'),
					$Language->getText('top_toplist','change'));

echo $HTML->listTableTop($arr);

$res_top = db_query("SELECT groups.group_id,groups.group_name,groups.unix_group_name,top_group.$rankfield,".
	"top_group.rank_$rankfield,top_group.rank_".$rankfield."_old ".
	"FROM groups,top_group ".
	"WHERE top_group.$rankfield > 0 ".
	"AND top_group.group_id=groups.group_id ".
	"ORDER BY top_group.rank_$rankfield",100);

echo db_error();

while ($row_top = db_fetch_array($res_top)) {
	$i++;
	print '<tr '. $HTML->boxGetAltRowStyle($i) .'><td>&nbsp;&nbsp;'.$row_top["rank_$rankfield"]
		.'</td><td><a href="/projects/'. strtolower($row_top['unix_group_name']) .'/">'
		.stripslashes($row_top['group_name'])."</a>"
		.'</td><td align="right">'.$row_top["$rankfield"]
		.'&nbsp;&nbsp;&nbsp;</td><td align="right">'.$row_top["rank_$rankfield"."_old"]
		.'&nbsp;&nbsp;&nbsp;</td>'
		.'<td align="right">';

	// calculate change
	$diff = $row_top["rank_$rankfield"."_old"] - $row_top["rank_$rankfield"];
	if (($row_top["rank_$rankfield"."_old"] == 0) || ($row_top["rank_$rankfield"] == 0)) {
		print $Language->getText('top','NA');
	}
	else if ($diff == 0) {
		print $Language->getText('top','same');
	}
	else if ($diff > 0) {
		print "<FONT color=\"#009900\">".$Language->getText('top','up',$diff)."</FONT>";
	}
	else if ($diff < 0) {
		print "<FONT color=\"#CC0000\">".$Language->getText('top','down',(0-$diff))."</FONT>";
	}

	print '&nbsp;&nbsp;&nbsp;</td></tr>
';
}

echo $HTML->listTableBottom();

$HTML->footer(array());
?>
