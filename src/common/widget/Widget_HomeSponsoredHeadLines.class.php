<?php
/**
 * Copyright 2019,2021-2022, Franck Villaume - TrivialDev
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
require_once $gfcommon.'diary/DiaryFactory.class.php';

class Widget_HomeSponsoredHeadLines extends Widget {
	function __construct() {
		parent::__construct('homesponsoredheadlines');
		if (forge_get_config('use_diary') || forge_get_config('use_news')) {
			$this->title = forge_get_config('forge_name').' '.('Headlines');
		}
	}

	function getTitle() {
		return $this->title;
	}

	function getDescription() {
		return _('Display Sponsored Headlines for last 30 days: i.e diary & notes, project news.');
	}

	function isAvailable() {
		return isset($this->title);
	}

	function getContent() {
		global $HTML;
		$content = '';
		$old_date = time()-60*60*24*30;
		$diaryFactory = new DiaryFactory();
		if ($diaryFactory->hasSponsoredNotes($old_date)) {
			foreach ($diaryFactory->getSponsoredIDS($old_date) as $diarynoteid) {
				$diaryNoteObject = diarynote_get_object($diarynoteid);
				$content .= $diaryNoteObject->getAbstract();
			}
		}
		$result = db_query_params('
				SELECT groups.group_name, groups.unix_group_name, groups.group_id,
				users.user_name, users.realname, users.user_id,
				news_bytes.forum_id, news_bytes.summary, news_bytes.post_date,
				news_bytes.details
				FROM users,news_bytes,groups
				WHERE (news_bytes.group_id=$1 AND news_bytes.is_approved <> 4 OR 1!=$2)
				AND (news_bytes.is_approved=1 OR 1 != $3)
				AND users.user_id=news_bytes.submitted_by
				AND news_bytes.group_id=groups.group_id
				AND groups.status=$4
				AND post_date > $5
				ORDER BY post_date DESC',
				array (GROUP_IS_NEWS,
					0,
					1,
					'A',
					$old_date));

		if ($result && db_numrows($result)) {
			$rows = db_numrows($result);
			for ($i = 0; $i < $rows; $i++) {
				if (strstr(db_result($result,$i,'details'),'<br/>')) {
					// the news is html, fckeditor made for example
					$arr = explode("<br/>",db_result($result,$i,'details'));
				} else {
					$arr = explode("\n",db_result($result,$i,'details'));
				}
				$summ_txt = util_make_links($arr[0]);
				$proj_name = util_make_link_g(strtolower(db_result($result,$i,'unix_group_name')),db_result($result,$i,'group_id'),db_result($result,$i,'group_name'));
				$t_thread_title = db_result($result,$i,'summary');
				$t_thread_url = "/forum/forum.php?forum_id=" . db_result($result,$i,'forum_id');
				$t_thread_author = util_display_user(db_result($result,$i,'user_name'), db_result($result,$i,'user_id'), db_result($result,$i,'realname'));
				$news = html_e('div', array('class' => 'widget-sticker-header box'), html_e('div', array(), util_make_link ($t_thread_url, $t_thread_title).'&nbsp;'._('by').'&nbsp;').$t_thread_author);
				$news .= html_e('div', array('class' => 'widget-sticker-body'), $summ_txt.html_e('br').util_make_link($t_thread_url, _('... Read more')));
				$news .= html_e('div', array('class' => 'widget-sticker-footer'), _('Posted')._(': ').date(_('Y-m-d H:i'),db_result($result,$i,'post_date')).' - '.$proj_name);
				$content .= html_e('div', array('class' => 'widget-sticker-container'), $news);
			}
		}

		if (strlen($content)) {
			return $content.html_e('div', array('style' => 'clear:both'), '&nbsp;');
		}
		return $HTML->warning_msg(_('No Sponsored element found'));
	}
}
