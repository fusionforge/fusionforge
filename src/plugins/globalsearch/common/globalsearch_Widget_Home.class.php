<?php
/**
 * Copyright 2016, Franck Villaume - TrivialDev
 *
 * This file is a part of FusionForge.
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
 * along with FusionForge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'common/include/HTTPRequest.class.php';

class globalsearch_Widget_Home extends Widget {
	var $title = '';
	var $content = '';

	function __construct($owner_type, $owner_id) {
		$request =& HTTPRequest::instance();
		if ($owner_type == WidgetLayoutManager::OWNER_TYPE_HOME) {
			$this->widget_id = 'plugin_globalsearch_home';
			$this->group_id = $owner_id;
		}
		parent::__construct($this->widget_id);
		$this->setOwner($owner_id, $owner_type);
	}

	function getTitle() {
		return ($this->title ? $this->title : _('Global Project Search across multiple forges'));
	}

	function getDescription() {
		return _('Search for projects existing on linked forges.');
	}

	function getContent() {
		$pluginObject = plugin_get_object('globalsearch');
		return $pluginObject->search_box();
	}
}
