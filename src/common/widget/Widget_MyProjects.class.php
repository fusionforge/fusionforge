<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Widget.class.php');
require_once('common/rss/RSS.class.php');

/**
* Widget_MyProjects
* 
* PROJECT LIST
*/
class Widget_MyProjects extends Widget {
    function Widget_MyProjects() {
        $this->Widget('myprojects');
    }
    function getTitle() {
        return _("My Projects");
    }

    function getContent() {
        $html_my_projects = '';

	$user = session_get_user () ;
	$groups = $user->getGroups() ;
	sortProjectList ($groups) ;
	$roles = RBACEngine::getInstance()->getAvailableRolesForUser ($user) ;
	sortRoleList ($roles) ;

	if (count ($groups) < 1) {
		$html_my_projects .= _("You're not a member of any project");
        } else {
		$html_my_projects .= '<table style="width:100%">';
		$i = 0 ;
		$ra = RoleAnonymous::getInstance() ;
		foreach ($groups as $g) {
			$i++ ;
			if ($i % 2 == 0) {
				$class="bgcolor-white";
			}
			else {
				$class="bgcolor-grey";
			}
			
			$html_my_projects .= '
			<TR class="'. $class .'"><TD WIDTH="99%">'.
				'<A href="/projects/'. $g->getUnixName() .'/">'.
				$g->getPublicName().'</A>';
			
			$isadmin = false ;
			$role_names = array () ;
			foreach ($roles as $r) {
				if ($r instanceof RoleExplicit
				    && $r->getHomeProject() != NULL
				    && $r->getHomeProject()->getID() == $g->getID()) {
					$role_names[] = $r->getName() ;
					if ($r->hasPermission ('project_admin', $g->getID())) {
						$isadmin = true ;
					}
				}
			}
			if ($isadmin) {
				$html_my_projects .= ' <small><A HREF="/project/admin/?group_id='.$g->getID().'">['._("Admin").']</A></small>';
			}
			$html_my_projects .= ' <small>('.htmlspecialchars (implode (', ', $role_names)).')</small>';
			if (!$ra->hasPermission('project_read', $group->getID())) {
				$html_my_projects .= ' (*)';
				$private_shown = true;
			}
			if (!$isadmin) {
				$html_my_projects .= '</TD>'.
					'<td><A href="rmproject.php?group_id='. $g->getID().
					'" onClick="return confirm(\''._("Quit this project?").'\')">'.
					'<IMG SRC="'.$GLOBALS['HTML']->imgroot.'ic/trash.png" HEIGHT="16" WIDTH="16" BORDER="0"></A></TD></TR>';
			} else {
				$html_my_projects .= '</td><td>&nbsp;</td></TR>';
			}
		}
		
		if (isset($private_shown) && $private_shown) {
			$html_my_projects .= '
                <tr class="'.$class .'"><td colspan="2" class="small">'.
                '(*)&nbsp;<em>' . _("Private project").'</em></td></tr>';
		}
		$html_my_projects .= '</table>';
	}
	return $html_my_projects;
    }
    function hasRss() {
	    return true;
    }
    function displayRss() {
	    $rss = new RSS(array(
            		'title'       => forge_get_config('forge_name').' - MyProjects',
				    'description' => 'My projects',
				    'link'        => get_server_url(),
				    'language'    => 'en-us',
				    'copyright'   => 'Copyright Xerox',
				    'pubDate'     => gmdate('D, d M Y G:i:s',time()).' GMT',
				));
	    $projects = UserManager::instance()->getCurrentUser()->getGroups() ;
	    sortProjectList ($projects) ;

	    if (!$result || $rows < 1) {
		    $rss->addItem(array(
					  'title'       => 'Error',
					  'description' => _("You're not a member of any project") . db_error(),
					  'link'        => util_make_url()
					  ));
		    $rss->display();
		    return ;
	    } 

	    foreach ($projects as $project) {
		    $pid = $project->getID() ;
		    $title = $project->getPublicName() ;
		    $url = util_make_url('/projects/' . $project->getUnixName()) ;

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
    }

    function getDescription() {
	    return _("List the projects you belong to. Selecting any of these projects brings you to the corresponding Project Summary page.");
    }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
