<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
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

class ExportProjectSearchQuery extends SearchQuery {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function ExportProjectSearchQuery($words, $offset, $isExact) {	
		$this->SearchQuery($words, $offset, $isExact, 200);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {

		$qpa = db_construct_qpa () ;
		if (forge_get_config('use_fti')) {
			$words = $this->getFormattedWords();
			if(count($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT headline(unix_group_name, q) as unix_group_name, headline(short_description, q) as short_description, type_id, groups.group_id, license, register_time FROM groups, groups_idx, to_tsquery($1) q ',
							 array (implode (' ', $words))) ;
				$qpa = db_construct_qpa ($qpa,
							 'WHERE status IN ($1, $2) AND short_description <> $3 AND groups.group_id = groups_idx.group_id',
							 array ('A',
								'H',
								'')) ;
				$qpa = db_construct_qpa ($qpa,
							 ' AND (vectors @@ q' ) ;
				if (count($this->phrases)) {
					$qpa = db_construct_qpa ($qpa,
								 $this->getOperator()) ;
					$qpa = db_construct_qpa ($qpa,
								 '(') ;
					$qpa = $this->addMatchCondition($qpa, 'group_name');
					$qpa = db_construct_qpa ($qpa,
								 ') OR (') ;
					$qpa = $this->addMatchCondition($qpa, 'unix_group_name');
					$qpa = db_construct_qpa ($qpa,
								 ') OR (') ;
					$qpa = $this->addMatchCondition($qpa, 'short_description');
					$qpa = db_construct_qpa ($qpa,
								 ')') ;
				}
				$qpa = db_construct_qpa ($qpa,
							 ') ORDER BY rank(vectors, q) DESC, group_name ASC') ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT unix_group_name, short_description, type_id, groups.group_id, license, register_time FROM groups ') ;
				$qpa = db_construct_qpa ($qpa,
							 'WHERE status IN ($1, $2) AND short_description <> $3',
							 array ('A',
								'H',
								'')) ;
				if (count($this->phrases)) {
					$qpa = db_construct_qpa ($qpa,
								 ' AND (' ) ;
					$qpa = db_construct_qpa ($qpa,
								 '(') ;
					$qpa = $this->addMatchCondition($qpa, 'group_name');
					$qpa = db_construct_qpa ($qpa,
								 ') OR (') ;
					$qpa = $this->addMatchCondition($qpa, 'unix_group_name');
					$qpa = db_construct_qpa ($qpa,
								 ') OR (') ;
					$qpa = $this->addMatchCondition($qpa, 'short_description');
					$qpa = db_construct_qpa ($qpa,
								 '))') ;
				}
				$qpa = db_construct_qpa ($qpa,
							 ') ORDER BY group_name' ) ;
			}
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT group_name,unix_group_name,type_id,groups.group_id, short_description,license,register_time FROM groups WHERE status IN ($1, $2) AND short_description <> $3 AND groups.group_id = groups_idx.group_id',
							 array ('A',
								'H',
								'')) ;
                        $qpa = db_construct_qpa ($qpa,
                                                 ' AND ((') ;
                        $qpa = $this->addIlikeCondition ($qpa, 'group_name') ;
                        $qpa = db_construct_qpa ($qpa,
                                                 ') OR (') ;
                        $qpa = $this->addIlikeCondition ($qpa, 'unix_group_name') ;
			$qpa = db_construct_qpa ($qpa,
                                                 ') OR (') ;
                        $qpa = $this->addIlikeCondition ($qpa, 'short_description') ;
			$qpa = db_construct_qpa ($qpa,
                                                 '))') ;
		}
		return $qpa ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
