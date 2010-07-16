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

$res=db_query_params ('SELECT * FROM users WHERE unix_uid=1',
			array());

$rows=db_numrows($res);

echo "<br />affect users: $rows<br />";

for ($i=0; $i<$rows; $i++) {

	echo "<br />fixing: ".db_result($res,$i,'user_id');

	$user=user_get_object(db_result($res,$i,'user_id'));
	if (!$user->setUpUnixUID()) {
		echo $user->getErrorMessage();
	}

}


?>
