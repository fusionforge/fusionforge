<?php
/**
 * Trove Browsing Facility
 *
 * Copyright 2004 Guillaume Smet / Open Wide
 * http://fusionforge.org/
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

function getLanguageSelectionPopup($alreadyDefined = array()) {
	$res = db_query_params ('SELECT * FROM supported_languages WHERE language_id <> ALL ($1) ORDER BY name ASC',
				array (db_int_array_to_any_clause ($alreadyDefined)));
	return html_build_select_box ($res, 'language_id', 'xzxz', false);
}

function getFilterUrl($filterArray, $currentId = 0) {
	$url = '';
	if($currentId) {
		$currentPosition = array_search($currentId, $filterArray);
		if($currentPosition !== false) {
			unset($filterArray[$currentPosition]);
		}
	}
	if(sizeof($filterArray) > 0) {
		$url = '&discrim='.implode(',', $filterArray);
	}
	return $url;
} 
 
?>
