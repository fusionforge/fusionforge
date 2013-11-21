<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2013, Franck Villaume - TrivialDev
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
require_once 'common/survey/SurveyFactory.class.php';

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
	var $_survey_show;

	function __construct() {
		$this->Widget('mysurveys');
		$this->_survey_show = UserManager::instance()->getCurrentUser()->getPreference('my_surveys_show');
		if($this->_survey_show === false) {
			$this->_survey_show = 'AN';
			UserManager::instance()->getCurrentUser()->setPreference('my_surveys_show', $this->_survey_show);
		}
		$user = session_get_user();
		$projects = $user->getGroups();
		sortProjectList($projects);
		$tmp = array();
		foreach ($projects as $p) {
			if ($p->usesSurvey()) {
				$sf = new SurveyFactory($p);
				foreach ($sf->getSurveys() as $s) {
					$tmp[] = $p;
					break;
				}
			}
		}
		$projects = $tmp;

		$html_my_surveys = '';
		if (count ($projects) < 1) {
			$html_my_surveys .= '<div class="warning">'. _("There are no surveys in your projects.") ."</div>\n";
		} else {
			global $HTML;
			$request =& HTTPRequest::instance();
			$html_my_surveys .= '<table style="width:100%">';
			foreach ($projects as $project) {
				$group_id = $project->getID();
				$surveyfacto = new SurveyFactory($project);
				$surveys = $surveyfacto->getSurveys();
				for ($i = 0; $i < count($surveys); $i++) {
					if ($surveys[$i]->isActive()) {
						switch ($this->_survey_show) {
							case 'A':
								if (!$surveys[$i]->isUserVote($user->getID())) {
									unset($surveys[$i]);
								}
								break;
							case 'N':
								if ($surveys[$i]->isUserVote($user->getID())) {
									unset($surveys[$i]);
								}
								break;
							case 'AN':
							default:
								break;
						}
					} else {
						unset($surveys[$i]);
					}
				}
				$surveys = array_values($surveys);
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

				$html_hdr = '<tr class="boxitem"><td colspan="2">'.
					$hide_url.'<a href="/survey/?group_id='.$group_id.'">'.
					$project->getPublicName().'</a>    ';

				$html = '';
				$count_new = max(0, $count_diff);
				$i = 0 ;
				foreach ($surveys as $survey) {
					$i++ ;
					if (!$hide_now) {
						$group_survey_id = $survey->getId();
						$survey_title = $survey->getTitle();
						$html .= '
							<tr '. $HTML->boxGetAltRowStyle($i) .'><td style="width:99%">'.
							'&nbsp;&nbsp;&nbsp;-&nbsp;<a href="/survey/survey.php?group_id='.$group_id.'&amp;survey_id='.$group_survey_id.'">'.
							$survey_title.'</a></td></tr>';
					}
				}

				$html_hdr .= '['.count($surveys).($count_new ? ", <b>".sprintf(_('%d new'), $count_new)."</b>]" : ']').'</td></tr>';
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
		return _("List the surveys in your projects.");
	}

	function hasPreferences() {
		return true;
	}

	function getPreferences() {
		$optionsArray = array('A','N','AN');
		$textsArray = array();
		$textsArray[] = _('answered [A]');
		$textsArray[] = _('not yet answered [N]');
		$textsArray[] = _('any status [AN]');
		$prefs = _('Display surveys:').html_build_select_box_from_arrays($optionsArray, $textsArray, "show", $this->_survey_show);
		return $prefs;
	}

	function updatePreferences(&$request) {
		$request->valid(new Valid_String('cancel'));
		$vShow = new Valid_WhiteList('show', array('A', 'N', 'AN'));
		$vShow->required();
		if (!$request->exist('cancel')) {
			if ($request->valid($vShow)) {
				switch($request->get('show')) {
					case 'A':
						$this->_survey_show = 'A';
						break;
					case 'N':
						$this->_survey_show = 'N';
						break;
					case 'AN':
					default:
						$this->_survey_show = 'AN';
						break;
				}
				UserManager::instance()->getCurrentUser()->setPreference('my_surveys_show', $this->_survey_show);
			}
		}
		return true;
	}
}
