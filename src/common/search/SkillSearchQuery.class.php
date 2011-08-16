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

class SkillSearchQuery extends SearchQuery {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function SkillSearchQuery($words, $offset, $isExact) {
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
			if(count($this->words)) {
				$words = $this->getFormattedWords();
				$qpa = db_construct_qpa ($qpa,
							 'SELECT skills_data.skills_data_id, skills_data.type, skills_data.start, skills_data.finish, ts_headline(skills_data.title, q) as title, ts_headline(skills_data.keywords, q) as keywords FROM skills_data, users, skills_data_types, to_tsquery($1) AS q, skills_data_idx WHERE (vectors @@ q ',
							 array ($words)) ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT skills_data.skills_data_id, skills_data.type, skills_data.start, skills_data.finish, FROM skills_data, users, skills_data_types  WHERE (vectors @@ q ') ;
			}

			if (count ($this->phrases)) {
				if (count ($this->words)) {
					$qpa = db_construct_qpa ($qpa,
								 $this->getOperator()) ;
				}
				$qpa = db_construct_qpa ($qpa,
							 ' ((') ;
				$qpa = $this->addMatchCondition ($qpa, 'skills_data.title') ;
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition ($qpa, 'skills_data.keywords') ;
				$qpa = db_construct_qpa ($qpa,
							 '))') ;
			}
			$qpa = db_construct_qpa ($qpa,
						 ')') ;
			if (count ($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 'AND skills_data.skills_data_id = skills_data_idx.skills_data_id ') ;
			}
			$qpa = db_construct_qpa ($qpa,
						 'AND (skills_data.user_id=users.user_id) AND (skills_data.type=skills_data_types.type_id) ') ;
			if (count ($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 'ORDER BY ts_rank(vectors, q) DESC, finish DESC') ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'ORDER BY finish DESC') ;
			}
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT * FROM skills_data, users, skills_data_types WHERE ((') ;
			$qpa = $this->addIlikeCondition ($qpa, 'skills_data.title') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'skills_data.keywords') ;
			$qpa = db_construct_qpa ($qpa,
						 ')) AND (skills_data.user_id=users.user_id) AND (skills_data.type=skills_data_types.type_id) ORDER BY finish DESC') ;
		}
		return $qpa ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
