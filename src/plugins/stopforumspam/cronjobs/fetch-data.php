#! /usr/bin/php -f
<?php
/**
 * FusionForge source control management
 *
 * Copyright 2023, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require 'env.inc.php';
require_once $gfwww.'include/squal_pre.php';
require_once $gfcommon.'include/cron_utils.php';

$sources = array(
	"email" => array(
		"1day" => array(
			"refreshperiod" => 3500, // Slightly less than one hour
			"url" => "https://www.stopforumspam.com/downloads/listed_email_1.gz",
			),
		"365days" => array(
			"refreshperiod" => 86000, // Slightly less than one day
			"url" => "https://www.stopforumspam.com/downloads/listed_email_365.gz",
			),
		),
	"ipv4" => array(
		"1day" => array(
			"refreshperiod" => 3500,
			"url" => "https://www.stopforumspam.com/downloads/listed_ip_1.gz",
			),
		"365days" => array(
			"refreshperiod" => 86000,
			"url" => "https://www.stopforumspam.com/downloads/listed_ip_365.gz",
			),
		),
	"ipv6" => array(
		"1day" => array(
			"refreshperiod" => 3500,
			"url" => "https://www.stopforumspam.com/downloads/listed_ip_1_ipv6.gz",
			),
		"365days" => array(
			"refreshperiod" => 86000,
			"url" => "https://www.stopforumspam.com/downloads/listed_ip_365_ipv6.gz",
			),
		),
	);

$now = time();

// Fetch and store data from stopforumspam.com
foreach ($sources as $type => $periods) {
	foreach ($periods as $period => $data) {

		if ($period == "365days") {
			continue;
		}

		$res = db_query_params ('SELECT last_fetch FROM plugin_stopforumspam_last_fetch WHERE datatype=$1 AND period=$2', array($type, $period));

		if (!$res) {
			$err =  "Error: Database Query Failed: ".db_error();
			cron_debug($err);
			cron_entry(23,$err);
			exit;
		}

		$lastfetch = 0;
		while ( $row = db_fetch_array($res) ) {
			$lastfetch = $row['last_fetch'];
		}

		if ($now - $lastfetch < $data['refreshperiod']) {
			continue;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $data['url']);
		$gzbody = curl_exec($ch);
		$body = gzinflate($gzbody);

		$line = strtok($body, PHP_EOL);
		while ($line !== FALSE) {
			$res = db_query_params ('SELECT count(last_seen) FROM plugin_stopforumspam_known_entries WHERE datatype=$1 AND entry=$2', array($type, $line));
			if (db_result($res,0,0) > 0) {
				db_query_params ('UPDATE plugin_stopforumspam_known_entries SET last_seen=$1 WHERE datatype=$2 AND entry=$3', array($now, $type, $line));
			} else {
				db_query_params ('INSERT INTO plugin_stopforumspam_known_entries (datatype, entry, last_seen) VALUES ($1, $2, $3)', array($type, $line, $now));
			}
			$line = strtok(PHP_EOL);
		}
	}
}

// Expire old data

$expire_horizon = $now - 7*86400;

$res = db_query_params ('DELETE FROM plugin_stopforumspam_known_entries WHERE last_seen < $1', array($expire_horizon));


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
