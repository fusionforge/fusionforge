<?php

/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010, Franck Villaume - Capgemini
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

/* webdav extended class based on pear package */
/* http://pear.php.net/package/HTTP_WebDAV_Server/ */

require_once "HTTP/WebDAV/Server.php";

class HTTP_WebDAV_Server_Docman_DB extends HTTP_WebDAV_Server {

	function GET(&$options) {
		$arr_path = explode('/',$options['path']);
		$group_id = $arr_path[3];

		if (!$group_id)
		    exit_no_group();

		$g =& group_get_object($group_id);
		if (!$g || !is_object($g))
			exit_no_group();

		/* is this group using docman ? */
		if (!$g->usesDocman())
			exit_disabled();

		if (!$g->useWebdav())
			exit_disabled();

		if ($g->isError())
			exit_error($g->getErrorMessage(),'docman');

		if ( 4 < count($arr_path)) {
		    for ($i=5;$i<count($arr_path);$i++){
		       $subpath .= '/'.$arr_path[$i];
		    }
		}

		if (!isset($subpath)) {
			$subpath = '/';
		}

		if ( $subpath == '/' ) {
			$doc_group_id = 0;
			$last_path = '/';
		} else {
			$last_path = strrchr($options['path'],'/');
			$doc_group_id = '70';
		}

		echo "<html><head><title>Index of ".htmlspecialchars($subpath)."</title></head>\n";
		echo "<h1>Index of ".htmlspecialchars($subpath)."</h1>\n";
		echo "<ul>";
		if ($this->isDir($last_path,$group_id,$doc_group_id)) {
			if ( '/' != $subpath ) {
				$back_url = substr($options['path'],0,strrpos($options['path'],strrchr($options['path'],'/')));
				echo '<a href="'.util_make_url($back_url).'">..</a>';
			}
			$res = db_query_params('select * from doc_groups where group_id = $1 and parent_doc_group = $2',
								array($group_id,$doc_group_id));
			if (!$res) {
				exit_error(_('webdav db error:').' '.db_error(),'docman');
			}
			while ($arr = db_fetch_array($res)) {
				if ( '/' != substr($subpath,-1)) {
					$subpath .= '/';
				}
				echo '<li><a href="'.util_make_url('/docman/view.php/'.$group_id.'/webdav'.$subpath.$arr['groupname']).'">'.$arr['groupname'].'</a></li>';
			}
		}
		
		echo "</ul>";
		echo "</html>\n";
		exit;
	}

	function isDir($string,$group_id,$doc_group_id = 0) {
		if ( $string == '/') {
			return true;
		}
		$string = substr($string,1);
		$res = db_query_params('select * from doc_groups where group_id = $1 and groupname = $2 and doc_group = $3',
							array($group_id,$string,$doc_group_id));
		if (!$res) {
			exit_error(_('webdav db error:').' '.db_error(),'docman');
		}

		while ($arr = db_fetch_array($res)) {
			return true;
		}

		return false;
	}

}
?>
