<?php
/**
 * ProjectLabelsPlugin Class
 *
 * Copyright 2016, Franck Villaume - TrivialDev
 *
 * This file is part of Fusionforge.
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class ProjectLabelsPlugin extends Plugin {
	function __construct() {
		parent::__construct();
		$this->name = "projectlabels";
		$this->text = _("Project labels");
		$this->pkg_desc =
_("This can be used to highlight some projects on a forge, for instance
for a “project of the month”.");
		$this->hooks[] = "project_before_widgets" ;
		$this->hooks[] = "site_admin_option_hook" ;
	}

	function CallHook($hookname, &$params) {
		if ($hookname == "site_admin_option_hook") {
			echo html_e('li', array(), util_make_link('/plugins/'.$this->name.'/index.php', _('Project labels plugin')));
		} elseif ($hookname == "project_before_widgets") {
			$group_id=$params['group_id'];
			$project = group_get_object($group_id);
			if (!$project || !is_object($project))
				return;
			if ($project->isError())
				return;
			$res_tabs = db_query_params ('SELECT plugin_projectlabels_labels.label_text FROM plugin_projectlabels_labels, plugin_projectlabels_group_labels
					      WHERE plugin_projectlabels_group_labels.group_id=$1 AND plugin_projectlabels_group_labels.label_id = plugin_projectlabels_labels.label_id',
						     array ($group_id));
			while ($row_tab = db_fetch_array($res_tabs)) {
				print ($row_tab['label_text']);
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
