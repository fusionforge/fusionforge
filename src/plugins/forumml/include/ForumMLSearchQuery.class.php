<?php
/**
 * FusionForge Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $gfcommon;
require_once $gfcommon.'search/SearchQuery.class.php';

class ForumMLSearchQuery extends SearchQuery {

	/**
	* group id
	*
	* @var int $groupId
	*/
	var $groupId;

	/**
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 */
	function __construct($words, $offset, $isExact, $groupId) {
		$this->groupId = $groupId;

		parent::__construct($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {

		$pat = '_g'.$this->groupId.'_';
		$len = strlen($pat)+1;
		$qpa = db_construct_qpa() ;
		$qpa = db_construct_qpa($qpa,
					  'SELECT mh.id_message, mh.value as subject, m.id_list '.
                        ' FROM plugin_forumml_message m, plugin_forumml_messageheader mh'.
                        ' WHERE mh.id_header = $1'.
                        ' AND m.id_parent = 0'.
                        ' AND m.id_message = mh.id_message AND ',
					 array (4)) ;
	$qpa=$this->addIlikeCondition($qpa, 'mh.value');
		return $qpa ;
	}
}
