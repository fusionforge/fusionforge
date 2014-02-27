<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2013, French Ministry of National Education
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

require_once 'Widget.class.php';
require_once 'common/rss/RSS.class.php';

/**
 * Widget_MyProjects
 *
 * PROJECT LIST
 */

class Widget_MyProjects extends Widget {
	function __construct() {
		$this->Widget('myprojects');
	}
	function getTitle() {
		return _("My Projects");
	}

	function getContent() {
		global $HTML;
		$html_my_projects = '';

		$user = session_get_user();
		$groups = $user->getGroups();
		sortProjectList($groups);
		$roles = RBACEngine::getInstance()->getAvailableRolesForUser($user);
		sortRoleList ($roles) ;

		if (count ($groups) < 1) {
			$html_my_projects .= '<div class="warning">'. _("You're not a member of any project") .'</div>';
		} else {
			$html_my_projects .= '<table style="width:100%">';
			$i = 0;
			$ra = RoleAnonymous::getInstance();
			foreach ($groups as $g) {
				$i++;
				$html_my_projects .= '
				<tr '. $HTML->boxGetAltRowStyle($i) .'><td style="width:99%">'.
					'<a href="/projects/'. $g->getUnixName() .'/">'.
					$g->getPublicName().'</a>';

				$isadmin = false;
				$role_names = array();
				foreach ($roles as $r) {
					if ($r instanceof RoleExplicit
						&& $r->getHomeProject() != NULL
						&& $r->getHomeProject()->getID() == $g->getID()) {
						$role_names[] = $r->getName();
						if ($r->hasPermission('project_admin', $g->getID())) {
							$isadmin = true;
						}
					}
				}
				if ($isadmin) {
					$html_my_projects .= ' <small><a href="/project/admin/?group_id='.$g->getID().'">['._("Admin").']</a></small>';
				}
				$html_my_projects .= ' <small>('.htmlspecialchars (implode (', ', $role_names)).')</small>';
				if (!$ra->hasPermission('project_read', $g->getID())) {
					$html_my_projects .= ' (*)';
					$private_shown = true;
				}
				if (!$isadmin) {
					$html_my_projects .= '</td>'.
						'<td><a href="rmproject.php?group_id='. $g->getID().
						'" onClick="return confirm(\''._("Quit this project?").'\')">'.
						'<img src="'.$GLOBALS['HTML']->imgroot.'ic/trash.png" alt="'._('Leave project').'" height="16" width="16" /></a></td></tr>';
				} else {
					$html_my_projects .= '</td><td>&nbsp;</td></tr>';
				}
			}
			$html_my_projects .= '</table>';
			if (isset($private_shown) && $private_shown) {
				$html_my_projects .= '
					<span>(*)&nbsp;<em>'._("Private project").'</em></span>';
			}

		}
		return $html_my_projects;
	}

	function hasRss() {
		return true;
	}

	function displayRss() {
		$rss = new RSS(array(
				'title'       => forge_get_config('forge_name').' - '. _('MyProjects'),
				'description' => _('My projects'),
				'link'        => get_server_url(),
				'language'    => 'en-us',
				'copyright'   => 'Copyright Xerox',
				'pubDate'     => gmdate('D, d M Y G:i:s',time()).' GMT',
			));
		$projects = UserManager::instance()->getCurrentUser()->getGroups();
		sortProjectList($projects);

		if (!$projects || count($projects) < 1) {
			$rss->addItem(array(
					  'title'       => forge_get_config('forge_name'),
					  'description' => _("You're not a member of any project") . db_error(),
					  'link'        => util_make_url()
				  ));
		}

		foreach ($projects as $project) {
			$pid = $project->getID();
			$title = $project->getPublicName();
			$url = util_make_url('/projects/' . $project->getUnixName());

			if ( !RoleAnonymous::getInstance()->hasPermission('project_read',$pid)) {
				$title .= ' (*)';
			}

			$desc = "Project: $url\n";
			if (forge_check_perm ('project_admin', $pid)) {
				$desc .= '<br />Admin: '. util_make_url('/project/admin/?group_id='.$pid);
			}

			$rss->addItem(array(
						'title'       => $title,
						'description' => $desc,
						'link'        => $url)
					);
		}
		$rss->display();
	}

	function getDescription() {
		return _("List the projects you belong to. Selecting any of these projects brings you to the corresponding Project Summary page.");
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
