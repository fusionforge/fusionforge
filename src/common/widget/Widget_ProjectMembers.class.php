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

/**
 * Widget_ProjectMembers
 */
class Widget_ProjectMembers extends Widget {
	public function __construct() {
		$this->Widget('projectmembers');
	}
	public function getTitle() {
		return _('Project Members');
	}
	public function getContent() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$pm = ProjectManager::instance();
		$project = $pm->getProject($group_id);

		$admins = $project->getAdmins() ;
		$members = $project->getUsers() ;
		$seen = array () ;

		$iam_member = false ;
		if (count($admins) > 0) {
			echo "<p>\n";
			echo '<span class="develtitle">'._('Project Admins').'</span><br />';
			foreach ($admins as $u) {
				echo '<div rel="doap:maintainer">'."\n";
				// A foaf:Person that holds an account on the forge
				$developer_url = util_make_url_u ($u->getUnixName(),$u->getID());
				echo '<div typeof="foaf:Person" about="'.
					$developer_url.'#me' .'" >'."\n";
				echo '<div rel="foaf:account">'."\n";
				echo '<div typeof="sioc:UserAccount" about="'.
					$developer_url.
					'">'."\n";
				echo util_display_user($u->getUnixName(),$u->getID(),$u->getRealName())."\n";
				echo "</div>\n"; // /sioc:UserAccount
				echo "</div>\n"; // /foaf:holdsAccount
				echo "</div>\n"; // /foaf:Person
				echo "</div>\n"; // /doap:maintainer|developer
				if ($u->getID() == user_getid()) {
					$iam_member = true ;
				}
				$seen[] = $u->getID() ;
			}
			echo "</p>\n";
		}
		$seen_member = false ;
		if (count($members) > 0) {
			echo "<p>\n";
			foreach ($members as $u) {
				if (in_array ($u->getID(), $seen)) {
					continue ;
				}
				if (!$seen_member) {
					echo '<span class="develtitle">'. _('Members').':</span><br />';
					$seen_member = true ;
				}
				echo '<div rel="doap:developer">'."\n";
				// A foaf:Person that holds an account on the forge
				$developer_url = util_make_url_u ($u->getUnixName(),$u->getID());
				echo '<div typeof="foaf:Person" about="'.
					$developer_url.'#me' .'" >'."\n";
				echo '<div rel="foaf:account">'."\n";
				echo '<div typeof="sioc:UserAccount" about="'.
					$developer_url.
					'">'."\n";
				echo util_display_user($u->getUnixName(),$u->getID(),$u->getRealName())."\n";
				echo "</div>\n"; // /sioc:UserAccount
				echo "</div>\n"; // /foaf:holdsAccount
				echo "</div>\n"; // /foaf:Person
				echo "</div>\n"; // /doap:maintainer|developer
				if ($u->getID() == user_getid()) {
					$iam_member = true ;
				}
			}
			echo "</p>\n";
		}

		echo '<p><span rel="sioc:has_usergroup">';
		echo '<span about="members/" typeof="sioc:UserGroup">';
		echo '<span rel="http://www.w3.org/2002/07/owl#sameAs">';
		echo util_make_link ('/project/memberlist.php?group_id='.$group_id,sprintf(_('View the %1$d Member(s)'),count($members)));
		echo '</span>';
		echo '</span>';
		echo '</span></p>';
		// end of project usergroup description

		if (!$iam_member) {
			echo '<p>'.util_make_link ('/project/request.php?group_id='.$group_id,_('Request to join')).'</p>';
		}
}
public function canBeUsedByProject(&$project) {
	return true;
}
function getDescription() {
	return _('List the project members.');
}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
