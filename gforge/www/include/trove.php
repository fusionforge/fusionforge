<?php
/**
 * trove.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
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
	$res_update = db_query('UPDATE trove_cat SET fullpath=\''
		.$myfullpath.'\',fullpath_ids=\''
		.$myfullpathids.'\' WHERE trove_cat_id='.$mynode);
	// now generate paths for all children by recursive call
	if($mynode!=0)
	{
		$res_child = db_query("
			SELECT trove_cat_id,fullname
			FROM trove_cat
			WHERE parent='$mynode'
			AND trove_cat_id!=0;
		", -1, 0, SYS_DB_TROVE);

		while ($row_child = db_fetch_array($res_child)) {
			trove_genfullpaths($row_child['trove_cat_id'],
				$myfullpath.' :: '.$row_child['fullname'],
				$myfullpathids.' :: '.$row_child['trove_cat_id']);
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
	$res_verifycat = db_query("
		SELECT trove_cat_id,fullpath_ids
		FROM trove_cat
		WHERE trove_cat_id='$trove_cat_id'
	", -1, 0, SYS_DB_TROVE);

	if (db_numrows($res_verifycat) != 1) return 1;
	$row_verifycat = db_fetch_array($res_verifycat);

	// if we didnt get a rootnode, find it
	if (!$rootnode) {
		$rootnode = trove_getrootcat($trove_cat_id);
	}

	// must first make sure that this is not a subnode of anything current
	$res_topnodes = db_query("
		SELECT trove_cat.trove_cat_id AS trove_cat_id,
			trove_cat.fullpath_ids AS fullpath_ids
		FROM trove_cat,trove_group_link 
		WHERE trove_cat.trove_cat_id=trove_group_link.trove_cat_id
		AND trove_group_link.group_id='$group_id'
		AND trove_cat.root_parent='$rootnode'");

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
	$res_checksubs = db_query("
		SELECT trove_cat_id
		FROM trove_group_link
		WHERE group_id='$group_id'
		AND trove_cat_root='$rootnode'");

	while ($row_checksubs = db_fetch_array($res_checksubs)) {
		// check against all subnodeids
		for ($i=0;$i<count($subnodeids);$i++) {
			if ($subnodeids[$i] == $row_checksubs['trove_cat_id']) {
				// then delete subnode
				db_query('DELETE FROM trove_group_link WHERE '
					.'group_id='.$group_id.' AND trove_cat_id='
					.$subnodeids[$i]);
			}
		}
	}

	// if we got this far, must be ok
	db_query('INSERT INTO trove_group_link (trove_cat_id,trove_cat_version,'
		.'group_id,trove_cat_root) VALUES ('.$trove_cat_id.','
		.time().','.$group_id.','.$rootnode.')');
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
		$res_par = db_query("
			SELECT parent
			FROM trove_cat
			WHERE trove_cat_id='$current_cat'");

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
	$res = db_query("
		SELECT trove_cat_id,fullname
		FROM trove_cat 
		WHERE parent=0");

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
	print "<BR><SELECT name=\"$name\">";
	print '  <OPTION value="0">None Selected'."\n";
	$res_cat = db_query("
		SELECT trove_cat_id,fullpath
		FROM trove_cat
		WHERE root_parent='$node'
		ORDER BY fullpath");

	while ($row_cat = db_fetch_array($res_cat)) {
		print '  <OPTION value="'.$row_cat['trove_cat_id'].'"';
		if ($selected == $row_cat['trove_cat_id']) print (' selected');
		print '>'.$row_cat['fullpath']."\n";
	}
	print "</SELECT>\n";
}

/**
 * trove_getcatlisting() - Gets discriminator listing for a group
 *
 * @param		int		The group ID
 * @param		bool	Whether filters have already been applied
 * @param		bool	Whether to print category links
 */
function trove_getcatlisting($group_id,$a_filter,$a_cats) {
	global $discrim_url;
	global $expl_discrim;
	global $form_cat;
	global $Language;

	$res_trovecat = db_query("
		SELECT trove_cat.fullpath AS fullpath,
			trove_cat.fullpath_ids AS fullpath_ids,
			trove_cat.trove_cat_id AS trove_cat_id 
		FROM trove_cat,trove_group_link
		WHERE trove_cat.trove_cat_id=trove_group_link.trove_cat_id
		AND trove_group_link.group_id='$group_id'
		ORDER BY trove_cat.fullpath");

	$return = '';
	if (db_numrows($res_trovecat) < 1) {
		$return .= $Language->getText('trove','not_categorized')
			.' <A href="/softwaremap/trove_list.php">'
			. $Language->getText('trove','title')
			.'</A>.';
	}

	// first unset the vars were using here
	$proj_discrim_used='';
	$isfirstdiscrim = 1;
	$return .= '<UL>';
	while ($row_trovecat = db_fetch_array($res_trovecat)) {
		$folders = explode(" :: ",$row_trovecat['fullpath']);
		$folders_ids = explode(" :: ",$row_trovecat['fullpath_ids']);
		$folders_len = count($folders);
		// if first in discrim print root category
		if (!$proj_discrim_used[$folders_ids[0]]) {
			if (!$isfirstdiscrim) $return .= '<BR>';
				$return .= ('<LI> '.$folders[0].': ');
		}

		// filter links, to add discriminators
		// first check to see if filter is already applied
		$filterisalreadyapplied = 0;
		for ($i=0;$i<sizeof($expl_discrim);$i++) {
			if ($folders_ids[$folders_len-1] == $expl_discrim[$i])
				$filterisalreadyapplied = 1;
			}
			// then print the stuff
			if ($proj_discrim_used[$folders_ids[0]]) $return .= ', ';

			if ($a_cats) $return .= '<A href="/softwaremap/trove_list.php?form_cat='
				.$folders_ids[$folders_len-1].$discrim_url.'">';
			$return .= ($folders[$folders_len-1]);
			if ($a_cats) $return .= '</A>';

			if ($a_filter) {
				if ($filterisalreadyapplied) {
					$return .= ' <b>(Now Filtering)</b> ';
				} else {
					$return .= ' <A href="/softwaremap/trove_list.php?form_cat='
						.$form_cat;
					if ($discrim_url) {
						$return .= $discrim_url.','.$folders_ids[$folders_len-1];
					} else {
						$return .= '&discrim='.$folders_ids[$folders_len-1];
					}
					$return .= '">[Filter]</A> ';
				}
			}
		$proj_discrim_used[$folders_ids[0]] = 1;
		$isfirstdiscrim = 0;
	}
	$return .= '</UL>';
	return $return;
}

/**
 * trove_getfullname() - Returns cat fullname
 *
 * @param		int		The node
 */
function trove_getfullname($node) {
	$res = db_query("
		SELECT fullname
		FROM trove_cat
		WHERE trove_cat_id='$node'");
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
		$res = db_query("
			SELECT trove_cat_id,parent,fullname
			FROM trove_cat 
			WHERE trove_cat_id='$currentcat'");
		$row = db_fetch_array($res);
		$return = $row["fullname"] . ($first ? "" : " :: ") . $return;
		$currentcat = $row["parent"];
		$first = 0;
	}
	return $return;
}

?>
