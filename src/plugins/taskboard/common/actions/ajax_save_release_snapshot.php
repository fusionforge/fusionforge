<?php

/*
 * Copyright (C) 2015 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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

$ret = array(
	'message' => '',
	'action' => ''
);

$ret['message'] = '';

$release_id = getIntFromRequest('release_id');
$snapshot_date = getStringFromRequest('snapshot_date', '');

$release = new TaskBoardRelease( $taskboard, $release_id);
if( $release ) {
	$snapshot_datetime = strtotime($snapshot_date);
	if( $snapshot_datetime ) {
		if( $snapshot_datetime >= $release->getStartDate() && $snapshot_datetime <= $release->getEndDate() ) {
			db_begin();
			if( !$release->saveSnapshot( $snapshot_datetime ) ) {
				$ret['alert'] = _('Cannot save release snapshot');	
				db_rollback();
			} else {
				db_commit();
			}
		} else {
			$ret['alert'] = _('Snapshot date should be withing release period');
		}
	}
} else {
	$ret['alert'] = _('Cannot save release snapshot');
	$ret['action'] = 'reload';
}

echo json_encode( $ret );
