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

  /** This script will destroy a mediawiki instance of a specific project.     */
require (dirname(__FILE__) . '/../../env.inc.php');
require_once ($gfwww . 'include/squal_pre.php');

if (!isset($mediawiki_var_path))
	$mediawiki_var_path = "$sys_var_path/plugins/mediawiki";
if (!isset($mediawiki_projects_path))
	$mediawiki_projects_path = "$mediawiki_var_path/projects";

if ($argc < 2 ) {
	echo "Usage " . $argv[0] . " <project>\n";
	exit (0);
}

array_shift($argv);
foreach ($argv as $project) {
  echo "Removing project wiki of $project.\n";

  $project_dir = "$mediawiki_projects_path/$project";
  echo "  Deleting project subdir $project_dir.\n";
  if (!is_dir($project_dir)) {
    echo "$project_dir does not exist!\n";
  } else {
    system("rm -rf $project_dir");
  }

  $schema = "plugin_mediawiki_$project";
  strtr($schema, "-", "_");
  echo "  Dropping database schema $schema.\n";
  $res = db_query_params("DROP SCHEMA $schema CASCADE", array());
  if (!$res) {
    echo db_error();
  }
}

  // Local Variables:
  // mode: php
  // c-file-style: "bsd"
  // End:

?>

