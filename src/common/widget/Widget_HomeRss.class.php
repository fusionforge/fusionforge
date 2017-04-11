<?php
/**
 * Copyright (c) Xerox, 2009. All Rights Reserved.
 * Originally written by Nicolas Terray, 2009. Xerox Codendi Team.
 * Copyright 2017, Franck Villaume - TrivialDev
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

require_once 'Widget_Rss.class.php';
require_once 'Widget.class.php';

/**
* Widget_RSS
*
* Allow to add RSS feed into forge homepage
*
*/
class Widget_HomeRss extends Widget_Rss {
	function __construct() {
		$request =& HTTPRequest::instance();
		parent::__construct('homerss', 0, WidgetLayoutManager::OWNER_TYPE_HOME);
	}

	function getDescription() {
		return _("Include public rss (or atom) feeds into forge homepage.");
	}
}
