<?php
/*
	Temporary redirect to prevent breakage of existing installs/links
*/
require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';

$group_id = getIntFromRequest ('group_id');
$release_id = getIntFromRequest ('release_id');

if ($group_id) {
	if ($release_id) {
		header ("Location: ".util_make_uri("/frs/?group_id=$group_id&release_id=$release_id")); 
	} else {
		header ("Location: ".util_make_uri("/frs/?group_id=$group_id")); }
} else {
	header ("Location: ".util_make_uri("/")); 
}
?>
