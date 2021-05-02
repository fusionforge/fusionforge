<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012,2014,2016,2021, Franck Villaume - TrivialDev
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
require_once $gfcommon.'docman/Document.class.php';

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
	 *
	 * @param	array	$options
	 * @return	bool
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
		$files['files'] = array();
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];

		/**
		 * 4 is coming from the url: /docman/view.php/6/webdav/the/directory
		 * 1 = docman
		 * 2 = view.php
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
		$path = rtrim($options['path'], '/');
		if ($analysed_path['isdir']) {
			$i = 0;
			$res = db_query_params('select * from doc_groups where group_id = $1 and doc_group = $2 and stateid = ANY ($3)',
						array($group_id, $analysed_path['doc_group'], db_int_array_to_any_clause(array(1, 5))));
			if (!$res) {
				return '404';
			}
			if (db_numrows($res)) {
				$arr = db_fetch_array($res);
			} else {
				//we setup for the specific root / folder which does not exist in database
				$g = group_get_object($group_id);
				$arr = array();
				$arr['groupname'] = '/';
				$arr['createdate'] = $g->getStartDate();
				$arr['updatedate'] = 0;
			}
			if ($arr['updatedate']) {
				$lastmodifieddate = $arr['updatedate'];
			} else {
				$lastmodifieddate = $arr['createdate'];
			}
			$files['files'][$i] = array();
			$files['files'][$i]['path'] = $path;
			$files['files'][$i]['props'] = array();
			$files['files'][$i]['props'][] = $this->mkprop('displayname', $arr['groupname']);
			$files['files'][$i]['props'][] = $this->mkprop('creationdate', $arr['createdate']);
			$files['files'][$i]['props'][] = $this->mkprop('getlastmodified', $lastmodifieddate);
			$files['files'][$i]['props'][] = $this->mkprop('lastaccessed', '');
			$files['files'][$i]['props'][] = $this->mkprop('ishidden', false);
			$files['files'][$i]['props'][] = $this->mkprop('resourcetype', 'collection');
			$files['files'][$i]['props'][] = $this->mkprop('getcontenttype', 'httpd/unix-directory');
			$res = db_query_params('select * from doc_groups where group_id = $1 and parent_doc_group = $2 and stateid = ANY ($3)',
						array($group_id, $analysed_path['doc_group'], db_int_array_to_any_clause(array(1, 5))));
			if (!$res) {
				return '404';
			}
			while ($arr = db_fetch_array($res)) {
				$i++;
				if ($arr['updatedate']) {
					$lastmodifieddate = $arr['updatedate'];
				} else {
					$lastmodifieddate = $arr['createdate'];
				}
				$files['files'][$i] = array();
				$files['files'][$i]['path']  = $path.'/'.$arr['groupname'];
				$files['files'][$i]['props'] = array();
				$files['files'][$i]['props'][] = $this->mkprop('displayname', $arr['groupname']);
				$files['files'][$i]['props'][] = $this->mkprop('creationdate', $arr['createdate']);
				$files['files'][$i]['props'][] = $this->mkprop('getlastmodified', $lastmodifieddate);
				$files['files'][$i]['props'][] = $this->mkprop('lastaccessed', '');
				$files['files'][$i]['props'][] = $this->mkprop('ishidden', false);
				$files['files'][$i]['props'][] = $this->mkprop('resourcetype', 'collection');
				$files['files'][$i]['props'][] = $this->mkprop('getcontenttype', 'httpd/unix-directory');
			}
			$res = db_query_params('select filename,filetype,filesize,createdate,updatedate from docdata_vw where group_id = $1 and doc_group = $2',
				array($group_id, $analysed_path['doc_group']));
			if (!$res) {
				return '404';
			}
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
		} elseif (isset($analysed_path['docid'])) {
			$res = db_query_params('select filename,filetype,filesize,createdate,updatedate from docdata_vw where group_id = $1 and docid = $2',
				array($group_id, $analysed_path['docid']));
			if (!$res) {
				return '404';
			}
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
	 * @return	bool
	 */
	function GET(&$options) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];

		/**
		 * 4 is coming from the url: /docman/view.php/6/webdav/the/directory
		 * 1 = docman
		 * 2 = view.php
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
			echo "<html><head><meta http-equiv='Content-Type' content='text/html charset=UTF-8' /><title>"._('Index of').' '.urldecode($subpath)."</title></head>\n";
			echo "<body>\n";
			echo html_e('h1', array(), _('Index of').' '.urldecode($subpath));
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
			$res = db_query_params('select groupname from doc_groups where group_id = $1 and parent_doc_group = $2 and stateid = ANY ($3)',
						array($group_id, $analysed_path['doc_group'], db_int_array_to_any_clause(array(1, 5))));
			if (!$res) {
				exit_error(_('webdav db error')._(': ').db_error(),'docman');
			}
			if ( '/' != substr($subpath,-1)) {
				$subpath .= '/';
			}
			while ($arr = db_fetch_array($res)) {
				echo '<li>'.util_make_link('/docman/view.php/'.$group_id.'/webdav'.$subpath.urlencode($arr['groupname']), $arr['groupname']).'</li>';
			}
			$res = db_query_params('select filename, filetype from docdata_vw where group_id = $1 and doc_group = $2 and stateid = ANY ($3)',
						array($group_id, $analysed_path['doc_group'], db_int_array_to_any_clause(array(1, 5))));
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
			$options['stream'] = fopen($d->getFilePath(), 'rb');
			$options['mimetype'] = $d->getFileType();
			$options['size'] = $d->getFileSize();
			if ($d->getUpdated()) {
				$options['mtime'] = $d->getUpdated();
			} else {
				$options['mtime'] = $d->getCreated();
			}
			return true;
		}
	}

	/**
	 * PUT
	 * called by HTTP_WebDAV_Server
	 *
	 * @param	array	$options	options passed by previous functions in HTTP_WebDAV_Server
	 * @return	string
	 */
	function PUT(&$options) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];
		if (!forge_check_perm('docman', $group_id, 'approve')) {
			return '403';
		}

		$newfilename = $arr_path[count($arr_path) - 1];
		$dgId = $this->findDgID($arr_path, $group_id);
		/* Open a file for writing */
		$tmpfile = sys_get_temp_dir().'/'.uniqid();
		$fp = fopen($tmpfile, "w");
		while ($data = fread($options['stream'], 1024)) {
			fwrite($fp, $data);
		}
		/* Close the streams */
		fclose($fp);
		fclose($options['stream']);
		$g = group_get_object($group_id);

		$dg = new DocumentGroup($g, $dgId);

		$docid = $dg->hasDocument($newfilename);
		if ($docid) {
			$d = document_get_object($docid, $g->getID());
			if (!$d->getReserved() && !$d->getLocked()) {
				if ($d->update($d->getFileName(), $d->getFileType(), $tmpfile, $dgId, $d->getName(), $d->getDescription(), $d->getStateID())) {
					@unlink($tmpfile);
					return '200';
				}
			} else {
				return '423';
			}
		} else {
			$d = new Document($g);
			if (strlen($newfilename) < 5) {
				$title = $newfilename.' '._('(Title must be at least 5 characters.)');
			} else {
				$title = $newfilename;
			}
			if (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$uploaded_data_type = finfo_file($finfo, $tmpfile);
			} else {
				$uploaded_data_type = $options['content_type'];
			}
			if ($d->create($newfilename, $uploaded_data_type, $tmpfile, $dgId, $title, _('Injected by WebDAV')._(': ').date(DATE_ATOM))) {
				@unlink($tmpfile);
				return '200';
			}
		}
		@unlink($tmpfile);
		return '409';
	}

	/**
	 * DELETE
	 * called by HTTP_WebDAV_Server
	 *
	 * @param	array	$options	options passed by previous functions in HTTP_WebDAV_Server
	 * @return	string
	 */
	function DELETE(&$options) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];
		if (!forge_check_perm('docman', $group_id, 'approve')) {
			return '403';
		}

		/**
		 * 4 is coming from the url: /docman/view.php/6/webdav/the/directory
		 * 1 = docman
		 * 2 = view.php
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
		$g = group_get_object($group_id);
		if ($analysed_path['isdir']) {
			/* set this doc_group to trash */
			$dg = new DocumentGroup($g, $analysed_path['doc_group']);
			if ($dg->trash()) {
				return '200';
			}
			return '423';
		} else {
			if ($analysed_path['docid']) {
				$d = new Document($g, $analysed_path['docid']);
				if ($d->trash()) {
					return '200';
				}
				return '423';
			}
		}
		return '404';
	}

	/**
	 * MKCOL
	 * called by HTTP_WebDAV_Server
	 *
	 * @param	array	$options	options passed by previous functions in HTTP_WebDAV_Server
	 * @return	string
	 */
	function MKCOL(&$options) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];
		if (!forge_check_perm('docman', $group_id, 'approve')) {
			return '403';
		}

		$coltocreate = $arr_path[count($arr_path) - 1];
		$dgId = $this->findDgID($arr_path, $group_id);
		if ($dgId >= 0) {
			$g = group_get_object($group_id);
			if (!$g || !is_object($g)) {
				exit_no_group();
			}
			$dg = new DocumentGroup($g);
			if (!$dg->create($coltocreate, $dgId)) {
				return '409';
			}
			return '201';
		}
		return '405';
	}

	/**
	 * MOVE
	 * called by HTTP_WebDAV_Server
	 *
	 * @param	array	$options	options passed by previous functions in HTTP_WebDAV_Server
	 * @return	string
	 */
	function MOVE(&$options) {
		$arr_path = explode('/', rtrim($options['path'], '/'));
		$group_id = $arr_path[3];
		if (!forge_check_perm('docman', $group_id, 'approve')) {
			return '403';
		}

		if (empty($options['dest'])) {
			//ok... need to find the destination
			//let's try with dest_url
			if (util_check_url($options['dest_url'])) {
				$urlArray = explode('/', rtrim($options['dest_url'], '/'));
				$arr_dest = array_slice($urlArray, 2);
			}
		} else {
			$arr_dest = explode('/', rtrim($options['dest'], '/'));
		}

		// are we doing something ?
		$arr_diff1 = array_diff($arr_dest, $arr_path);
		$arr_diff2 = array_diff($arr_path, $arr_dest);
		if ((count($arr_diff1) == 0) && (count($arr_diff2) == 0)) {
			return '403';
		}

		/**
		 rebuild the dest element and src element to find what are we doing :
		 1) moving a file to a new dir ?
		 2) moving a dir to a new dir ?
		 3) renaming a file ?
		 4) renaming a dir ?
		*/

		/**
		 * 4 is coming from the url: /docman/view.php/6/webdav/the/directory
		 * 1 = docman
		 * 2 = view.php
		 * 3 = id group
		 * 4 = webdav
		 * the rest is the path /the/directory
		 */
		if ( 4 < count($arr_path)) {
			$src_element = '';
			for ($i = 5; $i < count($arr_path); $i++){
				$src_element .= '/'.$arr_path[$i];
			}
		}

		if (empty($src_element)) {
			$src_element = '/';
		}

		if ( 4 < count($arr_dest)) {
			$dest_element = '';
			for ($i = 5; $i < count($arr_dest) - 1; $i++){
				$dest_element .= '/'.$arr_dest[$i];
			}
		}

		if (empty($dest_element)) {
			$dest_element = '/';
		}

		$analysed_src_element = $this->analyse($src_element, $group_id);
		$analysed_dest_element = $this->analyse($dest_element, $group_id);
		$g = group_get_object($group_id);
		if (isset($analysed_src_element['docid'])) {
			// we are playing with a file
			if ($analysed_dest_element['isdir']) {
				$d = new Document($g, $analysed_src_element['docid']);
				if ($d->getDocGroupID() == $analysed_dest_element['doc_group']) {
					// we are renaming the file
					$filename = end($arr_dest);
				} else {
					// we are moving the file to a new directory
					$filename = $d->getFileName();
				}
				if ($d->update($filename, $d->getFileType(), false, $analysed_dest_element['doc_group'], $d->getName(), $d->getDescription(), $d->getStateID())) {
					return '201';
				}
				return '403';
			}
		} elseif ($analysed_src_element['isdir']) {
			// we are playing with a directory
			if ($analysed_dest_element['isdir']) {
				$src_dg = new DocumentGroup($g, $analysed_src_element['doc_group']);
				if ($src_dg->getParentID() == $analysed_dest_element['doc_group']) {
					// we are renaming the directory
					$dirname = end($arr_dest);
				} else {
					// we are moving the directory to a new directory
					$dirname = $src_dg->getName();
				}
				if ($src_dg->update($dirname, $analysed_dest_element['doc_group'], 1, $src_dg->getState())) {
					return '201';
				}
				return '403';
			}
		}
		return '403';
	}

	function LOCK(&$options) {
		return true;
	}

	function UNLOCK(&$options) {
		return true;
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
		$res = db_query_params('select doc_group from doc_groups where group_id = $1 and groupname = $2 and stateid = ANY ($3) and parent_doc_group = $4',
						array($group_id, $name, db_int_array_to_any_clause(array(1, 5)), $parent_doc_group));
		if (!$res) {
			exit_error(_('webdav db error')._(': ').db_error(),'docman');
		}
		$arr = db_fetch_array($res);
		return $arr['doc_group'];
	}

	/**
	 * analyse - find if the path is a file or a directory
	 *
	 * @param	string	$path		the path to analyse
	 * @param	int	$group_id	group id
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
			$analysed_path = $this->whatIsIt(urldecode($path_arr[$i]), $group_id, $analysed_path);
		}

		return $analysed_path;
	}

	/**
	 * whatIsIt - do the analyse
	 *
	 * @param	string	$string		the path to analyse
	 * @param	int	$group_id	group id
	 * @param	array	$path_array	the previous path analysed
	 * @return	array	the path analysed
	 */
	function whatIsIt($string, $group_id, $path_array) {
		$return_path_array['isdir'] = false;
		$res = db_query_params('select doc_group from doc_groups where group_id = $1 and groupname = $2 and parent_doc_group = $3 and stateid = ANY ($4)',
							array($group_id, $string, $path_array['doc_group'], db_int_array_to_any_clause(array(1, 5))));
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

		$res = db_query_params('select docid from docdata_vw where group_id = $1 and doc_group = $2 and filename = $3',
					array($group_id, $path_array['doc_group'], $string));
		while ($arr = db_fetch_array($res)) {
			$return_path_array['docid'] = $arr['docid'];
			$return_path_array['filename'] = $string;
		}

		return $return_path_array;
	}

	/**
	 * doWeUseDocman - verify if this group_id is using docman and webdav extension
	 * @param	int	$group_id
	 * @return	bool	true on success
	 */
	function doWeUseDocman($group_id) {
		$g = group_get_object($group_id);
		if (!$g || !is_object($g)) {
			exit_no_group();
		}
		if (!$g->usesDocman() || !$g->useWebdav()) {
			exit_disabled();
		}
		if ($g->isError()) {
			exit_error($g->getErrorMessage(), 'docman');
		}
		return true;
	}
}
