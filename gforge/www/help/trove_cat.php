<?php
/**
  *
  * SourceForge Help Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

$res_cat = db_query("
	SELECT *
	FROM trove_cat
	WHERE trove_cat_id='$trove_cat_id'");

if (db_numrows($res_cat)<1) {
	print "No such trove category";
	exit;
}

$row_cat = db_fetch_array($res_cat);

help_header("Trove Category - ".$row_cat['fullname']);

print '<table width="100%" cellpadding="0" cellspacing="0" border="0">'."\n";
print '<tr><td>Full Category Name:</td><td><strong>'.$row_cat['fullname']."</strong></td>\n";
print '<tr><td>Short Name:</td><td><strong>'.$row_cat['shortname']."</strong></td>\n";
print "</table>\n";
print '<p>Description:<br /><em>'.$row_cat['description'].'</em>'."</p>\n";

help_footer();

?>
