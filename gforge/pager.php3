<?php

require ('squal_pre.php');

if (!$conn) {
	echo "false\n";
} else {
	$result=db_query("SELECT count(*) FROM users");

	if (!$result || db_numrows($result) < 1) {
		echo "false\n";
	} else {
		echo "true\n";
	}
}

?>
