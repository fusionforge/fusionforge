<?php
/**
 * trove.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 */

// ################################## Trove Globals

$TROVE_MAXPERROOT = 3;
$TROVE_BROWSELIMIT = 20;
$TROVE_HARDQUERYLIMIT = -1;

// ##################################

/**
 * trove_genfullpaths() - Regenerates full path entries for $node and all subnodes
 *
 * @param		int		The node
 * @param		string	The full path for this node
 * @param		int		The full path IDs
 */
function trove_genfullpaths($mynode,$myfullpath,$myfullpathids) {
	// first generate own path
	$res_update = db_query_params ('UPDATE trove_cat SET fullpath=$1,fullpath_ids=$2 WHERE trove_cat_id=$3',
				       array ($myfullpath,
					      $myfullpathids,
					      $mynode));
	// now generate paths for all children by recursive call
	if($mynode!=0)
	{
		$res_child = db_query_params ('
			SELECT trove_cat_id,fullname
			FROM trove_cat
			WHERE parent=$1
			AND trove_cat_id != 0',
					      array ($mynode));

		while ($row_child = db_fetch_array($res_child)) {
			trove_genfullpaths($row_child['trove_cat_id'],
				addslashes(quotemeta($myfullpath)) . ' :: ' . addslashes(quotemeta($row_child['fullname'])),
				$myfullpathids.' :: '.$row_child['trove_cat_id']);
		}
	}
}

// ##################################

/**
 * trove_updaterootparent() - Regenerates full path entries for $node and all subnodes
 *
 * @param		int		The node
 * @param		int		The root parent node
 */
function trove_updaterootparent($mynode,$rootnode) {
	// first generate own path
	if($mynode!=$rootnode) $res_update = db_query_params ('UPDATE trove_cat SET root_parent=$1 WHERE trove_cat_id=$2',
							      array ($rootnode,
								     $mynode));
	else $res_update = db_query_params ('UPDATE trove_cat SET root_parent=0 WHERE trove_cat_id=$1',
					    array ($mynode));
	// now generate paths for all children by recursive call
	if($mynode!=0)
	{
		$res_child = db_query_params ('
			SELECT trove_cat_id
			FROM trove_cat
			WHERE parent=$1
			AND trove_cat_id!=0',
					      array ($mynode));

		while ($row_child = db_fetch_array($res_child)) {
			trove_updaterootparent($row_child['trove_cat_id'],$rootnode);
		}
	}
}

// #########################################

/**
 * trove_setnode() - Adds a group to a trove node
 *
 * @param		int		The group ID
 * @param		int		The trove category ID
 * @param		int		The root node
 */
function trove_setnode($group_id,$trove_cat_id,$rootnode=0) {
	// verify we were passed information
	if ((!$group_id) || (!$trove_cat_id)) return 1;

	// verify trove category exists
	$res_verifycat = db_query_params ('
		SELECT trove_cat_id,fullpath_ids
		FROM trove_cat
		WHERE trove_cat_id=$1',
					  array ($trove_cat_id));

	if (db_numrows($res_verifycat) != 1) return 1;
	$row_verifycat = db_fetch_array($res_verifycat);

	// if we didnt get a rootnode, find it
	if (!$rootnode) {
		$rootnode = trove_getrootcat($trove_cat_id);
	}

	// must first make sure that this is not a subnode of anything current
	$res_topnodes = db_query_params ('
		SELECT trove_cat.trove_cat_id AS trove_cat_id,
			trove_cat.fullpath_ids AS fullpath_ids
		FROM trove_cat,trove_group_link
		WHERE trove_cat.trove_cat_id=trove_group_link.trove_cat_id
		AND trove_group_link.group_id=$1
		AND trove_cat.root_parent=$2',
					 array ($group_id,
						$rootnode));

	while($row_topnodes = db_fetch_array($res_topnodes)) {
		$pathids = explode(' :: ',$row_topnodes['fullpath_ids']);
		for ($i=0;$i<count($pathids);$i++) {
			// anything here will invalidate this setnode
			if ($pathids[$i] == $trove_cat_id) {
				return 1;
			}
		}
	}

	// need to see if this one is more specific than another
	// if so, delete the other and proceed with this insertion
	$subnodeids = explode(' :: ',$row_verifycat['fullpath_ids']);
	$res_checksubs = db_query_params ('
		SELECT trove_cat_id
		FROM trove_group_link
		WHERE group_id=$1
		AND trove_cat_root=$2',
					  array($group_id,
						$rootnode));

	while ($row_checksubs = db_fetch_array($res_checksubs)) {
		// check against all subnodeids
		for ($i=0;$i<count($subnodeids);$i++) {
			if ($subnodeids[$i] == $row_checksubs['trove_cat_id']) {
				// then delete subnode
				db_query_params ('DELETE FROM trove_group_link WHERE group_id=$1 AND trove_cat_id=$2',
						 array ($group_id,
							$subnodeids[$i]));
			}
		}
	}

	// if we got this far, must be ok
	db_query_params ('INSERT INTO trove_group_link (trove_cat_id,trove_cat_version,group_id,trove_cat_root) VALUES ($1,$2,$3,$4)',
			 array ($trove_cat_id,
				time(),
				$group_id,
				$rootnode));
	return 0;
}

/**
 * trove_getrootcat() - Get the root categegory
 *
 * @param		int		Trove category ID
 */
function trove_getrootcat($trove_cat_id) {
	$parent = 1;
	$current_cat = $trove_cat_id;

	while ($parent > 0) {
		$res_par = db_query_params ('
			SELECT parent
			FROM trove_cat
			WHERE trove_cat_id=$1',
					    array($current_cat));

		$row_par = db_fetch_array($res_par);
		$parent = $row_par["parent"];
		if ($parent == 0) return $current_cat;
		$current_cat = $parent;
	}

	return 0;
}

/**
 * trove_getallroots() - Returns an associative array of all project roots
 */
function trove_getallroots() {
	$res = db_query_params ('
		SELECT trove_cat_id,fullname
		FROM trove_cat
		WHERE parent=0
		AND trove_cat_id!=0',
			array());

	while ($row = db_fetch_array($res)) {
		$tmpcatid = $row["trove_cat_id"];
		$CATROOTS[$tmpcatid] = $row["fullname"];
	}
	return $CATROOTS;
}

/**
 * trove_catselectfull() - Returns full select output for a particular root
 *
 * @param		int		The node
 * @param		string	The category to pre-select
 * @param		string	THe select-box name
 */
function trove_catselectfull($node,$selected,$name) {
	print "<br /><select name=\"$name\">";
	print '  <option value="0">'._('None Selected')."</option>\n";
	$res_cat = db_query_params ('
		SELECT trove_cat_id,fullpath
		FROM trove_cat
		WHERE root_parent=$1
		ORDER BY fullpath',
			array($node));

	while ($row_cat = db_fetch_array($res_cat)) {
		print '  <option value="'.$row_cat['trove_cat_id'].'"';
		if ($selected == $row_cat['trove_cat_id']) print (' selected="selected"');
		print '>'.$row_cat['fullpath']."</option>\n";
	}
	print "</select>\n";
}

/**
 * trove_getcatlisting() - Gets discriminator listing for a group
 *
 * @param		int		The group ID
 * @param		bool	Whether filters have already been applied
 * @param		bool	Whether to print category links
 * @param		bool	???
 */
function trove_getcatlisting($group_id,$a_filter,$a_cats,$a_complete=0) {
	global $discrim_url;
	global $expl_discrim;
	global $form_cat;

	$res_trovecat = db_query_params ('
		SELECT trove_cat.fullpath AS fullpath,
			trove_cat.fullpath_ids AS fullpath_ids,
			trove_cat.trove_cat_id AS trove_cat_id
		FROM trove_cat,trove_group_link
		WHERE trove_cat.trove_cat_id=trove_group_link.trove_cat_id
		AND trove_group_link.group_id=$1
		ORDER BY trove_cat.fullpath',
			array($group_id));

	$return = '';
	$need_close_ul_tag = 0;
	if (db_numrows($res_trovecat) < 1) {
		$return .= _('This project has not yet categorized itself in the').' '
			.util_make_link ('/softwaremap/trove_list.php',_('Trove Software Map'))
			.'<p />';
	} else {
		$return .= '<ul>';
		$need_close_ul_tag = 1;
	}

	// first initialise the vars we're using here
	$proj_discrim_used=array();
	$isfirstdiscrim = 1;
	while ($row_trovecat = db_fetch_array($res_trovecat)) {
		$folders = explode(" :: ",$row_trovecat['fullpath']);
		$folders_ids = explode(" :: ",$row_trovecat['fullpath_ids']);
		$folders_len = count($folders);
		// if first in discrim print root category
		if (!array_key_exists($folders_ids[0], $proj_discrim_used)) {
			if (!$isfirstdiscrim) {
				$return .= "</li>\n";
			}
			$return .= '<li>';
			if ($a_complete==0) {
				$return .= $folders[0].' : ';
			}
		}

		// filter links, to add discriminators
		// first check to see if filter is already applied
		$filterisalreadyapplied = 0;
		for ($i=0;$i<sizeof($expl_discrim);$i++) {
			if ($folders_ids[$folders_len-1] == $expl_discrim[$i]) {
				$filterisalreadyapplied = 1;
			}
		}
		// then print the stuff
		if (array_key_exists($folders_ids[0], $proj_discrim_used)) {
			$return .= '</li><li>';
		}

		if ($a_complete) {
			for ($i=0;$i<$folders_len;$i++) {
				if ($a_cats) {
					$return .= '<a href="'.util_make_url ('/softwaremap/trove_list.php?form_cat='.$folders_ids[$i].$discrim_url).'">';
				}
				$return .= ($folders[$i]);
				if ($a_cats) {
					$return .= '</a>';
				}
				if ($i!=$folders_len-1) {
					$return .= " : ";
				}
			}
		} else {
			if ($a_cats) {
				$return .= '<a href="'.util_make_url ('/softwaremap/trove_list.php?form_cat='.$folders_ids[$folders_len-1].$discrim_url).'">';
			}
			$return .= ($folders[$folders_len-1]);
			if ($a_cats) {
				$return .= '</a>';
			}
			if ($i!=$folders_len-1) {
				$return .= " : ";
			}
		}

		if ($a_filter) {
			if ($filterisalreadyapplied) {
				$return .= ' <strong>'._('(Now Filtering)').'</strong> ';
			} else {
				$return .= ' <a href="'.util_make_url ('/softwaremap/trove_list.php?form_cat='.$form_cat);
				if ($discrim_url) {
					$return .= $discrim_url.','.$folders_ids[$folders_len-1];
				} else {
					$return .= '&amp;discrim='.$folders_ids[$folders_len-1];
				}
				$return .= '">'._('[Filter]').'</a> ';
			}
		}
		$proj_discrim_used[$folders_ids[0]] = 1;
		$isfirstdiscrim = 0;
	}
	if ($need_close_ul_tag)
	{
		$return .= '</li></ul>';
	}
	return $return;
}

/**
 * trove_getfullname() - Returns cat fullname
 *
 * @param		int		The node
 */
function trove_getfullname($node) {
	$res = db_query_params ('
		SELECT fullname
		FROM trove_cat
		WHERE trove_cat_id=$1',
			array($node));
	$row = db_fetch_array($res);
	return $row['fullname'];
}

/**
 * trove_getfullpath() - Returns a full path for a trove category
 *
 * @param		int		The node
 */
function trove_getfullpath($node) {
	$currentcat = $node;
	$first = 1;
	$return = '';

	while ($currentcat > 0) {
		$res = db_query_params ('
			SELECT trove_cat_id,parent,fullname
			FROM trove_cat
			WHERE trove_cat_id=$1',
			array($currentcat));
		$row = db_fetch_array($res);
		$return = $row["fullname"] . ($first ? "" : " :: ") . $return;
		$currentcat = $row["parent"];
		$first = 0;
	}
	return $return;
}

/**
 * trove_del_cat_id() - Delete the selected node (and its sub-nodes) in the trove tree
 *
 * @param		int		The node
 */
function trove_del_cat_id($node) {
        $res=db_query_params ('SELECT * FROM trove_cat WHERE parent=$1',
			array($node));
        if (!$res) {
                exit_error( _('Error In Trove Operation'), db_error());
        }
        if (db_numrows($res)>0) {
                for ($i=0; $i<db_numrows($res); $i++) {
                        trove_del_cat_id(db_result($res,$i,'trove_cat_id'));
                }
	}
        $res=db_query_params ('DELETE FROM trove_treesums WHERE trove_cat_id=$1',
			array($node));
        if (!$res) {
                exit_error( _('Error In Trove Operation'), db_error());
        }
        $res=db_query_params ('DELETE FROM trove_group_link WHERE trove_cat_id=$1',
			array($node));
        if (!$res) {
                exit_error( _('Error In Trove Operation'), db_error());
        }
        $res=db_query_params ('DELETE FROM trove_agg WHERE trove_cat_id=$1',
			array($node));
        if (!$res) {
                exit_error( _('Error In Trove Operation'), db_error());
        }
        $res=db_query_params ('DELETE FROM trove_cat WHERE trove_cat_id=$1',
			array($node));
        if (!$res || db_affected_rows($res)<1) {
                exit_error( _('Error In Trove Operation'), db_error());
        }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
