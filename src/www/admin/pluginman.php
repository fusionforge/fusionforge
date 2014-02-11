<?php
/**
 * FusionForge Plugin Activate / Deactivate Page
 *
 * Copyright 2005 GForge, LLC
 * Copyright 2010 FusionForge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011, Alain Peyrat - Alcatel-Lucent
 * Copyright (C) 2011, 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

session_require_global_perm('forge_admin');

// Skip non compatible plugins.
$plugins_disabled = array('scmccase');

// Skip non actionable plugins due to general configuration
if (!forge_get_config('use_scm')) {
	array_push($plugins_disabled, 'scmarch', 'scmbzr', 'scmcpold', 'scmcvs', 'scmdarcs', 'scmgit', 'scmhg', 'scmsvn');
}

$pm = plugin_manager_get_object();

if (getStringFromRequest('update')) {
	$pluginname = getStringFromRequest('update');

	if ((getStringFromRequest('action') == 'deactivate')) {

		$res = db_query_params('DELETE FROM user_plugin WHERE plugin_id = (SELECT plugin_id FROM plugins WHERE plugin_name = $1)',
			array($pluginname));
		if (!$res) {
			exit_error(db_error(), 'admin');
		} else {
			$feedback .= sprintf(ngettext('%d user detached from plugin.', '%d users detached from plugin.', db_affected_rows($res)), db_affected_rows($res));
		}

		$res = db_query_params('DELETE FROM group_plugin WHERE plugin_id = (SELECT plugin_id FROM plugins WHERE plugin_name = $1)',
			array($pluginname));
		if (!$res) {
			exit_error(db_error(),'admin');
		} else {
			$feedback .= sprintf(ngettext('%d project detached from plugin.', '%d projects detached from plugin.', db_affected_rows($res)), db_affected_rows($res));
		}

		$res = $pm->deactivate($pluginname);
		if (!$res) {
			exit_error(db_error(), 'admin');
		} else {
			$feedback = sprintf(_('Plugin %s updated Successfully'), $pluginname);

			// Load the plugin and now get information from it.
			$plugin = $pm->GetPluginObject($pluginname);
			if (!$plugin || $plugin->isError()) {
				exit_error(_("Could not get plugin object"), 'admin');
			}
			$installdir = $plugin->getInstallDir();

			// Remove the symbolic link made if plugin has a www.
			if (is_dir(forge_get_config('plugins_path') . '/' . $pluginname . '/www')) { // if the plugin has a www dir delete the link to it
				if (file_exists('../'.$installdir)) {
					$result = unlink('../'.$installdir);
					if (!$result) {
						$feedback .= '<br />'._("Soft link wasn't removed in www/plugins folder, please do so manually.");
					}
				} else {
					$result = 0;
				}
			}

			// Remove the symbolic link made if plugin has a config.
			if (file_exists(forge_get_config('config_path'). '/plugins/'.$pluginname)) {
				$result = unlink(forge_get_config('config_path'). '/plugins/'.$pluginname); // the apache group or user should have write perms in forge_get_config('config_path')/plugins folder...
				if (!$result) {
					$feedback .= _('Success, config not deleted');
				}
			}
		}
	} else {

		$res = $pm->activate($pluginname);
		if (!$res) {
			exit_error(db_error(), 'admin');
		} else {
			// Load the plugin and now get information from it.
			$pm = plugin_manager_get_object();
			$pm->LoadPlugin($pluginname);

			$plugin = $pm->GetPluginObject($pluginname);
			if (!$plugin || $plugin->isError()) {
				// we need to deactivate the plugin, something went wrong
				$pm->deactivate($pluginname);
				exit_error(_("Could not get plugin object"), 'admin');
			} else {
				if (method_exists($plugin, 'install')) {
					$plugin->install();
				}
			}
			if ($plugin->isError()) {
				$error_msg = $plugin->getErrorMessage();
			} else {
				$feedback = sprintf(_('Plugin %s updated Successfully'), $pluginname);
			}
		}
	}
}

site_admin_header(array('title'=>_('Plugin Manager')));

?>
<form name="theform" action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<?php
echo '<p>';
echo _('Here you can activate / deactivate site-wide plugins which are in the plugins/ folder. Then, you should activate them also per project, per user or whatever the plugin specifically applies to.');
echo '</p>';
echo '<p class="important">' . _('Be careful because some projects/users can be using the plugin. Deactivating it will remove the plugin from all users/projects.') . '</p>';

$title_arr = array( _('Plugin Name'),
		    _('Status'),
		    _('Action'),
		    _('Users Using it'),
		    _('Projects Using it'),
		    _('Global Administration View'));
echo $HTML->listTableTop($title_arr);

// Get the activated plugins.
$pm = plugin_manager_get_object();

// Simple hack to disable dependent plugins.
if (!$pm->PluginIsInstalled('scmcvs')) {
	$plugins_disabled[] = 'cvssyncmail';
	$plugins_disabled[] = 'cvstracker';
}

// [#411] Prevent admin from desactivating the last auth plugin.
$plugins = $pm->GetPlugins();
$auth_plugins = array();
foreach($plugins as $p) {
	if (preg_match('/^auth/', $p)) {
		$auth_plugins[] = $p;
	}
}
if (count($auth_plugins) == 1) {
	$plugin = $auth_plugins[0];
	$action[$plugin]['deactivate'] = false;
}

//get the directories from the plugins dir

$filelist = array();
if($handle = opendir(forge_get_config('plugins_path'))) {
	while (($filename = readdir($handle)) !== false) {
		if ($filename != '..' && $filename != '.' && $filename != ".svn" && $filename != "CVS" &&
			is_dir(forge_get_config('plugins_path').'/'.$filename) &&
			!in_array($filename, $plugins_disabled)) {
			$addPlugin = 1;
			if (forge_get_config('plugin_status', $filename) !== 'valid') {
				$addPlugin = 0;
			}
			$used = false;
			$res = db_query_params('SELECT u.user_name FROM plugins p, user_plugin up, users u WHERE p.plugin_name = $1 and up.user_id = u.user_id and p.plugin_id = up.plugin_id',
				array($filename));
			if ($res) {
				if (db_numrows($res)>0) {
					$used = true;
				}
			}
			$res1 = db_query_params('SELECT g.group_name FROM plugins p, group_plugin gp, groups g WHERE plugin_name = $1 and gp.group_id = g.group_id and p.plugin_id = gp.plugin_id',
				array($filename));
			if ($res1) {
				if (db_numrows($res1) > 0) {
					$used = true;
				}
			}
			if (forge_get_config('installation_environment') === 'development' || $used) {
				$addPlugin = 1;
			}
			if ($addPlugin) {
				$filelist[] = $filename;
			}
		}
	}
	closedir($handle);
}
sort($filelist);

$j = 0;

foreach ($filelist as $filename) {
	$pluginObject = $pm->GetPluginObject($filename);
	if ($pm->PluginIsInstalled($filename)) {
		$msg = _('Active');
		$status = "active";
		$next = 'deactivate';
		$link = util_make_link("/admin/pluginman.php?update=$filename&amp;action=deactivate", _('Deactivate'));

		$res = db_query_params ('SELECT u.user_name FROM plugins p, user_plugin up, users u WHERE p.plugin_name = $1 and up.user_id = u.user_id and p.plugin_id = up.plugin_id',
			array($filename));
		if ($res) {
			if (db_numrows($res)>0) {
				$users = " ";
				$nb_users = db_numrows($res);
				for($i=0;$i<$nb_users;$i++) {
					$users .= db_result($res,$i,0) . " | ";
				}
				$users = substr($users,0,strlen($users) - 3); //remove the last |
				// If there are too many users, replace the list with number of users
				if ($nb_users > 100) {
					$users = util_make_link("/admin/userlist.php?usingplugin=$filename", '<b>'.sprintf(_("%d users"), $nb_users).'</b>');
				}
			} else {
				$users = _('None');
			}
		}

		$res = db_query_params ('SELECT g.group_name FROM plugins p, group_plugin gp, groups g WHERE plugin_name = $1 and gp.group_id = g.group_id and p.plugin_id = gp.plugin_id',
			array($filename));
		if ($res) {
			if (db_numrows($res)>0) {
				$groups = " ";
				$nb_groups = db_numrows($res);
				for($i=0;$i<$nb_groups;$i++) {
					$groups .= db_result($res,$i,0) . " | ";
				}
				$groups = substr($groups,0,strlen($groups) - 3); //remove the last |
				// If there are too many projects, replace the list with number of projects
				if ($nb_groups > 100) {
					$groups = util_make_link("/admin/grouplist.php?usingplugin=$filename", '<b>'.sprintf(_("%d projects"), $nb_groups).'</b>');
				}
			} else {
				$groups = _('None');
			}
		}
		$adminlink = '';
		if (method_exists($pluginObject, 'getAdminOptionLink')) {
			$adminlink = $pluginObject->getAdminOptionLink();
		}
	} else {
		$msg = _('Inactive');
		$status = "inactive";
		$next = 'activate';
		$link = util_make_link("/admin/pluginman.php?update=$filename&amp;action=activate", _('Activate'));
		$users = _('None');
		$groups = _('None');
		$adminlink = '';
	}
	$description = '';
	if (method_exists($pluginObject, 'getPluginDescription')) {
		$description = $pluginObject->getPluginDescription();
	}
	// Disable link to action if action is not possible.
	if (isset($action[$filename][$next]) && $action[$filename][$next] === false) {
		$link = '';
	}

	// Disable link to action if action is not possible.
	if (isset($action[$filename][$next]) && $action[$filename][$next] === false) {
		$link = '';
	}

	$title = _('Current plugin status is'). ' ' .forge_get_config('plugin_status', $filename);
	echo '<tr '. $HTML->boxGetAltRowStyle($j+1) .'>'.
		'<td title="'. $description.' '.$title .'">'. $filename.'</td>'.
		'<td class="'.$status.'" class="align-center">'. $msg .'</td>'.
		'<td class="align-center">'. $link .'</td>'.
		'<td class="align-left">'. $users .'</td>'.
		'<td class="align-left">'. $groups .'</td>'.
		'<td class="align-left">'. $adminlink .'</td></tr>'."\n";
	$j++;
}

echo $HTML->listTableBottom();

?>

</form>

<?php

site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
