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

		$user = session_get_user () ;
		$projects = $user->getGroups() ;
		sortProjectList ($projects) ;
		$tmp = array () ;
		foreach ($projects as $p) {
			$sf = new SurveyFactory($p);
			foreach ($sf->getSurveys() as $s) {
				$tmp[] = $p ;
				break ;
			}
		}
		$projects = $tmp ;
		
		$html_my_surveys = '';
		if (count ($projects) < 1) {
			$html_my_surveys .= '<div class="warning">'. _("There are no surveys in your projects.") ."</div>\n";
		} else {
			global $HTML;
			$request =& HTTPRequest::instance();
			$html_my_surveys .= '<table style="width:100%">';
			$j = 0;
			foreach ($projects as $project) {
				$j++;
				$group_id = $project->getID() ;
				$surveyfacto = new SurveyFactory($project);
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
					$hide_url.'<a href="/survey/?group_id='.$group_id.'">'.
					$project->getPublicName().'</a>    ';

				$html = '';
				$count_new = max(0, $count_diff);
				$i = 0 ;
				foreach ($surveys as $survey) {
					$i++ ;
					if (!$hide_now) {
						$group_survey_id= $survey->getId();
						$survey_title = $survey->getTitle();
						$devsurvey_is_active = $survey->isActive();
						if($devsurvey_is_active == 1 ) {
							$html .= '
								<tr '. $HTML->boxGetAltRowStyle($i) .'><td width="99%">'.
								'&nbsp;&nbsp;&nbsp;-&nbsp;<a href="/survey/survey.php?group_id='.$group_id.'&amp;survey_id='.$group_survey_id.'">'.
								$survey_title.'</a></td></tr>';
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
