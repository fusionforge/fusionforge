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
* Widget_MyAdmin
* 
* Personal Admin
*/
class Widget_MyAdmin extends Widget {
    function Widget_MyAdmin() {
        $this->Widget('myadmin');
    }
    function getTitle() {
        return _("Pending administrative tasks");
    }
    function getContent() {
	$i = 0;
	$html_my_admin = '<table width="100%">';

	if (forge_check_global_perm ('forge_admin')) {
		$res = db_query_params("SELECT count(*) AS count FROM users WHERE status='P' OR status='V' OR status='W'",array());
		$row = db_fetch_array($res);
		$pending_users = $row['count'];
		$res = db_query_params("SELECT count(*) AS count FROM users WHERE status='V' OR status='W'",array());
		$row = db_fetch_array($res);
		$validated_users = $row['count'];
		
		$html_my_admin .= $this->_get_admin_row(
			$i++, 
			vsprintf(_('Users in <a href="%s"><b>P</b> (pending) Status</a>'), array("/admin/userlist.php?status=P")),
			$pending_users,
			$this->_get_color($pending_users)
			);
		
		if (isset($GLOBALS['sys_user_approval']) && $GLOBALS['sys_user_approval'] == 1) {
			$html_my_admin .= $this->_get_admin_row(
				$i++, 
				vsprintf(_('Validated users <a href="%s"><b>pending email activation</b></a>'), array("/admin/approve_pending_users.php?page=validated")),
				$validated_users,
				$this->_get_color($validated_users)
				);
		}
	}

	if (forge_check_global_perm ('approve_projects')) {
		$res = db_query_params("SELECT count(*) AS count FROM groups WHERE status='P'",array());
		$row = db_fetch_array($res);
		$pending_projects = $row['count'];

		$html_my_admin .= $this->_get_admin_row(
			$i++, 
			vsprintf(_('Groups in <a href="%s"><b>P</b> (pending) Status</a>'), array("/admin/approve-pending.php")),
			$pending_projects,
			$this->_get_color($pending_projects)
			);
	}

	if (forge_check_global_perm ('approve_news')) {
		$old_date = time()-60*60*24*30;
		$res = db_query_params('SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=0
			AND news_bytes.group_id=groups.group_id
			AND post_date > $1
			AND groups.status=$2
			ORDER BY post_date',
					  array ($old_date, 'A')) ;
		$pending_news = db_numrows($res);		
		
		$html_my_admin .= $this->_get_admin_row(
			$i++, 
			'<a href="/news/admin">'. _("Site News Approval") .'</a>',
			$pending_news,
			$this->_get_color($pending_news)
			);
	}
	$html_my_admin .= '</table>';

	return $html_my_admin;
    }
    function _get_color($nb) {
	    return $nb == 0 ? 'green' : 'orange';
    }
    function _get_admin_row($i, $text, $value, $bgcolor, $textcolor = 'white') {
	    $i=$i++;
	    if ($i % 2 == 0) {
		    $class="bgcolor-white";
	    }
	    else {
		    $class="bgcolor-grey";
	    }



	    return '<tr class="'. $class.'"><td>'. $text .'</td><td nowrap="nowrap" style="width:20%; background:'. $bgcolor .'; color:'. $textcolor .'; padding: 2px 8px; font-weight:bold; text-align:center;">'. $value .'</td></tr>';
    }
}
?>
