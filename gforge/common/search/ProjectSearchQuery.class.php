<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
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
	 * getQuery - get the sql query built to get the search results
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {
		global $sys_use_fti;
		if ($sys_use_fti) {
			if(count($this->words)) {
				$tsquery0 = "headline(group_name, q) as group_name, " .
						"unix_group_name, " .
						"headline(short_description, q) as short_description";
				$words = $this->getFormattedWords();
				$tsquery = ", to_tsquery('$words') AS q, groups_idx as i ";
				$tsmatch = "vectors @@ q";
				$rankCol = "";
				$tsjoin = 'AND g.group_id = i.group_id';
				$distinctOn = "rank(vectors, q), group_name";
				$orderBy = "ORDER BY rank(vectors, q) DESC, group_name";
				$phraseOp = $this->getOperator();
			} else {
				$tsquery0 = "group_name, unix_group_name, short_description";
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "";
				$distinctOn = "group_name";
				$orderBy = "ORDER BY group_name";
				$phraseOp = "";
			}
			$phraseCond = '';
			if(count($this->phrases)) {
				$groupNameCond = $this->getMatchCond('group_name', $this->phrases);
				$groupDescriptionCond = $this->getMatchCond('short_description', $this->phrases);
				$groupUnixNameCond = $this->getMatchCond('unix_group_name', $this->phrases);
				$phraseCond = $phraseOp.' (('.$groupNameCond.') OR ('.$groupDescriptionCond.') OR ('.$groupUnixNameCond.'))';
			}
			$sql = "SELECT DISTINCT ON ($distinctOn) type_id, g.group_id, " .$tsquery0.
					" FROM groups AS g ".$tsquery.
					" WHERE g.status in ('A', 'H') AND ($tsmatch $phraseCond) $tsjoin $orderBy";
		} else {
			$groupNameCond = $this->getIlikeCondition('group_name', $this->words);
			$groupDescriptionCond = $this->getIlikeCondition('short_description', $this->words);
			$groupUnixNameCond = $this->getIlikeCondition('unix_group_name', $this->words);
			
			$sql = 'SELECT group_name, unix_group_name, type_id, group_id, short_description '
				.'FROM groups '
				.'WHERE status IN (\'A\', \'H\') '
				.'AND is_public=\'1\' '
				.'AND (('.$groupNameCond.') OR ('.$groupDescriptionCond.') OR ('.$groupUnixNameCond.'))';
		}
		return $sql;
	}
	
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
