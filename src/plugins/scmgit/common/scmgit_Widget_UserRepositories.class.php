<?php
/**
 * Copyright 2019, Franck Villaume - TrivialDev
 * http://fusionforge.org
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
require_once 'common/widget/Widget.class.php';
require_once 'common/widget/WidgetLayoutManager.class.php';

class scmgit_Widget_UserRepositories extends Widget {
	function __construct($owner_type, $owner_id) {
		parent::__construct('plugin_scmgit_user_userrepositories');
		$this->setOwner($owner_id, $owner_type);
	}

	function getTitle() {
		return _('Git cloned Repositories List');
	}

	function getCategory() {
		return _('SCM');
	}

	function getDescription() {
		return _('Get the list of URLS of personal Git repository cloned from projects, limited to public Git Repositories or shared with visitors.');
	}

	function getContent() {
		global $HTML;
		$user = session_get_user();
		$GitRepositories = $this->getMyRepositoriesList();
		if (count($GitRepositories)) {
			$ssh_port = '';
			if (forge_get_config('ssh_port') != 22) {
				$ssh_port = ':'.forge_get_config('ssh_port');
			}
			$returnhtml = null;
			foreach ($GitRepositories as $GitRepository) {
				$project = group_get_object($GitRepository);
				$scmgitPluginObject = PluginManager::instance()->GetPluginObject('scmgit')
				if (forge_check_perm('scm', $project->getID(), 'read')) {
					$cells = array();
					$cells[][] = '<kbd>git clone git+ssh://'.$user->getUnixName().'@' . $scmgitPluginObject->getBoxForProject($project).$ssh_port.forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/users/'. $user->getUnixName() .'.git</kbd>';
					$cells[][] = util_make_link('/scm/browser.php?group_id='.$project->getID().'&user_id='.$user->getID(), _('Browse Git Repository'));
					$returnhtml .= $HTML->multiTableRow(array(), $cells);
				}
			}
			if ($returnhtml) {
				return $HTML->listTableTop().$returnhtml.$HTML->listTableBottom();
			}
		}
		return $HTML->warning_msg(_('No personal git repository.'));
	}

	function getMyRepositoriesList() {
		$scmgitplugin_id = array_search('scmgit', PluginManager::instance()->getPlugins());
		$returnedArray = array();
		$res = db_query_params('SELECT group_id FROM scm_personal_repos WHERE user_id = $1 AND plugin_id = $2',
					array($this->owner_id, $scmgitplugin_id));
		if (!$res) {
			return $returnedArray;
		} else {
			$rows = db_numrows($res);
			for ($i = 0; $i < $rows; $i++) {
				$returnedArray[] = db_result($res, $i, 'group_id');
			}
		}
		return $returnedArray;
	}
}
