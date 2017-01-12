<?php
/**
 * FusionForge ForgeLog class file
 *
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * ForgeLog simple log class.
 *
 * Log events are displayed in the browser or in a logfile.
 *
 * Configuration example for production system (in .ini):
 * log = error,auth
 * log_destination = file
 *
 * Configuration example for development system (in .ini):
 * log = error,auth,info,debug,sql
 * log_destination = browser
 *
 * @since 5.2
 */

require_once $gfcommon.'include/FFError.class.php';

class ForgeLog extends FFError
{
	static private $_logs = array();
	static private $_format = "{ip} {login} [{date}] {type} \"{message}\" {url}\n";

	/**
	 * @param string $message
	 * @param string $type
	 */
	static public function log($message, $type='error') {
		if (forge_get_config('log')) {
			if (array_intersect(explode(',', forge_get_config('log')), explode(',', $type))) {
				$date = new DateTime('now', new DateTimeZone( forge_get_config('default_timezone')));
				$user = function_exists('session_get_user')? session_get_user() : false;
				$login = $user ? $user->getUnixName() : '';

				self::$_logs[] = array(
					'date'    => $date->format(DateTime::ISO8601),
					'type'    => $type,
					'ip'      => getStringFromServer('REMOTE_ADDR'),
					'login'   => $login,
					'message' => $message,
					'url'     => getStringFromServer('REQUEST_URI')
				);
			}
		}
	}

	static public function getLogs() {
		return self::$_logs;
	}

	static public function getFormattedLogs($format='') {
		if (!self::$_logs) {
			return '';
		}
		if (!$format) {
			$format = self::$_format;
		}

		$output = '';
		foreach(self::$_logs as $log) {
			$msg = str_replace('{date}',    $log['date'], $format);
			$msg = str_replace('{type}',    $log['type'], $msg);
			$msg = str_replace('{ip}',      $log['ip'], $msg);
			$msg = str_replace('{login}',   $log['login'], $msg);
			$msg = str_replace('{message}', str_replace("\n", ' ', $log['message']), $msg);
			$msg = str_replace('{url}',     $log['url'], $msg);
			$output .= $msg;
		}
		return $output;
	}

	static public function saveLogs() {
		if (!self::$_logs) {
			return;
		}
		if (forge_get_config('log_destination') != 'file') {
			return;
		}
		$filename = forge_get_config('log_path').'/forge-'.date('Ymd').'.log';
		if (!file_exists($filename)) {
			touch($filename);
			chown($filename, forge_get_config('apache_user'));
		}
		$fp = fopen($filename, 'a+');
		fwrite($fp, self::getFormattedLogs()."\n");
		fclose($fp);
	}
}

register_shutdown_function('ForgeLog::saveLogs');
