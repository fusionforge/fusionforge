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

require_once('../../env.inc.php');
require_once('pre.php');    
require_once('trove.php');
require_once('www/project/admin/project_admin_utils.php');

$group_id = getIntFromRequest('group_id');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// Check for submission. If so, make changes and redirect

if (getStringFromRequest('submit') && getStringFromRequest('root1')) {
	 if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	 }
	group_add_history ('Changed Trove',$group_id,$group_id);

	// there is at least a $root1[xxx]
	$allroots = array();
	$allroots = getStringFromRequest('root1');
	//$eachroot = ;//must make this bypass because it wouldn´t compile otherwise
	while (list($rootnode,$value) = each($allroots)) {
		// check for array, then clear each root node for group
		db_query("
			DELETE FROM trove_group_link
			WHERE group_id='$group_id'
			AND trove_cat_root='$rootnode'
		");
		
		for ($i=1;$i<=$TROVE_MAXPERROOT;$i++) {
			$varname = 'root'.$i;
			// check to see if exists first, then insert into DB
			//@TODO change this to use the escaping utils
			$var_aux = getStringFromRequest($varname);
			$category = $var_aux[$rootnode];
			if ($category) {
				trove_setnode($group_id,$category,$rootnode);
			}
		}
	}
	session_redirect('/project/admin/?group_id='.$group_id);
}

project_admin_header(array('title'=>_('Group Trove Information'),'group'=>$group_id));

?>
<?php echo _('<h3>Edit Trove Categorization</h3><p>Select up to three locations for this project in each of the Trove root categories. If the project does not require any or all of these locations, simply select "None Selected".</p><p> IMPORTANT: Projects should be categorized in the most specific locations available in the map. Simultaneous categorization in a specific category AND a parent category will result in only the more specific categorization being accepted</p>.') ?>

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

	for ($i=1;$i<=$TROVE_MAXPERROOT;$i++) {
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
<input type="hidden" name="form_key" value="<?php echo form_generate_key();?>"/>
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<p><input type="submit" name="submit" value="<?php echo _('Update All Category Changes') ?>" /></p>
</form>

<?php

project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
