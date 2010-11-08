<?php
/**
 * Sitewide Statistics
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'stats/site_stats_utils.php';

session_require_global_perm ('forge_stats', 'read') ;

$HTML->header(array('title' => sprintf(_('%1$s I18n Statistics: Languages Distributions'), forge_get_config ('forge_name'))));
echo "<h1>".sprintf(_('Languages Distributions'), forge_get_config ('forge_name'))." </h1>";
echo $GLOBALS['HTML']->listTableTop(array(_('Language')."",_('Users')."","%"));

$total=db_result(db_query_params('SELECT count(user_name) AS total FROM users', array()),0,'total');

$res = db_query_params ('SELECT supported_languages.name AS lang,count(user_name) AS cnt
FROM supported_languages LEFT JOIN users ON language_id=users.language
GROUP BY lang,language_id,name
ORDER BY cnt DESC',
			array ());
$non_english=0;
$i=0;
while ($lang_stat = db_fetch_array($res)) {
	if ($lang_stat['cnt'] > 0) {
		echo '<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'><td>'.$lang_stat['lang'].'</td>'.
		'<td align="right">'.$lang_stat['cnt'].' </td>'.
		'<td align="right">'.sprintf("%.2f",$lang_stat['cnt']*100/$total)." </td></tr>\n";
		if ($lang_stat['lang']!='English') $non_english+=$lang_stat['cnt'];
	}
}

echo '<tr><td><strong>'._('Total Non-English').'</strong></td>'.
'<td align="right"><strong>'.$non_english.' </strong></td>'.
'<td align="right"><strong>'.sprintf("%.2f",$non_english*100/$total).' </strong></td></tr>';

echo $GLOBALS['HTML']->listTableBottom();
echo "<p>"._('This is a list of the preferences that users have chosen in their user preferences; it does not include languages which are selected via cookies or browser preferences')."</p>";

$HTML->footer( array() );

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
