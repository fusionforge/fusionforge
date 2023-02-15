<?php
/**
 * stopforumspamPlugin Class
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class stopforumspamPlugin extends Plugin {
	function __construct($id=0) {
		parent::__construct($id) ;
		$this->name = "stopforumspam";
		$this->text = "StopForumSpam"; // To show in the tabs, use...
		$this->_addHook('account_register_checks');
		$this->_addHook('delete_user_form');
		$this->_addHook('delete_user_form_submit');
	}

	function CallHook($hookname, &$params) {
		global $use_stopforumspamplugin,$G_SESSION,$HTML;
		if (!in_array('error',$params)) {
			$params['error'] = array();
		}
		if ($hookname == "account_register_checks") {
			$email = $params['email'];
			if ($this->check_data($email,'email')) {
				$params['block'] = true;
				array_push($params['error'],sprintf(_("Email %s blocked by stopforumspam plugin"),htmlspecialchars($email)));
			}
			$ip = $_SERVER['REMOTE_ADDR'];
			if (preg_match(':',$ip)) {
				$family = "ipv6";
				$check_ip = true;
			} else {
				$family = "ipv4";
				$check_ip = false;
			}
			if ($check_ip && $this->check_data($ip,$family)) {
				$params['block'] = true;
				array_push($params['error'],sprintf(_("IP address %s blocked by stopforumspam plugin"),htmlspecialchars($ip)));
			}
 		}
		if ($hookname == "delete_user_form") {
			$api_key = forge_get_config('api_key','stopforumspam');
			if ($api_key == '') {
				return;
			}

			$user = $params['user'];
			$res = db_query_params ('SELECT ip_addr FROM user_session WHERE user_id=$1 AND ip_addr != $2 ORDER BY time DESC', array($user->getId(), ''));
			if (db_numrows($res) == 0) {
				return;
			}
			$ip = db_result($res,0,0);
			?>
	<input id="report-to-stopforumspam"  type="checkbox" name="report_to_stopforumspam" value="1" />
	<label for="report-to-stopforumspam"><?php echo _("Also report email and IP address for this user to stopforumspam.com"); ?></label>&nbsp;
			<?php
		}
		if ($hookname == "delete_user_form_submit") {
			if (getStringFromRequest('report_to_stopforumspam') != '1') {
				return;
			}
			$api_key = forge_get_config('api_key','stopforumspam');
			if ($api_key == '') {
				return;
			}

			$user = $params['user'];
			$res = db_query_params ('SELECT ip_addr FROM user_session WHERE user_id=$1 AND ip_addr != $2 ORDER BY time DESC', array($user->getId(), ''));
			if (db_numrows($res) == 0) {
				return;
			}
			$ip = db_result($res,0,0);
			$url = "https://www.stopforumspam.com/add.php";
			$url .= "?username=".urlencode($user->getUnixName());
			$url .= "&ip_addr=".urlencode($ip);
			$url .= "&evidence=";
			$url .= "&email=".urlencode($user->getEmail());
			$url .= "&api_key=$api_key";
		}
	}

	function check_data($datatype, $entry) {
		$res = db_query_params ('SELECT count(last_seen) FROM plugin_stopforumspam_known_entries WHERE datatype=$1 AND entry=$2', array($datatype, $data));
		if (db_result($res,0,0) > 0) {
			return true;
		} else {
			return false;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
