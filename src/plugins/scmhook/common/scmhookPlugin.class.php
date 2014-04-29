<?php
/**
 * scmhookPlugin Class
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2014, Franck Villaume - TrivialDev
 * Copyright 2012, Benoit Debaenst - TrivialDev
 * Copyright 2014, Sylvain Beucler - Inria
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
	function __construct() {
		$this->Plugin();
		$this->name = 'scmhook';
		$this->text = 'Scmhook'; // To show in the tabs, use...
		$this->_addHook('groupmenu');	// To put into the project tabs
		$this->_addHook('groupisactivecheckbox'); // The "use ..." checkbox in editgroupinfo
		$this->_addHook('groupisactivecheckboxpost'); //
		$this->_addHook('scm_admin_page');
		$this->_addHook('scm_admin_update');
		$this->_addHook('artifact_extra_detail');
		$this->_addHook('task_extra_detail');
	}

	function CallHook($hookname, &$params) {
		switch ($hookname) {
			case 'scm_admin_page': {
				$group_id = $params['group_id'];
				$scm_plugin = $params['scm_plugin'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name) && $scm_plugin) {
					$this->displayScmHook($group_id, $scm_plugin);
				}
				break;
			}
			case 'scm_admin_update': {
				$this->update($params);
				break;
			}
			case 'artifact_extra_detail': {
				$group_id = $params['group_id'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					$this->artifact_extra_detail($params);
				}
				break;
			}
			case 'task_extra_detail': {
				$group_id = $params['group_id'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					$this->task_extra_detail($params);
				}
				break;
			}
		}
		return true;
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
		$hooks = $this->getAvailableHooks($group_id);
		foreach($params as $key => $value) {
			if ($key == strstr($key, 'scm')) {
				$hookname = preg_replace('/scm[a-z][a-z]+_/','',$key);
				if ($key != $hookname) {	//handle the case of scm_enable_anonymous
					if (strlen($hooksString)) {
						$hooksString .= '|'.$hookname;
					} else {
						$hooksString .= $hookname;
					}
				}
			}
		}
		$res = db_query_params('UPDATE plugin_scmhook set hooks = $1, need_update = 1 where id_group = $2',
					array($hooksString, $group_id));

		// Save parameters
		foreach($hooks as $hook) {
			$hook_params = $hook->getParams();
			if (count($hook_params) == 0)
				continue;
			// Build 3 arrays for inconvenient db_query_params()
			$i = 1;
			$sql_cols = array_keys($hook_params);
			$sql_vals = array();
			$sql_vars = array();
			foreach($hook_params as $pname => $pconf) {
				$val = $params["scmsvn_{$hook->getClassname()}_$pname"];
				// Validation
				switch($pconf['type']) {
				case 'emails':
					$emails = array_map('trim', explode(',', $val));
					$strict = true;
					$invalid = array_search(false, array_map('validate_email', $emails), $strict) !== false;
					if ($invalid)
						exit_error($hook->getName() . _(": ") . _("invalid e-mails"). ' ' . $val);
					$val = implode(',', $emails);
				}
				$sql_vals[] = $val;
				$sql_vars[] = '$'.$i;
				$i++;
			}
			$sql_cols[] = 'group_id';
			$sql_vals[] = $group_id;
			$sql_vars[] = '$'.$i;
			$table = 'plugin_scmhook_scmsvn_'.strtolower($hook->getClassname());
			db_query_params('BEGIN', array());
			db_query_params('DELETE FROM '.$table.' WHERE group_id=$1', array($group_id));
			db_query_params('INSERT INTO '.$table.' (' . implode(',', $sql_cols)
					. ') VALUES (' . implode(',', $sql_vars) . ')',
					$sql_vals);
			db_query_params('COMMIT', array());
		}

		if (!$res)
			return false;

		return true;
	}

	function displayScmHook($group_id, $scm) {
		global $HTML;
		use_javascript('/js/sortable.js');
		echo $HTML->getJavascripts();
		$hooksAvailable = $this->getAvailableHooks($group_id);
		$statusDeploy = $this->getStatusDeploy($group_id);
		$hooksEnabled = $this->getEnabledHooks($group_id);
		if (count($hooksAvailable)) {
			echo '<div id="scmhook">';
			if ($statusDeploy)
				echo $HTML->warning_msg(_('Hooks management update process waiting ...'));

			echo '<h2>'._('Enable Repository Hooks').'</h2>';
			switch ($scm) {
				case "scmsvn": {
					$this->displayScmSvnHook($hooksAvailable, $statusDeploy, $hooksEnabled, $group_id);
					break;
				}
				case "scmhg": {
					$this->displayScmHgHook($hooksAvailable, $statusDeploy, $hooksEnabled);
					break;
				}
				case "scmgit": {
					$this->displayScmGitHook($hooksAvailable, $statusDeploy, $hooksEnabled);
					break;
				}
				default: {
					echo $HTML->warning_msg(_('SCM Type not supported yet by scmhook'));
					break;
				}
			}
			echo '</div><p />';
		} else {
			echo $HTML->information(_('No hooks available'));
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
		$group = group_get_object($group_id);
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
		$listHooks = array_values(array_diff(scandir(dirname(__FILE__).'/../library/'.$scm), array('.', '..', '.svn', 'skel', 'hooks', 'depends', 'cronjobs')));
		$validHooks = array();
		foreach ($listHooks as $hook) {
			if (!stristr($hook,'~')) {
				include_once dirname(__FILE__).'/../library/'.$scm.'/'.$hook;
				$hookClassName = preg_replace('/.class.php/','', $hook);
				$hookObject = new $hookClassName;
				$validHooks[] = $hookObject;
			}
		}
		return $validHooks;
	}

	function artifact_extra_detail($params) {
		$hooksAvailable = $this->getAvailableHooks($params['group_id']);
		$hooksEnabled = $this->getEnabledHooks($params['group_id']);
		foreach ($hooksAvailable as $hookAvailable) {
			if (in_array($hookAvailable->getClassname(), $hooksEnabled)) {
				if (method_exists($hookAvailable,'artifact_extra_detail')) {
					$hookAvailable->artifact_extra_detail($params);
				}
			}
		}
	}

	function task_extra_detail($params) {
		$hooksAvailable = $this->getAvailableHooks($params['group_id']);
		$hooksEnabled = $this->getEnabledHooks($params['group_id']);
		foreach ($hooksAvailable as $hookAvailable) {
			if (in_array($hookAvailable->getClassname(), $hooksEnabled)) {
				if (method_exists($hookAvailable,'task_extra_detail')) {
					$hookAvailable->task_extra_detail($params);
				}
			}
		}
	}

	/**
	 * override default groupisactivecheckboxpost function for init value in db
	 */
	function groupisactivecheckboxpost(&$params) {
		// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
		$group = group_get_object($params['group']);
		$flag = strtolower('use_'.$this->name);
		if (getIntFromRequest($flag) == 1) {
			$group->setPluginUse($this->name);
			$this->add($group->getID());
		} else {
			$group->setPluginUse($this->name, false);
			$this->remove($group->getID());
		}
		return true;
	}


	function displayScmSvnHook($hooksAvailable, $statusDeploy, $hooksEnabled, $group_id) {
		global $HTML;
		// Group available hooks by type
		$hooks_by_type = array();
		foreach ($hooksAvailable as $hook)
			$hooks_by_type[$hook->getHookType()][] = $hook;
		// Display available hooks, in specific order
		foreach (array('pre-commit', 'pre-revprop-change', 'post-commit') as $hooktype) {
			$hooks = $hooks_by_type[$hooktype];
			if (count($hooks)) {
				echo html_e('h3', array(), sprintf(_('%s Hooks'), $hooktype), false);
				$tabletop = array('', _('Hook'), _('Description'));
				$classth = array('unsortable', '', '');
				echo $HTML->listTableTop($tabletop, false, "sortable_scmhook_$hooktype", 'sortable', $classth);
				foreach ($hooks as $hook) {
					$isdisabled = 0;
					if (! empty($hook->onlyGlobalAdmin) && ! Permission::isGlobalAdmin()) {
						echo '<tr class="hide" ><td>';
					}
					else {
						echo '<tr><td>';
					}
					echo '<input type="checkbox" ';
					echo 'name="'.$hook->getLabel().'_'.$hook->getClassname().'" ';
					if (in_array($hook->getClassname(), $hooksEnabled))
						echo ' checked="checked"';

					if ($statusDeploy) {
						$isdisabled = 1;
						echo ' disabled="disabled"';
					}
					if (!$isdisabled && !$hook->isAvailable())
						echo ' disabled="disabled"';

					echo ' />';
					if (in_array($hook->getClassname(), $hooksEnabled) && $statusDeploy) {
						echo '<input type="hidden" ';
						echo 'name="'.$hook->getLabel().'_'.$hook->getClassname().'" ';
						echo 'value="on" />';
					}
					echo '</td><td';
					if (!$hook->isAvailable())
						echo ' title="'.$hook->getDisabledMessage().'"';

					echo ' >';
					echo $hook->getName();
					echo '</td><td>';
					echo $hook->getDescription();
					echo '</td></tr>';
					$table = 'plugin_scmhook_scmsvn_'.strtolower($hook->getClassname());
					if (db_check_table_exists($table)) {
						$res = db_query_params('SELECT * FROM '.$table.' WHERE group_id=$1', array($group_id));
						$values = db_fetch_array($res);
						foreach ($hook->getParams() as $pname => $pconf) {
							echo "<tr><td></td><td>{$pconf['description']}</td><td>";
							$val = ($values[$pname] != null) ? $values[$pname] : $pconf['default'];
							switch($pconf['type']) {
							case 'emails':
								print "<input type='text' name='scmsvn_{$hook->getClassname()}_$pname' value='$val' size=40/>";
								break;
							}
							echo '</td></tr>';
						}
					}
				}
				echo $HTML->listTableBottom();
			}
		}
	}

	function displayScmHgHook($hooksAvailable, $statusDeploy, $hooksEnabled) {
		global $HTML;
		$hooksServePushPullBundle = array();
		foreach ($hooksAvailable as $hook) {
			switch ($hook->getHookType()) {
				case "serve-push-pull-bundle": {
					$hooksServePushPullBundle[] = $hook;
					break;
				}
				default: {
					//byebye hook.... we do not know you...
					break;
				}
			}
		}
		if (count($hooksServePushPullBundle)) {
			echo html_e('h3', array(), _('serve-push-pull-bundle Hooks'), false);
			$tabletop = array('', _('Hook Name'), _('Description'));
			$classth = array('unsortable', '', '');
			echo $HTML->listTableTop($tabletop, false, 'sortable_scmhook_serve-push-pull-bundle', 'sortable', $classth);
			foreach ($hooksServePushPullBundle as $hookServePushPullBundle) {
				$isdisabled = 0;
				if (! empty($hookServePushPullBundle->onlyGlobalAdmin) && ! Permission::isGlobalAdmin()) {
					echo '<tr class="hide" ><td>';
				}
				else {
					echo '<tr><td>';
				}
				echo '<input type="checkbox" ';
				echo 'name="'.$hookServePushPullBundle->getLabel().'_'.$hookServePushPullBundle->getClassname().'" ';
				if (in_array($hookServePushPullBundle->getClassname(), $hooksEnabled))
					echo ' checked="checked"';

				if ($statusDeploy) {
					$isdisabled = 1;
					echo ' disabled="disabled"';
				}

				if (!$isdisabled && !$hookServePushPullBundle->isAvailable())
					echo ' disabled="disabled"';

				echo ' />';
				if (in_array($hookServePushPullBundle->getClassname(), $hooksEnabled) && $statusDeploy) {
					echo '<input type="hidden" ';
					echo 'name="'.$hookServePushPullBundle->getLabel().'_'.$hookServePushPullBundle->getClassname().'" ';
					echo 'value="on" />';
				}

				echo '</td><td';
				if (!$hookServePushPullBundle->isAvailable())
					echo ' title="'.$hookServePushPullBundle->getDisabledMessage().'"';

				echo ' >';
				echo $hookServePushPullBundle->getName();
				echo '</td><td>';
				echo $hookServePushPullBundle->getDescription();
				echo '</td></tr>';
			}
			echo $HTML->listTableBottom();
		}
	}
	function displayScmGitHook($hooksAvailable, $statusDeploy, $hooksEnabled) {
		global $HTML;
		$hooksPostReceive = array();
		foreach ($hooksAvailable as $hook) {
			switch ($hook->getHookType()) {
				case "post-receive": {
					$hooksPostReceive[] = $hook;
					break;
				}
				default: {
					//byebye hook.... we do not know you...
					break;
				}
			}
		}
		if (count($hooksPostReceive)) {
			echo html_e('h3', array(), _('post-receive Hooks'), false);
			$tabletop = array('', _('Hook Name'), _('Description'));
			$classth = array('unsortable', '', '');
			echo $HTML->listTableTop($tabletop, false, 'sortable_scmhook_post-receive', 'sortable', $classth);
			foreach ($hooksPostReceive as $hookPostReceive) {
				$isdisabled = 0;
				if (! empty($hookPostReceive->onlyGlobalAdmin) && ! Permission::isGlobalAdmin()) {
					echo '<tr class="hide" ><td>';
				}
				else {
					echo '<tr><td>';
				}
				echo '<input type="checkbox" ';
				echo 'name="'.$hookPostReceive->getLabel().'_'.$hookPostReceive->getClassname().'" ';
				if (in_array($hookPostReceive->getClassname(), $hooksEnabled))
					echo ' checked="checked"';

				if ($statusDeploy) {
					$isdisabled = 1;
					echo ' disabled="disabled"';
				}

				if (!$isdisabled && !$hookPostReceive->isAvailable())
					echo ' disabled="disabled"';

				echo ' />';
				if (in_array($hookPostReceive->getClassname(), $hooksEnabled) && $statusDeploy) {
					echo '<input type="hidden" ';
					echo 'name="'.$hookPostReceive->getLabel().'_'.$hookPostReceive->getClassname().'" ';
					echo 'value="on" />';
				}

				echo '</td><td';
				if (!$hookPostReceive->isAvailable())
					echo ' title="'.$hookPostReceive->getDisabledMessage().'"';

				echo ' >';
				echo $hookPostReceive->getName();
				echo '</td><td>';
				echo $hookPostReceive->getDescription();
				echo '</td></tr>';
			}
			echo $HTML->listTableBottom();
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
