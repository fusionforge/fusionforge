<?php

/**
 * GForge Trove Browsing Facility
 *
 * Copyright 2004 Guillaume Smet / Open Wide
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

function getLanguageSelectionPopup($alreadyDefined = array()) {
	$where = '';
	if(!empty($alreadyDefined)) {
		$where = ' WHERE language_id NOT IN('.implode(',', $alreadyDefined).')';
	}
	$res = db_query('SELECT * FROM supported_languages'.$where.' ORDER BY name ASC');
	
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