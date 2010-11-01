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

class HTTP_WebDAV_Server_Docman extends HTTP_WebDAV_Server {

    function checkAuth($group_id,$user,$pass) {
		$g =& group_get_object($group_id);
		if (!$g || !is_object($g))
			return false;

		/* is this group using docman ? */
		if (!$g->usesDocman())
            return false;

        if (!$g->useWebdav())
            return false;

		if ($g->isError())
            return false;

		if (!session_login_valid($user,$pass)) {
			if (forge_check_perm ('docman',$group_id,'read')) {
				return true;
			}
			return false;
		} else {
			$u = &user_get_object_by_name($user);
			foreach ($u->getGroups() as $key => $memberOfThisGroup) {
				if ($memberOfThisGroup->getID() == $group_id) {
					return true;
				}
			}
		}
		return false;
	}

    function HEAD(&$options) {
        return true;
    }

    function PROPFIND(&$options,&$files) {
		$arr_path = explode('/',$options['path']);
		$group_id = $arr_path[3];

		if (!$group_id)
		    return false;

		$g =& group_get_object($group_id);
		if (!$g || !is_object($g))
			return false;

		/* is this group using docman ? */
		if (!$g->usesDocman())
            return false;

        if (!$g->useWebdav())
            return false;

		if ($g->isError())
            return false;

		if ( 4 < count($arr_path)) {
            $subpath = '';
		    for ($i=5;$i<count($arr_path);$i++){
		       $subpath .= '/'.$arr_path[$i];
		    }
		}

		if (empty($subpath)) {
			$subpath = '/';
		}

		$analysed_path = $this->analyse($subpath,$group_id);

		if ($analysed_path['isdir']) {
            $i = 0;
            $files["files"] = array();
            $path = $options['path'];
            $name = basename($path);
            $files["files"][$i] = array();
            $files["files"][$i]["path"]  = $path;
            $files["files"][$i]["props"] = array();
            $files["files"][$i]["props"][] = $this->mkprop("displayname",$name);
            $files["files"][$i]["props"][] = $this->mkprop("creationdate",'');
            $files["files"][$i]["props"][] = $this->mkprop("getlastmodified",'');
            $files["files"][$i]["props"][] = $this->mkprop("lastaccessed",'');
            $files["files"][$i]["props"][] = $this->mkprop("ishidden",false);
            $files["files"][$i]["props"][] = $this->mkprop("resourcetype","collection");
            $files["files"][$i]["props"][] = $this->mkprop("getcontenttype","httpd/unix-directory");
			$res = db_query_params('select * from doc_groups where group_id = $1 and parent_doc_group = $2',
								array($group_id,$analysed_path['doc_group']));
			if (!$res) {
				return false;
			}
			while ($arr = db_fetch_array($res)) {
                $i++;
                $files["files"][$i] = array();
                $files["files"][$i]["path"]  = $path.'/'.$arr['groupname'];
                $files["files"][$i]["props"] = array();
                $files["files"][$i]["props"][] = $this->mkprop("displayname",$arr['groupname']);
                $files["files"][$i]["props"][] = $this->mkprop("creationdate",'');
                $files["files"][$i]["props"][] = $this->mkprop("getlastmodified",'');
                $files["files"][$i]["props"][] = $this->mkprop("lastaccessed",'');
                $files["files"][$i]["props"][] = $this->mkprop("ishidden",false);
                $files["files"][$i]["props"][] = $this->mkprop("resourcetype","collection");
                $files["files"][$i]["props"][] = $this->mkprop("getcontenttype","httpd/unix-directory");
            }
            $res = db_query_params('select filename,filetype,filesize,createdate,updatedate from doc_data where group_id = $1 and doc_group = $2',
                array($group_id,$analysed_path['doc_group']));
            if (!$res) {
				return false;
            }
			while ($arr = db_fetch_array($res)) {
                $i++;
                $files["files"][$i] = array();
                $files["files"][$i]["path"]  = $path.'/'.$arr['filename'];
                $files["files"][$i]["props"] = array();
                $files["files"][$i]["props"][] = $this->mkprop("displayname",$arr['filename']);
                $files["files"][$i]["props"][] = $this->mkprop("creationdate",$arr['createdate']);
                $files["files"][$i]["props"][] = $this->mkprop("getlastmodified",$arr['updatedate']);
                $files["files"][$i]["props"][] = $this->mkprop("lastaccessed",'');
                $files["files"][$i]["props"][] = $this->mkprop("ishidden",false);
                $files["files"][$i]["props"][] = $this->mkprop("getcontentlength",$arr['filesize']);
                $files["files"][$i]["props"][] = $this->mkprop("getcontenttype",$arr['filetype']);
            }
        }
        return true;
    }

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
            $subpath = '';
		    for ($i=5;$i<count($arr_path);$i++){
		       $subpath .= '/'.$arr_path[$i];
		    }
		}

		if (empty($subpath)) {
			$subpath = '/';
		}

		$analysed_path = $this->analyse($subpath,$group_id);

		if ($analysed_path['isdir']) {
		    echo "<html><head><title>Index of ".htmlspecialchars($subpath)."</title></head>\n";
            echo "<body>\n";
		    echo "<h1>Index of ".htmlspecialchars($subpath)."</h1>\n";
		    echo "<ul>";
			if ( '/' != $subpath ) {
                if ('/' == strrchr($options['path'],'/')) {
                    $lastpath = substr($options['path'],0,-1);
                } else {
                    $lastpath = $options['path'];
                }
				$back_url = substr($options['path'],0,strrpos($options['path'],strrchr($lastpath,'/')));
				echo '<a href="'.util_make_url($back_url).'">..</a>';
			}
			$res = db_query_params('select * from doc_groups where group_id = $1 and parent_doc_group = $2',
								array($group_id,$analysed_path['doc_group']));
			if (!$res) {
				exit_error(_('webdav db error:').' '.db_error(),'docman');
			}
			if ( '/' != substr($subpath,-1)) {
				$subpath .= '/';
			}
			while ($arr = db_fetch_array($res)) {
				echo '<li><a href="'.util_make_url('/docman/view.php/'.$group_id.'/webdav'.$subpath.$arr['groupname']).'">'.$arr['groupname'].'</a></li>';
			}
            $res = db_query_params('select filename,filetype from doc_data where group_id = $1 and doc_group = $2',
                array($group_id,$analysed_path['doc_group']));
            if (!$res) {
				exit_error(_('webdav db error:').' '.db_error(),'docman');
            }
			while ($arr = db_fetch_array($res)) {
				switch ($arr['filetype']) {
				case "URL":
					echo '<li><a href="'.$arr['filename'].'">'.$arr['filename'].'</a></li>';
					break;
				default:
					echo '<li><a href="'.util_make_url('/docman/view.php/'.$group_id.'/webdav'.$subpath.$arr['filename']).'">'.$arr['filename'].'</a></li>';
				}
			}

		    echo "</ul>";
		    echo "</body></html>\n";
        } else {
            session_redirect('/docman/view.php/'.$group_id.'/'.$analysed_path['docid'].'/'.$analysed_path['filename']);
        }
		
		exit;
	}

    function analyse($path,$group_id) {
		$analysed_path['isdir'] = true;
        $analysed_path['doc_group'] = 0;
        $analysed_path['docid'] = NULL;
		if ( $path == '/') {
			return $analysed_path;
		}

        $path_arr = explode('/',$path);
        for ($i = 1; $i < count($path_arr); $i++) {
            if ($path_arr[$i] == '') {
                continue;
            }
            $analysed_path = $this->whatIsIt($path_arr[$i],$group_id,$analysed_path);
        }
        return $analysed_path;
    }

	function whatIsIt($string,$group_id,$path_array) {
		$return_path_array['isdir'] = false;
		$res = db_query_params('select doc_group from doc_groups where group_id = $1 and groupname = $2 and parent_doc_group = $3',
							array($group_id,$string,$path_array['doc_group']));
		if (!$res) {
			exit_error(_('webdav db error:').' '.db_error(),'docman');
		}

		while ($arr = db_fetch_array($res)) {
			$return_path_array['isdir'] = true;
            $return_path_array['doc_group'] = $arr['doc_group'];
		}

        if ($return_path_array['isdir']) {
            return $return_path_array;
        }

        $res = db_query_params('select docid from doc_data where group_id = $1 and doc_group = $2 and filename = $3',
                        array($group_id,$path_array['doc_group'],$string));
        while ($arr = db_fetch_array($res)) {
            $return_path_array['docid'] = $arr['docid'];
            $return_path_array['filename'] = $string;
        }

        return $return_path_array;
	}
}
?>
