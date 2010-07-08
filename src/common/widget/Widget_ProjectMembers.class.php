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
		$res_admin = db_query_params ('SELECT users.user_id,users.user_name,users.realname,user_group.admin_flags
				FROM users,user_group
				WHERE user_group.user_id=users.user_id
				AND user_group.group_id=$1
				AND users.status=$2
				ORDER BY admin_flags DESC,realname',
				array($group_id,
					'A'));

		$iam_member = false ;
		if (db_numrows($res_admin) > 0) {
			echo "<p>\n";
			echo '<span class="develtitle">'._('Project Admins').'</span><br />';
			$started_developers = false;

			while ($row_admin = db_fetch_array($res_admin)) {
				if (trim($row_admin['admin_flags']) != 'A' && !$started_developers) {
					$started_developers=true;
					echo '<span class="develtitle">'. _('Members').':</span><br />';
				}
				if (!$started_developers) {
					echo '<div rel="doap:maintainer">'."\n";
				} else {
					echo '<div rel="doap:developer">'."\n";
				}
				// A foaf:Person that holds an account on the forge
				$developer_url = util_make_url_u ($row_admin['user_name'],$row_admin['user_id']);
				echo '<div typeof="foaf:Person" xmlns:foaf="http://xmlns.com/foaf/0.1/" about="'.
					$developer_url.'#me' .'" >'."\n";
				echo '<div rel="foaf:account">'."\n";
				echo '<div typeof="sioc:UserAccount" about="'.
					$developer_url.
					'" xmlns:sioc="http://rdfs.org/sioc/ns#">'."\n";
				echo util_make_link_u ($row_admin['user_name'],$row_admin['user_id'],$row_admin['realname']) ."<br />\n";
				echo "</div>\n"; // /sioc:UserAccount
				echo "</div>\n"; // /foaf:holdsAccount
				echo "</div>\n"; // /foaf:Person
				echo "</div>\n"; // /doap:maintainer|developer
				if ($row_admin['user_id'] == user_getid())
					$iam_member = true ;
			}
			echo "</p>\n";
		}

		$members = $project->getUsers();
		echo '<p><span rel="sioc:has_usergroup" xmlns:sioc="http://rdfs.org/sioc/ns#">';
		echo '<div about="members/" typeof="sioc:UserGroup">';
		echo '<span rel="http://www.w3.org/2002/07/owl#sameAs">';
		echo util_make_link ('/project/memberlist.php?group_id='.$group_id,sprintf(_('View the %1$d Member(s)'),count($members)));
		echo '</span>';
		echo '</div>';
		echo '</span></p>';
		// end of project usergroup description

		if (!$iam_member) {
			echo '<p>'.util_make_link ('/project/request.php?group_id='.$group_id,_('Request to join')).'</p>';
		}
		/*echo '<span class="develtitle">' . _('Project admins').':</span><br />';
		  while ($row_admin = db_fetch_array($res_admin)) {
		  $display_name = '';
		  $em->processEvent('get_user_display_name', array(
		  'user_id'           => $row_admin['user_id'],
		  'user_name'         => $row_admin['user_name'],
		  'realname'          => $row_admin['realname'],
		  'user_display_name' => &$display_name
		  ));
		  if (!$display_name) {
		  $display_name = $user_helper->getDisplayNameFromUserId($row_admin['user_id']);
		  }
		  echo '<a href="/users/'.$row_admin['user_name'].'/">'. $display_name .'</a><br />';
		  }
		  echo '<hr width="100%" size="1" NoShade>';
		  }
		  echo '<span class="develtitle">' . _('Developers') . ':</span><br />';
		// count of developers on this project
		$res_count = db_query_params("SELECT user_id FROM user_group WHERE group_id=$1",array($group_id));
		echo db_numrows($res_count);
		echo ' <a href="/project/memberlist.php?group_id=' . $group_id . '">[' . _('View members') . ']</a>';*/
}
public function canBeUsedByProject(&$project) {
	return true;
}
function getDescription() {
	return _('List the project members.');
}
}

?>
