<?php
/**
  *
  * Site Admin: Trove Admin: browse entire Trove tree
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/include/trove.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

// #######################################################

// print current node, then all subnodes
function printnode ($nodeid,$text) {
	print ('<BR>');

	for ($i=0;$i<$GLOBALS[depth];$i++) {
		print "&nbsp; &nbsp; ";
	}

	print html_image('images/ic/cfolder15.png','15','13',array());
	print ('&nbsp; '.$text." ");
	print ('<A href="trove_cat_edit.php?trove_cat_id='.$nodeid.'">[Edit]</A> ');
	print (help_button('trove_cat',$nodeid)."\n");

	$GLOBALS['depth']++;
	$res_child = db_query("
		SELECT trove_cat_id,fullname FROM trove_cat 
		WHERE parent='$nodeid'
	");
	while ($row_child = db_fetch_array($res_child)) {
		printnode($row_child["trove_cat_id"],$row_child["fullname"]);
	}
	$GLOBALS["depth"]--;
}

// ########################################################

site_admin_header(array('title'=>'Site Admin: Trove - Category List'));

?>

<h3>Browse Trove Tree</h3>

<?php

printnode(0,"root");

site_admin_footer(array());

?>
