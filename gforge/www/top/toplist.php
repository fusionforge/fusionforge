<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    
require_once "common/include/Stats.class";    

$stats = new Stats();

if ($GLOBALS[type] == 'downloads_week') {
	$title = $Language->getText('top_toplist','top_download_7_days');
	$column1 = $Language->getText('top_toplist','download');
}
else if ($GLOBALS[type] == 'pageviews_proj') {
	$res_top = $stats->getTopPageViews();
	$title = $Language->getText('top_toplist','top_weekly_pagesviews',array($GLOBALS['sys_default_domain'],$GLOBALS['sys_name']));
	$column1 = $Language->getText('top_toplist','pageviews');
}
else if ($GLOBALS[type] == 'forumposts_week') {
	$res_top = $stats->getTopMessagesPosted();
	$title = $Language->getText('top_toplist','top_forum_post_count');
	$column1 = $Language->getText('top_toplist','posts');
}
// default to downloads
else {
	$title = $Language->getText('top_toplist','top_download');
	$column1 = $Language->getText('top_toplist','download');
}
$HTML->header(array('title'=>$title));
print '<p><a href="/top/">['.$Language->getText('top','view_other_top_category').']</a>';
$arr=array($Language->getText('top_toplist','rank'),$Language->getText('top_toplist','project_name'),"$column1");
echo $HTML->listTableTop($arr);

echo db_error();

$display_rank = 0;
while ($row_top = db_fetch_array($res_top)) {
	$i++;
	if ($row_top["items"] == 0) {
		continue;
	}
	$display_rank++;
	print '<tr '. $HTML->boxGetAltRowStyle($i) .'><td>&nbsp;&nbsp;'.$display_rank
		.'</td><td><a href="/projects/'. strtolower($row_top['unix_group_name']) .'/">'
		.stripslashes($row_top['group_name'])."</a>"
		.'</td><td align="right">'.$row_top['items']
		.'&nbsp;&nbsp;&nbsp;</td>'
		.'<td align="right">';
	print '&nbsp;&nbsp;&nbsp;</td></tr>
';
}

echo $HTML->listTableBottom();

$HTML->footer(array());
?>
