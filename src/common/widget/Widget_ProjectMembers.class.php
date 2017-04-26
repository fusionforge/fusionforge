<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2016, Franck Villaume - TrivialDev
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

		$admins = $project->getAdmins();
		$members = $project->getUsers();
		$seen = array();

		$iam_member = false;
		if (count($admins) > 0) {
			$result .= '<div class="extractadmin"><span class="develtitle">'._('Project Admins').'</span><br />';
			$resultcomplete = '<span class="completeadmin" style="display:none">';
			$resultmore = false;
			foreach ($admins as $key => $u) {
				$resultlocal = '<div rel="doap:maintainer">'."\n";
				// A foaf:Person that holds an account on the forge
				$developer_url = util_make_url_u($u->getUnixName(),$u->getID());
				$resultlocal .= '<div typeof="foaf:Person" about="'.
					$developer_url.'#person' .'" >'."\n";
				$resultlocal .= '<div rel="foaf:account">'."\n";
				$resultlocal .= '<div typeof="sioc:UserAccount" about="'.
					$developer_url.
					'">'."\n";
				$resultlocal .= util_display_user($u->getUnixName(),$u->getID(),$u->getRealName())."\n";
				$resultlocal .= "</div>\n"; // /sioc:UserAccount
				$resultlocal .= "</div>\n"; // /foaf:holdsAccount
				$resultlocal .= "</div>\n"; // /foaf:Person
				$resultlocal .= "</div>\n"; // /doap:maintainer|developer
				if ($u->getID() == user_getid()) {
					$iam_member = true;
				}
				$seen[] = $u->getID();
				if ($key < 10) {
					$result .= $resultlocal;
				} else {
					$resultcomplete .= $resultlocal;
					$resultmore = true;
				}
			}
			$resultcomplete .= '</span>';
			$result .= $resultcomplete;
			if ($resultmore) {
				$result .= '<input type="button" class="seemoreadmin" value="'._('See more ...').'" />';
				$result .= '<script type="text/javascript">';
				$result .= 'toggleAdmin = function() { jQuery(".completeadmin").toggle(); };';
				$result .= 'jQuery(document).ready(jQuery(".seemoreadmin").click(jQuery.proxy(this, "toggleAdmin")));';
				$result .= '</script>';
			}
			$result .= '</div>';
		}

		if (count($members) > 0) {
			$headerresult = true;
			$resultcomplete = '<span class="completemember" style="display: none">';
			$resultmore = false;
			foreach ($members as $key => $u) {
				if (in_array($u->getID(), $seen)) {
					continue;
				}
				if ($headerresult) {
					if (count($admins) > 0) {
						$result .= '<hr/>';
					}
					$result .= '<div class="extractmember"><span class="develtitle">'. _('Members')._(':').'</span><br />';
					$headerresult = false;
				}
				$resultlocal = '<div rel="doap:developer">'."\n";
				// A foaf:Person that holds an account on the forge
				$developer_url = util_make_url_u($u->getUnixName(),$u->getID());
				$resultlocal .= '<div typeof="foaf:Person" about="'.
					$developer_url.'#person' .'" >'."\n";
				$resultlocal .= '<div rel="foaf:account">'."\n";
				$resultlocal .= '<div typeof="sioc:UserAccount" about="'.
					$developer_url.
					'">'."\n";
				$resultlocal .= util_display_user($u->getUnixName(),$u->getID(),$u->getRealName())."\n";
				$resultlocal .= "</div>\n"; // /sioc:UserAccount
				$resultlocal .= "</div>\n"; // /foaf:holdsAccount
				$resultlocal .= "</div>\n"; // /foaf:Person
				$resultlocal .= "</div>\n"; // /doap:maintainer|developer
				if ($u->getID() == user_getid()) {
					$iam_member = true;
				}
				if ($key < 10) {
					$result .= $resultlocal;
				} else {
					$resultcomplete .= $resultlocal;
					$resultmore = true;
				}
			}
			$resultcomplete .= '</span>';
			$result .= $resultcomplete;
			if ($resultmore && !$headerresult) {
				$result .= '<input type="button" class="seemoremember" value="'._('See more ...').'" />';
				$result .= '<script type="text/javascript">';
				$result .= 'toggleMember = function() { jQuery(".completemember").toggle(); };';
				$result .= 'jQuery(document).ready(jQuery(".seemoremember").click(jQuery.proxy(this, "toggleMember")));';
				$result .= '</script>';
			}
			if (!$headerresult) {
				$result .= '</div>';
			}
		}

		$result .= '<p><span rel="sioc:has_usergroup">';
		$result .= '<span about="members/" typeof="sioc:UserGroup">';
		$result .= '<span rel="http://www.w3.org/2002/07/owl#sameAs">';
		$result .= util_make_link('/project/memberlist.php?group_id='.$group_id,sprintf(_('View the %d Member(s)'),count($members)));
		$result .= '</span>';
		$result .= '</span>';
		$result .= '</span></p>';
		// end of project usergroup description

		if (!$iam_member) {
			$result .= '<p>'.util_make_link('/project/request.php?group_id='.$group_id,_('Request to join')).'</p>';
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
