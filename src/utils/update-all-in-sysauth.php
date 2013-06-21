#!/usr/bin/php -f

<?php
require "/usr/share/gforge/common/include/env.inc.php";
require_once $gfcommon."include/pre.php";

foreach (user_get_all_users() as $u) {
	$params = array();
	$params['user'] = $u;
	$params['user_id'] = $u->getID();
	plugin_hook ('user_update', $params);
}

foreach (group_get_all_projects() as $g) {
	$params = array();
	$params['group'] = $g;
	$params['group_id'] = $g->getID();
	plugin_hook ('group_update', $params);
}
