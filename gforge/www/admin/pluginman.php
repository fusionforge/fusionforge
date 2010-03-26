<?php
/**
 * GForge Plugin Activate / Deactivate Page
 *
 * @version 
 * @author 
 * @copyright 
 * Copyright 2005 GForge, LLC
 * http://gforge.org/
 *
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

// Skip non compatible plugins.
$plugins_disabled = array('helloworld', 'webcalendar', 'scmccase', 'scmcpold');

site_admin_header(array('title'=>_('Site admin')));

?>

<script type="text/javascript">

	function change(url,plugin)
	{
		field = document.theform.elements[plugin];
		if (field.checked) {
			window.location=(url + "&init=yes");
		} else {
			window.location=(url);
		}
	}

</script>

<form name="theform" action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<?php

$pm = plugin_manager_get_object();

if (getStringFromRequest('update')) {
	$pluginname = getStringFromRequest('update');
	
	if ((getStringFromRequest('action')=='deactivate')) {
		if (getStringFromRequest('delusers')) {

			$res = db_query_params ('DELETE FROM user_plugin WHERE plugin_id = (SELECT plugin_id FROM plugins WHERE plugin_name = $1)',
			array($pluginname));
			if (!$res) {
				exit_error("SQL ERROR",db_error());
			} else {
				$feedback .= sprintf(ngettext('%d user detached from plugin.', '%d users detached from plugin.', db_affected_rows($res)), db_affected_rows($res));
			}
		}
		if (getStringFromRequest('delgroups')) {

			$res = db_query_params ('DELETE FROM group_plugin WHERE plugin_id = (SELECT plugin_id FROM plugins WHERE plugin_name = $1)',
			array($pluginname));
			if (!$res) {
				exit_error("SQL ERROR",db_error());
			} else {
				$feedback .= sprintf(ngettext('%d project detached from plugin.', '%d projects detached from plugin.', db_affected_rows($res)), db_affected_rows($res));
			}
		}
		$res = $pm->deactivate($pluginname);
		if (!$res) {
			exit_error("SQL ERROR",db_error());
		} else {
			$feedback = sprintf(_('Plugin %1$s updated Successfully'), $pluginname);
			
			// Load the plugin and now get information from it.
			$plugin = $pm->GetPluginObject($pluginname);
			$installdir = $plugin->getInstallDir();
			
			// Remove the symbolic links made if plugin has a www.
			if (is_dir($sys_plugins_path . '/' . $pluginname . '/www')) { // if the plugin has a www dir delete the link to it
				if (file_exists('../'.$installdir)) {
					$result = unlink('../'.$installdir);
					if (!$result) {
						$feedback .= _('<br />Soft link wasn\'t removed in www/plugins folder, please do so manually.');
					}
				} else {
					$result = 0;
				}
				if (file_exists($sys_etc_path. '/plugins/'.$pluginname)) {
					$result = unlink($sys_etc_path. '/plugins/'.$pluginname); // the apache group or user should have write perms in $sys_etc_path/plugins folder...
					if (!$result) {
						$feedback .= _('Success, config not deleted');
					}			
				}
			}			
		}
	} else {

		$res = $pm->activate($pluginname);
		if (!$res) {
			exit_error("SQL ERROR",db_error());
		} else {
			$feedback = sprintf(_('Plugin %1$s updated Successfully'), $pluginname);

			// Load the plugin and now get information from it.
			$pm = plugin_manager_get_object();
			$pm->LoadPlugin($pluginname);
			$plugin = $pm->GetPluginObject($pluginname);
			$installdir = $plugin->getInstallDir();

			// Create a symbolic links to plugins/<plugin>/www (if directory exists).
			if (is_dir($sys_plugins_path . '/' . $pluginname . '/www')) { // if the plugin has a www dir make a link to it
				// The apache group or user should have write perms the www/plugins folder...
				if (!is_link('../'.$installdir)) {
					$code = symlink($sys_plugins_path . '/' . $pluginname . '/www', '../'.$installdir); 
					if (!$code) {
						$feedback .= '<br />['.'../'.$installdir.'->'.$sys_plugins_path . '/' . $pluginname . '/www]';
						$feedback .= _('<br />Soft link to www couldn\'t be created. Check the write permissions for apache in gforge www/plugins dir or create the link manually.');
					}
				}
			}
				
			// Create a symbolic links to plugins/<plugin>/etc/plugins/<plugin> (if directory exists).
			if (is_dir($sys_plugins_path . '/' . $pluginname . '/etc/plugins/' . $pluginname)) {
				// The apache group or user should have write perms in /etc/gforge/plugins folder...
				$code = symlink($sys_plugins_path . '/' . $pluginname . '/etc/plugins/' . $pluginname, $sys_etc_path. '/plugins/'.$pluginname); 
				if (!$code) {
					$feedback .= '<br />['.$sys_etc_path. '/plugins/'.$pluginname.'->'.$sys_plugins_path . '/' . $pluginname . '/etc/plugins/' . $pluginname . ']';
					$feedback .= sprintf(_('<br />Config file could not be linked to etc/gforge/plugins/%1$s. Check the write permissions for apache in /etc/gforge/plugins or create the link manually.'), $pluginname);
				}
			}

			if (getStringFromRequest('init')) {
				// now we're going to check if there's a XX-init.sql file and run it
				$db_init = $sys_plugins_path . '/' . $pluginname . '/db/' . $pluginname . '-init-'. $sys_database_type .'.sql';
				if (!is_file($db_init)) {
					$db_init = $sys_plugins_path . '/' . $pluginname . '/db/' . $pluginname . '-init.sql';
					if (!is_file($db_init)) {
						$db_init = 0;
					}
				}
					
				if ($db_init) {
					$res = db_query_from_file($db_init);
					
					if ($res) {
						while ($res) {
							db_free_result($res);
							$res = db_next_result();
						}
					} else {
						$feedback .= _('Initialisation error<br />Database said: ').db_error();
					}
				}	
				//we check for a php script	
				if (is_file($sys_plugins_path . '/' . $pluginname . '/script/' . $pluginname . '-init.php')) {
					include($sys_plugins_path . '/' . $pluginname . '/script/' . $pluginname . '-init.php');		
				} else {
					
				}
			}
		}
	}
}

if ($feedback) echo '<div class="feedback">' . $feedback . '</div>';
echo _('Here you can activate / deactivate site-wide plugins which are in the plugins/ folder. Then, you should activate them also per project, per user or whatever the plugin specifically applies to.<br /><span class="important">Be careful because some groups/users can be using the plugin. Deactivating it will remove the plugin from all users/groups.<br />Be <strong>extra</strong> careful not to run the init-script again when the plugin is reactivated, because some scripts have DROP TABLE statements.</span><br /><br />');
$title_arr = array( _('Plugin Name'),
		    _('Status'),
		    _('Action'),
		    _('Run Init Script?'),
		    _('Users Using it'),
		    _('Groups Using it'),);
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

$handle = opendir($sys_plugins_path);
$filelist = array();
while (($filename = readdir($handle)) !== false) {
	if ($filename!='..' && $filename!='.' && $filename!=".svn" && $filename!="CVS" &&
		is_dir($sys_plugins_path.'/'.$filename) &&
		!in_array($filename, $plugins_disabled)) {

		$filelist[] = $filename;
	}
}
closedir($handle);

sort($filelist);

$j = 0;

foreach ($filelist as $filename) {
	if ($pm->PluginIsInstalled($filename)) {
		$msg = _('Active');
		$status="active";
		$link = "<a href=\"javascript:change('" . getStringFromServer('PHP_SELF') . "?update=$filename&amp;action=deactivate";

		$res = db_query_params ('SELECT  u.user_name FROM plugins p, user_plugin up, users u WHERE p.plugin_name = $1 and up.user_id = u.user_id and p.plugin_id = up.plugin_id',
			array($filename));
		if ($res) {
			if (db_numrows($res)>0) {
				// tell the form to delete the users, so that we don't re-do the query
				$link .= "&amp;delusers=1";
				$users = " ";
				for($i=0;$i<db_numrows($res);$i++) {
					$users .= db_result($res,$i,0) . " | ";
				}
				$users = substr($users,0,strlen($users) - 3); //remove the last |
			} else {
				$users = "none";
			}
		}

		$res = db_query_params ('SELECT g.group_name FROM plugins p, group_plugin gp, groups g WHERE plugin_name = $1 and gp.group_id = g.group_id and p.plugin_id = gp.plugin_id',
			array($filename));
		if ($res) {
			if (db_numrows($res)>0) {
				// tell the form to delete the groups, so that we don't re-do the query
				$link .= "&amp;delgroups=1";
				$groups = " ";
				for($i=0;$i<db_numrows($res);$i++) {
					$groups .= db_result($res,$i,0) . " | ";
				}
				$groups = substr($groups,0,strlen($groups) - 3); //remove the last |
			} else {
				$groups = "none";
			}
		}
		$link .= "','$j');" . '">' . _('Deactivate') . "</a>";
		$init = '<input id="n'.$j.'" type="checkbox" disabled name="script[]" value="'.$filename.'" />';
	} else {
		$msg = _('Inactive');
		$status = "inactive";
		$link = "<a href=\"javascript:change('" . getStringFromServer('PHP_SELF') . "?update=$filename&amp;action=activate','n$j');" . '">' . _('Activate') . "</a>";
		$init = '<input id="n'.$j.'" type="checkbox" name="script[]" value="'.$filename.'" />';
		$users = "none";
		$groups = "none";
	}

	echo '<tr '. $HTML->boxGetAltRowStyle($j+1) .'>'.
		'<td>'. $filename.'</td>'.
		'<td class="'.$status.'" style="text-align:center">'. $msg .'</td>'.
		'<td><div align="center">'. $link .'</div></td>'.
		'<td><div align="center">'. $init .'</div></td>'.
		'<td><div align="left">'. $users .'</div></td>'.
		'<td><div align="left">'. $groups .'</div></td></tr>'."\n";

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
