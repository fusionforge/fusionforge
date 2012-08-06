<?php
/**
 * Trove Admin: browse entire Trove tree
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://fusionforge.org/
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

require_once('pre.php');
require_once('www/include/trove.php');
require_once('www/admin/admin_utils.php');

session_require_global_perm ('forge_admin');

// #######################################################

// print current node, then all subnodes
function printnode ($nodeid,$text) {
	print ('<br />');

	for ($i=0;$i<$GLOBALS[depth];$i++) {
		print "&nbsp; &nbsp; ";
	}

	print html_image('ic/cfolder15.png','15','13',array());
	print ('&nbsp; '.$text." ");
	print (url_make_link ("trove_cat_add.php?parent_trove_cat_id='.$nodeid.'",
			      "["._('Add')."]"));
	if ($nodeid != 0) {
		print (url_make_link ("trove_cat_edit.php?parent_trove_cat_id='.$nodeid.'",
				      "["._('Edit')."]"));
		print (help_button('trove_cat',$nodeid)."\n");
	}

	$GLOBALS['depth']++;
	$res_child = db_query_params ('
		SELECT trove_cat_id,fullname FROM trove_cat
		WHERE parent=$1
		AND trove_cat_id!=0;
	',
			array($nodeid));
	while ($row_child = db_fetch_array($res_child)) {
		printnode($row_child["trove_cat_id"],$row_child["fullname"]);
	}
	$GLOBALS["depth"]--;
}

// ########################################################

site_admin_header(array('title'=>_('Site Admin: Trove - Category List')));

?>

<h3><?php echo _('Browse Trove Tree'); ?></h3>

<?php

printnode(0,"root");

site_admin_footer(array());

?>
