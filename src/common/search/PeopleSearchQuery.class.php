<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
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
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function PeopleSearchQuery($words, $offset, $isExact) {
		$this->SearchQuery($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {


		$qpa = db_construct_qpa () ;

		if (forge_get_config('use_fti')) {
			if (count ($this->words)) {
				$words = $this->getFormattedWords();
				$qpa = db_construct_qpa ($qpa,
							 'SELECT users.user_id, user_name, headline(realname, q) as realname FROM users, to_tsquery($1) AS q, users_idx WHERE status=$2 AND users_idx.user_id = users.user_id AND (vectors @@ q ',
							 array ($words,
								'A'));
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT users.user_id, user_name, realname FROM users WHERE status=$1 AND users_idx.user_id = users.user_id AND (',
							 array ('A'));
			}
			if (count ($this->phrases)) {
				if (count ($this->words)) {
					$qpa = db_construct_qpa ($qpa,
								 $this->getOperator()) ;
				}
				$qpa = db_construct_qpa ($qpa,
							 '(') ;
				$qpa = $this->addMatchCondition($qpa, 'user_name');
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition($qpa, 'realname');
				$qpa = db_construct_qpa ($qpa,
							 ')') ;
			}
			if (count ($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 ') ORDER BY rank(vectors, q) DESC, user_name') ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 ') ORDER BY user_name') ;
			}
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT user_name,user_id,realname FROM users WHERE ((') ;
			$qpa = $this->addIlikeCondition ($qpa, 'user_name') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'realname') ;
			$qpa = db_construct_qpa ($qpa,
						 ')) AND status=$1 ORDER BY user_name',
						 array ('A')) ;
		}
		return $qpa ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
