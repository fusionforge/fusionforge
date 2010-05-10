<?php
/**
 * @copyright Copyright (c) Xerox Corporation, Codendi 2007-2008.
 *
 * This file is licensed under the GNU General Public License version 2. See the file COPYING.
 * 
 * @author Marc Nazarian <marc.nazarian@xrce.xerox.com>
 *
 * HudsonPlugin
 */

require_once('PluginHudsonJobDao.class.php');

class hudsonPlugin extends Plugin {

	function hudsonPlugin($id=0) {
		$this->Plugin($id);
		$this->name = "hudson" ;
		$this->text = _('Continuous Integration') ; // To show in the tabs, use...
		$this->_addHook("user_personal_links");//to make a link to the user�s personal part of the plugin
		$this->_addHook("usermenu") ;
		$this->_addHook("groupmenu");	// To put into the project tabs
		$this->_addHook("groupisactivecheckbox") ; // The "use ..." checkbox in editgroupinfo
		$this->_addHook("groupisactivecheckboxpost") ; //
		$this->_addHook("userisactivecheckbox") ; // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost") ; //
		$this->_addHook("project_admin_plugins"); // to show up in the admin page fro group
		$this->_addHook('javascript_file', 'jsFile', false);
		$this->_addHook('javascript',  false);
		$this->_addHook('cssfile', 'cssFile', false);

		$this->_addHook('project_is_deleted', 'projectIsDeleted', false);

		$this->_addHook('widget_instance', 'myPageBox', false);
		$this->_addHook('widgets', 'widgets', false);

		$this->_addHook('get_available_reference_natures', 'getAvailableReferenceNatures', false);
		$this->_addHook('ajax_reference_tooltip', 'ajax_reference_tooltip', false);
	}
	function CallHook ($hookname, $params) {
		global $use_hudsonplugin,$G_SESSION,$HTML,$gfcommon,$gfwww,$gfplugins;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("hudson")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we�re calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
						array ('/plugins/hudson/index.php' . $param ));				
			}
		} elseif ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$project = &group_get_object($group_id);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if (!$project->isProject()) {
				return;
			}
			if ( $project->usesPlugin ( $this->name ) ) {
				$params['TITLES'][]=$this->text;
				$params['DIRS'][]='/plugins/hudson/index.php?group_id=' . $group_id . "&pluginname=" . $this->name; // we indicate the part we�re calling is the project one
			} 
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );

		} elseif ($hookname =='cssfile') {
			echo '<link rel="stylesheet" type="text/css" href="/plugins/hudson/themes/default/css/style.css" />';
		} elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_hudsonplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ( $group->usesPlugin ( $this->name ) ) {
				echo "CHECKED";
			}
			echo "><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>Use ".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";

		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			$use_hudsonplugin = getStringFromRequest('use_hudsonplugin');
			if ( $use_hudsonplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "userisactivecheckbox") {
			//check if user is active
			// this code creates the checkbox in the user account manteinance page to activate/deactivate the plugin
			$user = $params['user'];
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_hudsonplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "CHECKED";
			}
			echo ">    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "userisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the user account manteinance page
			$user = $params['user'];
			$use_hudsonplugin = getStringFromRequest('use_hudsonplugin');
			if ( $use_hudsonplugin == 1 ) {
				$user->setPluginUse ( $this->name );
			} else {
				$user->setPluginUse ( $this->name, false );
			}
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_hudsonplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "CHECKED";
			}
			echo ">    Use ".$this->text." Plugin";
			echo "</td>";
		} elseif ($hookname == "cssfile") {
			$this->cssFile($params);
		} elseif ($hookname == "javascript_file") {
			$this->jsFile($params);
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

		}	
	}
	function &getPluginInfo() {
		if (!is_a($this->pluginInfo, 'hudsonPluginInfo')) {
			require_once('hudsonPluginInfo.class.php');
			$this->pluginInfo =& new hudsonPluginInfo($this);
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
			echo '<link rel="stylesheet" type="text/css" href="'.$this->getThemePath().'/css/style.css" />';
		}
	}

	function jsFile($params) {
		// Only include the js files if we're actually in the IM pages.
		// This stops styles inadvertently clashing with the main site.
			echo '<script type="text/javascript" src="/scripts/prototype/prototype.js"></script>'."\n";
			echo '<script type="text/javascript" src="/scripts/scriptaculous/scriptaculous.js"></script>'."\n";
			echo '<script type="text/javascript" src="/scripts/codendi/Tooltip.js"></script>'."\n";
			echo '<script type="text/javascript" src="/scripts/codendi/LayoutManager.js"></script>'."\n";
			echo '<script type="text/javascript" src="/scripts/codendi/ReorderColumns.js"></script>'."\n";
			echo '<script type="text/javascript" src="/scripts/codendi/codendi-1236793993.js"></script>'."\n";
			echo '<script type="text/javascript" src="hudson_tab.js"></script>'."\n";
		if (strpos($_SERVER['REQUEST_URI'], $this->getPluginPath()) === 0) {
		//	echo '<script type="text/javascript" src="/scripts/codendi/cross_references.js"></script>'."\n";
			echo '<script type="text/javascript" src="hudson_tab.js"></script>'."\n";
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
		require_once('common/widget/WidgetLayoutManager.class.php');

		$user = UserManager::instance()->getCurrentUser();

		// MY
		if ($params['widget'] == 'plugin_hudson_my_jobs') {
			require_once('hudson_Widget_MyMonitoredJobs.class.php');
			$params['instance'] = new hudson_Widget_MyMonitoredJobs($this);
		}
		if ($params['widget'] == 'plugin_hudson_my_joblastbuilds') {
			require_once('hudson_Widget_JobLastBuilds.class.php');
			$params['instance'] = new hudson_Widget_JobLastBuilds(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
		if ($params['widget'] == 'plugin_hudson_my_jobtestresults') {
			require_once('hudson_Widget_JobTestResults.class.php');
			$params['instance'] = new hudson_Widget_JobTestResults(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
		if ($params['widget'] == 'plugin_hudson_my_jobtesttrend') {
			require_once('hudson_Widget_JobTestTrend.class.php');
			$params['instance'] = new hudson_Widget_JobTestTrend(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
		if ($params['widget'] == 'plugin_hudson_my_jobbuildhistory') {
			require_once('hudson_Widget_JobBuildHistory.class.php');
			$params['instance'] = new hudson_Widget_JobBuildHistory(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
		if ($params['widget'] == 'plugin_hudson_my_joblastartifacts') {
			require_once('hudson_Widget_JobLastArtifacts.class.php');
			$params['instance'] = new hudson_Widget_JobLastArtifacts(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}

		// PROJECT
		if ($params['widget'] == 'plugin_hudson_project_jobsoverview') {
			require_once('hudson_Widget_ProjectJobsOverview.class.php');
			$params['instance'] = new hudson_Widget_ProjectJobsOverview($this);
		}
		if ($params['widget'] == 'plugin_hudson_project_joblastbuilds') {
			require_once('hudson_Widget_JobLastBuilds.class.php');
			$params['instance'] = new hudson_Widget_JobLastBuilds(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
		if ($params['widget'] == 'plugin_hudson_project_jobtestresults') {
			require_once('hudson_Widget_JobTestResults.class.php');
			$params['instance'] = new hudson_Widget_JobTestResults(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
		if ($params['widget'] == 'plugin_hudson_project_jobtesttrend') {
			require_once('hudson_Widget_JobTestTrend.class.php');
			$params['instance'] = new hudson_Widget_JobTestTrend(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
		if ($params['widget'] == 'plugin_hudson_project_jobbuildhistory') {
			require_once('hudson_Widget_JobBuildHistory.class.php');
			$params['instance'] = new hudson_Widget_JobBuildHistory(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
		if ($params['widget'] == 'plugin_hudson_project_joblastartifacts') {
			require_once('hudson_Widget_JobLastArtifacts.class.php');
			$params['instance'] = new hudson_Widget_JobLastArtifacts(WidgetLayoutManager::OWNER_TYPE_GROUP, $GLOBALS['group_id']);
		}
	}
	function widgets($params) {
		require_once('common/widget/WidgetLayoutManager.class.php');
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
		require_once('HudsonJob.class.php');
		require_once('HudsonBuild.class.php');
		require_once('hudson_Widget_JobLastBuilds.class.php');

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
					echo '<strong>' . _("Status:") . '</strong> ' . $build->getResult();
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
							$html .= ' <li>'._("Last Success:").' <a href="/plugins/hudson/?action=view_build&group_id='.$group_id.'&job_id='.$job_id.'&build_id='.$job->getLastSuccessfulBuildNumber().'"># '.$job->getLastSuccessfulBuildNumber().'</a></li>';
							$html .= ' <li>'._("Last Failure:").' <a href="/plugins/hudson/?action=view_build&group_id='.$group_id.'&job_id='.$job_id.'&build_id='.$job->getLastFailedBuildNumber().'"># '.$job->getLastFailedBuildNumber().'</a></li>';
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
		require_once('hudson.class.php');
		$controler =& new hudson();
		$controler->process();
	}

}

?>
