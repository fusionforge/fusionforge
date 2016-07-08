<?php

class ProjectLabelsPlugin extends Plugin {
	function __construct() {
		parent::__construct();
		$this->name = "projectlabels";
		$this->text = _("Project labels");
		$this->pkg_desc =
_("This can be used to highlight some projects on a forge, for instance
for a \"project of the month\".");
		$this->hooks[] = "project_before_widgets" ;
		$this->hooks[] = "site_admin_option_hook" ;
	}

	function CallHook ($hookname, &$params) {
		global $HTML;

		if ($hookname == "site_admin_option_hook") {
			echo '<li>' . util_make_link ('/plugins/projectlabels/index.php',
						      _('Project labels'). ' [' . _('Project labels plugin') . ']') . '</li>';
		} elseif ($hookname == "project_before_widgets") {
			$group_id=$params['group_id'];
			$project = group_get_object($group_id);
			if (!$project || !is_object($project))
				return;
			if ($project->isError())
				return;
			if (!$project->isProject())
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
