<?php
/**
 * FusionForge extratabs plugin
 *
 * Copyright 2005, Raphaël Hertzog
 * Copyright 2009, Roland Mas
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2010, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

class ExtraTabsPlugin extends Plugin {
	function ExtraTabsPlugin () {
		$this->Plugin() ;
		$this->name = "extratabs" ;
		$this->text = "Extra tabs";
		$this->_addHook('groupisactivecheckbox'); // The "use ..." checkbox in editgroupinfo
		$this->_addHook('groupisactivecheckboxpost');
		$this->_addHook('project_admin_plugins');
		$this->_addHook('groupmenu'); // To put into the project tabs
		$this->_addHook('clone_project_from_template');
	}

	function CallHook($hookname, &$params) {
		global $HTML;

		switch ($hookname) {
			case "project_admin_plugins": {
				$group_id = $params['group_id'];
				$project = group_get_object($group_id);
				if ($project->usesPlugin($this->name)) {
					echo '<p>'.util_make_link('/plugins/extratabs/index.php?group_id='.$group_id,
					     _('Extra Tabs Admin')) . '</p>';
				}
				break;
			}

			case "groupmenu": {
				$group_id = $params['group'];
				$project = group_get_object($group_id);
				if (!$project || !is_object($project))
					return;
				if ($project->isError())
					return;
				if (!$project->isProject())
					return;
				$res_tabs = db_query_params('SELECT tab_name, tab_url, type FROM plugin_extratabs_main WHERE group_id=$1 ORDER BY index',
							    array($group_id));
				while ($row_tab = db_fetch_array($res_tabs)) {
					$params['TITLES'][] = $row_tab['tab_name'];
					switch ($row_tab['type']) {
					case 0: // Link
						$params['DIRS'][] = $row_tab['tab_url'];
						break;
						
					case 1: // Iframe
						$params['DIRS'][] = '/plugins/'.$this->name.'/iframe.php?group_id='.$group_id.'&amp;tab_name='.$row_tab['tab_name'];
						if (isset($params['toptab']) && ($params['toptab'] == $row_tab['tab_name'])) {
							$params['selected'] = count($params['TITLES']) - 1;
						}
						break;
						
					default:
						return;
					}
				}
				break;
			}
			case "clone_project_from_template": {
				$tabs = array () ;
				$res = db_query_params ('SELECT tab_name, tab_url, index FROM plugin_extratabs_main WHERE group_id=$1 ORDER BY index',
							array ($params['template']->getID())) ;
				while ($row = db_fetch_array($res)) {
					$data = array () ;
					$data['tab_url'] = $params['project']->replaceTemplateStrings ($row['tab_url']) ;
					$data['tab_name'] = $params['project']->replaceTemplateStrings ($row['tab_name']) ;
					$data['index'] = $row['index'] ;
					$tabs[] = $data ;
				}			 
				
				foreach ($tabs as $tab) {
					db_query_params ('INSERT INTO plugin_extratabs_main (tab_url, tab_name, index, group_id) VALUES ($1,$2,$3,$4)',
							 array ($data['tab_url'],
								$data['tab_name'],
								$data['index'],
								$params['project']->getID())) ;
				}
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
