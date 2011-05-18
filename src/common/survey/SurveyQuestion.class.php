<?php
/**
 * FusionForge surveys
 *
 * Copyright 2004, Sung Kim/GForge, LLC
 * Copyright 2009, Roland Mas
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

require_once $gfcommon.'include/Error.class.php';

class SurveyQuestion extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group; //group object

	/**
	 * Constructor.
	 *
	 * @param	object	The Group object to which this Survey Question is associated.
	 * @param	int	The questtion_id.
	 * @param	array	The associative array of data.
	 * @return	boolean	success.
	 */
	function SurveyQuestion(&$Group, $question_id = false, $arr = false) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError(sprintf(_('%1$s:: No Valid Group Object'), 'Survey Question'));
			return false;
		}
		if ($Group->isError()) {
			$this->setError('Survey:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($question_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($question_id)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError(_('Group_id in db result does not match Group Object'));
					$this->data_array = null;
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * create - use this function to create a survey question
	 *
	 * @param	string	The question
	 * @param	int	The question type
	 *                      1: Radio Buttons 1-5
	 *                      2: Text Area
	 *                      3: Radio Buttons Yes/No
	 *                      4: Comment Only
	 *                      5: Text Field
	 *                      6: None
	 * @return	boolean	success.
	 */
	function create($question, $question_type = 1) {
		if (strlen($question) < 3) {
			$this->setError(_('Question is too short'));
			return false;
		} else {
			// Current permissions check.
			// permission should be checked in higer level to faciliate usability
		}

		$group_id = $this->Group->GetID();

		$res = db_query_params('INSERT INTO survey_questions (group_id,question,question_type) VALUES ($1,$2,$3)',
					array($group_id,
					      htmlspecialchars($question),
					      $question_type));
		if (!$res) {
			$this->setError(_('Question Added').db_error());
			return false;
		} 

		/* Load question to data array */
		$question_id = db_insertid($res,'survey_questions','question_id');
		return $this->fetchData($question_id);
	}



	/**
	 * update - use this function to update a survey question
	 *
	 * @param	string	The question
	 * @param	int	The question type
	 *                      1: Radio Buttons 1-5
	 *                      2: Text Area
	 *                      3: Radio Buttons Yes/No
	 *                      4: Comment Only
	 *                      5: Text Field
	 *                      6: None
	 *	@return	boolean	success.
	 */
	function update($question, $question_type = 1) {
		if (strlen($question) < 3) {
			$this->setError(_('Question is too short'));
			return false;
		} else {
			// Current permissions check.
			// permission should be checked in higer level to faciliate usability
		}

		$group_id = $this->Group->GetID();
		$question_id = $this->getID();

		$res = db_query_params('UPDATE survey_questions SET question=$1, question_type=$2 where question_id=$3 AND group_id=$4',
					array(htmlspecialchars($question),
					      $question_type,
					      $question_id,
					      $group_id));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('UPDATE FAILED').db_error());
			return false;
		}
		return $this->fetchData($question_id);
	}

	/**
	 * delete - use this function to delete a survey question
	 *
	 * @return	boolean	success.
	 */
	function delete() {
		$group_id = $this->Group->GetID();
		$question_id = $this->getID();

		$res = db_query_params('DELETE FROM survey_questions where question_id=$1 AND group_id=$2',
					array($question_id,
					      $group_id));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Delete failed').db_error());
			return false;
		}

		$this->data_array = null;
		return true;
	}

	/**
	 * fetchData - re-fetch the data for this survey question from the database.
	 *
	 * @param	int	The survey question_id.
	 * @return	boolean	success.
	 */
	function fetchData($question_id) {
		$group_id = $this->Group->GetID();
		
		$res = db_query_params('SELECT survey_questions.*, survey_question_types.type 
		      FROM survey_questions ,survey_question_types 
		      WHERE survey_question_types.id=survey_questions.question_type 
		      AND survey_questions.question_id=$1
		      AND survey_questions.group_id=$2',
					array($question_id,
					      $group_id));

		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Error finding question').db_error());
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getGroup - get the Group object this SurveyQuestion is associated with.
	 *
	 * @return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getID - Get the id of this Survey Question
	 *
	 * @return	int	The question_id
	 */
	function getID() {
		return $this->data_array['question_id'];
	}

	/**
	 * getQuestion - Get the question
	 *
	 * @return	string	the question
	 */
	function getQuestion() {
		return $this->data_array['question'];
	}


	/**
	 * getQuestionType - Get the question type
	 *
	 * @return	int	the question type
	 */
	function getQuestionType() {
		return $this->data_array['question_type'];
	}


	/**
	 * getQuestionStringType - Get the type from survey_question_types
	 *
	 * @return	string	the question type
	 */
	function getQuestionStringType() {
		return $this->data_array['type'];
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
