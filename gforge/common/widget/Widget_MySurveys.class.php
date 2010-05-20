<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Widget.class.php');
//require_once('common/survey/SurveySingleton.class.php');
require_once ('common/survey/SurveyFactory.class.php');
/**
 * Widget_MySurveys
 * 
 * DEVELOPER SURVEYS
 * 
 * This needs to be updated manually to display any given survey
 * Default behavior: get first survey from group #1 
 */
class Widget_MySurveys extends Widget {
	var $content;
	var $can_be_displayed;

	function Widget_MySurveys() {
		$this->Widget('mysurveys');
		$no_survey = true;
		$html_my_surveys = '';
		$sql="SELECT groups.group_id, groups.group_name ".
			"FROM groups,surveys,user_group ".
			"WHERE groups.group_id=surveys.group_id ".
			"AND user_group.group_id=groups.group_id ".
			"AND groups.status = 'A' ".
			"AND user_group.user_id=$1 ";

		$result=db_query_params($sql,array(user_getid()));
		$rows=db_numrows($result);
		if (!$result || $rows < 1) {
			$html_my_surveys .= _("<P>There are no surveys in your groups.<P><BR>&nbsp;");
		} else {
			$request =& HTTPRequest::instance();
			$html_my_surveys .= '<table style="width:100%">';
			for ($j=0; $j<$rows; $j++) {
				$group_id = db_result($result,$j,'group_id');
				$surveyfacto       =& new SurveyFactory(group_get_object($group_id));
				$surveys = $surveyfacto->getSurveys();
				$vItemId = new Valid_UInt('hide_item_id');
				$vItemId->required();
				if($request->valid($vItemId)) {
					$hide_item_id = $request->get('hide_item_id');
				} else {
					$hide_item_id = null;
				}

				$vForum = new Valid_WhiteList('hide_survey', array(0, 1));
				$vForum->required();
				if($request->valid($vForum)) {
					$hide_survey = $request->get('hide_survey');
				} else {
					$hide_survey = null;
				}

				list($hide_now,$count_diff,$hide_url) = my_hide_url('survey',$group_id,$hide_item_id,count($surveys),$hide_survey);

				$html_hdr = ($j ? '<tr class="boxitem"><td colspan="2">' : '').
					$hide_url.'<A HREF="/survey/?group_id='.$group_id.'">'.
					db_result($result,$j,'group_name').'</A>&nbsp;&nbsp;&nbsp;&nbsp;';

				$html = '';
				$count_new = max(0, $count_diff);
				for ($i=0; $i<count($surveys); $i++) {
					$survey=$surveys[$i];
					if (!$hide_now) {
						$class = $HTML->boxGetAltRowStyle($i);
						$group_survey_id= $survey->getId();
						$survey_title = $survey->getTitle();
						$devsurvey_is_active = $survey->isActive();
						if($devsurvey_is_active == 1 ) {
							$html .= '
								<TR class="'. $class .'"><TD WIDTH="99%">'.
								'&nbsp;&nbsp;&nbsp;-&nbsp;<A HREF="/survey/survey.php?group_id='.$group_id.'&survey_id='.$group_survey_id.'">'.
								$survey_title.'</A></TD></TR>';
						}
					}
				}

				$html_hdr .= '['.count($surveys).($count_new ? ", <b>".sprintf(_('%s new'), array($count_new))."</b>]" : ']').'</td></tr>';
				$html_my_surveys .= $html_hdr.$html;
			}
			$html_my_surveys .= '</table>';
		}
	$this->content = $html_my_surveys;
	}

	/*	$sql          = "SELECT * from surveys WHERE survey_id=$1";
		$result       = db_query_params($sql,array($developer_survey_id));
		$group_id     = db_result($result, 0, 'group_id');
		$survey_title = $survey->getSurveyTitle(db_result($result, 0, 'survey_title'));

	// Check that the survey is active
	$devsurvey_is_active = db_result($result, 0, 'is_active');

	if ($devsurvey_is_active==1) {

	$sql="SELECT * FROM survey_responses ".
	"WHERE survey_id=$1 AND user_id=$2";
	$result = db_query_params($sql,array($developer_survey_id,user_getid()));

	if (db_numrows($result) < 1) {
	$no_survey = false;
	$this->content .= '<a href="/survey/survey.php?group_id='. $group_id .'&survey_id='. $developer_survey_id .'">'. $survey_title .'</a>';
	}          */   
	function getTitle() {
		return _("Quick Survey");
	}
	function getContent() {
		return $this->content;
	}
	function getDescription() {
		return _("List the surveys you have not answered.");
	}
}

?>
