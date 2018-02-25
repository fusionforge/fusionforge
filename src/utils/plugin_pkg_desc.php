<?php
/**
 * Plugin stanza for use in .deb or .rpm packages
 *
 * Copyright (C) 2014  Inria (Sylvain Beucler)
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

// Don't try to connect to the DB, just reading plugin metadata
putenv('FUSIONFORGE_NO_DB=true');

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';

require_once dirname(__FILE__).'/../common/include/PluginManager.class.php';
require_once dirname(__FILE__).'/../common/include/Plugin.class.php';
require_once dirname(__FILE__).'/../common/include/SCMPlugin.class.php';

if (count($argv) != 3) {
  file_put_contents('php://stderr', "Usage: {$argv[0]} plugin_name {deb|rpm}\n");
  exit(1);
}
$pluginname = $argv[1];
$method = $argv[2];

forge_reset_config_item('plugins_path', 'core', dirname(__FILE__).'/../plugins');
$pm = plugin_manager_get_object();
$pm->LoadPlugin($pluginname);
$plugin = plugin_get_object($pluginname);
if ($plugin == null) {
  file_put_contents('php://stderr', "Couldn't load plugin $pluginname.\n");
  exit(1);
}

// Don't actually translate, just mark for transaltion (packaging is in English)
setlocale(LC_ALL, 'C');

$shortdesc_prefix = _("FusionForge plugin");
$desc_prefix =
_("FusionForge provides many tools to aid collaboration in a
development project, such as bug-tracking, task management,
mailing-lists, SCM repository, forums, support request helper,
web/FTP hosting, release management, etc. All these services are
integrated into one web site and managed through a web interface.");


if ($method == 'deb') {

  $desc_prefix = implode("\n ", preg_split('/\R/', $desc_prefix));
  $desc = implode("\n ", preg_split('/\R/', $plugin->pkg_desc));
  echo "Description: {$shortdesc_prefix} - {$plugin->text}\n";
  echo " $desc_prefix\n";
  echo " .\n";
  echo " $desc\n";

} elseif ($method == 'rpm') {

  $shortdesc_prefix = ucfirst($shortdesc_prefix);  // rpmlint summary-not-capitalized
  echo "Summary: {$shortdesc_prefix} - {$plugin->text}\n";
  echo "%description plugin-{$plugin->name}\n";
  echo "$desc_prefix\n";
  echo "\n";
  echo "{$plugin->pkg_desc}\n";

}
