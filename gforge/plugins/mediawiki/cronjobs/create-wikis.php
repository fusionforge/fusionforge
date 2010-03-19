#! /usr/bin/php5
<?php
  /* 
   * Copyright (C) 2010  Olaf Lenz
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
   * You should have received a copy of the GNU General Public License
   * along with FusionForge; if not, write to the Free Software
   * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   */

  /** This script will automatically create mediawiki instances for
   projects that do not yet have it.
   
   It is intended to be started in a cronjob.
   */

# TODO: How to use cronjob history?
# Required variables:
# $mediawiki_src_path: the directory where the mediawiki sources are installed
# $mediawiki_var_path: the directory where mediawiki can store its data (i.e. LocalSettings.php and images/)

require (dirname(__FILE__) . '/../../env.inc.php');
require_once ($gfwww . 'include/squal_pre.php');
require $gfcommon.'include/cron_utils.php';

$project_settings_filename = "ProjectSettings.php";
$upload_dir_basename = "images";

if (!isset($mediawiki_var_path))
	$mediawiki_var_path = "$sys_var_path/plugins/mediawiki";
if (!isset($mediawiki_src_path))
	$mediawiki_src_path = "/usr/share/mediawiki";
if (!isset($mediawiki_projects_path))
	$mediawiki_projects_path = "$mediawiki_var_path/projects";
if (!isset($mediawiki_master_path))
	$mediawiki_master_path = "$mediawiki_var_path/master";

# Find the project settings template
$project_settings_template = 
	"$sys_etc_path/plugins/mediawiki/$project_settings_filename";
if (!file_exists($project_settings_template))
	$project_settings_template =
		"$sys_opt_path/plugins/mediawiki/etc/plugins/mediawiki/$project_settings_filename";

# Owner of files - apache
$file_owner = $sys_apache_user.':'.$sys_apache_group;
if (empty($sys_apache_user) || empty($sys_apache_group)) {
	$err =  "Error: sys_apache_user Is Not Set Or sys_apache_group Is Not Set!";
	cron_debug($err);
	cron_entry(23,$err);
	exit;
}


# Get all projects that use the mediawiki plugin
$res = db_query ("SELECT g.unix_group_name from groups g, group_plugin gp, plugins p where g.group_id = gp.group_id and gp.plugin_id = p.plugin_id and p.plugin_name = 'mediawiki';");
if (!$res) {
	$err =  "Error: Database Query Failed: ".db_error();
	cron_debug($err);
	cron_entry(23,$err);
	exit;
}

# Loop over all projects that use the plugin
while ( $row = db_fetch_array($res) ) {
	$project = $row['unix_group_name'];
	$project_dir = "$mediawiki_projects_path/$project";
	cron_debug("Checking $project...");

	// Check whether the image (and project) directory exists
	$upload_dir = "$project_dir/$upload_dir_basename";
	if (!is_dir($upload_dir)) {
		cron_debug("  Creating upload dir $upload_dir.");
		system("mkdir -p $upload_dir");
	} else {
		cron_debug("  Upload dir $upload_dir exists.");
	}

	// Check whether the project settings file exists
	$project_settings = "$project_dir/$project_settings_filename";
	if (!file_exists($project_settings)) {
		cron_debug("  Copying $project_settings_template to $project_settings.");
		if (!copy($project_settings_template, $project_settings)) {
			$err = ("Error: Failed to copy $project_settings_template to $project_settings!");
			cron_debug($err);
			cron_entry(23,$err);
		}
		$create_db = true;
	} else {
		cron_debug("  File $project_settings exists.");
		$create_db = false;
	}

	// Create the DB
	if ($create_db) {
		$schema = "plugin_mediawiki_$project";
		// Sanitize schema name
		strtr($schema, "-", "_");

		cron_debug("  Creating schema $schema.");
		$res = db_query("CREATE SCHEMA $schema ;");
		if (!$res) {
			$err =  "Error: Schema Creation Failed: " . 
				db_error();
			cron_debug($err);
			cron_entry(23,$err);
			exit;
		}

		cron_debug("  Creating mediawiki database.");
		$table_file = "$mediawiki_src_path/maintenance/postgres/tables.sql";
		if (!file_exists($table_file)) {
			$err =  "Error: Couldn't find Mediawiki Database Creation File $mediawiki_creation_file!";
			cron_debug($err);
			cron_entry(23,$err);
			exit;
		}
			
		$creation_query = file_get_contents($table_file);
		$res = db_query("SET search_path = \"$schema\" ;" 
				. $creation_query
				. "CREATE TEXT SEARCH CONFIGURATION $schema.default ( COPY = pg_catalog.english );"
				. "COMMIT ;");
		if (!$res) {
			$err =  "Error: Mediawiki Database Creation Failed: " . 
				db_error();
			cron_debug($err);
			cron_entry(23,$err);
			exit;
		}
		

	} else {
		cron_debug("  Nothing to be done.");
	}
}


  // Local Variables:
  // mode: php
  // c-file-style: "bsd"
  // End:

?>