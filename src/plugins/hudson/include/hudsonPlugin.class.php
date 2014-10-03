<?php
/**
 * hudsonPlugin
 *
 * Copyright (c) Xerox Corporation, Codendi 2007-2008.
 * @author Marc Nazarian <marc.nazarian@xrce.xerox.com>
 * Copyright (C) 2010-2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2014, Franck Villaume - TrivialDev
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

require_once 'PluginHudsonJobDao.class.php';

class hudsonPlugin extends Plugin {

	function hudsonPlugin($id=0) {
		$this->Plugin($id);
		$this->name = "hudson";
		$this->text = _('Hudson/Jenkins'); // To show in the tabs, use...
		$this->_addHook("user_personal_links"); //to make a link to the user's personal part of the plugin
		//$this->_addHook("usermenu") ;
		$this->_addHook("groupmenu");	// To put into the project tabs
		$this->_addHook("groupisactivecheckbox") ; // The "use ..." checkbox in editgroupinfo
		$this->_addHook("groupisactivecheckboxpost") ; //
		//$this->_addHook("userisactivecheckbox") ; // The "use ..." checkbox in user account
		//$this->_addHook("userisactivecheckboxpost") ; //
		$this->_addHook("project_admin_plugins"); // to show up in the admin page fro group
		$this->_addHook('javascript',  false);
		$this->_addHook('cssfile', 'cssFile', false);
		$this->_addHook('project_is_deleted', 'projectIsDeleted', false);
		$this->_addHook('widget_instance', 'myPageBox', false);
		$this->_addHook('widgets', 'widgets', false);
		$this->_addHook('get_available_reference_natures', 'getAvailableReferenceNatures', false);
		$this->_addHook('ajax_reference_tooltip', 'ajax_reference_tooltip', false);
		$this->_addHook('role_get');
		$this->_addHook('role_normalize');
		$this->_addHook('role_unlink_project');
		$this->_addHook('role_translate_strings');
		$this->_addHook('role_has_permission');
		$this->_addHook('role_get_setting');
		$this->_addHook('list_roles_by_permission');
	}

	function CallHook ($hookname, &$params) {
		global $G_SESSION,$HTML;
// 		if ($hookname == "usermenu") {
// 			$text = $this->text; // this is what shows in the tab
// 			if ($G_SESSION->usesPlugin("hudson")) {
// 				$param = '?type=user&amp;id=' . $G_SESSION->getId()."&amp;pluginname=".$this->name; // we indicate the part we're calling is the user one
// 				echo $HTML->PrintSubMenu(array($text), array('/plugins/hudson/index.php'.$param));
// 			}
// 		} elseif ($hookname == "groupmenu") {
		if ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$project = group_get_object($group_id);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if (!$project->isProject()) {
				return;
			}
			if ( $project->usesPlugin( $this->name )) {
				$params['TITLES'][] = $this->text;
				$params['DIRS'][] = '/plugins/hudson/index.php?group_id=' . $group_id . "&amp;pluginname=" . $this->name; // we indicate the part we're calling is the project one
				$params['ADMIN'][] = '';
				$params['TOOLTIPS'][] = _('Continuus Integration Scheduler');
			}
			if (isset($params['toptab'])) {
				(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
			}
		} elseif ($hookname =='cssfile') {
			use_stylesheet('/plugins/hudson/themes/default/css/style.css');
		} elseif ($hookname == "cssfile") {
			$this->cssFile($params);
		} elseif ($hookname == "project_is_deleted") {
			$this->projectIsDeleted($params);
		} elseif ($hookname == "widget_instance") {
			$this->myPageBox($params);
		} elseif ($hookname == "widgets") {
			$this->widgets($params);
		} elseif ($hookname == "get_available_reference_natures") {
			$this->getAvailableReferenceNatures($params);
		} elseif ($hookname == "ajax_reference_tooltip") {
			$this->ajax_reference_tooltip($params);
		} elseif ($hookname == 'role_get') {
			$this->role_get($params);
		} elseif ($hookname == 'role_normalize') {
			$this->role_normalize($params);
		} elseif ($hookname == 'role_translate_strings') {
			$this->role_translate_strings($params);
		} elseif ($hookname == 'role_has_permission') {
			$this->role_has_permission($params);
		} elseif ($hookname == 'role_get_setting') {
			$this->role_get_setting($params);
		} elseif ($hookname == 'list_roles_by_permission') {
			$this->list_roles_by_permission($params);
		}
	}

	function &getPluginInfo() {
		if (!is_a($this->pluginInfo, 'hudsonPluginInfo')) {
			require_once 'hudsonPluginInfo.class.php';
			$this->pluginInfo = new hudsonPluginInfo($this);
		}
		return $this->pluginInfo;
	}

	function cssFile($params) {
		// Only show the stylesheet if we're actually in the hudson pages.
		// This stops styles inadvertently clashing with the main site.
		if (strpos($_SERVER['REQUEST_URI'], $this->getPluginPath()) === 0 ||
				strpos($_SERVER['REQUEST_URI'], '/my/') === 0 ||
				strpos($_SERVER['REQUEST_URI'], '/projects/') === 0 ||
				strpos($_SERVER['REQUEST_URI'], '/widgets/') === 0
		   ) {
			use_stylesheet($this->getThemePath().'/css/style.css');
		}
	}

	/**
	 * When a project is deleted,
	 * we delete all the hudson jobs of this project
	 *
	 * @param mixed $params ($param['group_id'] the ID of the deleted project)
	 */
	function projectIsDeleted($params) {
		$group_id = $params['group_id'];
		$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
		$dar = $job_dao->deleteHudsonJobsByGroupID($group_id);
	}

	function myPageBox($params) {
		require_once 'common/widget/WidgetLayoutManager.class.php';

		$user = UserManager::instance()->getCurrentUser();

		// MY
		if ($params['widget'] == 'plugin_hudson_my_jobs') {
			require_once 'hudson_Widget_MyMonitoredJobs.class.php';
			$params['instance'] = new hudson_Widget_MyMonitoredJobs($this);
		}
		if ($params['widget'] == 'plugin_hudson_my_joblastbuilds') {
			require_once 'hudson_Widget_JobLastBuilds.class.php';
			$params['instance'] = new hudson_Widget_JobLastBuilds(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
		if ($params['widget'] == 'plugin_hudson_my_jobtestresults') {
			require_once 'hudson_Widget_JobTestResults.class.php';
			$params['instance'] = new hudson_Widget_JobTestResults(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
		if ($params['widget'] == 'plugin_hudson_my_jobtesttrend') {
			require_once 'hudson_Widget_JobTestTrend.class.php';
			$params['instance'] = new hudson_Widget_JobTestTrend(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
		if ($params['widget'] == 'plugin_hudson_my_jobbuildhistory') {
			require_once 'hudson_Widget_JobBuildHistory.class.php';
			$params['instance'] = new hudson_Widget_JobBuildHistory(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
		if ($params['widget'] == 'plugin_hudson_my_joblastartifacts') {
			require_once 'hudson_Widget_JobLastArtifacts.class.php';
			$params['instance'] = new hudson_Widget_JobLastArtifacts(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}

		// PROJECT
		if ($params['widget'] == 'plugin_hudson_project_jobsoverview') {
			require_once 'hudson_Widget_ProjectJobsOverview.class.php';
			$params['instance'] = new hudson_Widget_ProjectJobsOverview($this);
		}
		if ($params['widget'] == 'plugin_hudson_project_joblastbuilds') {
			require_once 'hudson_Widget_JobLastBuilds.class.php';
			$params['instance'] = new hudson_Widget_JobLastBuilds(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
		if ($params['widget'] == 'plugin_hudson_project_jobtestresults') {
			require_once 'hudson_Widget_JobTestResults.class.php';
			$params['instance'] = new hudson_Widget_JobTestResults(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
		if ($params['widget'] == 'plugin_hudson_project_jobtesttrend') {
			require_once 'hudson_Widget_JobTestTrend.class.php';
			$params['instance'] = new hudson_Widget_JobTestTrend(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
		if ($params['widget'] == 'plugin_hudson_project_jobbuildhistory') {
			require_once 'hudson_Widget_JobBuildHistory.class.php';
			$params['instance'] = new hudson_Widget_JobBuildHistory(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
		if ($params['widget'] == 'plugin_hudson_project_joblastartifacts') {
			require_once 'hudson_Widget_JobLastArtifacts.class.php';
			$params['instance'] = new hudson_Widget_JobLastArtifacts(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
	}

	function widgets($params) {
		$group = group_get_object($GLOBALS['group_id']);
		if ( !$group || !$group->usesPlugin ( $this->name ) ) {
			return false;
		}

		require_once 'common/widget/WidgetLayoutManager.class.php';
		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_USER) {
			$params['codendi_widgets'][] = 'plugin_hudson_my_jobs';
			$params['codendi_widgets'][] = 'plugin_hudson_my_joblastbuilds';
			$params['codendi_widgets'][] = 'plugin_hudson_my_jobtestresults';
			$params['codendi_widgets'][] = 'plugin_hudson_my_jobtesttrend';
			$params['codendi_widgets'][] = 'plugin_hudson_my_jobbuildhistory';
			$params['codendi_widgets'][] = 'plugin_hudson_my_joblastartifacts';
		}
		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_GROUP) {
			$params['codendi_widgets'][] = 'plugin_hudson_project_jobsoverview';
			$params['codendi_widgets'][] = 'plugin_hudson_project_joblastbuilds';
			$params['codendi_widgets'][] = 'plugin_hudson_project_jobtestresults';
			$params['codendi_widgets'][] = 'plugin_hudson_project_jobtesttrend';
			$params['codendi_widgets'][] = 'plugin_hudson_project_jobbuildhistory';
			$params['codendi_widgets'][] = 'plugin_hudson_project_joblastartifacts';
		}
	}

	function getAvailableReferenceNatures($params) {
		$hudson_plugin_reference_natures = array(
				'hudson_build'  => array('keyword' => 'build', 'label' => _("Hudson Build")),
				'hudson_job' => array('keyword' => 'job', 'label' => _("Hudson Job")));
		$params['natures'] = array_merge($params['natures'], $hudson_plugin_reference_natures);
	}

	function ajax_reference_tooltip($params) {
		require_once 'HudsonJob.class.php';
		require_once 'HudsonBuild.class.php';
		require_once 'hudson_Widget_JobLastBuilds.class.php';

		$ref = $params['reference'];
		switch ($ref->getNature()) {
			case 'hudson_build':
				$val = $params['val'];
				$group_id = $params['group_id'];
				$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
				if (strpos($val, "/") !== false) {
					$arr = explode("/", $val);
					$job_name = $arr[0];
					$build_id = $arr[1];
					$dar = $job_dao->searchByJobName($job_name, $group_id);
				} else {
					$build_id = $val;
					$dar = $job_dao->searchByGroupID($group_id);
					if ($dar->rowCount() != 1) {
						$dar = null;
					}
				}
				if ($dar && $dar->valid()) {
					$row = $dar->current();
					$build = new HudsonBuild($row['job_url'].'/'.$build_id.'/');
					echo '<strong>' . _("Build performed on:") . '</strong> ' . $build->getBuildTime() . '<br />';
					echo '<strong>' . _("Status") . _(": ") . '</strong> ' . $build->getResult();
				} else {
					echo '<span class="error">'._("Error: Hudson object not found.").'</span>';
				}
				break;
			case 'hudson_job':
				$job_dao = new PluginHudsonJobDao(CodendiDataAccess::instance());
				$job_name = $params['val'];
				$group_id = $params['group_id'];
				$dar = $job_dao->searchByJobName($job_name, $group_id);
				if ($dar->valid()) {
					$row = $dar->current();
					try {
						$job = new HudsonJob($row['job_url']);
						$job_id = $row['job_id'];
						$html = '';
						$html .= '<table>';
						$html .= ' <tr>';
						$html .= '  <td colspan="2">';
						$html .= '   '.$job->getName().': <img src="'.$job->getStatusIcon().'" />';
						$html .= '  </td>';
						$html .= ' </tr>';
						$html .= ' <tr>';
						$html .= '  <td>';
						$html .= '   <ul>';
						if ($job->hasBuilds()) {
							$html .= ' <li>'._("Last Build:").' <a href="/plugins/hudson/?action=view_build&group_id='.$group_id.'&job_id='.$job_id.'&build_id='.$job->getLastBuildNumber().'"># '.$job->getLastBuildNumber().'</a></li>';
							$html .= ' <li>'._("Last Success")._(": ").'<a href="/plugins/hudson/?action=view_build&group_id='.$group_id.'&job_id='.$job_id.'&build_id='.$job->getLastSuccessfulBuildNumber().'"># '.$job->getLastSuccessfulBuildNumber().'</a></li>';
							$html .= ' <li>'._("Last Failure")._(": ").'<a href="/plugins/hudson/?action=view_build&group_id='.$group_id.'&job_id='.$job_id.'&build_id='.$job->getLastFailedBuildNumber().'"># '.$job->getLastFailedBuildNumber().'</a></li>';
						} else {
							$html .= ' <li>'. _("No build found for this job.") . '</li>';
						}
						$html .= '   </ul>';
						$html .= '  </td>';
						$html .= '  <td class="widget_lastbuilds_weather">';
						$html .= _("Weather Report:").'<img src="'.$job->getWeatherReportIcon().'" align="middle" />';
						$html .= '  </td>';
						$html .= ' </tr>';
						$html .= '</table>';
						echo $html;
					} catch (Exception $e) {
					}
				} else {
					echo '<span class="error">'._("Error: Hudson object not found.").'</span>';
				}
				break;
		}
	}

	function process() {
		require_once 'hudson.class.php';
		$controler = new hudson();
		$controler->process();
	}

	function role_get(&$params) {
		$role =& $params['role'];

		// Read access
		$right = new PluginSpecificRoleSetting($role, 'plugin_hudson_read');
		$right->SetAllowedValues(array('0', '1'));
		$right->SetDefaultValues(array('Admin' => '1',
						'Senior Developer' => '1',
						'Junior Developer' => '1',
						'Doc Writer' => '1',
						'Support Tech' => '1'));
		return true;
	}

	function role_normalize(&$params) {
		$role =& $params['role'];
		$new_pa =& $params['new_pa'];

		$projects = $role->getLinkedProjects();
		foreach ($projects as $p) {
			$role->normalizePermsForSection($new_pa, 'plugin_hudson_read', $p->getID());
		}
		return true;
	}

	function role_unlink_project(&$params) {
		$role =& $params['role'] ;
		$project =& $params['project'] ;

		$settings = array('plugin_hudson_read');
		
		foreach ($settings as $s) {
			db_query_params('DELETE FROM pfo_role_setting WHERE role_id=$1 AND section_name=$2 AND ref_id=$3',
					array($role->getID(),
					      $s,
					      $project->getID()));
		}
	}

	function role_translate_strings(&$params) {
		$right = new PluginSpecificRoleSetting($params['role'], 'plugin_hudson_read');
		$right->setDescription(_('Hudson access'));
		$right->setValueDescriptions(array('0' => _('No Access'),
							'1' => _('Full access')));
		return true;
	}

	function role_has_permission(&$params) {
		$value = $params['value'];

		switch ($params['section']) {
			case 'plugin_hudson_read':
			default:
				switch ($params['action']) {
					case 'read':
					default:
						$params['result'] |= ($value >= 1);
						break;
				}
				break;
		}
		return true;
	}

	function role_get_setting(&$params) {
		$role = $params['role'];
		$reference = $params['reference'];
		$value = $params['value'];

		switch ($params['section']) {
			case 'plugin_hudson_read':
			default:
				if ($role->hasPermission('project_admin', $reference)) {
					$params['result'] = 1;
				} else {
					$params['result'] = $value;
				}
				break;
		}
		return true;
	}

	function list_roles_by_permission(&$params) {
		switch ($params['section']) {
			case 'plugin_hudson_read':
			default:
				switch ($params['action']) {
					case 'read':
					default:
						$params['qpa'] = db_construct_qpa($params['qpa'], ' AND perm_val >= 1') ;
						break;
				}
				break;
		}
		return true;
	}
}
