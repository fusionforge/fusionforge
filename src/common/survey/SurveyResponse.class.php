<?php
/**
 * FusionForge surveys
 *
 * Copyright 2004, Sung Kim/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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

require_once $gfcommon.'include/FFError.class.php';

class SurveyResponse extends FFError {
	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $data_array.
	 */
	var $data_array;

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * @param	$Group
	 * @param	bool	$arr
	 * @internal	param	\The $object Group object to which this Survey Response is associated.
	 * @internal	param	\The $int question_id.
	 * @internal	param	\The $array associative array of data.
	 */
	function __construct(&$Group, $arr=false) {
		parent::__construct();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('No Valid Group Object'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('Survey: '.$Group->getErrorMessage());
			return;
		}
		$this->Group =& $Group;

		if ($arr && is_array($arr)) {
			$this->data_array =& $arr;
		}
	}

	/**
	 * create - use this function to create a survey response
	 *
	 * @param	$user_id
	 * @param	$survey_id
	 * @param	$question_id
	 * @param	$response
	 * @internal	param		\The $string question
	 * @internal	param		\The $int question type
	 *				1: Radio Buttons 1-5
	 *				2: Text Area
	 *				3: Radio Buttons Yes/No
	 *				4: Comment Only
	 *				5: Text Field
	 *				6: None
	 * @return	boolean		success.
	 */
	function create($user_id, $survey_id, $question_id, $response) {
		$res = db_query_params ('INSERT INTO survey_responses (user_id,group_id,survey_id,question_id,response,post_date) VALUES ($1,$2,$3,$4,$5,$6)',
					array ($user_id,
					       $this->Group->GetID(),
					       $survey_id,
					       $question_id,
					       htmlspecialchars ($response),
					       time ())) ;
		if (!$res) {
			$this->setError(_('Error').db_error());
			return false;
		}
		return true;
	}

	/**
	 * getGroup - get the Group object this SurveyResponse is associated with.
	 *
	 * @return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getUserID - Get the user id of this Survey response
	 *
	 * @return	int	The user_id
	 */
	function getUserID() {
		return $this->data_array['user_id'];
	}

	/**
	 * getGroup - Get the group id of this Survey response
	 *
	 * @return	int	The group_id
	 */
	function getGroupID() {
		return $this->data_array['group_id'];
	}

	/**
	 * getSurveyID - Get the survey id of this Survey response
	 *
	 * @return	int	The survey_id
	 */
	function getSurveyID() {
		return $this->data_array['survey_id'];
	}

	/**
	 * getQuestionID - Get the question id of this Survey response
	 *
	 * @return	int	The question_id
	 */
	function getQuestionID() {
		return $this->data_array['question_id'];
	}

	/**
	 * getUserID - Get the response of this Survey response
	 *
	 * @return	int	The response
	 */
	function getResponse() {
		return $this->data_array['response'];
	}

	/**
	 * getPostDate - Get the post date of this Survey response
	 *
	 * @return	int	The post date
	 */
	function getPostDate() {
		return $this->data_array['post_date'];
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
