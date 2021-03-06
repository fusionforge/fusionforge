<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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

require_once $gfcommon.'search/SearchQuery.class.php';

class SkillSearchQuery extends SearchQuery {

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {
		$words = $this->getFTIwords();
		$qpa = db_construct_qpa(false, 'SELECT skills_data.skills_data_id, skills_data.type, skills_data.start, skills_data.finish, ts_headline(skills_data.title, q) as title, ts_headline(skills_data.keywords, q) as keywords FROM skills_data, users, skills_data_types, to_tsquery($1) AS q, skills_data_idx WHERE (vectors @@ q ',
						array($words));
		if (count ($this->phrases)) {
			$qpa = db_construct_qpa($qpa, $this->getOperator());
			$qpa = db_construct_qpa($qpa, ' ((');
			$qpa = $this->addMatchCondition($qpa, 'skills_data.title');
			$qpa = db_construct_qpa($qpa, ') OR (');
			$qpa = $this->addMatchCondition($qpa, 'skills_data.keywords');
			$qpa = db_construct_qpa($qpa, '))');
		}
		$qpa = db_construct_qpa($qpa, ')');
		$qpa = db_construct_qpa($qpa, 'AND skills_data.skills_data_id = skills_data_idx.skills_data_id ');
		$qpa = db_construct_qpa($qpa, 'AND (skills_data.user_id=users.user_id) AND (skills_data.type=skills_data_types.type_id) ');
		$qpa = db_construct_qpa($qpa, 'ORDER BY ts_rank(vectors, q) DESC, finish DESC');
		return $qpa;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
