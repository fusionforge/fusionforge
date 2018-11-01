<?php
/**
 * Userhome Personal Information Widget Class
 *
 * Copyright 2018, Franck Villaume - TrivialDev
 * http://fusionforge.org
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
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
require_once $gfcommon.'include/user_profile.php';

class Widget_UserhomePersonalInformation extends Widget {

	function __construct($owner_id) {
		$this->owner_id = $owner_id;
		parent::__construct('uhpersonalinformation', $owner_id, WidgetLayoutManager::OWNER_TYPE_USERHOME);
		$this->title = _('Personal Information');
	}

	function getTitle() {
		return $this->title;
	}

	function isAvailable() {
		return isset($this->title);
	}

	function getContent() {
		$user = user_get_object($this->owner_id);
		echo user_personal_information($user);
	}
}
