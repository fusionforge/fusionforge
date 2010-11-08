<?php
/**
 * Developer's Diary Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/vote_function.php';

$diary_user = getIntFromRequest('diary_user');
if ($diary_user) {
	$diary_id = getIntFromRequest('diary_id');
  
	$user_obj=user_get_object($diary_user);
	if (!$user_obj) {
		exit_error(_('User could not be found.'),'home');
	} else if ($user_obj->isError()) {
		exit_error($user_obj->getErrorMessage(),'home');
	}

	$title = _('Diary And Notes For').': '. $user_obj->getRealName();
	echo $HTML->header(array('title'=>$title));
	echo '<h1>'. $title .'</h1>';

	if ($diary_id) {
		$res = db_query_params ('SELECT * FROM user_diary WHERE user_id=$1 AND id=$2 AND is_public=1',
					array ($diary_user,
					       $diary_id));

		if (!$res || db_numrows($res) < 1) {
			echo '<p>' . _('Entry Not Found For This User') . '</p>';
		} else {
			echo $HTML->boxTop(db_result($res,0,'summary'));
			echo '<p>' . _('Posted on ') . date(_('Y-m-d H:i'), db_result($res,0,'date_posted')).'</p>';
			echo db_result($res,0,'details');
			echo $HTML->boxBottom();
		}
	}

	echo $HTML->boxTop(_('Existing Diary And Note Entries'));
	echo '<table cellspacing="2" cellpadding="0" width="100%" border="0">';
	/*
		List all diary entries
	*/
	$result = db_query_params ('SELECT * FROM user_diary WHERE user_id=$1 AND is_public=1 ORDER BY id DESC',
				   array ($diary_user));
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		if (db_error()) {
			exit_error(db_error(),'home');
		} else {
			echo '
				<tr><td><strong>'._('This User Has No Diary Entries').'</strong></td></tr>';
		}
	} else {
		echo '
			<tr>
				<th>'._('Subject').'</th>
				<th>'._('Date').'</th>
			</tr>';
		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><a href="'. getStringFromServer('PHP_SELF') .'?diary_id='.
				db_result($result,$i,'id').'&amp;diary_user='. $diary_user .'">'.db_result($result,$i,'summary').'</a></td>'.
				'<td>'. date(_('Y-m-d H:i'), db_result($result,$i,'date_posted')).'</td></tr>';
		}
		echo '
		<tr><td colspan="2" class="tablecontent">&nbsp;</td></tr>';
	}
	echo "</table>\n";
	echo $HTML->boxBottom();

	echo $HTML->footer(array());

} else {
	exit_error(_('No User Selected'),'home');
}

?>
