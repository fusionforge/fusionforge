<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';

/**
 * Widget_ProjectMembers
 */
class Widget_ProjectMembers extends Widget {
	function __construct() {
		parent::__construct('projectmembers');
	}

	public function getTitle() {
		return _('Project Members');
	}

	public function getContent() {
		$result = '';

		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$pm = ProjectManager::instance();
		$project = $pm->getProject($group_id);

		$admins = $project->getAdmins() ;
		$members = $project->getUsers() ;
		$seen = array () ;

		$iam_member = false ;
		if (count($admins) > 0) {
			$result .= '<span class="develtitle">'._('Project Admins').'</span><br />';
			foreach ($admins as $u) {
				$result .= '<div rel="doap:maintainer">'."\n";
				// A foaf:Person that holds an account on the forge
				$developer_url = util_make_url_u ($u->getUnixName(),$u->getID());
				$result .= '<div typeof="foaf:Person" about="'.
					$developer_url.'#person' .'" >'."\n";
				$result .= '<div rel="foaf:account">'."\n";
				$result .= '<div typeof="sioc:UserAccount" about="'.
					$developer_url.
					'">'."\n";
				$result .= util_display_user($u->getUnixName(),$u->getID(),$u->getRealName())."\n";
				$result .= "</div>\n"; // /sioc:UserAccount
				$result .= "</div>\n"; // /foaf:holdsAccount
				$result .= "</div>\n"; // /foaf:Person
				$result .= "</div>\n"; // /doap:maintainer|developer
				if ($u->getID() == user_getid()) {
					$iam_member = true ;
				}
				$seen[] = $u->getID() ;
			}
		}

		if (count($members) > 0) {
			$result .= '<span class="develtitle">'. _('Members')._(':').'</span><br />';
			foreach ($members as $u) {
				if (in_array ($u->getID(), $seen)) {
					continue ;
				}

				$result .= '<div rel="doap:developer">'."\n";
				// A foaf:Person that holds an account on the forge
				$developer_url = util_make_url_u ($u->getUnixName(),$u->getID());
				$result .= '<div typeof="foaf:Person" about="'.
					$developer_url.'#person' .'" >'."\n";
				$result .= '<div rel="foaf:account">'."\n";
				$result .= '<div typeof="sioc:UserAccount" about="'.
					$developer_url.
					'">'."\n";
				$result .= util_display_user($u->getUnixName(),$u->getID(),$u->getRealName())."\n";
				$result .= "</div>\n"; // /sioc:UserAccount
				$result .= "</div>\n"; // /foaf:holdsAccount
				$result .= "</div>\n"; // /foaf:Person
				$result .= "</div>\n"; // /doap:maintainer|developer
				if ($u->getID() == user_getid()) {
					$iam_member = true ;
				}
			}
		}

		$result .= '<p><span rel="sioc:has_usergroup">';
		$result .= '<span about="members/" typeof="sioc:UserGroup">';
		$result .= '<span rel="http://www.w3.org/2002/07/owl#sameAs">';
		$result .= util_make_link ('/project/memberlist.php?group_id='.$group_id,sprintf(_('View the %d Member(s)'),count($members)));
		$result .= '</span>';
		$result .= '</span>';
		$result .= '</span></p>';
		// end of project usergroup description

		if (!$iam_member) {
			$result .= '<p>'.util_make_link ('/project/request.php?group_id='.$group_id,_('Request to join')).'</p>';
		}

		return $result;
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
