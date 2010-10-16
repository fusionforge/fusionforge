<?php
/**
 * Site Admin: Trove Admin: browse entire Trove tree
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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


require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/trove.php';
require_once $gfwww.'admin/admin_utils.php';

// print current node, then all subnodes
function printnode ($nodeid,$text) {
	print ('<br />');

	if (!isset($GLOBALS['depth']))
		$GLOBALS['depth'] = 0;

	for ($i=0;$i<$GLOBALS['depth'];$i++) {
		print "&nbsp; &nbsp; ";
	}

	$res_cat = db_query_params ('SELECT * FROM trove_cat WHERE trove_cat_id=$1', array($nodeid));
	if (db_numrows($res_cat)>=1) {
		$title = db_result($res_cat, 0, 'description');
	} else {
		$title = '';
	}

	print html_image('ic/cfolder15.png','15','13');
	print ('&nbsp; <span class="trove-nodes" title="'.$title.'">'.$text.'</span> ');
	if ($nodeid == 0) {
		print ('<a href="trove_cat_add.php?parent_trove_cat_id='.$nodeid.'">['._('Add').']</a> ');
	} else {
		print ('<a href="trove_cat_edit.php?trove_cat_id='.$nodeid.'">['._('Edit').']</a> ');
		print ('<a href="trove_cat_add.php?parent_trove_cat_id='.$nodeid.'">['._('Add').']</a> ');
	}

	$GLOBALS['depth']++;
	$res_child = db_query_params ('
		SELECT trove_cat_id,fullname FROM trove_cat 
		WHERE parent=$1
		AND trove_cat_id!=0 ORDER BY fullname;
	',
			array($nodeid)) ;

	while ($row_child = db_fetch_array($res_child)) {
		printnode($row_child["trove_cat_id"],$row_child["fullname"]);
	}
	$GLOBALS["depth"]--;
}

// ########################################################

html_use_tooltips();

site_admin_header(array('title'=>_('Site Admin: Trove - Category List')));

?>

<h1><?php echo _('Browse Trove Tree'); ?></h1>

<?php

printnode(0,"root");

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
