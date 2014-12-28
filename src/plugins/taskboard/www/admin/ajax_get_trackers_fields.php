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

$used_trackers = getArrayFromRequest('trackers' );

$ret = array( 'messages' => '' );
$common_fields = array();
$allowed_types = array( 1, 4, 9);
foreach( $allowed_types as $allowed_type) {
	$common_fields[$allowed_type] = array();
}

$at_arr = $atf->getArtifactTypes();
$init = true;
for ($j = 0; $j < count($at_arr); $j++) {
	if (!is_object($at_arr[$j])) {
		//just skip it
	} elseif ($at_arr[$j]->isError()) {
		echo  json_encode( array( 'message' => $at_arr[$j]->getErrorMessage() ) );
		exit();
	} else {
		$tracker_id = $at_arr[$j]->getID();

		if( in_array( $tracker_id, $used_trackers ) ) {
			// select common 'select' fields
			$fields = $at_arr[$j]->getExtraFields( $allowed_types );
			$tmp = array();
			foreach( $allowed_types as $allowed_type) {
				$tmp[$allowed_type] = array();
			}

			foreach( $fields as $field) {
				// exclude 'resolution' field
				if( $field['alias'] != 'resolution' ) {
					if( $init ) {
						if( in_array( $field['field_type'], $allowed_types) ) {
							$tmp[ $field['field_type'] ][ $field['alias'] ] = $field['field_name'];
						}
					} elseif( 
						in_array( $field['alias'], array_keys( $common_fields[ $field['field_type'] ]) ) && 
						in_array( $field['field_type'], $allowed_types)
						) {
						$tmp[ $field['field_type'] ][$field['alias']] = $field['field_name'];
					}
				}
			}
			$common_fields = $tmp;
			$init = false;
		}
	}
}

$ret['common_selects'] = $common_fields[1];
$ret['common_texts'] = $common_fields[4];
$ret['common_refs'] = array_merge( $common_fields[4], $common_fields[9] );

echo json_encode( $ret );
