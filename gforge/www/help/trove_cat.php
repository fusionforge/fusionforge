<?php
/**
 * GForge Help Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

require_once('../env.inc.php');
require_once('pre.php');

$trove_cat_id = getIntFromRequest('trove_cat_id');

$res_cat = db_query("
	SELECT *
	FROM trove_cat
	WHERE trove_cat_id='$trove_cat_id'");

if (db_numrows($res_cat)<1) {
	print _('No such trove category');
	exit;
}

$row_cat = db_fetch_array($res_cat);

help_header("Trove Category - ".$row_cat['fullname']);

print '<table width="100%" cellpadding="0" cellspacing="0" border="0">'."\n";
print '<tr><td>'._('Full Category Name').':</td><td><strong>'.$row_cat['fullname']."</strong></td>\n";
print '<tr><td>'._('Short Name').':</td><td><strong>'.$row_cat['shortname']."</strong></td>\n";
print "</table>\n";
print '<p>'._('Description').':<br /><em>'.$row_cat['description'].'</em>'."</p>\n";

help_footer();

?>
