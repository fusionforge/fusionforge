<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $group_id, $pluginTaskboard;

session_require_perm('tracker_admin', $group_id);

$confirmed = getStringFromRequest('confirmed', '');
$column_id = getStringFromRequest('column_id', '');
$column = &taskboard_column_get_object($column_id);

if($confirmed) {
	db_begin();
	if($column->delete()) {
		db_commit();
		$feedback.=_('Successfully Removed');
	} else {
		db_rollback();
	}
	session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?view=columns&group_id='.$group_id);
}
