<?php
/**
 * GForge SCM Frontend
 *
 * Copyright 2004 (c) Roland Mas, GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2004-05-19
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

require_once('../../env.inc.php');
require_once('pre.php');
require_once('www/scm/include/scm_utils.php');
require_once('common/scm/SCMFactory.class');

global $sys_use_scm;

$group_id = getIntFromRequest('group_id');

// Check permissions
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

scm_header(array('title'=>$Language->getText('scm_index','scm_repository'),'group'=>$group_id));

if (getStringFromRequest('submit')) {
	$hook_params = array ();
	$hook_params['group_id'] = $group_id;

	$scmradio = '';
	$scmvars = array_keys (_getRequestArray());
	foreach (_getRequestArray() as $key => $value) {
		foreach ($scm_list as $scm) {
			if ($key == strstr($key, $scm . "_")) {
				$hook_params[$key] = $value;
			} elseif ($key == 'scmradio') {
				$scmradio = $value;
			}
		}
	}

	$SCMFactory = new SCMFactory();
	$scm_plugins = $SCMFactory->getSCMs();

	if (in_array($scmradio, $scm_plugins)) {
		$group =& group_get_object($group_id);

		foreach ($scm_plugins as $plugin) {
			$myPlugin = plugin_get_object($plugin);
			if ($scmradio == $myPlugin->name) {
				$group->setPluginUse($myPlugin->name, 1);
			} else {
				$group->setPluginUse($myPlugin->name, 0);
			}
		}
	}

	plugin_hook ("scm_admin_update", $hook_params);
}

?>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>">
<?php

	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	$group =& group_get_object($group_id);

	$SCMFactory = new SCMFactory();
	$scm_plugins = $SCMFactory->getSCMs();
	if (count($scm_plugins) != 0) {	
		if (count($scm_plugins) > 1) {
			echo '<p>'.$Language->getText('scm_index','repos_change_note').'</p>';
			echo '<table><tbody><tr><td><strong>',$Language->getText('scm_index','scm_repository'),':</strong></td>';
			$checked=true;
			foreach ($scm_plugins as $plugin) {
				$myPlugin = plugin_get_object($plugin);
				echo '<td><input type="radio" name="scmradio" ';
				echo 'value="'.$myPlugin->name.'"';
				if ($group->usesPlugin($myPlugin->name)) {
					$scm = $myPlugin->name;
					echo ' checked="checked"';
				}
				echo '>'.$myPlugin->text.'</td>';
			}
			echo '</tr></tbody></table>'."\n";
		}
	} else {
		echo '<p>'.$Language->getText('scm_index','no_plugins_error').'</p>';
	}

	plugin_hook ("scm_admin_page", $hook_params) ;
?>
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<input type="submit" name="submit" value="<?php echo $Language->getText('general', 'update'); ?>">
</form>
<?php

scm_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
