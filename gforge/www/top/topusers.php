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
<BR><I>(Updated Daily)</I>

<P><A href="/top/">[View Other Top Categories]</A>

<P><TABLE width="100%" cellpadding=0 cellspacing=0 border=0>
<TR valign="top">
<TD><B>Rank</B></TD>
<TD><B>User Name<BR>&nbsp;</B></TD>
<TD><B>Real Name<BR>&nbsp;</B></TD>
<TD align="right"><B>Rating</B></TD>
<TD align="right"><B>Last Rank</B></TD>
<TD align="right"><B>Change</B>&nbsp;&nbsp;&nbsp;</TD></TR>
';

while ($row_top = db_fetch_array($res_top)) {
	$i++;
	print '<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD>&nbsp;&nbsp;'.$row_top['ranking']
		.'</TD><TD><A href="/users/'. $row_top['user_name'] .'/">'
		.$row_top['user_name'].'</A></td>'
		.'<td>'.$row_top['realname'].'</td>'
		.'</TD><TD align="right">'.sprintf('%.2f', $row_top['metric'])
		.'&nbsp;&nbsp;&nbsp;</TD><TD align="right">'.$row_top['old_ranking']
		.'&nbsp;&nbsp;&nbsp;</TD>'
		.'<TD align="right">';

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

	print '&nbsp;&nbsp;&nbsp;</TD></TR>
';
}

print '</TABLE>';

print ' <table width="100%">
	<TR BGCOLOR="'.$HTML->COLOR_LTBACK2.'">
        <TD>'.(($offset>=$LIMIT)?'<A HREF="topusers.php?&offset='.($offset-50).'"><B><-- More</B></A>':'&nbsp;').'</TD>
	<TD ALIGN="RIGHT"><A HREF="topusers.php?offset='.($offset+50).'"><B>More --></B></A></TD></TR>
	</table>
';

$HTML->footer(array());
?>
