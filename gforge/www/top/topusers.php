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
		'Information not available',
		'Information about highest ranked users is not available. '
		.db_error()
	);
}

$HTML->header(array('title'=>'Highest Ranked Users'));

print '<h1>Highest Ranked Users</h1>
<br /><em>(Updated Daily)</em>

<p><a href="/top/">[View Other Top Categories]</a>

<p><table width="100%" cellpadding=0 cellspacing=0 border=0>
<tr valign="top">
<td><strong>Rank</strong></td>
<td><strong>User Name<br />&nbsp;</strong></td>
<td><strong>Real Name<br />&nbsp;</strong></td>
<td align="right"><strong>Rating</strong></td>
<td align="right"><strong>Last Rank</strong></td>
<td align="right"><strong>Change</strong>&nbsp;&nbsp;&nbsp;</td></tr>
';
$arr=array('Rank','User Name','Real Name','Rating','Last Rank','Change');
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
		print "N/A";
	}
	else if ($diff == 0) {
		print "Same";
	}
	else if ($diff > 0) {
		print "<FONT color=\"#009900\">Up $diff</FONT>";
	}
	else if ($diff < 0) {
		print "<FONT color=\"#CC0000\">Down ".(0-$diff)."</FONT>";
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
