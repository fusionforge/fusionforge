<?php
/**
 * scmhookPlugin Class
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012, Benoit Debaenst - TrivialDev
 * Copyright 2012-2014,2017-2018, Franck Villaume - TrivialDev
 * Copyright 2014, Sylvain Beucler - Inria
 * Copyright 2014, Philipp Keidel - EDAG Engineering AG
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

require_once $gfcommon.'include/SysTasksQ.class.php';

class scmhookPlugin extends Plugin {
	public $systask_types = array(
		'SCMHOOK_UPDATE' => 'updateScmRepo.php',
	);

	function __construct() {
		parent::__construct();
		$this->name = 'scmhook';
		$this->text = _('Scmhook'); // To show in the tabs, use...
		$this->pkg_desc =
_("This plugin contains a set of commit hooks (e-mail notifications,
tracker integration, conformity...) that can be enabled for each
project independently.");
		$this->_addHook('groupmenu');	// To put into the project tabs
		if (forge_get_config('use_scm')) {
			$this->_addHook('groupisactivecheckbox'); // The "use ..." checkbox in editgroupinfo
		}
		$this->_addHook('groupisactivecheckboxpost'); //
		$this->_addHook('artifact_extra_detail');
		$this->_addHook('task_extra_detail');
	}

	function CallHook($hookname, &$params) {
		switch ($hookname) {
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
		if (!$res) {
			return false;
		}
		if (db_numrows($res)) {
			return true;
		}
		return false;
	}

	function remove($group_id) {
		if ($this->exists($group_id)) {
			$res = db_query_params('DELETE FROM plugin_scmhook where id_group = $1', array($group_id));
			if (!$res) {
				return false;
			}
		}
		return true;
	}

	function update($params) {
		$group_id = $params['group_id'];
		$repository_name = $params['repository_name'];
		$scm_plugin = $params['scm_plugin'];

		$group = group_get_object($group_id);
		if (!$group->usesPlugin($this->name)) {
			return;
		}

		$hooks = $this->getAvailableHooks($group_id);
		$available_hooknames = array();
		foreach ($hooks as $hook) {
			if ($hook->getLabel() == $scm_plugin) {
				$available_hooknames[] = $hook->getLabel().'_'.$hook->getClassname();
			}
		}

		$enabled_hooknames = array();
		if (isset($params['hooks'])) {
			foreach($params['hooks'] as $value) {
				if (in_array($value, $available_hooknames) !== FALSE) {
					$enabled_hooknames[] = preg_replace('/scm[a-z][a-z]+_/','', $value);
				}
			}
		}

		$existingHooksEnabled = $this->getEnabledHooks($group_id);
		if (isset($existingHooksEnabled[$scm_plugin][$repository_name])) {
			$res = db_query_params('UPDATE plugin_scmhook SET hooks = $1, need_update = 1 WHERE id_group = $2 AND repository_name = $3 AND scm_plugin = $4',
					array(implode('|', $enabled_hooknames), $group_id, $repository_name, $scm_plugin));
		} else {
			$res = db_query_params('INSERT INTO plugin_scmhook (hooks, need_update, id_group, repository_name, scm_plugin) VALUES ($1, $2, $3, $4, $5)',
					array(implode('|', $enabled_hooknames), 1, $group_id, $repository_name, $scm_plugin));
		}

		// Save parameters
		foreach($hooks as $hook) {
			$table = 'plugin_scmhook_'.$hook->getLabel().'_'.strtolower($hook->getClassname());
			if (db_check_table_exists($table)) {
				$hook_params = $hook->getParams();
				if (empty($hook_params)) {
					continue;
				}
				if (array_search($hook->getClassname(), $enabled_hooknames) === false) {
					continue;
				}
				// Build 3 arrays for inconvenient db_query_params()
				$i = 1;
				$sql_cols = array_keys($hook_params);
				$sql_vals = array();
				$sql_vars = array();
				foreach($hook_params as $pconf) {
					$vals = $params['hooks']['options'];
					foreach ($vals as $val) {
						// Validation
						switch($pconf['type']) {
							case 'emails':
								if (isset($val['dest'])) {
									$emails = array_map('trim', explode(',', $val['dest']));
									$strict = true;
									$invalid = array_search(false, array_map('validate_email', $emails), $strict) !== false;
									if ($invalid) {
										exit_error($hook->getName() . _(": ") . _("invalid e-mails"). ' ' . $val['dest']);
									}
									$val = implode(',', $emails);
								}
						}
						$sql_vals[] = $val;
						$sql_vars[] = '$'.$i;
						$i++;
					}
				}
				$sql_cols[] = 'group_id';
				$sql_vals[] = $group_id;
				$sql_vars[] = '$'.$i;
				$sql_cols[] = 'repository_name';
				$sql_vals[] = $repository_name;
				$sql_vars[] = '$'.++$i;
				db_begin();
				db_query_params('DELETE FROM '.$table.' WHERE group_id=$1 AND repository_name = $2', array($group_id, $repository_name));
				db_query_params('INSERT INTO '.$table.' (' . implode(',', $sql_cols)
						. ') VALUES (' . implode(',', $sql_vars) . ')',
						$sql_vals);
				db_commit();
			}
		}

		if (!$res) {
			return false;
		}
		$systasksq = new SysTasksQ();
		$systasksq->add($this->getID(), 'SCMHOOK_UPDATE', $group_id, user_getid());

		return true;
	}

	function displayScmHook($group_id, $scm) {
		global $HTML;
		html_use_tablesorter();
		echo $HTML->getJavascripts();
		$hooksAvailable = $this->getAvailableHooks($group_id);
		$hooksEnabled = $this->getEnabledHooks($group_id);
		if (count($hooksAvailable)) {
			echo $HTML->openForm(array('id' => 'scmhook_form', 'action' => '/scm/admin/?group_id='.$group_id, 'method' => 'post'));
			echo $HTML->html_input('scm_plugin', '', '', 'hidden', $scm);
			echo '<div id="scmhook">';
			echo html_e('h2', array(), _('Enable Repository Hooks'));
			switch ($scm) {
				case "scmsvn": {
					$this->displayScmSvnHook($hooksAvailable, $hooksEnabled, $group_id);
					break;
				}
				case "scmhg": {
					$this->displayScmHgHook($hooksAvailable, $hooksEnabled, $group_id);
					break;
				}
				case "scmgit": {
					$this->displayScmGitHook($hooksAvailable, $hooksEnabled, $group_id);
					break;
				}
				case "scmcvs": {
					$this->displayScmCVSHook($hooksAvailable, $hooksEnabled, $group_id);
					break;
				}
				default: {
					echo $HTML->warning_msg(_('SCM Type not supported yet by scmhook'));
					break;
				}
			}
			echo '</div>'."\n";
			echo $HTML->html_input('scmhook_submit', '', '', 'submit', _('Submit'));
			echo $HTML->closeForm();
		} else {
			echo $HTML->information(_('No hooks available'));
		}
	}

	function getStatusDeploy($group_id) {
		$res = db_query_params('SELECT need_update FROM plugin_scmhook WHERE id_group = $1', array($group_id));
		if (!$res) {
			return 1;
		}
		$row = db_fetch_array($res);
		return $row['need_update'];
	}

	function getAvailableHooks($group_id) {
		$available_hooks = array();
		$listScm = $this->getListLibraryScm();
		$group = group_get_object($group_id);
		for ($i = 0; $i < count($listScm); $i++) {
			if ($group->usesPlugin($listScm[$i])) {
				$available_hooks = array_merge($available_hooks, $this->getListLibraryHook($listScm[$i]));
			}
		}
		return $available_hooks;
	}

	function getEnabledHooks($group_id) {
		$enabledHooks = array();
		$res = db_query_params('SELECT hooks, repository_name, scm_plugin FROM plugin_scmhook WHERE id_group = $1', array($group_id));
		if (!$res) {
			return $enabledHooks;
		}
		while ($arr = db_fetch_array($res)) {
			$enabledHooks[$arr['scm_plugin']][$arr['repository_name']] = explode('|', $arr['hooks']);
		}
		return $enabledHooks;
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
				$hookClassName = preg_replace('/^scm/','', $scm).preg_replace('/.class.php/','', $hook);
				$hookObject = new $hookClassName;
				$validHooks[] = $hookObject;
			}
		}
		return $validHooks;
	}

	function artifact_extra_detail(&$params) {
		$hooksAvailable = $this->getAvailableHooks($params['group_id']);
		$hooksEnabled = $this->getEnabledHooks($params['group_id']);
		foreach ($hooksEnabled as $repos) {
			foreach ($repos as $repo) {
				foreach ($hooksAvailable as $hookAvailable) {
					if (in_array($hookAvailable->getClassname(), $repo)) {
						if (method_exists($hookAvailable,'artifact_extra_detail')) {
							$hookAvailable->artifact_extra_detail($params);
						}
					}
				}
			}
		}
	}

	function task_extra_detail($params) {
		$hooksAvailable = $this->getAvailableHooks($params['group_id']);
		$hooksEnabled = $this->getEnabledHooks($params['group_id']);
		foreach ($hooksEnabled as $repos) {
			foreach ($repos as $repo) {
				foreach ($hooksAvailable as $hookAvailable) {
					if (in_array($hookAvailable->getClassname(), $repo)) {
						if (method_exists($hookAvailable,'task_extra_detail')) {
							$hookAvailable->task_extra_detail($params);
						}
					}
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
		} else {
			$group->setPluginUse($this->name, false);
			$this->remove($group->getID());
		}
		return true;
	}

	function displayScmSvnHook($hooksAvailable, $hooksEnabled, $group_id) {
		global $HTML;
		$scm_plugin = plugin_get_object('scmsvn');
		$groupObject = group_get_object($group_id);
		$repositories = $scm_plugin->getRepositories($groupObject);
		// Group available hooks by type
		$hooks_by_type = array();
		$hookCount = 0;
		foreach ($hooksAvailable as $hook) {
			if ($hook->label == 'scmsvn') {
				$hooks_by_type[$hook->getHookType()][] = $hook;
				$hookCount++;
			}
		}
		if (count($hookCount)) {
			$tabletop = array(_('Repository'));
			$classth = array('');
			$titleArr = array('');

			foreach (array('pre-commit', 'pre-revprop-change', 'post-commit') as $hooktype) {
				$hooks = $hooks_by_type[$hooktype];
				foreach ($hooks as $hook) {
					$tabletop[] = $hook->getName();
					$classth[] = 'unsortable';
					$titleArr[] = $hook->getDescription().' ('.$hooktype.')';
				}
			}

			echo $HTML->listTableTop($tabletop, '', 'sortable_scmhook_scmsvn', 'sortable', $classth, $titleArr);
			foreach($repositories as $repository) {
				$cells = array();
				$cells[][] = $repository.html_e('input', array('type' => 'hidden', 'name' => 'repository['.$repository.'][]'));
				foreach (array('pre-commit', 'pre-revprop-change', 'post-commit') as $hooktype) {
					$hooks = $hooks_by_type[$hooktype];
					foreach ($hooks as $hook) {
						$attr = array('type' => 'checkbox', 'name' => 'repository['.$repository.'][]', 'value' => $hook->getLabel().'_'.$hook->getClassname());
						if ((!empty($hook->onlyGlobalAdmin) && !Permission::isSuperUser()) || !$hook->isAvailable()) {
							$attr = array_merge($attr, array('disabled' => 'disabled'));
							if (!$hook->isAvailable()) {
								$attr = array_merge($attr, array('title' => $hook->getDisabledMessage()));
							}
						}
						if (isset($hooksEnabled['scmsvn'][$repository]) && in_array($hook->getClassname(), $hooksEnabled['scmsvn'][$repository])) {
							$attr = array_merge($attr, array('checked' => 'checked'));
						}
						$content = '';
						$table = 'plugin_scmhook_scmsvn_'.strtolower($hook->getClassname());
						if (db_check_table_exists($table)) {
							$res = db_query_params('SELECT * FROM '.$table.' WHERE group_id = $1 and repository_name = $2', array($group_id, $repository));
							$values = db_fetch_array($res);
							foreach ($hook->getParams() as $pname => $pconf) {
								$val = ($values[$pname] != null) ? $values[$pname] : $pconf['default'];
								switch($pconf['type']) {
								case 'emails':
									$content = html_e('input', array('type' => 'text','title' => $pconf['description'], 'name' => $hook->getLabel().'_'.$hook->getClassname().'['.$repository.']['.$pname.']', 'value' => $val, 'size' => 40));
									break;
								}
							}
						}
						$cells[][] = html_e('input', $attr).$content;
					}
				}
				echo $HTML->multiTableRow(array(), $cells);
			}
			echo $HTML->listTableBottom();
		}
	}

	function displayScmHgHook($hooksAvailable, $hooksEnabled, $group_id) {
		global $HTML;
		$scm_plugin = plugin_get_object('scmhg');
		$groupObject = group_get_object($group_id);
		$repositories = $scm_plugin->getRepositories($groupObject);
		$hooksServePushPullBundle = array();
		foreach ($hooksAvailable as $hook) {
			if ($hook->label == 'scmhg') {
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
		}
		if (count($hooksServePushPullBundle)) {
			$tabletop = array(_('Repository'));
			$classth = array('');
			$titleArr = array('');
			foreach ($hooksServePushPullBundle as $hookServePushPullBundle) {
				$tabletop[] = $hookServePushPullBundle->getName();
				$classth[] = 'unsortable';
				$titleArr[] = $hookServePushPullBundle->getDescription();
			}

			echo $HTML->listTableTop($tabletop, '', 'sortable_scmhook_scmhg', 'sortable', $classth, $titleArr);
			foreach($repositories as $repository) {
				$cells = array();
				$cells[][] = $repository.html_e('input', array('type' => 'hidden', 'name' => 'repository['.$repository.'][]'));
				foreach ($hooksServePushPullBundle as $hookServePushPullBundle) {
					$attr = array('type' => 'checkbox', 'name' => 'repository['.$repository.'][]', 'value' => $hookServePushPullBundle->getLabel().'_'.$hookServePushPullBundle->getClassname());
					if ((!empty($hookServePushPullBundle->onlyGlobalAdmin) && !Permission::isSuperUser()) || !$hookServePushPullBundle->isAvailable()) {
						$attr = array_merge($attr, array('disabled' => 'disabled'));
						if (!$hookServePushPullBundle->isAvailable()) {
							$attr = array_merge($attr, array('title' => $hookServePushPullBundle->getDisabledMessage()));
						}
					}
					if (isset($hooksEnabled['scmhg'][$repository]) && in_array($hookServePushPullBundle->getClassname(), $hooksEnabled['scmhg'][$repository])) {
						$attr = array_merge($attr, array('checked' => 'checked'));
					}
					$content = '';
					$table = 'plugin_scmhook_scmhg_'.strtolower($hookServePushPullBundle->getClassname());
					if (db_check_table_exists($table)) {
						$res = db_query_params('SELECT * FROM '.$table.' WHERE group_id = $1 and repository_name = $2', array($group_id, $repository));
						$values = db_fetch_array($res);
						foreach ($hookServePushPullBundle->getParams() as $pname => $pconf) {
							$val = ($values[$pname] != null) ? $values[$pname] : $pconf['default'];
							switch($pconf['type']) {
							case 'emails':
								$content = html_e('input', array('type' => 'text','title' => $pconf['description'], 'name' => $hookServePushPullBundle->getLabel().'_'.$hookServePushPullBundle->getClassname().'['.$repository.']['.$pname.']', 'value' => $val, 'size' => 40));
								break;
							}
						}
					}
					$cells[][] = html_e('input', $attr).$content;
				}
				echo $HTML->multiTableRow(array(), $cells);
			}
			echo $HTML->listTableBottom();
		}
	}

	function displayScmGitHook($hooksAvailable, $hooksEnabled, $group_id) {
		global $HTML;
		$scm_plugin = plugin_get_object('scmgit');
		$groupObject = group_get_object($group_id);
		$repositories = $scm_plugin->getRepositories($groupObject);
		$hooksPostReceive = array();
		foreach ($hooksAvailable as $hook) {
			if ($hook->label == 'scmgit') {
				switch ($hook->getHookType()) {
					case 'post-receive': {
						$hooksPostReceive[] = $hook;
						break;
					}
					default: {
						//byebye hook.... we do not know you...
						break;
					}
				}
			}
		}
		if (count($hooksPostReceive)) {
			$tabletop = array(_('Repository'));
			$classth = array('');
			$titleArr = array('');
			foreach ($hooksPostReceive as $hookPostReceive) {
				$tabletop[] = $hookPostReceive->getName();
				$classth[] = 'unsortable';
				$titleArr[] = $hookPostReceive->getDescription();
			}

			echo $HTML->listTableTop($tabletop, '', 'sortable_scmhook_scmgit', 'sortable', $classth, $titleArr);
			foreach($repositories as $repository) {
				$cells = array();
				$cells[][] = $repository.html_e('input', array('type' => 'hidden', 'name' => 'repository['.$repository.'][]'));
				foreach ($hooksPostReceive as $hookPostReceive) {
					$attr = array('type' => 'checkbox', 'name' => 'repository['.$repository.'][]', 'value' => $hookPostReceive->getLabel().'_'.$hookPostReceive->getClassname());
					if ((!empty($hookPostReceive->onlyGlobalAdmin) && !Permission::isSuperUser()) || !$hookPostReceive->isAvailable()) {
						$attr = array_merge($attr, array('disabled' => 'disabled'));
						if (!$hookPostReceive->isAvailable()) {
							$attr = array_merge($attr, array('title' => $hookPostReceive->getDisabledMessage()));
						}
					}
					if (isset($hooksEnabled['scmgit'][$repository]) && in_array($hookPostReceive->getClassname(), $hooksEnabled['scmgit'][$repository])) {
						$attr = array_merge($attr, array('checked' => 'checked'));
					}
					$content = '';
					$table = 'plugin_scmhook_scmgit_'.strtolower($hookPostReceive->getClassname());
					if (db_check_table_exists($table)) {
						$res = db_query_params('SELECT * FROM '.$table.' WHERE group_id = $1 and repository_name = $2', array($group_id, $repository));
						$values = db_fetch_array($res);
						foreach ($hookPostReceive->getParams() as $pname => $pconf) {
							$val = ($values[$pname] != null) ? $values[$pname] : $pconf['default'];
							switch($pconf['type']) {
							case 'emails':
								$content = html_e('input', array('type' => 'text','title' => $pconf['description'], 'name' => $hookPostReceive->getLabel().'_'.$hookPostReceive->getClassname().'['.$repository.']['.$pname.']', 'value' => $val, 'size' => 40));
								break;
							}
						}
					}
					$cells[][] = html_e('input', $attr).$content;
				}
				echo $HTML->multiTableRow(array(), $cells);
			}
			echo $HTML->listTableBottom();
		}
	}

	function displayScmCVSHook($hooksAvailable, $hooksEnabled, $group_id) {
		global $HTML;
		$scm_plugin = plugin_get_object('scmcvs');
		$groupObject = group_get_object($group_id);
		$repositories = $scm_plugin->getRepositories($groupObject);
		$hooksPostCommit = array();
		foreach ($hooksAvailable as $hook) {
			if ($hook->label == 'scmcvs') {
				switch ($hook->getHookType()) {
					case "post-commit": {
						$hooksPostCommit[] = $hook;
						break;
					}
					default: {
						//byebye hook.... we do not know you...
						break;
					}
				}
			}
		}
		if (count($hooksPostCommit)) {
			$tabletop = array(_('Repository'));
			$classth = array('');
			$titleArr = array('');
			foreach ($hooksPostCommit as $hookPostCommit) {
				$tabletop[] = $hookPostCommit->getName();
				$classth[] = 'unsortable';
				$titleArr[] = $hookPostCommit->getDescription();
			}

			echo $HTML->listTableTop($tabletop, '', 'sortable_scmhook_scmcvs', 'sortable', $classth, $titleArr);
			foreach($repositories as $repository) {
				$cells = array();
				$cells[][] = $repository.html_e('input', array('type' => 'hidden', 'name' => 'repository['.$repository.'][]'));
				foreach ($hooksPostCommit as $hookPostCommit) {
					$attr = array('type' => 'checkbox', 'name' => 'repository['.$repository.'][]', 'value' => $hookPostCommit->getLabel().'_'.$hookPostCommit->getClassname());
					if ((!empty($hookPostCommit->onlyGlobalAdmin) && !Permission::isSuperUser()) || !$hookPostCommit->isAvailable()) {
						$attr = array_merge($attr, array('disabled' => 'disabled'));
						if (!$hookPostCommit->isAvailable()) {
							$attr = array_merge($attr, array('title' => $hookPostCommit->getDisabledMessage()));
						}
					}
					if (isset($hooksEnabled['scmcvs'][$repository]) && in_array($hookPostCommit->getName(), $hooksEnabled['scmcvs'][$repository])) {
						$attr = array_merge($attr, array('checked' => 'checked'));
					}
					$cells[][] = html_e('input', $attr);
				}
				echo $HTML->multiTableRow(array(), $cells);
			}
			echo $HTML->listTableBottom();
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
