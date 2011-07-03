<?php
/**
 * projects_hierarchyPlugin Class
 *
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
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

class projects_hierarchyPlugin extends Plugin {
	function projects_hierarchyPlugin() {
		$this->Plugin();
		$this->name = 'projects_hierarchy';
		$this->text = _('Project Hierarchy'); // To show in the tabs, use...
		$this->_addHook('groupisactivecheckbox'); // The "use ..." checkbox in editgroupinfo
		$this->_addHook('groupisactivecheckboxpost');
		$this->_addHook('hierarchy_views'); // include specific views
		$this->_addHook('display_hierarchy'); // to see the tree of projects
		$this->_addHook('group_delete'); // clean tables on delete
		$this->_addHook('project_admin_plugins'); // to show up in the admin page fro group
		$this->_addHook('site_admin_option_hook');
	}

	function CallHook($hookname, &$params) {
		global $G_SESSION, $HTML;
		$returned = false;
		switch($hookname) {
			case "display_hierarchy": {
				if ($this->displayHierarchy()) {
					$returned = true;
				}
				break;
			}
			case "hierarchy_views": {
				global $gfplugins;
				require_once $gfplugins.$this->name.'/include/hierarchy_utils.php';
				$group_id = $params[0];
				$project = group_get_object($group_id);
				if ($project->usesPlugin($this->name)) {
					switch ($params[1]) {
						case "admin":
						case "docman":
						case "home": {
							include($gfplugins.$this->name.'/view/'.$params[1].'_project_link.php');
							$returned = true;
							break;
						}
						default: {
							break;
						}
					}
				}
				break;
			}
			case "group_delete": {
				if ($params['group']->usesPlugins($this->name)) {
					if ($this->remove($params['group_id'])) {
						$returned = true;
					}
				} else {
					$returned = true;
				}
				break;
			}
			case "project_admin_plugins": {
				// this displays the link in the project admin options page to it's  MantisBT administration
				$group_id = $params['group_id'];
				$group = group_get_object($group_id);
				if ($group->usesPlugin($this->name)) {
					echo '<p>';
					echo util_make_link('/plugins/'.$this->name.'/?group_id='.$group_id.'&type=admin&pluginname='.$this->name, _('Hierarchy Admin'));
					echo '</p>';
				}
				$returned = true;
				break;
			}
			case "site_admin_option_hook": {
				echo '<li>'.$this->getAdminOptionLink().'</li>';
				$returned = true;
				break;
			}
		}
		return $returned;
	}

	function displayHierarchy() {
		$tree = $this->buildTree();
		$displayTree = $this->affTree($tree, 0);
		$this->dTreeJS();
		echo $displayTree;
		return true;
	}

	function dTreeJS() {
		echo '<link rel="StyleSheet" href="/plugins/projects_hierarchy/dtree.css" type="text/css" />
			<script type="text/javascript" src="/plugins/projects_hierarchy/dtree.js"></script>';
	}

	function buildTree() {
		global $project_name;
		$res = db_query_params('select p1.group_id as father_id,p1.unix_group_name as father_unix_name,p1.group_name as father_name,p2.group_id as son_id,p2.unix_group_name as son_unix_name,p2.group_name as son_name
					from groups as p1,groups as p2,plugin_projects_hierarchy_relationship
					where p1.group_id=plugin_projects_hierarchy_relationship.project_id
					and p2.group_id=plugin_projects_hierarchy_relationship.sub_project_id
					and plugin_projects_hierarchy_relationship.status=$1
					order by father_name, son_name',
					array ('t'));
		echo db_error();
		// construction du tableau associatif
		// key = name of the father
		// value = list of sons
		$tree = array();
		while ($row = db_fetch_array($res)) {
			//$tree[$row['father_name']][] = $row['son_name'];
			$tree[$row['father_id']][] = $row['son_id'];
			//get the unix name of the project
			$project_name[$row['father_id']][0] = $row['father_name'];
			$project_name[$row['son_id']][0] = $row['son_name'];
			$project_name[$row['father_id']][1] = $row['father_unix_name'];
			$project_name[$row['son_id']][1] = $row['son_unix_name'];
			}
		return $tree;
	}

	function affTree($tree, $lvl) {
		global $project_name;
		$returnTree = '';

		$arbre = array();
		$cpt_pere = 0;

		while (list($key, $sons) = each($tree)) {
			// Really don't know why there is a warning there, and added @
			if(@!$arbre[$key] != 0){
				$arbre[$key] = 0;
			}
			$cpt_pere = $key;
			foreach ($sons as $son) {
				$arbre[$son] = $cpt_pere;
			}
		}

		$returnTree .= '<table ><tr><td>';
		$returnTree .= '<script type="text/javascript">';
		$returnTree .= 'd = new dTree(\'d\');';
		$returnTree .= 'd.add(0,-1,\'Project Tree\');';
		reset($arbre);
		//construction automatique de l'arbre format : (num_fils, num_pere,nom,nom_unix)
		while (list($key2, $sons2) = each($arbre)) {
			$returnTree .= "d.add('".$key2."','".$sons2."','".$project_name[$key2][0]."','".util_make_url( '/projects/'.$project_name[$key2][1] .'/', $project_name[$key2][1] ) ."');";
		}

		$returnTree .= 'document.write(d);';
		$returnTree .= '</script>';
		$returnTree .= '</td></tr></table>';
		return $returnTree;
	}

	/**
	 * getFamily - find the children or parent group_id of this project.
	 *
	 * @param	integer	group_id to serach for
	 * @param	string	parent or child ?
	 * @param	boolean	recurcive or not ?
	 * @param	string	validated or pending or any relation ?
	 * @return	array	array of arrays with group_id of parent or childs
	 * @access	public
	 */
	function getFamily($group_id, $order, $deep = false, $status = 'any') {
		$localFamily = array();
		switch ($status) {
			case "validated": {
				$statusCondition = 't';
				break;
			}
			case "pending": {
				$statusCondition = 'f';
				break;
			}
			case "any":
			default: {
				break;
			}
		}
		switch ($order) {
			case "parent": {
				$qpa = db_construct_qpa(false, 'SELECT project_id as id FROM plugin_projects_hierarchy_relationship
							WHERE sub_project_id = $1 ', array($group_id));
				if (isset($statusCondition)) {
					db_construct_qpa($qpa, ' AND status = $1', array($statusCondition));
				}
				$res = db_query_qpa($qpa);
				break;
			}
			case "child": {
				$qpa = db_construct_qpa(false, 'SELECT sub_project_id as id FROM plugin_projects_hierarchy_relationship
							WHERE project_id = $1', array($group_id));
				if (isset($statusCondition)) {
					db_construct_qpa($qpa, ' AND status = $1', array($statusCondition));
				}
				$res = db_query_qpa($qpa);
				break;
			}
			default: {
				return $localFamily;
				break;
			}
		}
		if ($res && db_numrows($res) > 0) {
			while ($arr = db_fetch_array($res)) {
				$localFamily[] = array($arr['id']);
			}
		}

		if ($deep) {
			for ( $i = 0; $i < count($localFamily); $i++) {
				$localFamily[$i][] = $this->getFamily($localFamily[$i], $order, $deep, $status);
			}
		}
		return $localFamily;
	}

	function getDocmanStatus($group_id) {
		$res = db_query_params('SELECT docman FROM plugin_projects_hierarchy WHERE project_id = $1 or sub_project_id = $1 limit 1',
					array($group_id));
		if (!$res)
			return false;

		$resArr = db_fetch_array($res);
		if ($resArr['docman'] == 't')
			return true;

		return false;
	}

	/**
	 * setDocmanStatus - allow parent to browse your document manager and allow yourself to select your childs to be browsed.
	 *
	 * @param	integer	your groud_id
	 * @param	boolean	the status to set
	 * @return	boolean	success or not
	 */
	function setDocmanStatus($group_id, $status = 0) {
		$res = db_query_params('UPDATE plugin_projects_hierarchy set docman = $1 WHERE sub_project_id = $2 OR project_id = $3',
					array($status, $group_id, $group_id));

		if (!$res)
			return false;

		return true;
	}

	/**
	 * redirect - encapsulate session_redirect to handle correctly the redirection URL
	 *
	 * @param	string	usually http_referer from $_SERVER
	 * @param	string	type of feedback : error, warning, feedback
	 * @param	string	the message of feedback
	 * @access	public
	 */
	function redirect($http_referer, $type, $message) {
		switch ($type) {
			case 'warning_msg':
			case 'error_msg':
			case 'feedback': {
				break;
			}
			default: {
				$type = 'error_msg';
			}
		}
		$url = util_find_relative_referer($http_referer);
		if (strpos($url,'?')) {
			session_redirect($url.'&'.$type.'='.urlencode($message));
		}
		session_redirect($url.'?'.$type.'='.urlencode($message));
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

	function add($group_id) {
		if (!$this->exists($group_id)) {
			$res = db_query_params('INSERT INTO plugin_projects_hierarchy (project_id) VALUES ($1)', array($group_id));
			if (!$res)
				return false;

		}
		return true;
	}

	function remove($group_id) {
		if ($this->exists($group_id)) {
			db_begin();
			$res = db_query_params('DELETE FROM plugin_projects_hierarchy where project_id = $1', array($group_id));
			if (!$res) {
				db_rollback();
				return false;
			}

			$res = db_query_params('DELETE FROM plugin_projects_hierarchy_relationship where project_id = $1 OR sub_project_id = $1',
						array($group_id));
			if (!$res) {
				db_rollback();
				return false;
			}
			db_commit();
		}
		return true;
	}

	function addChild($project_id, $sub_project_id) {
		if ($this->exists($project_id) && $this->exists($sub_project_id)) {
			if (!$this->hasRelation($project_id, $sub_project_id)) {
				$res = db_query_params('INSERT INTO plugin_projects_hierarchy_relationship (project_id, sub_project_id)
							VALUES ($1, $2)', array($project_id, $sub_project_id));
				if (!$res)
					return false;

				return true;
			}
		}
		return false;
	}

	function removeChild($project_id, $sub_project_id) {
		if ($this->exists($project_id) && $this->exists($sub_project_id)) {
			if ($this->hasRelation($project_id, $sub_project_id)) {
				$res = db_query_params('DELETE FROM plugin_projects_hierarchy_relationship
							WHERE project_id = $1 AND sub_project_id = $2',
							array($project_id, $sub_project_id));
				if (!$res)
					return false;

				return true;
			}
		}
		return false;
	}

	function removeParent($project_id, $sub_project_id) {
		if ($this->exists($project_id) && $this->exists($sub_project_id)) {
			if ($this->hasRelation($project_id, $sub_project_id)) {
				$res = db_query_params('DELETE FROM plugin_projects_hierarchy_relationship
							WHERE project_id = $1 AND sub_project_id = $2',
							array($sub_project_id, $project_id));
				if (!$res)
					return false;

				return true;
			}
		}
		return false;
	}

	function hasRelation($project_id, $sub_project_id) {
		if ($this->exists($project_id) && $this->exists($sub_project_id)) {
			$res = db_query_params('SELECT * FROM plugin_projects_hierarchy_relationship
						WHERE ( project_id = $1 AND sub_project_id = $2 )
						OR ( project_id = $2 AND sub_project_id = $1 )',
						array($project_id, $sub_project_id));
			if ($res && db_numrows($res) == 1 )
				return true;
		}
		return false;
	}

	function validateRelationship($project_id, $sub_project_id, $status) {
		if ($this->exists($project_id) && $this->exists($sub_project_id)) {
			if ($this->hasRelation($project_id, $sub_project_id)) {
				if ($status) {
					$res = db_query_params('UPDATE plugin_projects_hierarchy_relationship
								SET status = $1
								WHERE project_id = $2 AND sub_project_id = $3',
								array($status, $project_id, $sub_project_id));
					if (!$res)
						return false;

					if (db_affected_rows($res))
						return true;
				} else {
					$res = db_query_params('DELETE FROM plugin_projects_hierarchy_relationship
								WHERE project_id = $1 AND sub_project_id = $2',
								array($project_id, $sub_project_id));

					if (!$res)
						return false;

					if (db_affected_rows($res))
						return true;
				}
			}
		}
		return false;
	}

	function exists($group_id) {
		$res = db_query_params('SELECT project_id FROM plugin_projects_hierarchy WHERE project_id = $1', array($group_id));
		if (!$res)
			return false;

		if (db_numrows($res))
			return true;

		return false;
	}

	function getAdminOptionLink() {
		return util_make_link('/plugins/'.$this->name.'/?type=globaladmin&pluginname='.$this->name,_('Global Hierarchy admin'));
	}

	/**
	 * getHeader - initialize header and js
	 * @param	string	type : user, project (aka group)
	 * @return	bool	success or not
	 */
	function getHeader($type) {
		global $gfplugins;
		$returned = false;
		switch ($type) {
			case 'globaladmin': {
				session_require_global_perm('forge_admin');
				global $gfwww;
				require_once($gfwww.'admin/admin_utils.php');
				site_admin_header(array('title'=>_('Site Global Hierarchy Admin'), 'toptab' => ''));
				$returned = true;
				break;
			}
			default: {
				break;
			}
		}
		return $returned;
	}

	function getGlobalAdminView() {
		global $gfplugins;
		$user = session_get_user();
		$use_tooltips = $user->usesTooltips();
		include $gfplugins.$this->name.'/view/admin/viewGlobalConfiguration.php';
		return true;
	}

	/**
	 * getGlobalconf - return the global configuration defined at forge level
	 *
	 * @return	array	the global configuration array
	 */
	function getGlobalconf() {
		$resGlobConf = db_query_params('SELECT * from plugin_projects_hierarchy_global',array());
		if (!$resGlobConf) {
			return false;
		}

		$row = db_numrows($resGlobConf);

		if ($row == null || count($row) > 2) {
			return false;
		}

		$resArr = db_fetch_array($resGlobConf);
		$returnArr = array();

		foreach($resArr as $column => $value) {
			if ($value == 't') {
				$returnArr[$column] = true;
			} else {
				$returnArr[$column] = false;
			}
		}

		return $returnArr;
	}

	function getFooter() {
		site_admin_footer(array());
	}

	/**
	 * updateGlobalConf - update the global configuration in database
	 *
	 * @param	array	configuration array (tree, docman, delegate)
	 * @return	bool	true on success
	 */
	function updateGlobalConf($confArr) {
		if (!isset($confArr['tree']) || !isset($confArr['docman']) || !isset($confArr['delegate']))
			return false;

		$res = db_query_params('truncate plugin_projects_hierarchy_global',array());
		if (!$res)
			return false;

		$res = db_query_params('insert into plugin_projects_hierarchy_global (tree, docman, delegate)
					values ($1, $2, $3)',
					array(
						$confArr['tree'],
						$confArr['docman'],
						$confArr['delegate'],
					));
		if (!$res)
			return false;

		return true;
	}

	function son_box($group_id, $name, $selected = 'xzxzxz') {
		$sons = $this->getFamily($group_id, 'child', true, 'any');
		$parent = $this->getFamily($group_id, 'parent', true, 'any');
		$skipped = array();
		$family = array_merge($parent, $sons);
		if (sizeof($family)) {
			//TODO : need to fix this. We only get parent here....
			foreach ($family as $element) {
				$skipped[] = $element[0];
			}
		}
		$son = db_query_params('SELECT group_id, group_name FROM groups
					WHERE status = $1
					AND group_id != $2
					AND group_id <> ALL ($3)
					AND group_id NOT IN (SELECT sub_project_id FROM plugin_projects_hierarchy_relationship)
					AND group_id IN (select group_id from group_plugin,plugins where group_plugin.plugin_id = plugins.plugin_id and plugins.plugin_name = $4);',
					array('A',
						$group_id,
						db_int_array_to_any_clause($skipped),
						$this->name));
		return html_build_select_box($son, $name, $selected, false);
	}
}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
