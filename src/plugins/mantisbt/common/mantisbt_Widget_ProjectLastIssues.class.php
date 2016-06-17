<?php
/**
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2014, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
require_once 'common/widget/Widget.class.php';
require_once 'common/widget/WidgetLayoutManager.class.php';

class mantisBT_Widget_ProjectLastIssues extends Widget {
	function __construct() {
		parent::__construct('plugin_mantisbt_project_latestissues');
	}

	function getTitle() {
		return _('MantisBT')._(': ')._('Latest 5 Issues.');
	}

	function getCategory() {
		return _('MantisBT');
	}

	function getDescription() {
		return _('MantisBT')._(': ')._('Display the 5 last issues of your project.');
	}

	function getContent() {
		global $HTML, $group_id;
		$mantisbt = plugin_get_object('mantisbt');
		$mantisbtConf = $mantisbt->getMantisBTConf($group_id);
		$group = group_get_object($group_id);
		if (session_loggedin()) {
			$user = session_get_user();
			$userperm = $group->getPermission();
			if ($userperm->IsMember()) {
				$mantisbtUserConf = $mantisbt->getUserConf($mantisbtConf['url']);
				if ($mantisbtUserConf) {
					$username = $mantisbtUserConf['user'];
					$password = $mantisbtUserConf['password'];
				}
			}
		}
		if (!isset($username) || !isset($password)) {
			$username = $mantisbtConf['soap_user'];
			$password = $mantisbtConf['soap_password'];
		}
		$clientSOAP = new SoapClient($mantisbtConf['url'].'/api/soap/mantisconnect.php?wsdl', array('trace' => true, 'exceptions' => true));
		$listStatus = $clientSOAP->__soapCall('mc_enum_status', array('username' => $username, 'password' => $password));
		$arrayBugs = $clientSOAP->__soapCall('mc_project_get_issue_headers', array('username' => $username, 'password' => $password, 'project_id' => $mantisbtConf['id_mantisbt'],  'page_number' => -1, 'per_page' => -1));
		$arrayBugs = array_slice($arrayBugs, 0, 5);
		$content = '';
		if (count($arrayBugs)) {
			use_javascript('/js/sortable.js');
			echo $HTML->getJavascripts();
			$arrTitle = array(_('ID'), _('Title'), _('Status'), _('Category'));
			$content .= $HTML->listTableTop($arrTitle, false, 'sortable_widget_mantisbt_listissues full', 'sortable');
			foreach ($arrayBugs as $bug) {
				$localCells = array();
				$localCells[][] = util_make_link('/plugins/'.$mantisbt->name.'/?type=group&group_id='.$group_id.'&idBug='.$bug->id.'&view=viewIssue', $bug->id);
				$localCells[][] = htmlspecialchars($bug->summary, ENT_QUOTES);
				foreach ($listStatus as $status) {
					if ($status->id == $bug->status) {
						$localCells[][] = $status->name;
					}
				}
				$localCells[][] = $bug->category;
				$content .= $HTML->multiTableRow(array(), $localCells);
			}
			$content .= $HTML->listTableBottom();
		} else {
			$content .= $HTML->information(_('No issues found.'));
		}
		return $content;
	}
}
