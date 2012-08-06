#! /usr/bin/php -f
<?php
/**
 * FusionForge source control management
 *
 * Copyright 2009, Roland Mas
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

require 'env.inc.php';
require_once $gfwww.'include/squal_pre.php';
require_once $gfcommon.'include/cron_utils.php';

// MailingList backend class
/* This is only sample
require_once $gfplugins.'oauthprovider/include/BackendHelloworld.class.php' ;


$res = db_query_params ('SELECT id,type, parameters FROM system_event WHERE status=$1 ORDER BY id DESC',
			array ('1'));
if (!$res) {
	printf('Unable to get list of events: '.db_error());
	return false;
}

while ($data = db_fetch_array ($res)) {
	if($data['type'] == 'HELLOWORLD_CREATE') {
		$result = BackendHelloworld::instance()->createList($data['parameters']);
	} elseif ($data['type'] == 'HELLOWORLD_DELETE') {
		$result = BackendHelloworld::instance()->deleteList($data['parameters']);
	}
	$result ? $log="DONE":$test="ERROR";
	$events[$data['id']]=$log;
	echo "\n Event ".$data['id']." : ".$data['type']." ".$log." for list id=".$data['parameters'];
}
if(isset($events)) {
	foreach($events as $event_id => $log) {
		$sql = "UPDATE system_event SET end_date=$1, log=$2, status='3' WHERE id=$3;";
		$result = db_query_params($sql,array(time(),$log,$event_id));
		if (!$result) {
			printf('Unable to update the list of events: '.db_error());
			return false;
		}
	}

}
*/


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
