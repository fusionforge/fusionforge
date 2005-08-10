<?php
/**
 *
 * GForge Top-Statistics: Top List
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

require "pre.php";    
require_once "common/include/Stats.class";    

$type = getStringFromRequest('type');

$stats = new Stats();

if ($type == 'downloads_week') {
	$title = $Language->getText('top_toplist','top_download_7_days');
	$column1 = $Language->getText('top_toplist','download');
}
else if ($type == 'pageviews_proj') {
	$res_top = $stats->getTopPageViews();
	$title = $Language->getText('top_toplist','top_weekly_pagesviews',array($GLOBALS['sys_default_domain'],$GLOBALS['sys_name']));
	$column1 = $Language->getText('top_toplist','pageviews');
}
else if ($type == 'forumposts_week') {
	$res_top = $stats->getTopMessagesPosted();
	$title = $Language->getText('top_toplist','top_forum_post_count');
	$column1 = $Language->getText('top_toplist','posts');
}
// default to downloads
else {
	$res_top = $stats->getTopDownloads();
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
