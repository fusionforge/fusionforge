#! /usr/bin/php5
<?php
/**
 * FusionForge Group Docman updater
 *
 * Copyright 2004 GForge, LLC
 * Copyright 2011, Iñigo Martinez (inigoml)
 * http://fusionforge.org/
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

require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfwww.'include/squal_pre.php';

$res = db_query_params('SELECT docid,filesize FROM doc_data',
			array());

if (!$res) {		// error
	echo db_error();
	exit(1);
} 

echo "Updating ".db_numrows($res)." documents\n";

for ($i=0; $i < db_numrows($res); $i++) {
	db_begin();
	$docid = db_result($res, $i, 'docid');
	$base64_data_res = db_query_params('SELECT data FROM doc_data where docid='.$docid,array());
	$base64_data = db_result($base64_data_res, 0, 'data');
	$data = base64_decode($base64_data);
	$size = strlen($data);
	$res2 = db_query_params('UPDATE doc_data SET filesize=$1 WHERE docid=$2',
				array ($size,
					$docid));
	if (!$res2) {
		echo "Couldn't update document #".$docid.":".db_error()."\n";
		db_rollback();
		exit(1);
	}
	echo "Updated document #".$docid." with size ".$size."\n";
	db_commit();
}

echo "SUCCESS\n";

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
