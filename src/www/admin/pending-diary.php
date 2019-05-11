<?php
/**
 * Diary Facility
 *
 * Copyright 2019, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfcommon.'diary/DiaryNote.class.php';

session_require_global_perm('approve_diary');

$post_change = getStringFromRequest('post_change');
$diary_id = getIntFromRequest('diary_id');
if (in_array($post_change, array('add', 'remove')) && $diary_id) {
	switch ($post_change) {
		case 'add':
			$res = db_query_params('UPDATE user_diary SET is_approved = $1 WHERE id = $2', array(1, $diary_id));
			$feedback = sprintf(_('diary note ID %d successfully published'), $diary_id);
			break;
		case 'remove':
			$res = db_query_params('UPDATE user_diary SET is_approved = $1 WHERE id = $2', array(2, $diary_id));
			$feedback = sprintf(_('diary note ID %d successfully removed'), $diary_id);
			break;
	}
}

site_admin_header(array('title'=>_('Diary Admin')));
echo html_e('p', array(), _('Select Diary & Notes to be on the frontpage. Only public diary entries can be selected and less than 30 days old.'));
$old_date = time()-60*60*24*30;
$res = db_query_params('SELECT user_diary.id FROM user_diary, users
						WHERE is_public = $1
						AND is_approved = $2
						AND user_diary.user_id = users.user_id
						AND users.status = $3
						AND date_posted > $4
						ORDER BY date_posted',
			array(1, 0, 'A', $old_date));
			
if ($res && db_numrows($res)) {
	echo html_e('h2', array(), _('Available diary entries eligible to frontpage'));
	$title_arr = array('', _('Date'), _('Subject'), _('User'), _('Actions'));
	echo $HTML->listTableTop($title_arr);
	while ($arr = db_fetch_array($res)) {
		$diarynoteObject = diarynote_get_object($arr['id']);
		$cells = array();
		$cells[][] = $HTML->html_checkbox('diary_approved_id', $arr['id'], 'diary_approved_id');
		$cells[][] = $diarynoteObject->getDatePostedOn();
		$cells[][] = util_make_link($diarynoteObject->getLink(), $diarynoteObject->getSummary());
		$cells[][] = $diarynoteObject->getUser()->getUnixname();
		$cells[][] = util_make_link('/admin/pending-diary.php?post_change=add&diary_id='.$arr['id'], $HTML->getAddPic());
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
} else {
	echo $HTML->information(_('No new available diary entries to push to frontpage'));
}

$res = db_query_params('SELECT user_diary.id FROM user_diary, users
						WHERE is_public = $1
						AND is_approved = $2
						AND user_diary.user_id = users.user_id
						AND date_posted > $3
						ORDER BY date_posted',
			array(1, 1, $old_date));
			
if ($res && db_numrows($res)) {
	echo html_e('h2', array(), _('Published diary entries to frontpage last 30 days.'));
	$title_arr = array('', _('Date'), _('Subject'), _('User'), _('Actions'));
	echo $HTML->listTableTop($title_arr);
	while ($arr = db_fetch_array($res)) {
		$diarynoteObject = diarynote_get_object($arr['id']);
		$cells = array();
		$cells[][] = $HTML->html_checkbox('diary_approved_id', $arr['id'], 'diary_approved_id');
		$cells[][] = $diarynoteObject->getDatePostedOn();
		$cells[][] = util_make_link($diarynoteObject->getLink(), $diarynoteObject->getSummary());
		$cells[][] = $diarynoteObject->getUser()->getUnixname();
		$cells[][] = util_make_link('/admin/pending-diary.php?post_change=remove&diary_id='.$arr['id'], $HTML->getRemovePic());
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
} else {
	echo $HTML->information(_('No diary entries pushed to frontpage last 30 days.'));
}


$res = db_query_params('SELECT user_diary.id FROM user_diary, users
						WHERE is_public = $1
						AND is_approved = $2
						AND user_diary.user_id = users.user_id
						AND date_posted > $3
						ORDER BY date_posted',
			array(1, 2, $old_date));
			
if ($res && db_numrows($res)) {
	echo html_e('h2', array(), _('Refused diary entries to frontpage last 30 days.'));
	$title_arr = array('', _('Date'), _('Subject'), _('User'), _('Actions'));
	echo $HTML->listTableTop($title_arr);
	while ($arr = db_fetch_array($res)) {
		$diarynoteObject = diarynote_get_object($arr['id']);
		$cells = array();
		$cells[][] = $HTML->html_checkbox('diary_approved_id', $arr['id'], 'diary_approved_id');
		$cells[][] = $diarynoteObject->getDatePostedOn();
		$cells[][] = util_make_link($diarynoteObject->getLink(), $diarynoteObject->getSummary());
		$cells[][] = $diarynoteObject->getUser()->getUnixname();
		$cells[][] = util_make_link('/admin/pending-diary.php?post_change=add&diary_id='.$arr['id'], $HTML->getAddPic());
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
} else {
	echo $HTML->information(_('No diary entries refused to frontpage last 30 days.'));
}

site_admin_footer();
