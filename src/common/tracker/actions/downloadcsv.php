<?php
/**
 * Copyright 2005 (c) GForge Group, LLC
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

require_once $gfcommon.'tracker/ArtifactFactory.class.php';

global $ath;

$headers = getIntFromRequest('headers');
$sep = getStringFromRequest('sep', ',');

$date = date('Y-m-d');

sysdebug_off();
header('Content-type: text/csv');
header('Content-disposition: filename="trackers-'.$date.'.csv"');

session_require_perm ('tracker', $ath->getID(), 'read') ;

$af = new ArtifactFactory($ath);
if (!$af || !is_object($af)) {
	exit_error(_('Could Not Get Factory'),'tracker');
} elseif ($af->isError()) {
	exit_error($af->getErrorMessage(),'tracker');
}

$offset = getStringFromRequest('offset');
$_sort_col = getStringFromRequest('_sort_col');
$_sort_ord = getStringFromRequest('_sort_ord');
$max_rows = getIntFromRequest('max_rows');
$set = getStringFromRequest('set');
$_assigned_to = getStringFromRequest('_assigned_to');
$_status = getStringFromRequest('_status');
$_changed_from = getStringFromRequest('_changed_from');

$af->setup($offset,$_sort_col,$_sort_ord,$max_rows,$set,$_assigned_to,$_status,$_changed_from);

$at_arr = $af->getArtifacts();

if ($headers) {
	echo 'artifact_id'.$sep.
		'status_id'.$sep.
		'status_name'.$sep.
		'priority'.$sep.
		'submitter_id'.$sep.
		'submitter_name'.$sep.
		'assigned_to_id'.$sep.
		'assigned_to_name'.$sep.
		'open_date'.$sep.
		'close_date'.$sep.
		'last_modified_date'.$sep.
		'summary'.$sep.
		'details'.$sep.
		'_votes'.$sep.
		'_voters'.$sep.
		'_votage';

	//
	//	Show the extra fields
	//
	$ef = $ath->getExtraFields();
	$keys=array_keys($ef);
	for ($i=0; $i<count($keys); $i++) {
		echo $sep.'"'.$ef[$keys[$i]]['field_name'].'"';
	}
	echo "\n";
}

for ($i=0; $i<count($at_arr); $i++) {

	$open_date   = $at_arr[$i]->getOpenDate() ? date(_('Y-m-d H:i'),$at_arr[$i]->getOpenDate()) : '';
	$update_date = $at_arr[$i]->getLastModifiedDate() ? date(_('Y-m-d H:i'),$at_arr[$i]->getLastModifiedDate()) : '';
	$close_date  = $at_arr[$i]->getCloseDate()? date(_('Y-m-d H:i'),$at_arr[$i]->getCloseDate()): '';

	$at_arr[$i]->getVotes();
	echo $at_arr[$i]->getID().$sep.
		$at_arr[$i]->getStatusID().$sep.
		'"'.$at_arr[$i]->getStatusName().'"'.$sep.
		$at_arr[$i]->getPriority().$sep.
		$at_arr[$i]->getSubmittedBy().$sep.
		'"'.$at_arr[$i]->getSubmittedRealName().'"'.$sep.
		$at_arr[$i]->getAssignedTo().$sep.
		'"'.$at_arr[$i]->getAssignedRealName().'"'.$sep.
		'"'.$open_date.'"'.$sep.
		'"'.$close_date.'"'.$sep.
		'"'.$update_date.'"'.$sep.
		'"'.fix4csv($at_arr[$i]->getSummary()).'"'.$sep.
		'"'.fix4csv($at_arr[$i]->getDetails()).'"'.$sep.
		$votes[0].$sep.
		$votes[1].$sep.
		$votes[2];

	//
	//	Show the extra fields
	//
 	$efd = $at_arr[$i]->getExtraFieldDataText();
 	foreach ( $efd as $efd_pair ) {
 		$value = $efd_pair["value"];
 		echo $sep.'"'. fix4csv($value) .'"';
 	}
 	echo "\n";
}

function fix4csv ($value) {
	$value = util_unconvert_htmlspecialchars( $value );
	$value = str_replace("\r\n", "\n", $value);
	$value = str_replace('"', '""', $value);
	return $value;
}
