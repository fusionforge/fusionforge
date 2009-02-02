<?php
/**
 * FusionForge surveys
 *
 * Copyright 2004, Sung Kim/GForge, LLC
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'survey/Survey.class.php';

class SurveyFactory extends Error {

       /**
        * The Group object.
        *
        * @var	 object  $Group.
        */
	var $Group;

	/**
	 * The survey array.
	 *
	 * @var	 array	survey.
	 */
	var $surveys;

	/**
	 *  Constructor. 
	 *
	 *	@param	object	The Group object to which this survey is associated.
         *      @param  int     The survey_id
	 */
	function SurveyFactory(&$Group) { 
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('No valid Group Object'));
			return false;
		}

		if ($Group->isError()) {
			$this->setError(_('Survey').':: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		return true;
	}

	/**
	 *	getGroup - get the Group object this SurveyQuestionFactory is associated with.
	 *
	 *	@return object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getSurveyQuestion - get an array of Survey Question objects 
         *                          for this Group and Survey id if survey_id is given.
	 *
 	 *	@return	array	The array of Survey Question objects.
	 */
	function &getSurveys() {
		/* We alread have it */
		if ($this->surveys) {
			return $this->surveys;
		}
		
		$group_id = $this->Group->GetID();
		
		$sql="SELECT * FROM surveys WHERE group_id='$group_id' order by survey_id DESC";
		$result = db_query ($sql);

		if (!$result) {
			$this->setError(_('No Survey is found').db_error());
			return false;
		} else {
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

?>
