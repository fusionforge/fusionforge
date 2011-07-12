<?php
/**
 * Copyright 2011, Franck Villaume - Capgemini
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
require_once('common/widget/Widget.class.php');
require_once('common/widget/WidgetLayoutManager.class.php');

class scmgit_Widget_MyRepositories extends Widget {
	function scmgit_Widget_MyRepositories($owner_type, $owner_id) {
		$this->Widget('plugin_scmgit_user_myrepositories');
		$this->setOwner($owner_id, $owner_type);
	}

	function getTitle() {
		return _("My Git personal Repositories List");
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
		$GitRepositories = $this->getMyRepositoriesList();
		if (count($GitRepositories)) {
			$returnhtml = '<table>';
			foreach ($GitRepositories as $GitRepository) {
				$project = group_get_object($GitRepository);
				$returnhtml .= '<tr><td><tt>git clone git+ssh://'.$user->getUnixName().'@' . $scmgitplugin->getBoxForProject($project) . forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/users/'. $user->getUnixName() .'.git</tt></p></td><tr>';
			}
			$returnhtml .= '</table>';
			return $returnhtml;
		} else {
			return '<p class="information">'._('No personal git repository').'</p>';
		}
	}

	function getMyRepositoriesList() {
		$returnedArray = array();
		$res = db_query_params('SELECT p.group_id FROM plugin_scmgit_personal_repos p, users u WHERE u.user_id=p.user_id AND u.unix_status = $1 AND u.user_id = $2',
					array('A',$this->owner_id));
		if (!$res) {
			return $returnedArray;
		} else {
			$rows = db_numrows($res);
			for ($i=0; $i<$rows; $i++) {
				$returnedArray[] = db_result($res,$i,'group_id');
			}
		}
		return $returnedArray;
	}
}

?>
