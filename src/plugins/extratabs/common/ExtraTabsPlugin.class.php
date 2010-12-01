<?php
/**
 * FusionForge extratabs plugin
 *
 * Copyright 2005, Raphaël Hertzog
 * Copyright 2009, Roland Mas
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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
		$this->hooks[] = "project_admin_plugins" ;
		$this->hooks[] = "groupmenu" ;  // To put into the project tabs
	}

	function CallHook ($hookname, &$params) {
		global $HTML;
		
		if ($hookname == "project_admin_plugins") {
			$group_id=$params['group_id'];
			echo '<p>'.util_make_link ('/plugins/extratabs/index.php?group_id='.$group_id,
					     _('Extra Tabs Admin')) . '</p>';	       
		} elseif ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$project = group_get_object($group_id);
			if (!$project || !is_object($project))
				return;
			if ($project->isError())
				return;
			if (!$project->isProject())
				return;
			$res_tabs = db_query_params ('SELECT tab_name, tab_url FROM plugin_extratabs_main WHERE group_id=$1 ORDER BY index',
						     array ($group_id)) ;
			while ($row_tab = db_fetch_array($res_tabs)) {
				$params['DIRS'][] = $row_tab['tab_url'];
				$params['TITLES'][] = $row_tab['tab_name'];
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
