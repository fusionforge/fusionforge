<?php
/**
 * Copyright 2005 (c) GForge Group, LLC
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
require_once('common/tracker/ArtifactFactory.class');

header('Content-type: text/comma-separated-values');
list($year, $month) = explode('-', date('Y-m'));
header('Content-disposition: filename="tracker_report-'.$year.'-'.$month.'.csv"');

if (!$ath->userCanView()) {
	exit_permission_denied();
}

$af = new ArtifactFactory($ath);
if (!$af || !is_object($af)) {
	exit_error('Error','Could Not Get Factory');
} elseif ($af->isError()) {
	exit_error('Error',$af->getErrorMessage());
}

$offset = getStringFromRequest('offset');
$_sort_col = getStringFromRequest('_sort_col');
$_sort_ord = getStringFromRequest('_sort_ord');
$max_rows = getStringFromRequest('max_rows');
$set = getStringFromRequest('set');
$_assigned_to = getStringFromRequest('_assigned_to');
$_status = getStringFromRequest('_status');
$_changed_from = getStringFromRequest('_changed_from');

$af->setup($offset,$_sort_col,$_sort_ord,$max_rows,$set,$_assigned_to,$_status,$_changed_from);

$at_arr =& $af->getArtifacts();

echo 'artifact_id,status_id,status_name,priority,submitter_id,submitter_name,assigned_to_id,assigned_to_name,open_date,close_date,last_modified_date,summary';

//
//	Show the extra fields
//
$ef =& $ath->getExtraFields(ARTIFACT_EXTRAFIELD_FILTER_INT);
$keys=array_keys($ef);
for ($i=0; $i<count($keys); $i++) {
	echo ',"'.$ef[$keys[$i]]['field_name'].'"';
}

$arrRemove = array("\r\n", "\n", ',');

for ($i=0; $i<count($at_arr); $i++) {

	echo "\n".$at_arr[$i]->getID().','.
		$at_arr[$i]->getStatusID().',"'.
		$at_arr[$i]->getStatusName().'",'.
		$at_arr[$i]->getPriority().','.
		$at_arr[$i]->getSubmittedBy().',"'.
		$at_arr[$i]->getSubmittedRealName().'",'.
		$at_arr[$i]->getAssignedTo().',"'.
		$at_arr[$i]->getAssignedRealName().'","'.
		date($sys_datefmt,$at_arr[$i]->getOpenDate()).'","'.
		date($sys_datefmt,$at_arr[$i]->getCloseDate()).'","'.
		date($sys_datefmt,$at_arr[$i]->getLastModifiedDate()).'","'.
		$at_arr[$i]->getSummary().'"';

	//
	//	Show the extra fields
	//
	$efd =& $at_arr[$i]->getExtraFieldData();
	for ($j=0; $j<count($keys); $j++) {
		$v=$efd[$keys[$j]];
		echo ',"'.$ath->getElementName($v).'"';
	}
}

?>
