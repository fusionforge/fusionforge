<?php

require('squal_pre.php');

$query = "SELECT COUNT(*) FROM users";

$result = db_query($query);

if (!$result || db_numrows($result) < 1) {
	echo 'mysql-bad';
} else {
	echo 'mysql-good';
}

echo "fuck the man!";

?>
