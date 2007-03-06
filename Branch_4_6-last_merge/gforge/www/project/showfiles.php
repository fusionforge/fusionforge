<?php
/*
	Temporary redirect to prevent breakage of existing installs/links
*/
$group_id = $HTTP_GET_VARS["group_id"];
header("Location: /frs/?group_id=$group_id");

?>
