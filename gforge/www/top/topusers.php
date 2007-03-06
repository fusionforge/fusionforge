<?php
/**
 * GForge Top-Statistics: Highest-Ranked Users
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
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

// Results per page
$LIMIT = 50; 

require_once('../env.inc.php');
require_once('pre.php');

$offset = getStringFromRequest('offset');

$yesterday = time()-60*60*24;
$yd_month = date('Ym', $yesterday);
$yd_day = date('d', $yesterday);

$res_top = db_query("
	SELECT user_metric.ranking,users.user_name,users.realname,
		user_metric.metric,user_metric_history.ranking AS old_ranking
	FROM users,user_metric LEFT JOIN user_metric_history 
		ON (user_metric.user_id=user_metric_history.user_id 
		    AND user_metric_history.month='$yd_month'
		    AND user_metric_history.day='$yd_day')
	WHERE users.user_id=user_metric.user_id
	ORDER BY ranking ASC
", $LIMIT, $offset);


if (!$res_top || db_numrows($res_top)<1) {
	exit_error(
		$Language->getText('top_topusers','info_not_available'),
		$Language->getText('top_topusers','info_not_available_more').' '
		.db_error()
	);
}

$HTML->header(array('title'=>$Language->getText('top_topusers','title')));

print '<h1>'.$Language->getText('top_topusers','title').'</h1>
<br /><em>('.$Language->getText('top','updated_daily').')</em>

<p><a href="'.$GLOBALS['sys_urlprefix'].'/top/">['.$Language->getText('top','view_other_top_category').']</a></p>';

$tableHeaders = array(
	$Language->getText('top_topusers','rank'),
	$Language->getText('top_topusers','user_name'),
	$Language->getText('top_topusers','real_name'),
	$Language->getText('top_topusers','rating'),
	$Language->getText('top_topusers','last_rank'),
	$Language->getText('top_topusers','change')
);

echo $HTML->listTableTop($tableHeaders);

while ($row_top = db_fetch_array($res_top)) {
	$i++;
	print '<tr '. $HTML->boxGetAltRowStyle($i) .'><td>&nbsp;&nbsp;'.$row_top['ranking']
		.'</td><td><a href="'.$GLOBALS['sys_urlprefix'].'/users/'. $row_top['user_name'] .'/">'
		.$row_top['user_name'].'</a></td>'
		.'<td>'.$row_top['realname'].'</td>'
		.'</td><td align="right">'.sprintf('%.2f', $row_top['metric'])
		.'&nbsp;&nbsp;&nbsp;</td><td align="right">'.$row_top['old_ranking']
		.'&nbsp;&nbsp;&nbsp;</td>'
		.'<td align="right">';

	// calculate change
	$diff = $row_top["old_ranking"] - $row_top["ranking"];
	if (!$row_top["old_ranking"] || !$row_top["ranking"]) {
		print $Language->getText('top','NA');
	}
	else if ($diff == 0) {
		print $Language->getText('top','same');
	}
	else if ($diff > 0) {
		print "<span class=\"up\"".$Language->getText('top','up',$diff)."</span>";
	}
	else if ($diff < 0) {
		print "<span class=\"down\">".$Language->getText('top','down',(0-$diff))."</span>";
	}

	print '&nbsp;&nbsp;&nbsp;</td></tr>
';
}

echo $HTML->listTableBottom();

/*
	<tr class="tablegetmore">
        <td>'.(($offset>=$LIMIT)?'<a href="topusers.php?&offset='.($offset-50).'"><strong><-- More</strong></a>':'&nbsp;').'</td>
	<td align="RIGHT"><a href="topusers.php?offset='.($offset+50).'"><strong>More --></strong></a></td></tr>
';*/

$HTML->footer(array());
?>
