<?php
/**
  *
  * SourceForge Top-Statistics: Highest-Ranked Users
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


// Results per page
$LIMIT = 50; 

require_once('pre.php');

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

<p><a href="/top/">['.$Language->getText('top','view_other_top_category').']</a>

<p>';
$arr=array($Language->getText('top','rank'),$Language->getText('top','user_name'),$Language->getText('top','real_name'),$Language->getText('top','rating'),
					$Language->getText('top','last_rank'),$Language->getText('top','change'));
echo $HTML->listTableTop($arr);

while ($row_top = db_fetch_array($res_top)) {
	$i++;
	print '<tr '. $HTML->boxGetAltRowStyle($i) .'><td>&nbsp;&nbsp;'.$row_top['ranking']
		.'</td><td><a href="/users/'. $row_top['user_name'] .'/">'
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
		print "<FONT color=\"#009900\">".$Language->getText('top','up',$diff)."</FONT>";
	}
	else if ($diff < 0) {
		print "<FONT color=\"#CC0000\">".$Language->getText('top','down',(0-$diff))."</FONT>";
	}

	print '&nbsp;&nbsp;&nbsp;</td></tr>
';
}

echo $HTML->listTableBottom();

/*
	<tr bgcolor="'.$HTML->COLOR_LTBACK2.'">
        <td>'.(($offset>=$LIMIT)?'<a href="topusers.php?&offset='.($offset-50).'"><strong><-- More</strong></a>':'&nbsp;').'</td>
	<td align="RIGHT"><a href="topusers.php?offset='.($offset+50).'"><strong>More --></strong></a></td></tr>
';*/

$HTML->footer(array());
?>
