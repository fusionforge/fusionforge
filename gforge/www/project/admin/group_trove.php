<?php
/**
  *
  * Project Admin page to edit Trove categorization of the project
  *
  * This page is linked from index.php
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');    
require_once('trove.php');
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// Check for submission. If so, make changes and redirect

if ($GLOBALS['submit'] && $root1) {
	group_add_history ('Changed Trove',$rm_id,$group_id);

	// there is at least a $root1[xxx]
	while (list($rootnode,$value) = each($root1)) {
		// check for array, then clear each root node for group
		db_query("
			DELETE FROM trove_group_link
			WHERE group_id='$group_id'
			AND trove_cat_root='$rootnode'
		");
		
		for ($i=1;$i<=$GLOBALS['TROVE_MAXPERROOT'];$i++) {
			$varname = 'root'.$i;
			// check to see if exists first, then insert into DB
			if (${$varname}[$rootnode]) {
				trove_setnode($group_id,${$varname}[$rootnode],$rootnode);
			}
		}
	}
	session_redirect('/project/admin/?group_id='.$group_id);
}

project_admin_header(array('title'=>'Group Trove Information','group'=>$group_id));

?>

<h3>Edit Trove Categorization</h3>

<p>
Select up to three locations for this project in each of the
Trove root categories. If the project does not require any or all of these
locations, simply select "None Selected".
</p>

<p>
IMPORTANT: Projects should be categorized in the most specific locations
available in the map. Simulteneous categorization in a specific category
AND a parent category will result in only the more specific categorization
being accepted.
</p>

<form action="<?php echo $PHP_SELF; ?>" method="post">

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
<p><input type="submit" name="submit" value="Update All Category Changes" /></p>
</form>

<?php

project_admin_footer(array());

?>
