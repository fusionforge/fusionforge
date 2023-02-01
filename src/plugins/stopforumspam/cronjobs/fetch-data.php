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

require_once dirname(__FILE__) . '/../../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
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

		db_prepare ('INSERT INTO plugin_stopforumspam_known_entries (datatype, entry, last_seen) VALUES ($1, $2, $3) ON CONFLICT (datatype,entry) DO UPDATE SET last_seen=$3', 'insert_into_plugin_stopforumspam_known_entries');

        $fp = fopen('compress.zlib://'.$data['url'],'r');
		while (($line = fgets($fp, 4096)) !== false) {
			db_execute ('insert_into_plugin_stopforumspam_known_entries', array($type, $line, $now));
		}

		db_query_params ('INSERT INTO plugin_stopforumspam_last_fetch (datatype, period, last_fetch) VALUES ($1, $2, $3) ON CONFLICT (datatype, period) DO UPDATE SET last_fetch=$3', array($type, $period, $now));

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
