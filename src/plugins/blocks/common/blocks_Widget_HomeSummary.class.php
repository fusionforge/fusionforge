<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2016,2021, Franck Villaume - TrivialDev
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'common/include/HTTPRequest.class.php';

class blocks_Widget_HomeSummary extends Widget {
	var $title = '';
	var $content = '';

	function __construct($owner_type, $owner_id) {
		if ($owner_type == WidgetLayoutManager::OWNER_TYPE_HOME) {
			$this->widget_id = 'plugin_blocks_home_summary';
			$this->group_id = $owner_id;
		}
		parent::__construct($this->widget_id);
		$this->setOwner($owner_id, $owner_type);

	}

	function getTitle() {
		return ($this->title ? $this->title : _('Summary Page block of text'));
	}

	function getDescription() {
		return _('Add a free block on the forge homepage to allow giving information.');
	}

	function isUnique() {
		return false;
	}
	function hasPreferences() {
		return true;
	}
	private function getPartialPreferencesForm($title, $content) {
		$prefs  = '<table>';
		$prefs .= '<tr><td>'._('Title')._(':').'</td>';
		$prefs .= '<td><input type="text" class="textfield_medium" name="title" value="'. htmlspecialchars($title) .'" /></td></tr>';
		$prefs .= '<tr><td>'._('Content')._(':').'</td>';

		$params['body'] = $content;
		$params['width'] = "500";
		$params['height'] = "250";
		$params['group'] = $this->group_id;
		$params['toolbar'] = 'FusionForge-Basic';
		$params['content'] = '<textarea name="body"  rows="10" cols="55">'.$content.'</textarea>';
		plugin_hook_by_reference("text_editor", $params);
		$prefs .= '<td>'.$params['content'].'</td></tr>';
		$prefs .= '</table>';
		return $prefs;
	}

	function getPreferences() {
		return $this->getPartialPreferencesForm($this->getTitle(), $this->content);
	}

	function getInstallPreferences() {
		return $this->getPartialPreferencesForm(_('Enter title of block'), '');
	}
	function updatePreferences() {
		$done = false;
		if (existInRequest('content_id')) {
			$title = htmlspecialchars(getStringFromRequest('title', ''));

			if(existInRequest('body')) {
				$content = getStringFromRequest('body');
				if (getStringFromRequest('_body_content_type') == 'html') {
					$content = TextSanitizer::purify($content);
				} else {
					$content = htmlspecialchars($content);
				}
			} else {
				$content = '';
			}

			if ($content) {
				$sql = "UPDATE plugin_blocks SET title=$1, content=$2 WHERE group_id =$3 AND id = $4";
				$res = db_query_params($sql, array($title, $content, $this->group_id, getIntFromRequest('content_id')));
				$done = true;
			}
		}
		return $done;
	}

	function loadContent($id) {
		$this->title = plugin_get_object('blocks')->getTitleBlock('summary_block'.$id);
		$this->content = plugin_get_object('blocks')->getContentBlock('summary_block'.$id);
		$this->content_id = $id;
	}

	function create() {
		$title = getStringFromRequest('title');
		$content = getStringFromRequest('body');
		$res = db_query_params('INSERT INTO plugin_blocks (group_id, name, status, title, content)
			VALUES ($1, $2, 1, $3, $4)',
			array($this->owner_id, 'summary_block?', $title, $content));
		$content_id = db_insertid($res, 'plugin_blocks', 'id');
		$res = db_query_params('UPDATE plugin_blocks SET name=$1 WHERE id=$2',
			array('summary_block'.$content_id, $content_id));
		return $content_id;
	}

	function getContent() {
		return plugin_get_object('blocks')->parseContent($this->content);
	}

	function destroy($id) {
		$sql = 'DELETE FROM plugin_blocks WHERE id = $1 AND group_id = $2';
		db_query_params($sql,array($id,$this->group_id));
	}
}
