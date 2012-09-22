<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012, Franck Villaume - TrivialDev
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
 * along with FusionForge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';

/**
 * Widget_ProjectDocumentsActivity
 */

class Widget_ProjectDocumentsActivity extends Widget {
	var $content;
	var $_statistic_show;
	function __construct() {
		$this->Widget('projectdocumentsactivity');
		if (session_loggedin()) {
			$this->_statistic_show = UserManager::instance()->getCurrentUser()->getPreference('my_docman_project_activitity_show');
		} else {
			$this->_statistic_show = '60';
		}
		$request =& HTTPRequest::instance();
		$pm = ProjectManager::instance();
		$project = $pm->getProject($request->get('group_id'));
		if ($project && $this->canBeUsedByProject($project)) {
			$this->content['title'] = _('Last 4 weeks Documents Manager Activity');
		}
	}

	function hasPreferences() {
		if (session_loggedin()) {
			return true;
		}
		return false;
	}

	function updatePreferences(&$request) {
		$request->valid(new Valid_String('cancel'));
		$vShow = new Valid_WhiteList('show', array('F', 'D', 'U', 'FU', 'FD', 'FUD'));
		$vShow->required();
		if (!$request->exist('cancel')) {
			if ($request->valid($vShow)) {
				switch($request->get('show')) {
					case 'F':
						$this->_statistic_show = 'F';
						break;
					case 'D':
						$this->_statistic_show = 'D';
						break;
					case 'U':
						$this->_statistic_show = 'U';
						break;
					case 'FU':
						$this->_statistic_show = 'FU';
						break;
					case 'FD':
						$this->_statistic_show = 'FD';
						break;
					case 'FUD':
					default:
						$this->_statistic_show = 'FUD';
				}
				UserManager::instance()->getCurrentUser()->setPreference('my_docman_project_activitity_show', $this->_statistic_show);
			}
		}
		return true;
	}

	function getPreferences() {
		$optionsArray = array('F', 'D', 'U', 'FU', 'FD', 'FUD');
		$textsArray = array();
		$textsArray[] = _('new files'.' [F]');
		$textsArray[] = _('new directories'.' [D]');
		$textsArray[] = _('updated files'.' [U]');
		$textsArray[] = _('new and update Files'.' [FU]');
		$textsArray[] = _('new files and directories'.' [FD]');
		$textsArray[] = _('new and update files and directories'.' [FUD]');
		$prefs = _("Display statistics:").html_build_select_box_from_arrays($optionsArray, $textsArray, "show", $this->_statistic_show);
		return $prefs;
	}

	function getContent() {
		require_once $GLOBALS['gfcommon'].'docman/DocumentManager.class.php';
		global $HTML;
		html_use_jqueryjqplotpluginBar();
		echo $HTML->getJavascripts();
		echo $HTML->getStylesheets();
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$dm = new DocumentManager(group_get_object($group_id));
		$begin1 = strtotime('monday this week');
		$end1 = time();
		$begin2 = strtotime('-1 week', $begin1);
		$end2 = $begin1;
		$begin3 = strtotime('-1 week', $begin2);
		$end3 = $begin2;
		$begin4 = strtotime('-1 week', $begin3);
		$end4 = $begin3;
		$sections = array('docmannew', 'docgroupnew', 'docmanupdate');
		$activitysArray[] = $dm->getActivity($sections, $begin4, $end4);
		$activitysArray[] = $dm->getActivity($sections, $begin3, $end3);
		$activitysArray[] = $dm->getActivity($sections, $begin2, $end2);
		$activitysArray[] = $dm->getActivity($sections, $begin1, $end1);
		echo '<script>';
		switch($this->_statistic_show) {
			case 'F':
				echo 'var s1 = ['.$activitysArray[0]['docmannew'].', '.$activitysArray[1]['docmannew'].', '.$activitysArray[2]['docmannew'].', '.$activitysArray[3]['docmannew'].'];';
				echo 'var series = [s1];';
				echo 'var labels = [{label:\''._('new Files').'\'}];';
				break;
			case 'U': {
				echo 'var s2 = ['.$activitysArray[0]['docmanupdate'].', '.$activitysArray[1]['docmanupdate'].', '.$activitysArray[2]['docmanupdate'].', '.$activitysArray[3]['docmanupdate'].'];';
				echo 'var series = [s2];';
				echo 'var labels = [{label:\''._('updated Files').'\'}];';
				break;
			}
			case 'D': {
				echo 'var s3 = ['.$activitysArray[0]['docgroupnew'].', '.$activitysArray[1]['docgroupnew'].', '.$activitysArray[2]['docgroupnew'].', '.$activitysArray[3]['docgroupnew'].'];';
				echo 'var series = [s3];';
				echo 'var labels = [{label:\''._('new Directories').'\'}];';
				break;
			}
			case 'FU': {
				echo 'var s1 = ['.$activitysArray[0]['docmannew'].', '.$activitysArray[1]['docmannew'].', '.$activitysArray[2]['docmannew'].', '.$activitysArray[3]['docmannew'].'];';
				echo 'var s2 = ['.$activitysArray[0]['docmanupdate'].', '.$activitysArray[1]['docmanupdate'].', '.$activitysArray[2]['docmanupdate'].', '.$activitysArray[3]['docmanupdate'].'];';
				echo 'var series = [s1, s2];';
				echo 'var labels = [{label:\''._('new Files').'\'},
						{label:\''._('updated Files').'\'}];';
				break;
			}
			case 'FD': {
				echo 'var s1 = ['.$activitysArray[0]['docmannew'].', '.$activitysArray[1]['docmannew'].', '.$activitysArray[2]['docmannew'].', '.$activitysArray[3]['docmannew'].'];';
				echo 'var s3 = ['.$activitysArray[0]['docgroupnew'].', '.$activitysArray[1]['docgroupnew'].', '.$activitysArray[2]['docgroupnew'].', '.$activitysArray[3]['docgroupnew'].'];';
				echo 'var series = [s1, s3];';
				echo 'var labels = [{label:\''._('new Files').'\'},
						{label:\''._('new Directories').'\'}];';
				break;
			}
			default: {
				echo 'var s1 = ['.$activitysArray[0]['docmannew'].', '.$activitysArray[1]['docmannew'].', '.$activitysArray[2]['docmannew'].', '.$activitysArray[3]['docmannew'].'];';
				echo 'var s2 = ['.$activitysArray[0]['docmanupdate'].', '.$activitysArray[1]['docmanupdate'].', '.$activitysArray[2]['docmanupdate'].', '.$activitysArray[3]['docmanupdate'].'];';
				echo 'var s3 = ['.$activitysArray[0]['docgroupnew'].', '.$activitysArray[1]['docgroupnew'].', '.$activitysArray[2]['docgroupnew'].', '.$activitysArray[3]['docgroupnew'].'];';
				echo 'var series = [s1, s2, s3];';
				echo 'var labels = [{label:\''._('new Files').'\'},
						{label:\''._('updated Files').'\'},
						{label:\''._('new Directories').'\'}];';
				break;
			}
		}
		echo 'var ticks = [\''._('3 weeks ago').'\', \''._('2 weeks ago').'\', \''._('Last Week').'\', \''._('Current Week').'\'];';
		echo 'var plot1;';
		echo 'jQuery(document).ready(function(){
				plot1 = jQuery.jqplot(\'chart1\', series, {
					seriesDefaults: {
						renderer:jQuery.jqplot.BarRenderer,
						rendererOptions: {fillToZero: true}
					},
					series:
						labels
					,
					legend: {
						show: true,
						placement: \'insideGrid\',
						location: \'ne\'
					},
					axes: {
						xaxis: {
							renderer: jQuery.jqplot.CategoryAxisRenderer,
							ticks: ticks,
						}
					}
				});
			});';
		echo 'jQuery(window).resize(function() {
			plot1.replot( { resetAxes: true } );
			});';
		echo '</script>';
		echo '<div id="chart1"></div>';
		echo '<div class="underline-link">' . util_make_link('/docman/?group_id='.$group_id, _('Browse Documents Manager')) . '</div>';
	}

	function getTitle() {
		return $this->content['title'];
	}

	function isAvailable() {
		return isset($this->content['title']);
	}

	function canBeUsedByProject(&$project) {
		return $project->usesDocman();
	}

	function getCategory() {
		return 'Documents-Manager';
	}

	function getDescription() {
		return _(' Display activity about Document Manager (new documents, new edit, new directory ...) during the last 4 weeks.');
	}
}
