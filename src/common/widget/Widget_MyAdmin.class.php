<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012, Franck Villaume - TrivialDev
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

/**
 * Widget_MyAdmin
 *
 * Personal Admin
 */

class Widget_MyAdmin extends Widget {
	function __construct() {
		$this->Widget('myadmin');
	}
	function getTitle() {
		return _("Pending administrative tasks");
	}
	function getContent() {
		$i = 0;
		$html_my_admin = '<table class="fullwidth">';

		if (forge_check_global_perm ('forge_admin')) {
			$res = db_query_params("SELECT count(*) AS count FROM users WHERE status='P' OR status='V' OR status='W'",array());
			$row = db_fetch_array($res);
			$pending_users = $row['count'];
			$res = db_query_params("SELECT count(*) AS count FROM users WHERE status='V' OR status='W'",array());
			$row = db_fetch_array($res);
			$validated_users = $row['count'];

			$html_my_admin .= $this->_get_admin_row(
				$i++,
				vsprintf(_('Users in <a href="%s"><strong>P</strong> (pending) Status</a>'), array("/admin/userlist.php?status=P")),
				$pending_users,
				$this->_get_color($pending_users)
			);
		}

		if (forge_check_global_perm ('approve_projects')) {
			$res = db_query_params('SELECT count(*) AS count FROM groups
			WHERE group_id > 4
			    AND status = $1
			    AND register_time > 0
			    AND is_template = 0',
				array('P'));
			$row = db_fetch_array($res);
			$pending_projects = $row['count'];

			$html_my_admin .= $this->_get_admin_row(
				$i++,
				vsprintf(_('Groups in <a href="%s"><strong>P</strong> (pending) Status</a>'), array("/admin/approve-pending.php")),
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
		return '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'. $text .'</td><td style="white-space:nowrap; width:20%; background:'. $bgcolor .'; color:'. $textcolor .'; padding: 2px 8px; font-weight:bold; text-align:center;">'. $value .'</td></tr>';
	}
}
