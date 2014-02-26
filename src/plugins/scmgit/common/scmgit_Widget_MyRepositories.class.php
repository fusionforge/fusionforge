<?php
/**
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012-2014, Franck Villaume - TrivialDev
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

class scmgit_Widget_MyRepositories extends Widget {
	function scmgit_Widget_MyRepositories($owner_type, $owner_id) {
		$this->Widget('plugin_scmgit_user_myrepositories');
		$this->setOwner($owner_id, $owner_type);
	}

	function getTitle() {
		return _("My Git cloned Repositories List");
	}

	function getCategory() {
		return _('SCM');
	}

	function getDescription() {
		return _("Get the list of URLS of your personal Git repository cloned from projects.");
	}

	function getContent() {
		$user = UserManager::instance()->getCurrentUser();
		$scmgitplugin = plugin_get_object('scmgit');
		$GitRepositories = $this->getMyRepositoriesList($scmgitplugin);
		if (count($GitRepositories)) {
			$returnhtml = '<table>';
			foreach ($GitRepositories as $GitRepository) {
				$project = group_get_object($GitRepository);
				$returnhtml .= '<tr><td><tt>git clone git+ssh://'.$user->getUnixName().'@' . $scmgitplugin->getBoxForProject($project) .'/'. forge_get_config('scm_root', 'scmgit') .'/'. $project->getUnixName() .'/users/'. $user->getUnixName() .'.git</tt></td>'.
						'<td>'.util_make_link('/scm/browser.php?group_id='.$project->getID().'&user_id='.$user->getID(), _('Browse Git Repository')).'</td><tr>';
			}
			$returnhtml .= '</table>';
			return $returnhtml;
		} else {
			return '<p class="information">'._('No personal git repository').'</p>';
		}
	}

	function getMyRepositoriesList($scmgitplugin) {
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
