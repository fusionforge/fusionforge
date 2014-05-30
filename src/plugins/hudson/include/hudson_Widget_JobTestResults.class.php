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

require_once 'HudsonJobWidget.class.php';
require_once 'common/include/HTTPRequest.class.php';
require_once 'PluginHudsonJobDao.class.php';
require_once 'HudsonJob.class.php';
require_once 'HudsonTestResult.class.php';

class hudson_Widget_JobTestResults extends HudsonJobWidget {

	var $test_result;
	var $content;

	function hudson_Widget_JobTestResults($owner_type, $owner_id) {
		$request =& HTTPRequest::instance();
		if ($owner_type == WidgetLayoutManager::OWNER_TYPE_USER) {
			$this->widget_id = 'plugin_hudson_my_jobtestresults';
			$this->group_id = $owner_id;
		} else {
			$this->widget_id = 'plugin_hudson_project_jobtestresults';
			$this->group_id = $request->get('group_id');
		}
		$this->Widget($this->widget_id);
		$this->setOwner($owner_id, $owner_type);
		if ($this->widget_id == 'plugin_hudson_project_jobtestresults' && forge_check_perm('hudson', $this->group_id, 'read')) {
			$this->content['title'] = '';
			if ($this->job && $this->test_result) {
				$this->content['title'] .= vsprintf(_('%1$s Test Results (%2$s / %3$s)'),
						array($this->job->getName(), $this->test_result->getPassCount(), $this->test_result->getTotalCount()));
			} elseif ($this->job && ! $this->test_result) {
				$this->content['title'] .= sprintf(_('%s Test Results'), $this->job->getName());
			} else {
				$this->content['title'] .= _('Test Results');
			}
		} else {
			$this->content['title'] = '';
			if ($this->job && $this->test_result) {
				$this->content['title'] .= vsprintf(_('%1$s Test Results (%2$s / %3$s)'),
						array($this->job->getName(), $this->test_result->getPassCount(), $this->test_result->getTotalCount()));
			} elseif ($this->job && ! $this->test_result) {
				$this->content['title'] .= sprintf(_('%s Test Results'), $this->job->getName());
			} else {
				$this->content['title'] .= _('Test Results');
			}
		}
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getDescription() {
		return _("Show the test results of the latest build for the selected job.To display something, your job needs to execute tests and publish them. The result is shown on a pie chart.");
	}

	function loadContent($id) {
		$sql = "SELECT * FROM plugin_hudson_widget WHERE widget_name=$1 AND owner_id=$2 AND owner_type=$3 AND id=$4";
		$res = db_query_params($sql,array($this->widget_id,$this->owner_id,$this->owner_type,$id));
		if ($res && db_numrows($res)) {
			$data = db_fetch_array($res);
			$this->job_id = $data['job_id'];
			$this->content_id = $id;
			$jobs = $this->getAvailableJobs();
			if (array_key_exists($this->job_id, $jobs)) {
				$used_job = $jobs[$this->job_id];
				$this->job_url = $used_job->getUrl();
				$this->job = $used_job;
				try {
					$this->test_result = new HudsonTestResult($this->job_url);
				} catch (Exception $e) {
					$this->test_result = null;
				}

			} else {
				$this->job = null;
				$this->test_result = null;
			}
		}
	}

	function getContent() {
		$html = '';
		if ($this->job != null && $this->test_result != null) {
			$job = $this->job;
			$test_result = $this->test_result;
			$html .= '<div style="padding: 20px;">';
			$html .= ' <a href="/plugins/hudson/?action=view_last_test_result&group_id='.$this->group_id.'&job_id='.$this->job_id.'">'.$test_result->getTestResultPieChart().'</a>';
			$html .= '</div>';
		} else {
			if ($this->job != null) {
				$html .= _("No test found for this job.");
			} else {
				$html .= _("Job not found.");
			}
		}
		return $html;
	}

	function isAvailable() {
		return isset($this->content['title']);
	}
}
