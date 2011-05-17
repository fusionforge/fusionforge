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
require_once $gfcommon.'survey/SurveyQuestionFactory.class.php';

class Survey extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $data_array.
	 */
	var $data_array;

	/**
         * Questions array in this survey
         *
         * @var array   $question_array.
         */
	var $all_question_array;
          
	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group; 

	/**
	 *  Constructor.
	 *
	 *  @param  object	The Group object to which this servey is associated.
	 *  @param  int	        The servey_id.
	 *  @param  array	The associative array of data.
	 *  @return boolean	success.
	 */
	function Survey(&$Group, $survey_id=false, $arr=false) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError(sprintf(_('%1$s:: No Valid Group Object'), "Survey"));
			return false;
		}
		if ($Group->isError()) {
			$this->setError('Survey:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($survey_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($survey_id)) {
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
	 *	create - use this function to create a survey 
	 *
	 *	@param	string	          The survey title
	 *	@param	int array         The question numbers to be added
	 *	@param  is_active         1: Active, 0: Inactive
	 *	For future options
	 *	@param  is_public         0: Admins Only, 1: Group Members, 2: Gforge user, 3:Every body
	 *	@param  is_result_public  0: Admins Only, 1: Group Members, 2: Gforge user, 3:voted user 4:Every body
	 *	@param  double_vote       Allow double vote if it is 1
	 *	@return	boolean	success.
	 */
	function create($survey_title, $add_questions, $is_active=0, $is_public=1, $is_result_public=0, $double_vote=0) {
		if (!$survey_title) {
			$this->setError(_('Update Failed: Survey Title Required'));
			return false;
			/* We need at least one survey question at this point */	
		} else if (!$add_questions || !is_array($add_questions) || count($add_questions)<1) {
			$this->setError(_('Update Failed: Survey Questions Required'));
			return false;
		}

		$group_id = $this->Group->GetID();
		
		/* Make old style survey string from array: 1, 2, 3, ..., n */
		$survey_questions = $this->_makeQuestionString($add_questions);

		$result = db_query_params ('INSERT INTO surveys (survey_title,group_id,survey_questions,is_active) VALUES ($1,$2,$3,$4)',
					   array (htmlspecialchars($survey_title),
						  $group_id,
						  $survey_questions,
						  $is_active)) ;
		if (!$result) {
			$this->setError(_('Insert Error').db_error());
			return false;
		} 

		/* Load question to data array */
		$survey_id=db_insertid($result,'surveys','survey_id');
		return $this->fetchData($survey_id);
	}



	/**
	 *	update - use this function to update a survey 
	 *
	 *	@param	string	          The survey title
	 *	@param	int array         The question numbers to be added
	 *	@param	int array         The question numbers to be deleted
	 *	@param  is_active         1: Active, 0: Inactive
	 *	@param  is_public         0: Admins Only, 1: Group Members, 2: Gforge user, 3:Every body
	 *	@param  is_result_public  0: Admins Only, 1: Group Members, 2: Gforge user, 3:voted user 4:Every body
	 *	@param  double_vote       Allow double vote if it is 1
	 *	@return	boolean	success.
	 */
	function update($survey_title, &$add_questions, &$del_questions, $is_active=0, $is_public=1, $is_result_public=0, $double_vote=0) {
		if (!$survey_title) {
			$this->setError(_('Update Failed: Survey Title Required'));
			return false;
			/* We need at least one survey question at this point */	
		}

		$group_id = $this->Group->GetID();
		$survey_id = $this->getID();

		/* Ths Survey is not ready to update */
		if (!$survey_id) {
			$this->setError(_('The Survey data is not filled'));
			return false;
		}
		
		$survey_questions = $this->_updateQuestionString($add_questions, $del_questions);
		$result = db_query_params ('UPDATE surveys SET survey_title=$1, survey_questions=$2, is_active=$3 WHERE survey_id=$4 AND group_id=$5',
					   array (htmlspecialchars($survey_title),
						  $survey_questions,
						  $is_active,
						  $survey_id,
						  $group_id)) ;
		if (db_affected_rows($result) < 1) {
			 $this->setError(_('UPDATE FAILED').db_error());
			 return false;
		} 
		/* Update internal data */
		return $this->fetchData($survey_id);
	}

        /**
	 *	updateOrder - use this function to update question order
	 *
	 *	@param	int 	          Question number
	 *	@param	boolean           decide up or down. it is up if it is true
	 *	@return	boolean	success.
	 */
	function updateOrder($question_number, $is_up=true) {
		$group_id = $this->Group->GetID();
		$survey_id = $this->getID();

		/* Ths Survey is not ready to update */
		if (!$survey_id) {
			$this->setError(_('The Survey data is not filled'));
			return false;
		}

		/* Decide delta */
		if ($is_up) {
			$delta = -1;
		} else { 
			$delta = 1;
		}

		$survey_questions = $this->_updateQuestionStringOrder($question_number, $delta);
		$result = db_query_params ('UPDATE surveys SET survey_questions=$1 WHERE survey_id=$2 AND group_id=$3',
					   array ($survey_questions,
						  $survey_id,
						  $group_id)) ;
		if (db_affected_rows($result) < 1) {
			 $this->setError(_('UPDATE FAILED').db_error());
			 return false;
		} 
		
                /* Update internal data */
		return $this->fetchData($survey_id);
	}

	/**
	 *	delete - use this function to delete this survey
	 *               (We don't support delete yet)
	 *
	 *	@return	boolean	success.
	 */
	function delete() {
		$group_id = $this->Group->GetID();
		$survey_id = $this->getID();

		$res = db_query_params ('DELETE FROM surveys where survey_id=$1 AND group_id=$2',
					array ($survey_id,
					       $group_id)) ;
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Delete failed').db_error());
			return false;
		}
		
		/* Delete internal data */
		$this->data_array = null;
		return true;
	}

	/**
	 *  fetchData - re-fetch the data for this survey from the database.
	 *
	 *  @param  int	 The survey_id.
	 *  @return	boolean	success.
	 */
	function fetchData($survey_id) {
		$group_id = $this->Group->GetID();
		
		$res = db_query_params ('SELECT * FROM surveys where survey_id=$1 AND group_id=$2',
					array ($survey_id,
					       $group_id)) ;
	
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('No Survey is found').db_error());
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getGroup - get the Group object this Survey is associated with.
	 *
	 *	@return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getID - Get the id of this Survey
	 *
	 *	@return	int	The question_id
	 */
	function getID() {
		return $this->data_array['survey_id'];
	}

	/**
	 *	isActriv - return if it is active
	 *
	 *	@return	int	is active
	 */
	function isActive() {
		return $this->data_array['is_active'];
	}

	/**
	 *	getTitle - Get the Survey title
	 *
	 *	@return string the survey title
	 */
	function getTitle() {
		return $this->data_array['survey_title'];
	}

        /**
	 *	getQuestionString - Get the question string
	 *
	 *	@return string the question
	 */
	function getQuestionString() {
		return $this->data_array['survey_questions'];
	}

        /**
	 *	getNumberOfQuestion - Get the number of questions
	 *
	 *	@return int the number questions
	 */
	function getNumberOfQuestions() {
		return count($this->getQuestionArray());
	}

        /**
	 *	getNumberOfVotes - Get the number of votes
	 *
	 *	@return int the number votes
	 */
	function getNumberOfVotes() {
		$group_id = $this->Group->GetID();
		$survey_id = $this->getID();
		
		$res = db_query_params ('SELECT 1 FROM survey_responses WHERE survey_id=$1 AND group_id=$2 GROUP BY user_id',
					array ($survey_id,
					       $group_id)) ;
		$ret = db_numrows($res);
		db_free_result($res);		
		
		return $ret;
	}
	
	/**
	 *	isUserVote - Figure out the user voted or not
	 *
	 *	@param  int user_id
	 *	@return true of false
	 */
	function isUserVote($user_id) {
		$group_id = $this->Group->GetID();
		$survey_id = $this->getID();

		$res = db_query_params ('SELECT 1 FROM survey_responses where survey_id=$1 AND group_id=$2 AND user_id=$3',
					array ($survey_id,
					       $group_id,
					       $user_id)) ;
		$ret = db_numrows($res);
		db_free_result($res);		
		
		return $ret;
	}
	
	/**
	 *	getQuestionArray - Get the question string numbers in array
	 *
	 *	@return string the question
	 */
	function &getQuestionArray() {
		$ret_arr = array();
		$questions = $this->getQuestionString();
		if (!$questions) {
			return $ret_arr;
		}

		$arr_from_str = explode(',', $questions);

		/* Remove non existed questions */ 
		for ($i=0; $i<count($arr_from_str); $i++) {
			if ($this->_isValidQuestionID($arr_from_str[$i])) {
				$ret_arr[] = $arr_from_str[$i];
			}
		}
		
		return $ret_arr;
	}

	/**
	 *	getQuestionInstances - Get the SurveyQuestion array belongs to this Survey by order
	 *
	 *	@return string the question
	 */
	function &getQuestionInstances() {
		$ret = array();

		if (!$this->all_question_array || !is_array($this->all_question_array)) {
			$this->_fillSurveyQuestions();
		}

		$arr = & $this->getQuestionArray();
		
		for ($i=0; $i<count($arr); $i++) {
			for ($j=0; $j<count($this->all_question_array); $j++) {
				/* If it is, copy into new array in order */
				if ($this->all_question_array[$j]->getID()==$arr[$i]) {
					$ret[] = $this->all_question_array[$j];
					break;
				}
			}
		}
		
		return $ret;
	}
	
	/**
	 *	getAddableQuestionInstances - Get the addable SurveyQuestion from all questions
	 *
	 *	@return string the question
	 */
	function &getAddableQuestionInstances() {
		$ret = array();

		if (!$this->all_question_array || !is_array($this->all_question_array)) {
			 $this->_fillSurveyQuestions();
		}

		$arr = & $this->getQuestionArray();
		if ($arr) {		
			/* Copy questions only if it is not in question string */
			for ($i=0; $i<count($this->all_question_array); $i++) {
				if (array_search($this->all_question_array[$i]->getID(), $arr)==false && 
				    $this->all_question_array[$i]->getID()!=$arr[0]) {
					$ret[] = $this->all_question_array[$i];
				}
			}
		} else {
			$ret = $this->all_question_array;
		}
		
		return $ret;
	}

	/***************************************************************
         * private question string deal methods
         * TODO: Add a joint table for surveys and survey_questions. 
         *       Deal with DBMS not comma separated string
         ***************************************************************/

	/**
	 *      _fillSurveyQuestions - Get all Survey Questions using SurveyQuestionFactory
         *
         *      @return booelan suesssness
         */
	function _fillSurveyQuestions() {
		$sqf = new SurveyQuestionFactory($this->getGroup());
		$this->all_question_array = & $sqf->getSurveyQuestions();
	}

	
	/**
	 *  _isValidQuestionID - Check it is correct question id
	 *
	 *  @param int questioin id 
	 *  @return boolean true if it is valid question id
	 */
	function _isValidQuestionID($question_id) {
		if (!$this->all_question_array || !is_array($this->all_question_array)) {
			$this->_fillSurveyQuestions();
		}
		
		for ($i=0; $i<count($this->all_question_array); $i++) {
			if ($question_id == $this->all_question_array[$i]->getID()) {
				return true;
			}
		}
		return false;
	}
 	
	
	/**
	 *  _makeQuestionString - Make comma separated question number string
	 *
	 *  @param    int array	 Array of question number
	 *  @return   string     question_strong (example: 1, 2, 3, 7);
	 */
	function _makeQuestionString($arr) {
		
		/* No questions to add */
		if (!$arr || !is_array($arr) || count($arr)<1) {
			return '';
		}
		return join(',', $arr);
	}

	/**
	 *  _updateQuestionString - Update comma saparated question number string
	 *
	 *  @param    int array	 Array of questions to add
	 *  @param    int array	 Array of questions to delete
	 *  @return   string     question_strong (example: 1, 2, 3, 7);
	 */
	function _updateQuestionString(&$arr_to_add, &$arr_to_del) {
		/* Get array of current question string */
		$arr = & $this->getQuestionArray();

		/* questions to add */
		if (empty($arr)) {
			$arr = $arr_to_add;
		} else {
			if ($arr_to_add && is_array($arr_to_add) && count($arr_to_add)>0) {
				for ($i = 0; $i < count($arr_to_add); $i++) {
				/* Avoid double question */
					if ($arr_to_add[$i] && array_search($arr_to_add[$i], $arr)==false && $arr_to_add[$i]!=$arr[0]) {
						$arr[] = $arr_to_add[$i];
					}
				}  
			}
		}
		
		/* questions to delete */
		if ($arr_to_del && is_array($arr_to_del) && count($arr_to_del)>0) {
			$new_arr = array();
			for ($i = 0; $i < count($arr); $i++) {
				/* If the value is no in the delete array, copy it into new array */
				if ($arr[$i] && array_search($arr[$i], $arr_to_del)==false && $arr_to_del[0]!=$arr[$i]) {
					$new_arr[] = $arr[$i];
				}
			}  
			/* copy new_arr to arr */
			$arr = $new_arr;
		}

		/* converty array to String */
		return $this->_makeQuestionString($arr);
	}

        /**
	 *  _updateArrayOrder - Update array order
	 *
	 *  @param    int 	 question number
	 *  @param    int	 increment or decrement (must be 1 or -1)
	 *  @return   string     question_strong (example: 1, 2, 3, 7);
	 */
	function _updateQuestionStringOrder($question_number, $delta) {
		/* Get array of current question string */
		$arr = & $this->getQuestionArray();

		/* We are expectiong array */
		if (!$arr || !is_array($arr)) {
			return $this->getQuestionString();
		}

		$index = array_search($question_number, $arr);
		
		/* The question number is not in the array 
		 * We have nothing to change 
		 */ 
		if ($index==false && $question_number!=$arr[0]) {
			return $this->getQuestionString();
		}

		$new_index = $index + $delta;

		/* Out of boundary */
		if ($new_index < 0 || $new_index >= count($arr)) {
			return $this->getQuestionString();
		}

		/* swap */
		$tmp = $arr[$index];
		$arr[$index] = $arr[$new_index];
		$arr[$new_index] = $tmp;

		/* converty array to String */
		return $this->_makeQuestionString($arr);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
