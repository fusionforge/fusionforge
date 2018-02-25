<?php
/**
 * FusionForge surveys
 *
 * Copyright 2004, Sung Kim/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013,2017, Franck Villaume - TrivialDev
 * http://fusionforge.org
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
require_once $gfcommon.'survey/Survey.class.php';

class SurveyFactory extends FFError {

	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * The survey array.
	 *
	 * @var	array	survey.
	 */
	var $surveys;

	/**
	 * @param	object	$Group	The Group object to which this survey is associated.
	 * @param	bool	$skip_check
	 */
	function __construct(&$Group, $skip_check=false) {
		parent::__construct();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('Invalid Project'));
			return;
		}

		if ($Group->isError()) {
			$this->setError(_('Survey')._(': ').$Group->getErrorMessage());
			return;
		}
		if (!$skip_check && !$Group->usesSurvey()) {
			$this->setError(sprintf(_('%s does not use the Survey tool'),
							$Group->getPublicName()));
			return;
		}
		$this->Group =& $Group;
	}

	/**
	 * getGroup - get the Group object this SurveyQuestionFactory is associated with.
	 *
	 * @return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getSurveys - get an array of Survey objects for this Group.
	 *
	 * @return	array	The array of Survey objects.
	 */
	function &getSurveys() {
		/* We already have it */
		if ($this->surveys) {
			return $this->surveys;
		}
		$result = db_query_params('SELECT * FROM surveys WHERE group_id=$1 ORDER BY survey_id DESC',
						array ($this->Group->getID()));

		if (!$result) {
			$this->setError(_('No Survey is found').' '.db_error());
			return false;
		} else {
			$this->surveys = array();
			while ($arr = db_fetch_array($result)) {
				$this->surveys[] = new Survey($this->Group, $arr['survey_id'], $arr);
			}
			db_free_result($result);
		}
		return $this->surveys;
	}

	/**
	 * getSurveysIds - get an array of Survey IDs for this Group
	 *
	 * @return	array	The array of Survey IDs
	 */
	function getSurveysIds() {
		$surveyids = array();
		if ($this->surveys) {
			foreach ($this->surveys as $surveyObject) {
				$surveyids[] = $surveyObject->getID();
			}
		} else {
			$result = db_query_params('SELECT survey_id FROM surveys WHERE group_id = $1 ORDER BY survey_id DESC',
						array($this->Group->getID()));
			$surveyids = util_result_column_to_array($result);
		}
		return $surveyids;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
