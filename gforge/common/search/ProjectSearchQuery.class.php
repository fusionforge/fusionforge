<?php

/**
 * GForge Search Engine
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2004 (c) Guillaume Smet / Open Wide
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once('common/search/SearchQuery.class.php');

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

?>
