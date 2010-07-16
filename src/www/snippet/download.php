<?php
/**
  *
  * SourceForge Code Snippets Repository
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
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
	echo 'Error';
}

?>
