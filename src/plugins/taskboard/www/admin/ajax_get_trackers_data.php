<?php

/*
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

global $group_id, $HTML;

$atf = $taskboard->TrackersAdapter->getArtifactTypeFactory();
if (!$atf || !is_object($atf) || $atf->isError()) {
	echo  json_encode( array( 'message' => _('Could Not Get ArtifactTypeFactory') ) );
	exit();
}

$ret = array( 'messages' => '', 'data' => array() );
$at_arr = $atf->getArtifactTypes();
for ($j = 0; $j < count($at_arr); $j++) {
	if (!is_object($at_arr[$j])) {
		//just skip it
	} elseif ($at_arr[$j]->isError()) {
		echo  json_encode( array( 'message' => $at_arr[$j]->getErrorMessage() ) );
		exit();
	} else {
		// get only fields having 'select' type
		$fields = $at_arr[$j]->getExtraFields( 1 );

		foreach( $fields as $field) {
			if( $field['alias'] == 'resolution' ) {
				// this tracker uses 'resolution' select list, so could be used with taskboard
				$ret['data'][] = array(
					'id' => $at_arr[$j]->getID(),
					'name' => $at_arr[$j]->getName(),
					'desc' => $at_arr[$j]->getDescription(),
					'used' => '',
					'bgcolor' => '' 
				);
			}
		}
	}
}

echo json_encode( $ret );
