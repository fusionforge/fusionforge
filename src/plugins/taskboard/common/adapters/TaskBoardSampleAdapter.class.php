<?php
/**
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

global $gfplugins;
require_once $gfplugins.'taskboard/common/adapters/TaskBoardBasicAdapter.class.php';

class TaskBoardSampleAdapter extends TaskBoardBasicAdapter {
	function trackersColorChooser($name, $extra_field_alias = 'nature') {
		$ret = '';
		$extra_fields = $this->TaskBoard->getExtraFields(array(1));

		$ret = '<select name="' . $name. '">';
		$ret .= '<option value="">' ._('None'). '</option>';
		foreach($extra_fields[1] as $alias => $name) {
			$ret .= '<option value="' .$alias. '"' . ( $alias == $extra_field_alias ? ' selected' : '' ) . '>' . htmlspecialchars($name). '</option>';
		}
		$ret .= "</select>\n";

		return $ret;
	}

	function cardBackgroundColor($artifact, $extra_field_alias = 'resolution') {
		static $_cached = array();
		$ret = '';

		$tracker_id = $artifact->ArtifactType->getID();
		$element = 0;

		if ($extra_field_alias) {
			$value = '';
			$color = '';

			$fields_ids = $this->getFieldsIds( $tracker_id );
			if (array_key_exists($extra_field_alias, $fields_ids)) {
				$extra_field_id =  $fields_ids[$extra_field_alias];

				if ($extra_field_id) {
					$extra_data = $artifact->getExtraFieldData();

					$value = $extra_data[$extra_field_id];

					if ($value) {
						if (!array_key_exists($tracker_id, $_cached)) {
							$_cached[$tracker_id] = array();
						}

						if (!array_key_exists($extra_field_alias, $_cached[$tracker_id])) {
							$_cached[$tracker_id][$extra_field_alias] = array();
						}

						if (method_exists($artifact->ArtifactType, 'getElementColors')) {
							list( $bg_color, $fg_color) = $artifact->ArtifactType->getElementColors(
									$extra_field_id,
									$value
							);

							$color = $bg_color;
							$_cached[$tracker_id][$extra_field_alias][$value] = $color;

							$ret = $color;
						}
					}
				}
			}
		}
		return $ret;
	}
}
