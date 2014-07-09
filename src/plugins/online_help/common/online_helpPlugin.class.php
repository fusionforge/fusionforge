<?php

/**
 * online_helpPlugin Class
 *
 * Copyright 2008 Alain Peyrat <aljeux@free.fr>
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Description:
 *
 * This plugin adds a contextual online doc to GForge by adding a link 'Get Help'
 * on top of the page (near the login/logout links).
 * Documentation is based on the docbook manual converted in chunked HTML.
 */

class online_helpPlugin extends Plugin {

	function __construct() {
		$this->Plugin() ;
		$this->name = "online_help" ;
		$this->text = "online_help!" ; // To show in the tabs, use...
		$this->hooks[] = "headermenu"; // to show up in the admin page fro group
	}

	function headermenu($params) {
		$guide = util_make_uri('/plugins/online_help/');

		$user_guide = array(
			'user' => 'ug_user.html',
			'login' => 'ug_getting_started_login.html',
			'trove' => 'ug_sitewide_trove.html',
			'snippet' => 'ug_sitewide_snippet.html',
			'people' => 'ug_sitewide_project_help.html',
			'home' => 'ug_project.html',
			'admin' => 'ug_project_project_admin.html',
			'activity' => 'ug_project_activity.html',
			'forums' => 'ug_project_forums.html',
			'tracker' => 'ug_project_tracker.html',
			'mail' => 'ug_project_mailing_lists.html',
			'pm' => 'ug_project_task_manager.html',
			'docman' => 'ug_project_docman.html',
			'surveys' => 'ug_project_surveys.html',
			'news' => 'ug_project_news.html',
			'scm' => 'ug_project_subversion.html',
			'frs' => 'ug_project_file_releases.html',
			'wiki' => 'ug_project_wiki.html',
		);

		$uri = getStringFromServer('REQUEST_URI');

		if (strstr($uri,'softwaremap')) {
			$guide .= $user_guide['trove'];
		} elseif (strstr($uri,'/my/')) {
			$guide .= $user_guide['user'];
		} elseif (strstr($uri,'/account/login.php')) {
			$guide .= $user_guide['login'];
		} elseif (strstr($uri,'/account/')) {
			$guide .= $user_guide['user'];
		} elseif (strstr($uri,'/snippet/')) {
			$guide .= $user_guide['snippet'];
		} elseif (strstr($uri,'/people/')) {
			$guide .= $user_guide['people'];
		} elseif (isset($params['toptab']) && isset($user_guide[ $params['toptab'] ])) {
			$guide .= $user_guide[ $params['toptab'] ];
		} else {
			$guide .= 'index.html';
		}

		$params['links'][] = util_make_link('javascript:help_window(\''.$guide.'\')', _('Get Help'), array(), true);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
