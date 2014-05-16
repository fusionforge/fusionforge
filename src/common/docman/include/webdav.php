<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012,2014, Franck Villaume - TrivialDev
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

/**
 * webdav extended class based on pear package
 * http://pear.php.net/package/HTTP_WebDAV_Server/
 */

/**
 * INFORMATION : this PHP Webdav implementation is based on experience only.
 * I did not find any helpful php documentation.
 * I added as much as possible comments to explain how it works.
 * Feel free to add you on input.
 */

require_once 'HTTP/WebDAV/Server.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';

class HTTP_WebDAV_Server_Docman extends HTTP_WebDAV_Server {

	/**
	 * checkAuth - implement checkAuth called by HTTP_WebDAV_Server
	 * to ensure authentication against user and pass
	 *
	 * @param	int	$group_id	group id
	 * @param	string	$user		username
	 * @param	string	$pass		password
	 * @return	bool	success
	 */
	function checkAuth($group_id, $user, $pass) {
		$this->doWeUseDocman($group_id);
		if (session_login_valid($user, $pass)) {
			if (forge_check_perm('docman', $group_id, 'read')) {
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * HEAD - unused
	 * @todo Do a correct implementation
	 */
	function HEAD(&$options) {
		return true;
	}

	/**
	 * PROPFIND - use by any webdav client like cadaver / dolphin
	 * called by HTTP_WebDAV_Server
	 *
	 * @param	array	$options	options passed by previous functions in HTTP_WebDAV_Server
	 * @param	array	$files		files passed by previous functions in HTTP_WebDAV_Server
	 * @return 	string	http status
	 */
	function PROPFIND(&$options, &$files) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];

		$this->doWeUseDocman($group_id);

		/**
		 * 4 is coming from the url: http://yourforge/docman/6/webdav/the/directory
		 * 1 = http://yourforge
		 * 2 = docman
		 * 3 = id group
		 * 4 = webdav
		 * the rest is the path /the/directory
		 */
		if ( 4 < count($arr_path)) {
			$subpath = '';
			for ($i = 5; $i < count($arr_path); $i++){
				$subpath .= '/'.$arr_path[$i];
			}
		}

		if (empty($subpath)) {
			$subpath = '/';
		}

		$analysed_path = $this->analyse($subpath, $group_id);

		if ($analysed_path['isdir']) {
			$i = 0;
			$files["files"] = array();
			$path = rtrim($options['path'], '/');
			$res = db_query_params('select * from doc_groups where group_id = $1 and doc_group = $2 and stateid = 1',
						array($group_id, $analysed_path['doc_group']));
			if (!$res)
				return '404';

			$arr = db_fetch_array($res);
			if ($arr['updatedate']) {
				$lastmodifieddate = $arr['updatedate'];
			} else {
				$lastmodifieddate = $arr['createdate'];
			}
			$files["files"][$i] = array();
			$files["files"][$i]["path"] = $path;
			$files["files"][$i]["props"] = array();
			$files["files"][$i]["props"][] = $this->mkprop("displayname", $arr['groupname']);
			$files["files"][$i]["props"][] = $this->mkprop("creationdate", $arr['createdate']);
			$files["files"][$i]["props"][] = $this->mkprop("getlastmodified", $lastmodifieddate);
			$files["files"][$i]["props"][] = $this->mkprop("lastaccessed", '');
			$files["files"][$i]["props"][] = $this->mkprop("ishidden", false);
			$files["files"][$i]["props"][] = $this->mkprop("resourcetype", "collection");
			$files["files"][$i]["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");
			$res = db_query_params('select * from doc_groups where group_id = $1 and parent_doc_group = $2 and stateid = 1',
						array($group_id, $analysed_path['doc_group']));
			if (!$res)
				return '404';

			while ($arr = db_fetch_array($res)) {
				$i++;
				if ($arr['updatedate']) {
					$lastmodifieddate = $arr['updatedate'];
				} else {
					$lastmodifieddate = $arr['createdate'];
				}
				$files["files"][$i] = array();
				$files["files"][$i]["path"]  = $path.'/'.$arr['groupname'];
				$files["files"][$i]["props"] = array();
				$files["files"][$i]["props"][] = $this->mkprop("displayname", $arr['groupname']);
				$files["files"][$i]["props"][] = $this->mkprop("creationdate", $arr['createdate']);
				$files["files"][$i]["props"][] = $this->mkprop("getlastmodified", $lastmodifieddate);
				$files["files"][$i]["props"][] = $this->mkprop("lastaccessed", '');
				$files["files"][$i]["props"][] = $this->mkprop("ishidden", false);
				$files["files"][$i]["props"][] = $this->mkprop("resourcetype","collection");
				$files["files"][$i]["props"][] = $this->mkprop("getcontenttype","httpd/unix-directory");
			}
			$res = db_query_params('select filename,filetype,filesize,createdate,updatedate from doc_data where group_id = $1 and doc_group = $2',
				array($group_id, $analysed_path['doc_group']));
			if (!$res)
				return '404';

			while ($arr = db_fetch_array($res)) {
				$i++;
				if ($arr['updatedate']) {
					$lastmodifieddate = $arr['updatedate'];
				} else {
					$lastmodifieddate = $arr['createdate'];
				}
				$files['files'][$i] = array();
				$files['files'][$i]['path'] = $path.'/'.$arr['filename'];
				$files['files'][$i]['props'] = array();
				$files['files'][$i]['props'][] = $this->mkprop('displayname', $arr['filename']);
				$files['files'][$i]['props'][] = $this->mkprop('creationdate', $arr['createdate']);
				$files['files'][$i]['props'][] = $this->mkprop('getlastmodified', $lastmodifieddate);
				$files['files'][$i]['props'][] = $this->mkprop('lastaccessed', '');
				$files['files'][$i]['props'][] = $this->mkprop('ishidden', false);
				$files['files'][$i]['props'][] = $this->mkprop('getcontentlength', $arr['filesize']);
				$files['files'][$i]['props'][] = $this->mkprop('getcontenttype', $arr['filetype']);
			}
		} else {
			$res = db_query_params('select filename,filetype,filesize,createdate,updatedate from doc_data where group_id = $1 and docid = $2',
				array($group_id, $analysed_path['docid']));
			if (!$res)
				return '404';

			$arr = db_fetch_array($res);
			if ($arr['updatedate']) {
				$lastmodifieddate = $arr['updatedate'];
			} else {
				$lastmodifieddate = $arr['createdate'];
			}
			$files['files'][0] = array();
			$files['files'][0]['path'] = $path.'/'.$arr['filename'];
			$files['files'][0]['props'] = array();
			$files['files'][0]['props'][] = $this->mkprop('displayname', $arr['filename']);
			$files['files'][0]['props'][] = $this->mkprop('creationdate', $arr['createdate']);
			$files['files'][0]['props'][] = $this->mkprop('getlastmodified', $lastmodifieddate);
			$files['files'][0]['props'][] = $this->mkprop('lastaccessed', '');
			$files['files'][0]['props'][] = $this->mkprop('ishidden', false);
			$files['files'][0]['props'][] = $this->mkprop('getcontentlength', $arr['filesize']);
			$files['files'][0]['props'][] = $this->mkprop('getcontenttype', $arr['filetype']);
		}
		return '200';
	}

	/**
	 * GET - use by http webdav client like your browser firefox
	 * called by HTTP_WebDAV_Server
	 *
	 * @param	array	$options	options passed by previous functions in HTTP_WebDAV_Server
	 */
	function GET(&$options) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];

		$this->doWeUseDocman($group_id);

		/**
		 * 4 is coming from the url: http://yourforge/docman/6/webdav/the/directory
		 * 1 = http://yourforge
		 * 2 = docman
		 * 3 = id group
		 * 4 = webdav
		 * the rest is the path /the/directory
		 */
		if ( 4 < count($arr_path)) {
			$subpath = '';
			for ($i=5; $i<count($arr_path); $i++){
				$subpath .= '/'.$arr_path[$i];
			}
		}

		if (empty($subpath)) {
			$subpath = '/';
		}

		$analysed_path = $this->analyse($subpath, $group_id);

		if ($analysed_path['isdir']) {
			echo "<html><meta http-equiv='Content-Type' content='text/html charset=UTF-8' /><head><title>"._('Index of').' '.htmlspecialchars($subpath)."</title></head>\n";
			echo "<body>\n";
			echo html_e('h1', array(), _('Index of').' '.htmlspecialchars($subpath));
			echo "<ul>";
			if ( '/' != $subpath ) {
				if ('/' == strrchr($options['path'], '/')) {
					$lastpath = substr($options['path'], 0, -1);
				} else {
					$lastpath = $options['path'];
				}
				$back_url = substr($options['path'], 0, strrpos($options['path'], strrchr($lastpath,'/')));
				echo util_make_link($back_url, '..');
			}
			$res = db_query_params('select groupname from doc_groups where group_id = $1 and parent_doc_group = $2 and stateid = 1',
						array($group_id, $analysed_path['doc_group']));
			if (!$res) {
				exit_error(_('webdav db error')._(': ').db_error(),'docman');
			}
			if ( '/' != substr($subpath,-1)) {
				$subpath .= '/';
			}
			while ($arr = db_fetch_array($res)) {
				echo '<li>'.util_make_link('/docman/view.php/'.$group_id.'/webdav'.$subpath.$arr['groupname'], $arr['groupname']).'</li>';
			}
			$res = db_query_params('select filename, filetype from doc_data where group_id = $1 and doc_group = $2 and stateid = 1',
						array($group_id, $analysed_path['doc_group']));
			if (!$res) {
				exit_error(_('webdav db error')._(': ').db_error(),'docman');
			}
			while ($arr = db_fetch_array($res)) {
				switch ($arr['filetype']) {
					case "URL": {
						echo html_e('li', array(), util_make_link($arr['filename'], $arr['filename'], array(), true), false);
						break;
					}
					default: {
						echo html_e('li', array(), util_make_link('/docman/view.php/'.$group_id.'/webdav'.$subpath.$arr['filename'], $arr['filename']), false);
					}
				}
			}

			echo "</ul>";
			echo "</body></html>\n";
			//do not set return value... yet. The current implementation is rather buggy.
		} else {
			$g = group_get_object($group_id);
			$d = new Document($g, $analysed_path['docid']);
			$options['data'] = $d->getFileData();
			return true;
		}
	}

	function PUT(&$options) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];
		if (!forge_check_perm('docman', $group_id, 'approve')) {
			return '403';
		}
		$this->doWeUseDocman($group_id);
	}

	function DELETE(&$options) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];
		if (!forge_check_perm('docman', $group_id, 'approve')) {
			return '403';
		}
		$this->doWeUseDocman($group_id);
	}

	function MKCOL(&$options) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];
		if (!forge_check_perm('docman', $group_id, 'approve')) {
			return '403';
		}
		$this->doWeUseDocman($group_id);

		$coltocreate = $arr_path[count($arr_path) - 1];
		$dgId = $this->findDgID($arr_path, $group_id);
		if ($dgId >= 0) {
			$g = group_get_object($group_id);
			if (!$g || !is_object($g))
				exit_no_group();

			$dg = new DocumentGroup($g);
			if (!$dg->create($coltocreate, $dgId)) {
				return '409';
			}
			return '201';
		}
		return '405';
	}

	/**
	 * findDgID - get the ID of the document group where we are
	 *
	 * @param	array	$arr_path	the path as array
	 * @param	int	$group_id	the project groupid
	 * @return	int	the document group id
	 */
	function findDgID($arr_path, $group_id) {
		if ($arr_path[count($arr_path) - 2] == 'webdav') {
			// we are in root directory
			return 0;
		} else {
			$path = array_slice($arr_path, 5, count($arr_path) - 6);
			$parent_doc_group = 0;
			foreach ($path as $name) {
				$parent_doc_group = $this->findPdgIdFromPath($name, $parent_doc_group, $group_id);
			}
		}
		return $parent_doc_group;
	}

	function findPdgIdFromPath($name, $parent_doc_group, $group_id) {
		$res = db_query_params('select doc_group from doc_groups where group_id = $1 and groupname = $2 and stateid = 1 and parent_doc_group = $3',
						array($group_id, $name, $parent_doc_group));
		if (!$res) {
			exit_error(_('webdav db error')._(': ').db_error(),'docman');
		}
		$arr = db_fetch_array($res);
		return $arr['doc_group'];
	}

	/**
	 * analyse - find if the path is a file or a directory
	 *
	 * @param	string	the path to analyse
	 * @param	int	group id
	 * @return	array	the analysed path
	 */
	function analyse($path, $group_id) {
		$analysed_path['isdir'] = true;
		$analysed_path['doc_group'] = 0;
		$analysed_path['docid'] = NULL;
		if ( $path == '/') {
			return $analysed_path;
		}

		$path_arr = explode('/', $path);
		for ($i = 1; $i < count($path_arr); $i++) {
			if ($path_arr[$i] == '') {
				continue;
			}
			$analysed_path = $this->whatIsIt($path_arr[$i], $group_id, $analysed_path);
		}

		return $analysed_path;
	}

	/**
	 * whatIsIt - do the analyse
	 *
	 * @param	string	$string	the path to analyse
	 * @param	int		$group_id	group id
	 * @param	array	$path_array	the previous path analysed
	 * @return	array	the path analysed
	 */
	function whatIsIt($string, $group_id, $path_array) {
		$return_path_array['isdir'] = false;
		$res = db_query_params('select doc_group from doc_groups where group_id = $1 and groupname = $2 and parent_doc_group = $3 and stateid = 1',
							array($group_id, $string, $path_array['doc_group']));
		if (!$res) {
			exit_error(_('webdav db error')._(': ').db_error(),'docman');
		}

		while ($arr = db_fetch_array($res)) {
			$return_path_array['isdir'] = true;
			$return_path_array['doc_group'] = $arr['doc_group'];
		}

		if ($return_path_array['isdir']) {
			return $return_path_array;
		}

		$res = db_query_params('select docid from doc_data where group_id = $1 and doc_group = $2 and filename = $3',
					array($group_id, $path_array['doc_group'], $string));
		while ($arr = db_fetch_array($res)) {
			$return_path_array['docid'] = $arr['docid'];
			$return_path_array['filename'] = $string;
		}

		return $return_path_array;
	}

	/**
	 * doWeUseDocman - verify if this group_id is using docman and webdav extension
	 * @param	int	group_id
	 * @return	bool	true on success
	 */
	function doWeUseDocman($group_id) {
		$g = group_get_object($group_id);
		if (!$g || !is_object($g))
			exit_no_group();

		if (!$g->usesDocman())
			exit_disabled();

		if (!$g->useWebdav())
			exit_disabled();

		if ($g->isError())
			exit_error($g->getErrorMessage(), 'docman');

		return true;
	}
}
