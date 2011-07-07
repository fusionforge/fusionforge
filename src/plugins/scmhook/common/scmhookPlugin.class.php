<?php
/**
 * scmhookPlugin Class
 * Copyright 2011, Franck Villaume - Capgemini
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

class scmhookPlugin extends Plugin {
	function scmhookPlugin () {
		$this->Plugin() ;
		$this->name = "scmhook" ;
		$this->text = "Scmhook" ; // To show in the tabs, use...
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "scm_admin_page";
		$this->hooks[] = "scm_admin_update";
	}

	function CallHook($hookname, &$params) {
		switch ($hookname) {
			case "scm_admin_page": {
				$group_id = $params['group_id'];
				$group = &group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					$this->displayScmHook($group_id);
				}
				break;
			}
			case "scm_admin_update": {
				$this->update($params);
				break;
			}
		}
	}

	function exists($group_id) {
		$res = db_query_params('SELECT id_group FROM plugin_scmhook WHERE id_group = $1', array($group_id));
		if (!$res)
			return false;

		if (db_numrows($res))
			return true;

		return false;
	}

	function add($group_id) {
		if (!$this->exists($group_id)) {
			$res = db_query_params('INSERT INTO plugin_scmhook (id_group) VALUES ($1)', array($group_id));
			if (!$res)
				return false;

		}
		return true;
	}

	function remove($group_id) {
		if ($this->exists($group_id)) {
			$res = db_query_params('DELETE FROM plugin_scmhook where id_group = $1', array($group_id));
			if (!$res)
				return false;

		}
		return true;
	}

	function update($params) {
		$group_id = $params['group_id'];
		$hooksString = '';
		$updatedb = 0;
		foreach($params as $key => $value) {
			if ($key == strstr($key, 'scm')) {
				$updatedb = 1;
				$hookname = preg_replace('/scm[a-z][a-z][a-z]_/','',$key);
				$extensions = $this->getAllowedExtension();
				foreach($extensions as $extension) {
					$hookname = preg_replace('/_'.$extension.'$/', '.'.$extension, $hookname);
				}
				if (strlen($hooksString)) {
					$hooksString .= '|'.$hookname;
				} else {
					$hooksString .= $hookname;
				}
			}
		}
		if ($updatedb) {
			$res = db_query_params('UPDATE plugin_scmhook set hooks = $1, need_update = 1 where id_group = $2',
						array($hooksString, $group_id));

			if (!$res)
				return false;

		}
		return true;
	}

	function displayScmHook($group_id) {
		$hooksAvailable = $this->getAvailableHooks($group_id);
		$statusDeploy = $this->getStatusDeploy($group_id);
		if (count($hooksAvailable['pre-commit']) || count($hooksAvailable['post-commit'])) {
			$hooksEnabled = $this->getEnabledHooks($group_id);
			echo '<div id="scmhook">';
			if ($statusDeploy)
				echo '<p class="warning">'._('Hooks management update process waiting ...').'</p>';

			echo '<h3>'._('Enable Repository Hooks').'</h3>';
			if (count($hooksAvailable['pre-commit'])) {
				echo '<table>';
				echo '<thead><tr><th>'._('pre-commit Hooks').'</th></tr></thead>';
				echo '<tbody>';
				for ($i = 0; $i < count($hooksAvailable['pre-commit']); $i++) {
					$labelHook = preg_replace('/scm[a-z][a-z][a-z]_pre-commit_/','',$hooksAvailable['pre-commit'][$i]);
					echo '<tr><td>';
					echo '<input name="'.$hooksAvailable['pre-commit'][$i].'" type="checkbox"';

					if (in_array($labelHook, $hooksEnabled))
						echo ' checked="checked"';

					if ($statusDeploy)
						echo ' disabled="disabled"';

					echo '/>';
					echo '<label>'.$labelHook.'</label>';
					echo '</td></tr>';
				}
				echo '</tbody></table>';
			}
			if (count($hooksAvailable['post-commit'])) {
				echo '<table>';
				echo '<thead><tr><th>'._('post-commit Hooks').'</th></tr></thead>';
				echo '<tbody>';
				for ($i = 0; $i < count($hooksAvailable['post-commit']); $i++) {
					$labelHook = preg_replace('/scm[a-z][a-z][a-z]_post-commit_/','',$hooksAvailable['post-commit'][$i]);
					echo '<tr><td>';
					echo '<input name="'.$hooksAvailable['post-commit'][$i].'" type="checkbox"';

					if (in_array($labelHook, $hooksEnabled))
						echo ' checked="checked"';

					if ($statusDeploy)
						echo ' disabled="disabled"';

					echo '/>';
					echo '<label>'.$labelHook.'</label>';
					echo '</td></tr>';
				}
				echo '</tbody></table>';
			}
			echo '</div>';
		}
	}

	function getStatusDeploy($group_id) {
		$res = db_query_params('SELECT need_update FROM plugin_scmhook WHERE id_group = $1', array($group_id));
		if (!$res)
			return 1;

		$row = db_fetch_array($res);
		return $row['need_update'];
	}

	function getAvailableHooks($group_id) {
		$listScm = $this->getListLibraryScm();
		$group = &group_get_object($group_id);
		for ($i = 0; $i < count($listScm); $i++) {
			if ($group->usesPlugin($listScm[$i])) {
				return $this->getListLibraryHook($listScm[$i]);
			}
		}
		return array();
	}

	function getEnabledHooks($group_id) {
		$res = db_query_params('SELECT hooks FROM plugin_scmhook WHERE id_group = $1', array($group_id));
		if (!$res)
			return false;

		$row = db_fetch_array($res);
		if (count($row)) {
			return explode('|', $row['hooks']);
		}

		return array();
	}

	function getListLibraryScm() {
		return array_values(array_diff(scandir(dirname(__FILE__).'/../library/'), Array('.', '..', '.svn')));
	}

	function getListLibraryHook($scm) {
		$listHooksPrecommit = array_values(array_diff(scandir(dirname(__FILE__).'/../library/'.$scm.'/hooks/pre-commit'), Array('.', '..', '.svn')));
		$listHooksPostcommit = array_values(array_diff(scandir(dirname(__FILE__).'/../library/'.$scm.'/hooks/post-commit'), Array('.', '..', '.svn')));
		$listPrecommit = array_values(array_diff(scandir(dirname(__FILE__).'/../library/'.$scm.'/skel/pre-commit'), Array('.', '..', '.svn', 'pre-commit.head')));
		$listPostcommit = array_values(array_diff(scandir(dirname(__FILE__).'/../library/'.$scm.'/skel/post-commit'), Array('.', '..', '.svn', 'post-commit.head')));
		$validHooks = array();
		$validHooks['pre-commit'] = array();
		foreach($listHooksPrecommit as $hook) {
			if (in_array('pre-commit.'.$hook, $listPrecommit)) {
				$validHooks['pre-commit'][] = $scm.'_pre-commit_'.$hook;
			}
		}
		$validHooks['post-commit'] = array();
		foreach($listHooksPostcommit as $hook) {
			if (in_array('post-commit.'.$hook, $listPostcommit)) {
				$validHooks['post-commit'][] = $scm.'_post-commit_'.$hook;
			}
		}
		return $validHooks;
	}

	function getAllowedExtension() {
		return array("sh", "pl");
	}

	/**
	 * override default groupisactivecheckboxpost function for init value in db
	 */
	function groupisactivecheckboxpost(&$params) {
		// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
		$group = group_get_object($params['group']);
		$flag = strtolower('use_'.$this->name);
		if ( getIntFromRequest($flag) == 1 ) {
			$group->setPluginUse($this->name);
			$this->add($group->getID());
		} else {
			$group->setPluginUse($this->name, false);
			$this->remove($group->getID());
		}
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
