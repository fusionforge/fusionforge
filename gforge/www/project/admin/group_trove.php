<?php
/**
 * Project Admin page to edit Trove categorization of the project
 *
 * This page is linked from index.php
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('pre.php');    
require_once('trove.php');
require_once('www/project/admin/project_admin_utils.php');

$group_id = getIntFromRequest('group_id');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// Check for submission. If so, make changes and redirect

if (getStringFromRequest('submit') && getStringFromRequest('root1')) {
	// XXX ogi: What's $rm_id?
	group_add_history ('Changed Trove',$rm_id,$group_id);

	// there is at least a $root1[xxx]
	while (list($rootnode,$value) = each(getStringFromRequest('root1'))) {
		// check for array, then clear each root node for group
		db_query("
			DELETE FROM trove_group_link
			WHERE group_id='$group_id'
			AND trove_cat_root='$rootnode'
		");
		
		for ($i=1;$i<=$GLOBALS['TROVE_MAXPERROOT'];$i++) {
			$varname = 'root'.$i;
			// check to see if exists first, then insert into DB
			//@TODO change this to use the escaping utils
			$category = $_REQUEST[$varname][$rootnode];
			if ($category) {
				trove_setnode($group_id,$category,$rootnode);
			}
		}
	}
	session_redirect('/project/admin/?group_id='.$group_id);
}

project_admin_header(array('title'=>$Language->getText('project_admin_group_trove','title'),'group'=>$group_id));

?>
<?php echo $Language->getText('project_admin_group_trove','intro') ?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<?php

$CATROOTS = trove_getallroots();
while (list($catroot,$fullname) = each($CATROOTS)) {
	print "\n<hr />\n<p><strong>$fullname</strong> ".help_button('trove_cat',$catroot)."</p>\n";

	$res_grpcat = db_query("
		SELECT trove_cat_id
		FROM trove_group_link
		WHERE group_id='$group_id'
		AND trove_cat_root='$catroot'");

	for ($i=1;$i<=$GLOBALS['TROVE_MAXPERROOT'];$i++) {
		// each drop down, consisting of all cats in each root
		$name= "root$i"."[$catroot]";
		// see if we have one for selection
		if ($row_grpcat = db_fetch_array($res_grpcat)) {
			$selected = $row_grpcat["trove_cat_id"];	
		} else {
			$selected = 0;
		}
		trove_catselectfull($catroot,$selected,$name);
	}
}

?>

<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<p><input type="submit" name="submit" value="<?php echo $Language->getText('project_admin_group_trove','update_changes') ?>" /></p>
</form>

<?php

project_admin_footer(array());

?>
