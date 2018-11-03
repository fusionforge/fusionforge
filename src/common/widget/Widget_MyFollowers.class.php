<?php
/**
 * FusionForge Followers (monitored users and vice versa) Widget
 *
 * Copyright 2018, Franck Villaume - TrivialDev
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

require_once 'Widget.class.php';
require_once $gfcommon.'include/MonitorElement.class.php';

 class Widget_MyFollowers extends Widget {
	function __construct() {
		parent::__construct('myfollowers');
	}

	function getTitle() {
		return _('Followers');
	}

	function getDescription() {
		return _('List users you are following and users that follows you.');
	}

	function getContent() {
		global $HTML;
		$nousers = true;
		$monitorUser = new MonitorElement('user');
		$monitoredUserIds = $monitorUser->getMonitedByUserIdInArray(user_getid());
		$followerIds = $monitorUser->getMonitorUsersIdsInArray(user_getid());
		if (is_array($monitoredUserIds) && count($monitoredUserIds) > 0) {
			echo 'found something'."\n";
			$nousers = false;
		}
		if  (is_array($followerIds) && count($followerIds) > 0) {
			echo 'found another thing'."\n";
			$nousers = false;
		}
		if ($nousers) {
			echo $HTML->warning_msg(_('You are not following any user and not followed by any.'));
		}
	}
}
