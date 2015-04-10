<?php
/**
 * FusionForge source control management
 *
 * Copyright 2004-2009, Roland Mas
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012,2014 Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/scm.php';
require_once $gfcommon.'include/SysTasksQ.class.php';

abstract class SCMPlugin extends Plugin {
	/**
	 * SCMPlugin() - constructor
	 *
	 */
	function SCMPlugin() {
		$this->Plugin() ;
		$this->_addHook('group_plugin_use');
		$this->_addHook('scm_plugin');
		$this->_addHook('scm_page');
		$this->_addHook('scm_admin_page');
		$this->_addHook('scm_admin_update');
 		$this->_addHook('scm_stats');
		$this->_addHook('scm_create_repo');

		# Other common hooks that can be enabled per plugin:
		# scm_generate_snapshots
		# scm_gather_stats
		# scm_browser_page
		# scm_update_repolist

		$this->provides['scm'] = true;
	}

	function CallHook($hookname, &$params) {
		switch ($hookname) {
			case 'group_plugin_use': {
				if ($params['val'] == true) {
					$systasksq = new SysTasksQ();
					$systasksq->add(SYSTASK_CORE, 'SCM_REPO', $params['group_id']);
				}
				break;
			}
			case 'scm_plugin': {
				$scm_plugins=& $params['scm_plugins'];
				$scm_plugins[]=$this->name;
				break;
			}
			case 'scm_page': {
				$this->printPage($params);
				break ;
			}
			case 'scm_browser_page': {
				$this->printBrowserPage($params);
				break ;
			}
			case 'scm_admin_page': {
				$this->printAdminPage($params);
				break ;
			}
			case 'scm_admin_update': {
				$this->adminUpdate($params);
				break ;
			}
			case 'scm_stats': {
				$this->printShortStats($params);
				break;
			}
			case 'scm_create_repo': {
				session_set_admin();
				$this->createOrUpdateRepo($params);
				break;
			}
			case 'scm_update_repolist': {
				session_set_admin();
				$this->updateRepositoryList($params);
				break;
			}
			case 'scm_regen_apache_auth': {
				session_set_admin();
				$this->regenApacheAuth($params);
				break;
			}
			case 'scm_generate_snapshots': { // Optional
				session_set_admin();
				$this->generateSnapshots($params);
				break;
			}
			case 'scm_gather_stats': { // Optional
				session_set_admin();
				$this->gatherStats($params);
				break;
			}
			case 'widgets': { // Optional
				$this->widgets($params);
				break;
			}
			case 'widget_instance': { // Optional
				$this->myPageBox($params);
				break;
			}
			case 'activity': { //Optional
				$this->activity($params);
				break;
			}
			default: {
				// Default is to use method named as the hook
				$this->$hookname($params);
			}
		}
	}

	final function register() {
		global $scm_list;

		$scm_list[] = $this->name;
	}

	function browserDisplayable($project) {
		if ($project->usesSCM() && $project->usesPlugin($this->name)) {
			if ($project->enableAnonSCM() || forge_check_perm('scm', $project->getID(), 'read')) {
				return true;
			}
		}
		return false;
	}

	abstract function createOrUpdateRepo($params);

	function printShortStats($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return;
		}

		if ($project->usesPlugin($this->name)) {
			echo ' ('.$this->text.')' ;
		}
	}

	function getBlurb () {
		return html_e('p', array(), _('Unimplemented SCM plugin.'));
	}

	function getInstructionsForAnon ($project) {
		return html_e('p', array(), _('Instructions for anonymous access for unimplemented SCM plugin.'));
	}

	function getInstructionsForRW ($project) {
		return html_e('p', array(), _('Instructions for read-write access for unimplemented SCM plugin.'));
	}

	function getSnapshotPara ($project) {
		return html_e('p', array(), _('Instructions for snapshot access for unimplemented SCM plugin.'));
	}

	function getBrowserLinkBlock($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Repository Browser'));
		$b .= html_e('p', array(), _('Browsing the SCM tree is not yet implemented for this SCM plugin.'));
		$b .= html_e('p', array(), '['.util_make_link('/scm/?group_id='.$project->getID(), _('Not implemented yet')).']');
		return $b ;
	}

	function getBrowserBlock($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Repository Browser'));
		$b .= html_e('p', array(), _('Browsing the SCM tree is not yet implemented for this SCM plugin.'));
		return $b ;
	}

	function getStatsBlock($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Repository Statistics'));
		$b .= html_e('p', array(), _('Not implemented for this SCM plugin yet.'));
		return $b ;
	}

	function printPage($params) {
		global $HTML;

		$project = $this->checkParams($params);
		if (!$project) {
			return;
		}

		if ($project->usesPlugin($this->name)) {

			session_require_perm('scm', $project->getID(), 'read');
			// Table for summary info
			echo $HTML->listTableTop();
			$cells = array();
			$cellContent = $this->getBlurb();

			// Instructions for anonymous access
			if ($project->enableAnonSCM()) {
				$cellContent .= $this->getInstructionsForAnon($project);
			}

			// Instructions for developer access
			$cellContent .= $this->getInstructionsForRW($project);

			if ($this->browserDisplayable($project)) {
				$cellContent .= $this->getBrowserLinkBlock($project);
			}

			// Snapshot
			if ($this->browserDisplayable($project)) {
				$cellContent .= $this->getSnapshotPara($project);
			}
			$cells[] = array($cellContent, 'style' => 'width:65%');

			// Browsing
			$cellContent =  $HTML->boxTop(_('Repository History'));
			$cellContent .=  _('Data about current and past states of the repository.');
			if ($this->browserDisplayable($project)) {
				$cellContent .= $this->getStatsBlock($project);
			}

			$cellContent .= $HTML->boxBottom();
			$cells[] = array($cellContent, 'style' => 'width:35%', 'class' => 'top');
			echo $HTML->multiTableRow(array('class' => 'top'), $cells);
			echo $HTML->listTableBottom();
		}
	}

	function printBrowserPage($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return;
		}

		if ($project->usesPlugin ($this->name)) {
			if ($this->browserDisplayable ($project)) {
				// print '<iframe src="'.util_make_url('/scm/browser.php?title='.$group->getUnixName()).'" frameborder="0" width=100% height=700></iframe>' ;
			}
		}
	}

	function printAdminPage($params) {
		$group = group_get_object($params['group_id']);
		$ra = RoleAnonymous::getInstance() ;

		if ( $group->usesPlugin ( $this->name ) && $ra->hasPermission('project_read', $group->getID())) {
			$inputAttr = array('type' => 'checkbox', 'name' => 'scm_enable_anonymous', 'value' => 1);
			if ($group->enableAnonSCM()) {
				$inputAttr['checked'] = 'checked';
			}
			echo html_e('p', array(), html_e('input', $inputAttr).html_e('strong', array(), _('Enable Anonymous Read Access')));
		}
	}

	function adminUpdate($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return;
		}

		if ($project->usesPlugin($this->name) ) {
			if (isset($params['scm_enable_anonymous']) && $params['scm_enable_anonymous']) {
				$project->SetUsesAnonSCM(true);
			} else {
				$project->SetUsesAnonSCM(false);
			}
		}
	}

	function getBoxForProject($project) {
		$box = $project->getSCMBox();
		if ($box == '') {
			$box = forge_get_config('default_server', $this->name);
		}
		if ($box == '') {
			$box = forge_get_config('web_host');
		}
		return $box;
	}

	function scm_delete_repo(&$params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false ;
		}
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (!isset($params['repo_name'])) {
			return false;
		}

		$result = db_query_params('SELECT count(*) AS count FROM scm_secondary_repos WHERE group_id=$1 AND repo_name = $2 AND plugin_id=$3',
					  array ($params['group_id'],
						 $params['repo_name'],
						 $this->getID()));
		if (! $result) {
			$params['error_msg'] = db_error();
			return false;
		}
		if (db_result($result, 0, 'count') == 0) {
			$params['error_msg'] = sprintf(_('No repository %s exists'), $params['repo_name']);
			return false;
		}

		$result = db_query_params ('UPDATE scm_secondary_repos SET next_action = $1 WHERE group_id=$2 AND repo_name=$3 AND plugin_id=$4',
					   array (SCM_EXTRA_REPO_ACTION_DELETE,
						  $params['group_id'],
						  $params['repo_name'],
						  $this->getID()));
		if (! $result) {
			$params['error_msg'] = db_error();
			return false;
		}

		plugin_hook ("scm_admin_update", $params);
		return true;
	}

	function checkParams ($params) {
		$group_id = $params['group_id'] ;
		$project = group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
		}

		return $project ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
