<?php

/*

	A simple fix script

	We had some problem of unknown origin
	where a bunch of accounts had unix_uids of 1

*/
exit;
require('squal_pre.php');

if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
        exit_permission_denied();
}

$res=db_query("SELECT id,bin_data FROM db_images ORDER BY id ASC",50,$offset);

$rows=db_numrows($res);

echo "<br />affect users: $rows<br />";

for ($i=0; $i<$rows; $i++) {

	echo "<br />fixing: ".db_result($res,$i,'id');

	$data=addslashes(base64_encode(db_result($res,$i,'bin_data')));
	$res2=db_query("UPDATE db_images SET bin_data='$data' WHERE id='". db_result($res,$i,'id') ."'");
	if (!$res2 || db_affected_rows($res2) < 1) {
		echo db_error();
	}

}

?>
