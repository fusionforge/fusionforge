<?php
/*
	Temporary redirect to prevent breakage of existing installs/links
*/
require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$group_id = getIntFromRequest ('group_id');
$release_id = getIntFromRequest ('release_id');

if ($group_id) {
	if ($release_id) {
		session_redirect('/frs/?group_id='.$group_id.'&release_id='.$release_id);
	} else {
		session_redirect('/frs/?group_id='.$group_id); }
} else {
	session_redirect('/');
}
?>
