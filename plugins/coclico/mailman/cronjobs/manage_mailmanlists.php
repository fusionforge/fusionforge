#! /usr/bin/php5 -f
<?php
/**
 * FusionForge source control management
 *
 * Copyright 2009, Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */
require 'env.inc.php';
require_once $gfwww.'include/squal_pre.php';
require_once $gfcommon.'include/cron_utils.php';

// MailingList backend class
require_once $gfplugins.'mailman/include/BackendMailmanList.class.php' ;
			 

$res = db_query_params ('SELECT id,type, parameters FROM system_event WHERE status=$1 ORDER BY id DESC',
			array ('1')); 
if (!$res) {
	printf('Unable to get list of events: '.db_error());
	return false;
}

while ($data = db_fetch_array ($res)) {
	if($data['type'] == 'MAILMAN_LIST_CREATE') {
		BackendMailmanList::instance()->createList($data['parameters']);
	} elseif ($data['type'] == 'MAILMAN_LIST_DELETE') {
		BackendMailmanList::instance()->deleteList($data['parameters']);
	}
	$events[$data['id']]=$data['parameters'];
	echo "events[".$data['id']."]=".$data['parameters'];
}
if(isset($events)) {
	foreach($events as $event_id => $list_id) {
		$sql = "UPDATE system_event SET end_date='".time()."', log='DONE', status='3' WHERE id='".$event_id."';"; 
		$result = db_query($sql);
		if (!$result) {
			printf('Unable to update the list of events: '.db_error());
			return false;
		}
		$sql = "UPDATE mail_group_list SET status='3' WHERE group_list_id='".$list_id."';"; 
		$result = db_query($sql);
		if (!$result) {
			printf('Unable to update the list of events: '.db_error());
			return false;
		}
	}

}


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
