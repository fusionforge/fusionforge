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
		
		if ($GLOBALS['sys_user_approval'] == 1) {
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
		$sql="SELECT * FROM news_bytes WHERE is_approved=0 OR is_approved=3";
		$result=db_query_params($sql,array());
		$pending_news = 0;
		$rows=db_numrows($result);
		for ($i=0; $i<$rows; $i++) {
			//if the news is private, not display it in the list of news to be approved
			$forum_id=db_result($result,$i,'forum_id');
			/*$res = news_read_permissions($forum_id);
			// check on db_result($res,0,'ugroup_id') == $UGROUP_ANONYMOUS only to be consistent
			// with ST DB state
			if ((db_numrows($res) < 1) || (db_result($res,0,'ugroup_id') == $GLOBALS['UGROUP_ANONYMOUS'])) {
			$pending_news++;
			}*/
		}
		
		
		$html_my_admin .= $this->_get_admin_row(
			$i++, 
			'<a href="/news/admin">'. _("Site News Approval") .'</a>',
			$pending_news,
			$this->_get_color($pending_news)
			);
		
		$result = array();
		//$em =& EventManager::instance();
		//$em->processEvent('widget_myadmin', array('result' => &$result));
		foreach($result as $entry) {
			$html_my_admin .= $this->_get_admin_row(
				$i++, 
				$entry['text'],
				$entry['value'],
				$entry['bgcolor'],
				isset($entry['textcolor']) ? $entry['textcolor'] : 'white'
				);
		}
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
