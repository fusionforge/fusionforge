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

class PeopleSearchQuery extends SearchQuery {

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return	array	query+params array
	 */
	function getQuery() {
		$words = $this->getFTIwords();
		$qpa = db_construct_qpa(false, 'SELECT users.user_id, user_name, ts_headline(realname, q) as realname FROM users, to_tsquery($1) AS q, users_idx WHERE status=$2 AND users_idx.user_id = users.user_id AND (vectors @@ q ',
						array ($words, 'A'));
		if (count ($this->phrases)) {
			$qpa = db_construct_qpa($qpa, $this->getOperator());
			$qpa = db_construct_qpa($qpa, '(');
			$qpa = $this->addMatchCondition($qpa, 'user_name');
			$qpa = db_construct_qpa($qpa, ') OR (');
			$qpa = $this->addMatchCondition($qpa, 'realname');
			$qpa = db_construct_qpa($qpa, ')') ;
		}
		$qpa = db_construct_qpa($qpa, ') ORDER BY ts_rank(vectors, q) DESC, user_name');
		return $qpa;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
