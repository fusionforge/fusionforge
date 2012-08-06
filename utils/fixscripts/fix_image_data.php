<?php

/*

	A simple fix script

	We had some problem of unknown origin
	where a bunch of accounts had unix_uids of 1

*/
exit;
require_once $gfcommon.'include/pre.php';

if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
        exit_permission_denied();
}

$res=db_query_params('SELECT id,bin_data FROM db_images ORDER BY id ASC',array(),50,$offset);

$rows=db_numrows($res);

echo "<br />affect users: $rows<br />";

for ($i=0; $i<$rows; $i++) {

	echo "<br />fixing: ".db_result($res,$i,'id');

	$data=addslashes(base64_encode(db_result($res,$i,'bin_data')));
	$res2=db_query_params('UPDATE db_images SET bin_data=$1 WHERE id=$2',
			      array ($data,
				     db_result($res,$i,'id')));
	if (!$res2 || db_affected_rows($res2) < 1) {
		echo db_error();
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
