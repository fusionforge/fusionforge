<?php
/**
 * MoinMoinPlugin Class
 *
 * Copyright 2016, Franck Villaume - TrivialDev
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

forge_define_config_item('wiki_data_path','moinmoin', '$core/data_path/plugins/moinmoin/wikidata');
forge_define_config_item('use_frame', 'moinmoin', false);
forge_set_config_item_bool('use_frame', 'moinmoin');

class MoinMoinPlugin extends Plugin {
	public $systask_types = array(
		'MOINMOIN_CREATE_WIKI' => 'create-wikis.php',
	);

	function __construct($id = 0) {
		parent::__construct($id);
		$this->name = "moinmoin";
		$this->text = _("MoinMoinWiki") ; // To show in the tabs, use...
		$this->pkg_desc =
_("This plugin allows each project to embed MoinMoinWiki under a tab.");
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "project_public_area";
		$this->hooks[] = "role_get";
		$this->hooks[] = "role_normalize";
		$this->hooks[] = "role_unlink_project";
		$this->hooks[] = "role_translate_strings";
		$this->hooks[] = "role_get_setting";
		$this->hooks[] = "clone_project_from_template" ;
		$this->hooks[] = 'crossrefurl';
	}

	function getWikiUrl($project) {
		if (forge_get_config('use_frame', 'moinmoin')){
			return util_make_uri('/plugins/moinmoin/frame.php?group_id=' . $project->getID());
		} else {
			return util_make_uri('/plugins/moinmoin/'.$project->getUnixName().'/FrontPage');
		}
	}

	function CallHook($hookname, &$params) {
		if (isset($params['group_id'])) {
			$group_id=$params['group_id'];
		} elseif (isset($params['group'])) {
			$group_id=$params['group'];
		} else {
			$group_id=null;
		}
		if ($hookname == "groupmenu") {
			$project = group_get_object($group_id);
			if (!$project || !is_object($project) || $project->isError()) {
				return;
			}
			if ($project->usesPlugin($this->name)) {
				$params['TITLES'][] = $this->text;
				$params['DIRS'][] = $this->getWikiUrl($project);
				$params['TOOLTIPS'][] = _('MoinMoin Space');
				if (session_loggedin()) {
					$userperm = $project->getPermission();
					if ($userperm->isAdmin()) {
						$params['ADMIN'][] = '';
					}
				}
				if(isset($params['toptab']) && $params['toptab'] == $this->name){
					$params['selected'] = array_search($this->text, $params['TITLES']);
				}
			}
		} elseif ($hookname == "project_public_area") {
			$project = group_get_object($group_id);
			if (!$project || !is_object($project) || $project->isError()) {
				return;
			}
			if ( $project->usesPlugin ( $this->name ) ) {
				$params['result'] .= '<div class="public-area-box">';
				$params['result'] .= util_make_link($this->getWikiUrl($project), html_image('ic/wiki20g.png', 20, 20, array('alt' => 'Wiki')).' MoinMoin', array(), true);
				$params['result'] .= '</div>';
			}
		} elseif ($hookname == "role_get") {
			$role =& $params['role'] ;

			// MoinMoin access
			// 0 - None
			// 1 - Read
			// 2 - Write
			// 3 - Admin
			$right = new PluginSpecificRoleSetting ($role,
								'plugin_moinmoin_access') ;
			$right->SetAllowedValues (array ('0', '1', '2', '3')) ;
			$right->SetDefaultValues (array ('Admin' => '3',
							 'Senior Developer' => '2',
							 'Junior Developer' => '2',
							 'Doc Writer' => '2',
							 'Support Tech' => '1')) ;

		} elseif ($hookname == "role_normalize") {
			$role =& $params['role'] ;
			$new_pa =& $params['new_pa'] ;

			$projects = $role->getLinkedProjects() ;
			foreach ($projects as $p) {
				$role->normalizePermsForSection ($new_pa, 'plugin_moinmoin_access', $p->getID()) ;
			}
		} elseif ($hookname == "role_unlink_project") {
			$role =& $params['role'] ;
			$project =& $params['project'] ;

			$settings = array('plugin_moinmoin_access');

			foreach ($settings as $s) {
				db_query_params('DELETE FROM pfo_role_setting WHERE role_id=$1 AND section_name=$2 AND ref_id=$3',
						array($role->getID(),
						      $s,
						      $project->getID()));
			}
		} elseif ($hookname == "role_translate_strings") {
			$role =& $params['role'];
			$right = new PluginSpecificRoleSetting ($role, 'plugin_moinmoin_access') ;
			$right->setDescription (_('MoinMoin Wiki access')) ;
			$right->setValueDescriptions (array ('0' => _('No Access'),
							     '1' => _('Read access'),
							     '2' => _('Write access'),
							     '3' => _('Admin access'))) ;

		} elseif ($hookname == "role_get_setting") {
			$role = $params['role'] ;
			$reference = $params['reference'] ;
			$value = $params['value'] ;

			switch ($params['section']) {
			case 'plugin_moinmoin_access':
				if ($role->hasPermission('project_admin', $reference)) {
					$params['result'] = 3 ;
				} else {
					$params['result'] =  $value ;
				}
				break ;
			}
		} elseif ($hookname == 'clone_project_from_template') {
			$systasksq = new SystasksQ();
			$systasksq->add($this->getID(), 'MOINMOIN_CREATE_WIKI', $group_id);
		} elseif ($hookname == 'crossrefurl') {
			$project = group_get_object($group_id);
			if (!$project || !is_object($project) || $project->isError()) {
				return;
			}
			if ($project->usesPlugin($this->name) && isset($params['page'])) {
				$params['url'] = '/plugins/moinmoin/'.$project->getUnixName().'/'.$params['page'];
			}
			return;
		}
	}

	function groupisactivecheckboxpost(&$params) {
			if (!parent::groupisactivecheckboxpost($params)) {
				return false;
			}
			if (getIntFromRequest('use_moinmoin') == 1) {
				$systasksq = new SystasksQ();
				$group_id = $params['group'];
				$systasksq->add($this->getID(), 'MOINMOIN_CREATE_WIKI', $group_id);
			}
			return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
