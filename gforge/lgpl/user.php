<?php
/**
 * user.php - allows non-free software to be linked safely to GForge
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
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
