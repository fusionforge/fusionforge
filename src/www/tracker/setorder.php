<?php
require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';

if (session_loggedin()) {
	$order = getStringFromRequest('order');
	if ($order == 'up' || $order == 'down') {
		$u =& session_get_user();
		$u->setPreference('tracker_messages_order', $order);
	}
}
