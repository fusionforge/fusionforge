<?php
/**
 * GForge Developer's Diary Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

require_once('../env.inc.php');
require_once('pre.php');
require_once('vote_function.php');

$diary_user = getStringFromRequest('diary_user');
if ($diary_user) {
	$diary_id = getStringFromRequest('diary_id');
  
	$user_obj=user_get_object($diary_user);
	if (!$user_obj || $user_obj->isError()) {
		exit_error('ERROR','User could not be found: '.$user_obj->getErrorMessage());
	}

	echo $HTML->header(array('title'=>_('My Diary And Notes')));

	echo '
	<h2>'._('Diary And Notes For').': '. $user_obj->getRealName() .'</h2>';

	if ($diary_id) {
		$sql="SELECT * FROM user_diary WHERE user_id='$diary_user' AND id='$diary_id' AND is_public=1";
		$res=db_query($sql);

		echo $HTML->boxTop(_('Date').": ".date($sys_datefmt, db_result($res,$i,'date_posted')));
		if (!$res || db_numrows($res) < 1) {
			echo _('Entry Not Found For This User');
		} else {
			echo'<strong>'._('Subject').':</strong> '. db_result($res,$i,'summary') .'<p>
			<strong>'._('Body').':</strong><br />
			'. nl2br(db_result($res,$i,'details')) .'
			</p>';
		}
		echo $HTML->boxBottom();
	}

	echo $HTML->boxTop(_('Existing Diary And Note Entries'));
	echo '<table cellspacing="2" cellpadding="0" width="100%" border="0">
';
	/*

		List all diary entries

	*/
	$sql="SELECT * FROM user_diary WHERE user_id='$diary_user' AND is_public=1 ORDER BY id DESC";

	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<tr><td><strong>'._('This User Has No Diary Entries').'</strong></td></tr>';
		echo db_error();
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
				'<td>'. date($sys_datefmt, db_result($result,$i,'date_posted')).'</td></tr>';
		}
		echo '
		<tr><td colspan="2" class="tablecontent">&nbsp;</td></tr>';
	}
	echo "</table>\n";

	echo $HTML->boxBottom();

	echo $HTML->footer(array());

} else {

	exit_error(_('Error'),_('No User Selected'));

}

?>
