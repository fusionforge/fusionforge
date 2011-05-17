<?php
/**
 * Code Snippets Repository
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://fusionforge.org
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

$no_gz_buffer=true;

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require $gfwww.'snippet/snippet_utils.php';

global $SCRIPT_EXTENSION;

$id = getIntFromRequest('id');
$result = db_query_params ('SELECT language,code FROM (snippet NATURAL JOIN snippet_version) WHERE snippet_version_id = $1',
			   array ($id));

if ($result && db_numrows($result) > 0) {
	header('Content-Type: text/plain');
	header('Content-Disposition: attachment; filename="snippet_'.$id.$SCRIPT_EXTENSION[db_result($result,0,'language')].'"');
	if (strlen(db_result($result,0,'code')) > 1) {
		echo util_unconvert_htmlspecialchars( db_result($result,0,'code') );
	} else {
		echo 'nothing in here';
	}
} else {
	exit_error(_('Error'));
}

?>
