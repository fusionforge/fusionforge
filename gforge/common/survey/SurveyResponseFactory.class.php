<?php
/**
 * GForge Survey Facility: Get Survey Response Factory
 *
 * Copyright 2004 GForge, LLC
 * http://gforge.org/
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



/*
	Survery Response Factory
	By Sung Kim 2004/2/13
*/


require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'survey/SurveyResponse.class.php';

class SurveyResponseFactory extends Error {

       /**
        * The Survey object.
        *
        * @var	 object  $Survey.
        */
	var $Survey;

       /**
        * The Question object.
        *
        * @var	 object  $Question.
        */
	var $Question;

	/**
	 * The Response array.
	 *
	 * @var	 array	Response
	 */
	var $Responses;

	/**
	 * The Aggregated Result array for question.
	 *
	 * @var	 array	Response
	 */
	var $Result;

	/**
	 *  Constructor. 
	 *
	 *	@param	object	The Survey object 
	 *	@param	object	The Question object to which this survey Response is associated.
         *      @param  int     The survey_id
	 */
	function SurveyResponseFactory(&$Survey, &$Question ) { 
		$this->Error();

		if (!$Survey || !is_object($Survey)) {
			$this->setError(_('No valid Survey Object'));
			return false;
		}
		if ($Survey->isError()) {
			$this->setError(_('Survey').':: '.$Survey->getErrorMessage());
			return false;
		}
		if (!$Question || !is_object($Question)) {
			$this->setError(_('No valid Question Object'));
			return false;
		}
		if ($Question->isError()) {
			$this->setError(_('Survey').':: '.$Question->getErrorMessage());
			return false;
		}

		$this->Survey = &$Survey;
		$this->Question = &$Question;

		return true;
	}

	/**
	 *	getGroup - get the Group object this SurveyResponse is associated with.
	 *
	 *	@return object	The Group object.
	 */
	function &getGroup() {
		$Survey = $this->getSurvey();
		return $Survey->Group;
	}

        /**
	 *	getSurvey - get the Survey object this SurveyResponse is associated with.
	 *
	 *	@return object	The Survey object.
	 */
	function &getSurvey() {
		return $this->Survey;
	}

        /**
	 *	getQuestion - get the Question object this SurveyResponse is associated with.
	 *
	 *	@return object	The Question object.
	 */
	function &getQuestion() {
		return $this->Question;
	}

	/**
	 *	getSurveyResponses - get an array of Survey Response objects 
         *                           for the Survey and Question
	 *
 	 *	@return	array	The array of Survey Response objects.
	 */
	function &getSurveyResponses() {
		/* We alread have it */
		if ($this->Responses) {
			return $this->Responses;
		}

		$group = $this->getGroup();
		$group_id = $group->GetID();
		$survey = $this->getSurvey();
		$survey_id = $survey->GetID();
		$question = $this->getQUestion();
		$question_id = $question->GetID();
		
		$sql="SELECT * FROM survey_responses ".
			"WHERE survey_id='$survey_id' ".
			"AND question_id='$question_id' ".
			"AND group_id='$group_id' ORDER BY post_date DESC";

		$result = db_query ($sql);
		if (!$result) {
			$this->setError(_('No Survey Response is found').db_error());
			return false;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->Responses[] = new SurveyResponse($this->getGroup(), $arr);
			}
			db_free_result($result);
		}
		return $this->Responses;
	}


	/**
	 *	getNumberOfSurveyResponses - get the number of Survey Responses
         *                       
 	 *	@return	int      the number of survey responses
	 */
	function &getNumberOfSurveyResponsess() {
		$arr = &$this->getSurveyResponses();
		if (!$arr || !is_array($arr)) {
			return 0;
		}

		return count($arr);
	}

	/**
	 *	getResults - get the array of result for yes/no and 1-5 question
         *                       
 	 *	@return	int      the array of result
         *              for the yes/no question, it returns counts in arr[1] and arr[5];
         *              for the 1-5 question, it returns counts in arr[1], arr[1], ..., arr[5];
         *              for comments, we return arr[1], ...arr[n] with comments
	 */
	function &getResults() {
		if ($this->Results) {
			return $this->Results;
		}

		$arr = &$this->getSurveyResponses();
		if (!$arr || !is_array($arr)) {
			return false;
		}
		$count = count($arr); 
		
		$question = $this->getQuestion();
		if ($question->getQuestionType()=='1' || 
		    $question->getQuestionType()=='3') {
			/* This is a radio-button question. Values 1-5 or yes(1) no (5)question  */
			$is_radio = true;
			$this->Result = array(0, 0, 0, 0, 0, 0);
		} else {
			$is_radio=false;
		}
		
		for($i=0; $i<$count; $i++) {
			if ($arr[$i]->isError()) {
				echo $arr[$i]->getErrorMessage();
				continue;
			}
			
			$response = $arr[$i]->getResponse();
			
			if($is_radio) {
				/* We only counts */
				$this->Result[$response]++;
			} else {
				/* Save response */
				$this->Result[] = $response;
			}
		}

		return $this->Result;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
