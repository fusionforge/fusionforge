<?php
/**
 * FusionForge Plugin Activate / Deactivate Page
 *
 * Copyright 2005 GForge, LLC
 * Copyright 2010 FusionForge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * http://fusionforge.org/
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

// Skip non compatible plugins.
$plugins_disabled = array('webcalendar', 'scmccase');

// Skip non activable plugins due to general configuration
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
			$feedback = sprintf(_('Plugin %1$s updated Successfully'), $pluginname);
			
			// Load the plugin and now get information from it.
			$plugin = $pm->GetPluginObject($pluginname);
			if (!$plugin || $plugin->isError()) {
				exit_error(_("Couldn't get plugin object"), 'admin');
			}
			$installdir = $plugin->getInstallDir();
			
			// Remove the symbolic links made if plugin has a www.
			if (is_dir(forge_get_config('plugins_path') . '/' . $pluginname . '/www')) { // if the plugin has a www dir delete the link to it
				if (file_exists('../'.$installdir)) {
					$result = unlink('../'.$installdir);
					if (!$result) {
						$feedback .= _('<br />Soft link wasn\'t removed in www/plugins folder, please do so manually.');
					}
				} else {
					$result = 0;
				}
				if (file_exists(forge_get_config('config_path'). '/plugins/'.$pluginname)) {
					$result = unlink(forge_get_config('config_path'). '/plugins/'.$pluginname); // the apache group or user should have write perms in forge_get_config('config_path')/plugins folder...
					if (!$result) {
						$feedback .= _('Success, config not deleted');
					}
				}
			}
		}
	} else {

		$res = $pm->activate($pluginname);
		if (!$res) {
			exit_error(db_error(), 'admin');
		} else {
			$feedback = sprintf(_('Plugin %1$s updated Successfully'), $pluginname);

			// Load the plugin and now get information from it.
			$pm = plugin_manager_get_object();
			$pm->LoadPlugin($pluginname);

			$plugin = $pm->GetPluginObject($pluginname);
			$plugin->installCode();
			$plugin->installConfig();
			$plugin->installDatabase();
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
		    _('Projects Using it'),);
echo $HTML->listTableTop($title_arr);

// Get the activated plugins.
$pm = plugin_manager_get_object();

// Simple hack to disable dependent plugins.
if (!$pm->PluginIsInstalled('scmsvn')) {
	$plugins_disabled[] = 'svncommitemail';
	$plugins_disabled[] = 'svntracker';
}
if (!$pm->PluginIsInstalled('scmcvs')) {
	$plugins_disabled[] = 'cvssyncmail';
	$plugins_disabled[] = 'cvstracker';
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
	if ($pm->PluginIsInstalled($filename)) {
		$msg = _('Active');
		$status = "active";
		$link = util_make_link("/admin/pluginman.php?update=$filename&amp;action=deactivate", _('Deactivate'));

		$res = db_query_params ('SELECT u.user_name FROM plugins p, user_plugin up, users u WHERE p.plugin_name = $1 and up.user_id = u.user_id and p.plugin_id = up.plugin_id',
			array($filename));
		if ($res) {
			if (db_numrows($res)>0) {
				$users = " ";
				for($i=0;$i<db_numrows($res);$i++) {
					$users .= db_result($res,$i,0) . " | ";
				}
				$users = substr($users,0,strlen($users) - 3); //remove the last |
			} else {
				$users = _('None');
			}
		}

		$res = db_query_params ('SELECT g.group_name FROM plugins p, group_plugin gp, groups g WHERE plugin_name = $1 and gp.group_id = g.group_id and p.plugin_id = gp.plugin_id',
			array($filename));
		if ($res) {
			if (db_numrows($res)>0) {
				$groups = " ";
				for($i=0;$i<db_numrows($res);$i++) {
					$groups .= db_result($res,$i,0) . " | ";
				}
				$groups = substr($groups,0,strlen($groups) - 3); //remove the last |
			} else {
				$groups = _('None');
			}
		}
	} else {
		$msg = _('Inactive');
		$status = "inactive";
		$link = util_make_link("/admin/pluginman.php?update=$filename&amp;action=activate", _('Activate'));
		$users = _('None');
		$groups = _('None');
	}

	$title = _('Current plugin status:'). ' ' .forge_get_config('plugin_status', $filename);
	echo '<tr '. $HTML->boxGetAltRowStyle($j+1) .'>'.
		'<td title="'. $title .'" >'. $filename.'</td>'.
		'<td class="'.$status.'" style="text-align:center">'. $msg .'</td>'.
		'<td style="text-align:center;">'. $link .'</td>'.
		'<td style="text-align:left;">'. $users .'</td>'.
		'<td style="text-align:left;">'. $groups .'</td></tr>'."\n";

	$j++;
}

echo $HTML->listTableBottom();

?>

</form>

<?php

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
