<?php
/**
 *  user.php - allows non-free software to be linked safely to GForge
 *
 *  THIS FILE IS RELEASED UNDER THE LGPL
 *
 *  ANY MODIFICATIONS MUST BE ALSO RELEASED UNDER LGPL
 *
 */

$confirmed_user_id=false;

function get_user_id() {

	global $confirmed_user_id, $session_ser, $sys_session_key;

	if (!$confirmed_user_id && $session_ser) {

		//echo $session_ser;
		$temp = explode('-*-', $session_ser);
		$check_vars = base64_decode($temp[0]);

		$hash = md5($check_vars.$sys_session_key);

		if ($hash != $temp[1]) {
			return false;
		}

		$t2 = explode('-*-', $check_vars);
		$confirmed_user_id=$t2[0];

	}

	return $confirmed_user_id;

}

function is_logged_in() {
	return get_user_id();
}

?>
