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
					if ($this->delete($params['group_id'])) {
						$returned = true;
					}
				} else {
					$returned = true;
				}
				break;
			}
		}
		return $returned;
	}

	function delete($group_id) {
		$res_son = db_query_params('DELETE FROM plugin_projects_hierarchy WHERE project_id = $1 OR sub_project_id = $1 ',
						array($group_id));
		return true;
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
		$res = db_query_params('select p1.group_id as father_id,p1.unix_group_name as father_unix_name,p1.group_name as father_name,p2.group_id as son_id,p2.unix_group_name as son_unix_name,p2.group_name as son_name from groups as p1,groups as p2,plugin_projects_hierarchy where p1.group_id=plugin_projects_hierarchy.project_id and p2.group_id=plugin_projects_hierarchy.sub_project_id and plugin_projects_hierarchy.activated=$1 AND plugin_projects_hierarchy.link_type=$2 order by father_name, son_name',
			array ('t',
				'shar'));
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
	 * @param	boolean	recurvice or not ?
	 * @return	array	array of arrays with group_id of parent or childs
	 * @access	public
	 */
	function getFamily($group_id, $order, $deep = false) {
		$localFamily = array();
		switch ($order) {
			case "parent": {
				$res = db_query_params('SELECT project_id as id FROM plugin_projects_hierarchy WHERE sub_project_id = $1',
							array($group_id));
				break;
			}
			case "child": {
				$res = db_query_params('SELECT sub_project_id as id FROM plugin_projects_hierarchy WHERE project_id = $1',
							array($group_id));
				break;
			}
			default: {
				return $localFamily;
			}
		}
		if ($res || db_numrows($res) > 1) {
			while ($arr = db_fetch_array($res)) {
				$localFamily[] = $arr['id'];
			}
		}

		if ($deep) {
			for ( $i = 0; $i < count($localFamily); $i++) {
				$localFamily[$i] = $this->getFamily($localFamily[$i], $order, $deep);
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
}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
