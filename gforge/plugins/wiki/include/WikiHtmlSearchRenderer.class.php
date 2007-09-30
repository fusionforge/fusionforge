<?php
/**
 * Wiki Search Engine for GForge
 *
 * Copyright 2006 (c) Alain Peyrat
 *
 * @version $Id: NewsHtmlSearchRenderer.class,v 1.1 2004/10/16 16:36:31 gsmet Exp $
 */
 
require_once('www/search/include/renderers/HtmlGroupSearchRenderer.class.php');
require_once('WikiSearchQuery.class.php');

class WikiHtmlSearchRenderer extends HtmlGroupSearchRenderer {
	
	var $groupId;
	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param array $sections array of all sections to search in (array of strings)
	 *
	 */
	function WikiHtmlSearchRenderer($words, $offset, $isExact, $groupId) {
		global $Language;
		
		$this->groupId = $groupId;
		
		$searchQuery = new WikiSearchQuery($words, $offset, $isExact, $groupId);
		
		//init the searchrendererr
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_WIKI, $words, $isExact, 
			$searchQuery, $groupId, 'wiki');
		
		$this->tableHeaders = array(
			$Language->getText('plugin_wiki', 'pagename'),
			$Language->getText('plugin_wiki', 'author'),
			$Language->getText('plugin_wiki', 'date'),
		);
	}
	
	/**
	 * getRows - get the html output for result rows
	 *
	 * @return string html output
	 */
	function getRows() {
		$rowsCount = $this->searchQuery->getRowsCount();
		$result =& $this->searchQuery->getResult();
		$dateFormat = $GLOBALS['sys_datefmt'];
		
		$group = group_get_object($this->groupId);
		$group_name = $group->getUnixName();
		
		$data = unserialize(db_result($result, 0, 'versiondata'));
		
		$return = '';
		for($i = 0; $i < $rowsCount; $i++) {
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'
				. '<td><a href="/wiki/g/'. $group_name.'/'.db_result($result, $i, 'pagename').'">'
				. html_image('ic/msg.png', '10', '12', array('border' => '0'))
				. ' '.db_result($result, $i, 'pagename').'</a></td>
				<td width="15%">'.$data['author'].'</td>
				<td width="15%">'.date($dateFormat, db_result($result, $i, 'mtime')).'</td></tr>';
		}
		return $return;
	}
}

?>
