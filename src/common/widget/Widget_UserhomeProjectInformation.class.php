<?php
/**
 * Userhome Project Information Widget Class
 *
 * Copyright 2018, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

class Widget_UserhomeProjectInformation extends Widget {

	function __construct($owner_id) {
		$this->owner_id = $owner_id;
		parent::__construct('uhprojectinformation', $owner_id, WidgetLayoutManager::OWNER_TYPE_USERHOME);
		$this->title = _('Project Membership');
	}

	function getTitle() {
		return $this->title;
	}

	function isAvailable() {
		return isset($this->title);
	}

	function getContent() {
		global $HTML;
		$user = user_get_object($this->owner_id);
		$projects = $user->getGroups();
		sortProjectList ($projects) ;
		$roles = RBACEngine::getInstance()->getAvailableRolesForUser($user);
		sortRoleList($roles);
		$start = true;
		foreach ($projects as $p) {
			if (!forge_check_perm('project_read', $p->getID())) {
				continue;
			}
			if ($start) {
				echo html_e('p', array(), _('This user is a member of the following projects')._(':'));
				$start = false;
			}

			$project_link = util_make_link_g ($p->getUnixName(),$p->getID(),$p->getPublicName());
			$project_uri = util_make_url_g ($p->getUnixName(),$p->getID());
			// sioc:UserGroups for all members of a project are named after /projects/A_PROJECT/members/
			$usergroup_uri = $project_uri .'members/';

			print '<div rel="sioc:member_of">'."\n"
				.'<div about="'. $usergroup_uri .'" typeof="sioc:UserGroup">'."\n"
				.'<div rel="sioc:usergroup_of">'."\n"
				.'<div about="'. $project_uri .'" typeof="sioc:Space">';
			$role_names = array () ;
			$sioc_has_function_close = "";
			foreach ($roles as $r) {
				if ($r instanceof RoleExplicit
				&& $r->getHomeProject() != NULL
				&& $r->getHomeProject()->getID() == $p->getID()) {
					$role_names[] = $r->getName() ;
					print '<div property="sioc:has_function" content= "'.$r->getName().'">';
					$sioc_has_function_close .= "</div>";
				}
			}

			print ('<br />' . $project_link .' ('.htmlspecialchars (implode (', ', $role_names)).')');
			print "\n";

			if (forge_check_perm_for_user ($user, 'project_admin', $p->getID())) {
				echo html_e('div', array('rev' => 'doap:maintainer', 'resource' => '#me'));
			}
			else {
				echo html_e('div', array('rev' => 'doap:developer', 'resource' => '#me'));
			}

			echo $sioc_has_function_close."\n";  // sioc:has_function
			echo "</div>\n";  // sioc:Space .../projects/A_PROJECT/
			echo "</div>\n"; // sioc:usergroup_of
			echo "</div>\n";  // sioc:UserGroup .../projects/A_PROJECT/members
			echo "</div>\n"; // sioc:member_of
		}
		if ($start) {
			echo $HTML->information(_('This user is not a member of any project.'));
		}
	}
}
