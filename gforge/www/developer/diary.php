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

require_once('pre.php');
require_once('vote_function.php');

$diary_user = getStringFromRequest('diary_user');
if ($diary_user) {
	$diary_id = getStringFromRequest('diary_id');
  
	$user_obj=user_get_object($diary_user);
	if (!$user_obj || $user_obj->isError()) {
		exit_error('ERROR','User could not be found: '.$user_obj->getErrorMessage());
	}

	echo $HTML->header(array('title'=>$Language->getText('my_diary','title')));

	echo '
	<h2>'.$Language->getText('developer','diary_and_notes').': '. $user_obj->getRealName() .'</h2>';

	if ($diary_id) {
		$sql="SELECT * FROM user_diary WHERE user_id='$diary_user' AND id='$diary_id' AND is_public=1";
		$res=db_query($sql);

		echo $HTML->boxTop($Language->getText('developer','date').": ".date($sys_datefmt, db_result($res,$i,'date_posted')));
		if (!$res || db_numrows($res) < 1) {
			echo $Language->getText('developer','entry_not_found');
		} else {
			echo'<strong>'.$Language->getText('developer','subject').':</strong> '. db_result($res,$i,'summary') .'<p>
			<strong>'.$Language->getText('developer','body').':</strong><br />
			'. nl2br(db_result($res,$i,'details')) .'
			</p>';
		}
		echo $HTML->boxBottom();
	}

	echo $HTML->boxTop($Language->getText('my_diary','existing_entries'));
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
			<tr><td><strong>'.$Language->getText('developer','no_entries').'</strong></td></tr>';
		echo db_error();
	} else {
		echo '
			<tr>
				<th>'.$Language->getText('developer','subject').'</th>
				<th>'.$Language->getText('developer','date').'</th>
			</tr>';
		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><a href="'. getStringFromServer('PHP_SELF') .'?diary_id='.
				db_result($result,$i,'id').'&amp;diary_user='. $diary_user .'">'.db_result($result,$i,'summary').'</a></td>'.
				'<td>'. date($sys_datefmt, db_result($result,$i,'date_posted')).'</td></tr>';
		}
		echo '
		<tr><td colspan="2" class="content">&nbsp;</td></tr>';
	}
	echo "</table>\n";

	echo $HTML->boxBottom();

	echo $HTML->footer(array());

} else {

	exit_error($Language->getText('general','error'),$Language->getText('developer','no_user_selected'));

}

?>