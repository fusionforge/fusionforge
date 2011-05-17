<?php
/**
 * Top-Statistics: Highest-Ranked Users
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

// Results per page
$LIMIT = 50; 

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$offset = getStringFromRequest('offset');

$yesterday = time()-60*60*24;
$yd_month = date('Ym', $yesterday);
$yd_day = date('d', $yesterday);

$res_top = db_query_params ('SELECT user_metric.ranking,users.user_name,users.user_id,users.realname,
		user_metric.metric,user_metric_history.ranking AS old_ranking
	FROM users,user_metric LEFT JOIN user_metric_history
		ON (user_metric.user_id=user_metric_history.user_id
		    AND user_metric_history.month=$1
		    AND user_metric_history.day=$2)
	WHERE users.user_id=user_metric.user_id
	ORDER BY ranking ASC',
			    array ($yd_month,
				   $yd_day),
			    $LIMIT,
			    $offset);

if (!$res_top || db_numrows($res_top)<1) {
	exit_error( _('Information about highest ranked users is not available.').' ' .db_error(),'');
}

$HTML->header(array('title'=>_('Top users')));

print '<br /><em>('._('Updated Daily').')</em>

<p>'.util_make_link ('/top/','['._('View Other Top Categories').']').'</p>';

$tableHeaders = array(
	_('Rank'),
	_('User name'),
	_('Real name'),
	_('Rating'),
	_('Last Rank'),
	_('Change')
);

echo $HTML->listTableTop($tableHeaders);

$i=0;
while ($row_top = db_fetch_array($res_top)) {
	$i++;
	print '<tr '. $HTML->boxGetAltRowStyle($i) .'><td>&nbsp;&nbsp;'.$row_top['ranking']
		.'</td><td>'.util_make_link_u ($row_top['user_name'],$row_top['user_id'],$row_top['user_name']).'</td>'
		.'<td>'.$row_top['realname'].'</td>'
		.'</td><td align="right">'.sprintf('%.2f', $row_top['metric'])
		.'&nbsp;&nbsp;&nbsp;</td><td align="right">'.$row_top['old_ranking']
		.'&nbsp;&nbsp;&nbsp;</td>'
		.'<td align="right">';

	// calculate change
	$diff = $row_top["old_ranking"] - $row_top["ranking"];
	if (!$row_top["old_ranking"] || !$row_top["ranking"]) {
		print _('N/A');
	}
	else if ($diff == 0) {
		print _('Same');
	}
	else if ($diff > 0) {
		print "<span class=\"up\"".sprintf(_('Up %1$s'), $diff)."</span>";
	}
	else if ($diff < 0) {
		print "<span class=\"down\">".sprintf(_('Down %1$s'), (0-$diff))."</span>";
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
