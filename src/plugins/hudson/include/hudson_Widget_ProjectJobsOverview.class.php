<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once 'HudsonOverviewWidget.class.php';
require_once 'common/include/HTTPRequest.class.php';
require_once 'PluginHudsonJobDao.class.php';
require_once 'HudsonJob.class.php';

class hudson_Widget_ProjectJobsOverview extends HudsonOverviewWidget {

	var $plugin;
	var $group_id;
	var $_not_monitored_jobs;
	var $_use_global_status = true;
	var $_all_status;
	var $_global_status;
	var $_global_status_icon;
	var $content;

	function hudson_Widget_ProjectJobsOverview($plugin) {
		$this->Widget('plugin_hudson_project_jobsoverview');
		$this->plugin = $plugin;

		$request =& HTTPRequest::instance();
		$this->group_id = $request->get('group_id');

		if ($this->_use_global_status == "true") {
			$this->_all_status = array(
				'grey' => 0,
				'blue' => 0,
				'yellow' => 0,
				'red' => 0,
			);
			$this->computeGlobalStatus();
		}
		if (forge_check_perm('hudson', $this->group_id, 'read')) {
			$this->content['title'] = '';
			if ($this->_use_global_status == "true") {
				$this->content['title'] = '<img src="'.$this->_global_status_icon.'" title="'.$this->_global_status.'" alt="'.$this->_global_status.'" /> ';
			}
			$this->content['title'] .= _("Hudson Jobs");
		}
	}

	function computeGlobalStatus() {
		$jobs = $this->getJobsByGroup($this->group_id);
		foreach ($jobs as $job) {
			$this->_all_status[(string)$job->getColorNoAnime()] = $this->_all_status[(string)$job->getColorNoAnime()] + 1;
		}
		if ($this->_all_status['grey'] > 0 || $this->_all_status['red'] > 0) {
			$this->_global_status = _("One or more failure or pending job");
			$this->_global_status_icon = '/'.$this->plugin->getThemePath() . "/images/ic/status_red.png";
		} elseif ($this->_all_status['yellow'] > 0) {
			$this->_global_status = _("One or more unstable job");
			$this->_global_status_icon = '/'.$this->plugin->getThemePath() . "/images/ic/status_yellow.png";
		} else {
			$this->_global_status = _("Success");
			$this->_global_status_icon = '/'.$this->plugin->getThemePath() . "/images/ic/status_blue.png";
		}
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getDescription() {
		return _("Shows an overview of all the jobs associated with this project. You can always choose the ones you want to display in the widget (preferences link).");
	}

	function getContent() {
		$jobs = $this->getJobsByGroup($this->group_id);
		if (sizeof($jobs) > 0) {
			$html = '';
			$html .= '<table style="width:100%">';
			$cpt = 1;

			foreach ($jobs as $job_id => $job) {
				if ($cpt % 2 == 0) {
					$class="boxitemalt bgcolor-white";
				} else {
					$class="boxitem bgcolor-grey";
				}

				try {

					$html .= '<tr class="'. $class .'">';
					$html .= ' <td>';
					$html .= ' <img src="'.$job->getStatusIcon().'" title="'.$job->getStatus().'" >';
					$html .= ' </td>';
					$html .= ' <td style="width:99%">';
					$html .= '  <a href="/plugins/hudson/?action=view_job&group_id='.$this->group_id.'&job_id='.$job_id.'">'.$job->getName().'</a><br />';
					$html .= ' </td>';
					$html .= '</tr>';

					$cpt++;

				} catch (Exception $e) {
					// Do not display wrong jobs
				}
			}
			$html .= '</table>';
			return $html;
		}
	}

	function isAvailable() {
		return isset($this->content['title']);
	}
}
