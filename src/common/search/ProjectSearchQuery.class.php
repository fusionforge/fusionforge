<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
 * Copyright 2010, Roland Mas
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

class ProjectSearchQuery extends SearchQuery {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function ProjectSearchQuery($words, $offset, $isExact) {
		$this->SearchQuery($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {
		global  $LUSER;

		$qpa = db_construct_qpa () ;

		if (forge_get_config('use_fti')) {
			if (count ($this->words)) {
				$words = $this->getFormattedWords();
				$qpa = db_construct_qpa ($qpa,
							 'SELECT DISTINCT ON (ts_rank(vectors, q), group_name) type_id, g.group_id, ts_headline(group_name, q) as group_name, unix_group_name, ts_headline(short_description, q) as short_description FROM groups AS g, to_tsquery($1) AS q, groups_idx as i WHERE g.status in ($2, $3) ',
							 array ($words,
								'A',
								'H')) ;
				$qpa = db_construct_qpa ($qpa,
							 'AND vectors @@ q ') ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT DISTINCT ON (group_name) type_id, g.group_id, group_name, unix_group_name, short_description FROM groups AS g WHERE g.status in ($1, $2) ',
							 array ('A',
								'H')) ;
			}
			if (count($this->phrases)) {
				$qpa = db_construct_qpa ($qpa,
							 ' AND ((') ;
				$qpa = $this->addMatchCondition($qpa, 'group_name');
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition($qpa, 'short_description');
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition($qpa, 'unix_group_name');
				$qpa = db_construct_qpa ($qpa,
							 ')) ') ;
			}				
			if (count ($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 'AND g.group_id = i.group_id ORDER BY ts_rank(vectors, q) DESC, group_name') ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'ORDER BY group_name') ;
			}
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT g.group_name AS group_name, g.unix_group_name AS unix_group_name, g.type_id AS type_id, g.group_id AS group_id, g.short_description AS short_description FROM groups g WHERE g.status IN ($1, $2) AND ((',
						 array ('A', 'H')) ;
			$qpa = $this->addIlikeCondition ($qpa, 'g.group_name') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'g.short_description') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'g.unix_group_name') ;
			$qpa = db_construct_qpa ($qpa,
						 ')) ORDER BY g.group_name') ;
		}
		return $qpa ;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
