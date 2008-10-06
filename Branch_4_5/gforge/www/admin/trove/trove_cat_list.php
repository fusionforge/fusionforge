<?php
/**
 * Site Admin: Trove Admin: browse entire Trove tree
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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
require_once('www/include/trove.php');
require_once('www/admin/admin_utils.php');

// print current node, then all subnodes
function printnode ($nodeid,$text) {
	global $Language;
	print ('<br />');

	for ($i=0;$i<$GLOBALS['depth'];$i++) {
		print "&nbsp; &nbsp; ";
	}

	print html_image('ic/cfolder15.png','15','13',array());
	print ('&nbsp; '.$text." ");
	if ($nodeid == 0) {
		print ('<a href="trove_cat_add.php?parent_trove_cat_id='.$nodeid.'">['.$Language->getText('admin_trove_cat_list','add').']</a> ');
	} else {
		print ('<a href="trove_cat_edit.php?trove_cat_id='.$nodeid.'">['.$Language->getText('admin_trove_cat_list','edit').']</a> ');
		print ('<a href="trove_cat_add.php?parent_trove_cat_id='.$nodeid.'">['.$Language->getText('admin_trove_cat_list','add').']</a> ');
		print (help_button('trove_cat',$nodeid)."\n");
	}

	$GLOBALS['depth']++;
	$res_child = db_query("
		SELECT trove_cat_id,fullname FROM trove_cat 
		WHERE parent='$nodeid'
		AND trove_cat_id!=0;
	");
	while ($row_child = db_fetch_array($res_child)) {
		printnode($row_child["trove_cat_id"],$row_child["fullname"]);
	}
	$GLOBALS["depth"]--;
}

// ########################################################

site_admin_header(array('title'=>$Language->getText('admin_trove_cat_list','title')));

?>

<h3><?php echo $Language->getText('admin_trove_cat_list','browse_trove_tree'); ?></h3>

<?php

printnode(0,"root");

site_admin_footer(array());

?>
