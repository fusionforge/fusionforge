<?php
/**
 * Copyright 2016, Franck Villaume - TrivialDev
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
require_once $gfcommon.'include/tag_cloud.php';

class Widget_HomeTagCloud extends Widget {
	function __construct() {
		parent::__construct('hometagcloud');
		if (forge_get_config('use_project_tags')) {
			$this->content['title'] = _('Tag Cloud');
		}
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getContent() {
		return tag_cloud();
	}

	function isAvailable() {
		return isset($this->content['title']);
	}
}
