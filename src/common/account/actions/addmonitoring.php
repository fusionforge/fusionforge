<?php
/**
 * add monitoring user action
 *
 * Copyright 2012, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

if (!forge_get_config('use_diary')) {
	exit_disabled('home');
}

$diary_user = getStringFromRequest('diary_user');
$diary_user_object = user_get_object($diary_user);
if ($diary_user_object && is_object($diary_user_object) && $diary_user_object->isActive()) {
	$result = db_query_params ('SELECT * FROM user_diary_monitor WHERE user_id = $1 AND monitored_user = $2;', array(user_getid(), $diary_user));
	if (!$result || db_numrows($result) < 1) {
		$result = db_query_params ('INSERT INTO user_diary_monitor (monitored_user, user_id) VALUES ($1, $2)', array($diary_user, user_getid ()));
		if (!$result) {
			$error_msg = _('Error')._(': ')._('inserting into user_diary_monitor');
		} else {
			$feedback = sprintf(_('User %s is now being monitored.'), $diary_user_object->getUnixname());
		}
	} else {
		$warning_msg = sprintf(_('User %s already monitored'), $diary_user_object->getUnixname());
	}
} else {
	$error_msg = _('No user to monitor selected.');
}
session_redirect('/account/');
