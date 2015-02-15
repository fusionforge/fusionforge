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

global $group_id;

$used_trackers = getArrayFromRequest('trackers');

$ret = array('messages' => '');
$allowed_types = array(1, 4, 9);
$common_fields = $taskboard->getExtraFields($allowed_types, $used_trackers);

if (is_string( $common_fields)) {
	echo json_encode(array('message' => $common_fields));
	exit();
}

$ret['common_selects'] = $common_fields[1];
$ret['common_texts'] = $common_fields[4];
$ret['common_refs'] = array_merge($common_fields[4], $common_fields[9]);

echo json_encode($ret);
