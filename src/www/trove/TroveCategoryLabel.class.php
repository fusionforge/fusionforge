<?php
/**
 *
 * Trove facility
 *
 * Copyright 2004 (c) Guillaume Smet
 * http://fusionforge.org/
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

/**
CREATE TABLE trove_category_labels (
label_id serial,
category_id int REFERENCES trove_cat ON UPDATE CASCADE ON DELETE CASCADE,
language_id int REFERENCES supported_languages ON UPDATE CASCADE ON DELETE CASCADE,
label varchar(255),
PRIMARY KEY(label_id));
**/

class TroveCategoryLabel extends Error {

	var $labelId;
	var $category;
	var $dataArray = false;

	function TroveCategoryLabel(& $category, $labelId = false, $dataArray = false) {
		$this->Error();
		if (!$category || !is_object($category)) {
			return false;
		}
		if ($category->isError()) {
			//$this->setError('MailingList:: '.$Group->getErrorMessage());
			return false;
		}
		$this->category =& $category;

		if ($labelId) {
			$this->labelId = $labelId;
			if (!$dataArray || !is_array($dataArray)) {
				if (!$this->fetchData($labelId)) {
					return false;
				}
			} else {
				$this->dataArray =& $dataArray;
				if ($this->dataArray['category_id'] != $this->category->getId()) {
					$this->dataArray = null;
					return false;
				}
			}
		}

		return true;
	}

	function create($label, $languageId) {
		if(strlen($label) == 0) {
			// set error
			return false;
		}
		
		db_begin();
		$result = db_query_params("INSERT INTO trove_category_labels
			(category_id, label, language_id) VALUES ($1, $2, $3)",
			array($this->category->getId(), $label, $languageId));
		echo db_error();
		if (!$result) {
			db_rollback();
			return false;
		}
		$this->labelId = db_insertid($result, 'trove_category_labels', 'label_id');
		$this->fetchData($this->labelId);
		db_commit();
	}

	function update() {
	}
	
	function fetchData($labelId) {
		$res=db_query_params("SELECT trove_category_labels.*, supported_languages.name AS language_name FROM trove_category_labels, supported_languages "
			. "WHERE trove_category_labels.label_id=$1 "
			. "AND trove_category_labels.category_id=$2 "
			. "AND supported_languages.language_id=trove_category_labels.language_id",
			array($labelId, $this->category->getId()));

		if (!$res || db_numrows($res) < 1) {
			return false;
		}
		$this->dataArray =& db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	function remove() {
		db_begin();
		$res = db_query_params("DELETE FROM trove_category_labels WHERE label_id=$1", array($this->labelId));
		if(!res || db_affected_rows($res) != 1) {
			// $this->setError();
			db_rollback();
			return false;
		} else {
			db_commit();
			return true;
		}
	}

	function getId() {
		return $this->labelId;
	}

	function getLabel() {
		return $this->dataArray['label'];
	}
	
	function getLanguageId() {
		return $this->dataArray['language_id'];
	}
	
	function getLanguageName() {
		return $this->dataArray['language_name'];
	}

}

?>
