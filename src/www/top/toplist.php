<?php
/**
 *
 * Top-Statistics: Top List
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 * Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require ('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/Stats.class.php';

$type = getStringFromRequest('type');

$stats = new Stats();

if ($type == 'downloads_week') {
	$title = _('Top Downloads in the Past 7 Days');
	$column1 = _('Downloads');
}
else if ($type == 'pageviews_proj') {
	$res_top = $stats->getTopPageViews();
	$title = sprintf(_('Top Weekly Project Pageviews at *.%1$s (from impressions of %2$s logo)'), forge_get_config('web_host'), forge_get_config ('forge_name'));
	$column1 = _('Pageviews');
}
else if ($type == 'forumposts_week') {
	$res_top = $stats->getTopMessagesPosted();
	$title = _('Top Forum Post Counts');
	$column1 = _('Posts');
}
// default to downloads
else {
	$res_top = $stats->getTopDownloads();
	$title = _('Top Downloads');
	$column1 = _('Downloads');
}
$HTML->header(array('title'=>$title));
print '<p>'.util_make_link ('/top/','['._('View Other Top Categories').']').'</p>';
$arr=array(_('Rank'),_('Project name'),"$column1");
echo $HTML->listTableTop($arr);

echo db_error();

$display_rank = 0;
$i=0;
while ($row_top = db_fetch_array($res_top)) {
	$i++;
	if ($row_top["items"] == 0) {
		continue;
	}
	$display_rank++;
	print '<tr '. $HTML->boxGetAltRowStyle($i) .'><td>&nbsp;&nbsp;'.$display_rank
		.'</td><td>'.util_make_link_g (strtolower($row_top['unix_group_name']),@$row_top['group_id'],stripslashes($row_top['group_name']))
		.'</td><td align="right">'.$row_top['items']
		.'&nbsp;&nbsp;&nbsp;</td>'
		.'</tr>
';
}

echo $HTML->listTableBottom();

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
