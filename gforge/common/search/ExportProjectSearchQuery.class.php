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
	 * getQuery - get the sql query built to get the search results
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {
		global $sys_use_fti;
		if ($sys_use_fti) {
			$words = $this->getFormattedWords();
			if(count($this->words)) {
				$tsquery0 = "headline(unix_group_name, q) as unix_group_name, headline(short_description, q) as short_description";
				$tsquery = ", groups_idx, to_tsquery('".$words."') q";
				$tsmatch = "vectors @@ q";
				$rankCol = "";
				$tsjoin = 'AND groups.group_id = groups_idx.group_id ';
				$orderBy = "ORDER BY rank(vectors, q) DESC, group_name ASC";
				$phraseOp = $this->getOperator();
			} else {
				$tsquery0 = "unix_group_name, short_description";
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "";
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
			$sql = "SELECT $tsquery0,
				type_id,
				groups.group_id,
				license,
				register_time
				FROM groups $tsquery
				WHERE status IN ('A', 'H') AND is_public='1' AND short_description <> ''
				$tsjoin AND ($tsmatch $phraseCond)
				$orderBy";
		} else {
			$groupNameCond = $this->getIlikeCondition('group_name', $this->words);
			$groupDescriptionCond = $this->getIlikeCondition('short_description', $this->words);
			$groupUnixNameCond = $this->getIlikeCondition('unix_group_name', $this->words);
			
			$sql = 'SELECT group_name,unix_group_name,type_id,groups.group_id, '
				.'short_description,license,register_time '
				.'FROM groups '
				.'WHERE status IN (\'A\', \'H\') '
				.'AND is_public=\'1\' '
				.'AND groups.short_description<>\'\' '
				.'AND (('.$groupNameCond.') OR ('.$groupDescriptionCond.') OR ('.$groupUnixNameCond.'))';
		}
		return $sql;
	}
}

?>
