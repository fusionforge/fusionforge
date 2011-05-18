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

$date = date('Y-m-d');

header('Content-type: text/csv');
header('Content-disposition: filename="tracker_report-'.$date.'.csv"');

session_require_perm ('tracker', $ath->getID(), 'read') ;

$af = new ArtifactFactory($ath);
if (!$af || !is_object($af)) {
	exit_error('Error','Could Not Get Factory');
} elseif ($af->isError()) {
	exit_error('Error',$af->getErrorMessage());
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

echo 'artifact_id;status_id;status_name;priority;submitter_id;submitter_name;assigned_to_id;assigned_to_name;open_date;close_date;last_modified_date;summary;details';

//
//	Show the extra fields
//
$ef = $ath->getExtraFields();
$keys=array_keys($ef);
for ($i=0; $i<count($keys); $i++) {
	echo ';"'.$ef[$keys[$i]]['field_name'].'"';
}

for ($i=0; $i<count($at_arr); $i++) {

	$open_date   = $at_arr[$i]->getOpenDate() ? date(_('Y-m-d H:i'),$at_arr[$i]->getOpenDate()) : '';
	$update_date = $at_arr[$i]->getLastModifiedDate() ? date(_('Y-m-d H:i'),$at_arr[$i]->getLastModifiedDate()) : '';
	$close_date  = $at_arr[$i]->getCloseDate()? date(_('Y-m-d H:i'),$at_arr[$i]->getCloseDate()): '';

	echo "\n".$at_arr[$i]->getID().';'.
		$at_arr[$i]->getStatusID().';"'.
		$at_arr[$i]->getStatusName().'";'.
		$at_arr[$i]->getPriority().';'.
		$at_arr[$i]->getSubmittedBy().';"'.
		$at_arr[$i]->getSubmittedRealName().'";'.
		$at_arr[$i]->getAssignedTo().';"'.
		$at_arr[$i]->getAssignedRealName().'";"'.
		$open_date.'";"'.
		$close_date.'";"'.
		$update_date.'";"'.
		fix4csv($at_arr[$i]->getSummary()).'";"'.
		fix4csv($at_arr[$i]->getDetails()).'"';

	//
	//	Show the extra fields
	//
 	$efd = $at_arr[$i]->getExtraFieldDataText();
 	foreach ( $efd as $efd_pair ) {
 		$value = $efd_pair["value"];
 		echo ';"'. fix4csv($value) .'"';
 	}
}

function fix4csv ($value) {
	$value = util_unconvert_htmlspecialchars( $value );
	$value = str_replace("\r\n", "\n", $value);
	$value = str_replace('"', '""', $value);
	return $value;
}

?>
