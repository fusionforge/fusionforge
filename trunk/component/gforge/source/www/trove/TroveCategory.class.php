<?php

/**
 *
 * GForge Trove facility
 *
 * Copyright 2004 (c) Guillaume Smet
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('TroveCategoryLabel.class.php');

// should extend observable
class TroveCategory extends Error {
	
	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $dataArray.
	 */
	var $dataArray;
	
	/**
	 * Selected Trove category id
	 *
	 * @var int $categoryId
	 */
	var $categoryId;
	
	var $labels;
	var $children;
	var $parents;
	var $parent;
	
	var $filter;
	var $filterQueryAlias = '';
	var $filterQueryAnd = '';
	
	/**
	 *  Constructor.
	 *
	 * @param	int		The trove_cat_id.
	 * @param	array		The associative array of data.
	 * @return	boolean	success.
	 */
	function TroveCategory($categoryId = false, $dataArray = false) {
		if ($categoryId) {
			$this->categoryId = $categoryId;
			if (!$dataArray || !is_array($dataArray)) {
				if (!$this->fetchData($categoryId)) {
					$this->setError(_('Invalid Trove Category'),
							_('That Trove category does not exist.').' '.db_error()
					);
				}
			} else {
				$this->dataArray =& $dataArray;
			}
		} else {
			$this->setError(_('ERROR'), _('That Trove category does not exist.'));
		}
	}
	
	/**
	 *  fetchData - re-fetch the data for this category from the database.
	 *
	 *  @param  int	 The category_id.
	 *	@return	boolean	success.
	 */
	function fetchData($categoryId) {
		global $Language;
		$res=db_query("SELECT *
			FROM trove_cat
			WHERE trove_cat_id='".$categoryId."'", -1, 0, SYS_DB_TROVE);
		if (!$res || db_numrows($res) < 1) {
			return false;
		}
		$this->dataArray =& db_fetch_array($res);
		db_free_result($res);
		return true;
	}
	
	function update($shortName, $fullName, $description) {
		$shortName = trim($shortName);
		$fullName = trim($fullName);
		$description = trim($description);
		if(empty($shortName) || empty($fullName)) {
			$this->setError(_('ERROR'), _('Empty strings'));
			return false;
		} else {
			db_begin();
			$result = db_query("UPDATE trove_cat
				SET	shortname='".htmlspecialchars($shortName)."',
					fullname='".htmlspecialchars($fullName)."',
					description='".htmlspecialchars($description)."',
					version='".date('Ymd',time())."01'
				WHERE trove_cat_id='".$this->categoryId."'"
			);
			if(!$result || db_affected_rows($result) != 1) {
				$this->setError(_('ERROR'), _('Cannot update'));
				db_rollback();
				return false;
			} else {
				db_commit();
				$this->fetchData($this->categoryId);
				return true;
			}
		}
	}
	
	function move() {
	}
	
	function getId() {
		return $this->categoryId;
	}
	
	// returns a localized label if available
	function & getLabel($languageId) {
		if(!isset($this->labels)) {
			$this->getLabels();
		}
		if(isset($this->labels[$languageId])) {
			return $this->labels[$languageId];
		} else {
			//return false;
			return $this->labels;
		}
	}
	
	function getLocalizedLabel() {
		global $Language;
		$languageId = choose_language_from_context();
		$label = $this->getLabel($languageId);
		if($label) {
			return $label->getLabel();
		} else {
			return $this->getFullName();
		}
	}
	
	function & getLabels() {
		if(!isset($this->labels)) {
			$this->labels = array();
			$sql = 'SELECT  trove_category_labels.*, supported_languages.name AS language_name FROM trove_category_labels, supported_languages  WHERE category_id='.$this->categoryId.' AND supported_languages.language_id=trove_category_labels.language_id';
			$res = db_query($sql);
			
			if (!$res) {
				return $this->labels;
			}
			while($data =& db_fetch_array($res)) {
				$this->labels[$data['language_id']] = new TroveCategoryLabel($this, $data['label_id'], $data);
			}
			db_free_result($res);
		}
		return $this->labels;
	}
	
	function & getParents() {
		return $this->parents;
	}
	
	function & getChildren() {
		if(!isset($this->children)) {
			$this->children = array();
			
			$result = db_query("
				SELECT trove_cat.*,
				trove_treesums.subprojects AS subprojects
				FROM trove_cat LEFT JOIN trove_treesums USING (trove_cat_id) 
				WHERE (
					trove_treesums.limit_1=0 
					OR trove_treesums.limit_1 IS NULL
				)
				AND trove_cat.parent='".$this->categoryId."'
				ORDER BY fullname
			", -1, 0, SYS_DB_TROVE);
			
			if(!$result) {
				$this->setError();
				return false;
			} else {
				while ($array = db_fetch_array($result)) {
					$this->children[] = new TroveCategory($array['trove_cat_id'], $array);
				}
			}
		}
		return $this->children;
	}
	
	function getRootCategory() {
	}
	
	function getRootParentId() {
		return $this->dataArray['root_parent'];
	}

	function getFullName() {
		return $this->dataArray['fullname'];
	}
	
	function getShortName() {
		return $this->dataArray['shortname'];
	}
	
	function getDescription() {
		return $this->dataArray['description'];
	}
	
	function getSubProjectsCount() {
		return ($this->dataArray['subprojects'] ? $this->dataArray['subprojects'] : 0);
	}
	
		function setFilter($filterArray) {
		$this->filter = $filterArray;
		
		for($i = 0, $count = sizeof($filterArray); $i < $count; $i++) {
			$this->filterQueryAlias .= ', trove_agg trove_agg_'.$i.' ';
			$this->filterQueryAlias .= 'AND trove_agg_'.$i.'.trove_cat_id='
			.$filterArray[$i].' AND trove_agg_'.$i.'.group_id='
			.'trove_agg.group_id ';
		}
	}
	
	function getProjects($offset) {
		$result = db_query('
			SELECT * 
			FROM trove_agg, '.$this->filterQueryAlias.'
			WHERE trove_agg.trove_cat_id='.$this->categoryId.' '.$this->filterQueryAnd.'
			ORDER BY trove_agg.trove_cat_id ASC, trove_agg.ranking ASC
			', TROVE__PROJECTS_PER_PAGE, '.$offset.', SYS_DB_TROVE);
		return $result;
	}
	
	function addProject() {
	}
	
	function removeProject() {
	}
	
}

?>
