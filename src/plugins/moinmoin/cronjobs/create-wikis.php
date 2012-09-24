#! /usr/bin/php
<?php
/* 
 * Copyright 2010, Olaf Lenz
 * Copyright 2011, Roland Mas
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
 */

  /** This script will automatically create MoinMoin instances for
   projects that do not yet have them.
   
   It is intended to be started in a cronjob.
   */

require_once (dirname(__FILE__) . '/../../../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';

$wikidata = forge_get_config('wiki_data_path', 'moinmoin');

// Get all projects that use the mediawiki plugin
$project_res = db_query_params ("SELECT g.unix_group_name from groups g, group_plugin gp, plugins p where g.group_id = gp.group_id and gp.plugin_id = p.plugin_id and p.plugin_name = $1;", array("moinmoin"));
if (!$project_res) {
	$err =  "Error: Database Query Failed: ".db_error();
	cron_debug($err);
	cron_entry(23,$err);
	exit;
}

$need_reload = false;

// Loop over all projects that use the plugin
while ( $row = db_fetch_array($project_res) ) {
	$project = $row['unix_group_name'];
	$project_dir = "$wikidata/$project";
	cron_debug("Checking $project...");

	// Create the project directory if necessary
	if (!is_dir($project_dir)) {
		cron_debug("  Creating project dir $project_dir.");
		mkdir($project_dir, 0755, true);
		system("cp -r /usr/share/moin/data /usr/share/moin/underlay $project_dir/");
		system("chown -R gforge:gforge $project_dir");
		$template = forge_get_config('core', 'config_path') . "/plugins/moinmoin/PROJECT_NAME.py.tmpl";
		system('(echo "# Automatically generated on `date` from '.$template.'";'
                      . 'echo "# DO NOT EDIT";'
                      . "sed s/@PROJECT_NAME@/$project/ < $template) > $wikidata/$project.py");

		$need_reload = true;
	} 
}

if ($need_reload) {
	system("invoke-rc.d apache2 reload");
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
