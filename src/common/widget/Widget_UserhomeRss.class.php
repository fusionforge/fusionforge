<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2018, Franck Villaume - TrivialDev
 *
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
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget_Rss.class.php';
require_once 'common/widget/WidgetLayoutManager.class.php';

/**
 * Widget_UserhomeRss
 *
 * User rss reader. To integrate on user profile page.
 */

class Widget_UserhomeRss extends Widget_Rss {
	function __construct($owner_id) {
		$this->owner_id = $owner_id;
		parent::__construct('uhrss', $owner_id, WidgetLayoutManager::OWNER_TYPE_USERHOME);
	}

	function getDescription() {
		return _('Allow you to include public rss (or atom) feeds into your profile.');
	}
}
