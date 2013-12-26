<?php
/**
 * FusionForge surveys
 *
 * Copyright 2004, Sung Kim/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'survey/Survey.class.php';

class SurveyFactory extends Error {

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
	 * Constructor.
	 *
	 * @param	object	$Group	The Group object to which this survey is associated.
	 */
	function __construct(&$Group) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('No Valid Group Object'));
			return;
		}

		if ($Group->isError()) {
			$this->setError(_('Survey').':: '.$Group->getErrorMessage());
			return;
		}
		if (!$Group->usesSurvey()) {
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
	 * getSurveyQuestion - get an array of Survey Question objects
	 * for this Group and Survey id if survey_id is given.
	 *
 	 * @return	array	The array of Survey Question objects.
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

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
