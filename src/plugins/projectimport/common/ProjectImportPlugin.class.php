<?php
/**
 * ProjectImportPlugin Class
 * Copyright 2014, Franck Villaume - TrivialDev
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

forge_define_config_item('storage_base','projectimport','$core/data_path/plugins/projectimport/');
forge_define_config_item('libmagic_db','projectimport','/usr/share/misc/magic.mgc');

class ProjectImportPlugin extends Plugin {
	function __construct() {
		parent::__construct();
		$this->name = "projectimport" ;
		$this->text = "Project import" ; // To show in the tabs, use...
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
		// The plugin has a link added to the Project administration part of site admin
		$this->hooks[] = "site_admin_project_maintenance_hook";
		$this->hooks[] = "site_admin_user_maintenance_hook";
	}

	function CallHook($hookname, &$params) {
		global $use_projectimportplugin,$G_SESSION,$HTML;
		if ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$project = group_get_object($group_id);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if ( $project->usesPlugin ( $this->name ) ) {
				$params['TITLES'][]=$this->text;
				$params['ADMIN'][] = NULL;
				$params['TOOLTIPS'][] = NULL;
				$params['DIRS'][]=util_make_url ('/plugins/'.$this->name.'/?type=group&group_id=' . $group_id . "&pluginname=" . $this->name) ; // we indicate the part we're calling is the project one
			} else {
				$params['TITLES'][]=$this->text." is [Off]";
				$params['ADMIN'][] = NULL;
				$params['TOOLTIPS'][] = NULL;
				$params['DIRS'][]='';
			}
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		}
	}

	/**
	 * Displays the link in the Project Maintenance part of the Site Admin ('site_admin_project_maintenance_hook' plugin_hook_by_reference() -style hook)
	 * @param array $params for concatenating return value in ['results']
	 */
	function site_admin_project_maintenance_hook (&$params) {
		$html = $params['result'];
		$html .= '<li>'.
			util_make_link ('/plugins/'.$this->name.'/projectsimport.php',
						     _("Import projects"). ' [' . _('Project import plugin') . ']') .'</li>';
		$params['result'] = $html;
	}

	/**
	 * Displays the link in the User Maintenance part of the Site Admin ('site_admin_user_maintenance_hook' plugin_hook_by_reference() -style hook)
	 * @param array $params for concatenating return value in ['results']
	 */
	function site_admin_user_maintenance_hook (&$params) {
		$html = $params['result'];
		$html .= '<li>'.
			util_make_link ('/plugins/'.$this->name.'/usersimport.php',
						     _("Import users"). ' [' . _('Project import plugin') . ']') .'</li>';
		$params['result'] = $html;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
