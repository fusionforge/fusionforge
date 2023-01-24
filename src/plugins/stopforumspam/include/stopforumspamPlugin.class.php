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
			} else {
				$family = "ipv4";
			}
			if ($this->check_data($ip,$family)) {
				$params['block'] = true;
				array_push($params['error'],sprintf(_("IP address %s blocked by stopforumspam plugin"),htmlspecialchars($ip)));
			}
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
