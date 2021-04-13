<?php
/**
 * Copyright 2005 (c) GForge Group, LLC
 * Copyright 2016-2017, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

session_require_perm('tracker', $ath->getID(), 'read');

$af = new ArtifactFactory($ath);
if (!$af || !is_object($af)) {
	exit_error(_('Could Not Get Factory'),'tracker');
} elseif ($af->isError()) {
	exit_error($af->getErrorMessage(),'tracker');
}


$headers = getIntFromRequest('headers');
$sep = getFilteredStringFromRequest('sep', '/^[,;]$/', ',');
$date_format = _('Y-m-d');

sysdebug_off();
header('Content-type: text/csv');
header('Content-disposition: filename="trackers-'.date('Y-m-d-His').'.csv"');

$bom = getIntFromRequest('bom', 0);
$encoding = getStringFromRequest('encoding', 'UTF-8');
$offset = getStringFromRequest('offset');
$_sort_col = getStringFromRequest('_sort_col');
$_sort_ord = getStringFromRequest('_sort_ord');
$max_rows = getIntFromRequest('max_rows');
$set = getStringFromRequest('set');
$_assigned_to = getIntFromRequest('_assigned_to');
$_status = getIntFromRequest('_status');
$received_changed_from = getStringFromRequest('_changed_from', 0);
$overwrite_filter = getStringFromRequest('overwrite_filter', false);
if ($overwrite_filter) {
	$set = $overwrite_filter;
}
if ($received_changed_from) {
	$arrDateBegin = DateTime::createFromFormat($date_format, $received_changed_from);
	$_changed_from = $arrDateBegin->getTimestamp();
} else {
	$_changed_from = 0;
}

$af->setup($offset, $_sort_col, $_sort_ord, $max_rows, $set, $_assigned_to, $_status, array(), $_changed_from);

$at_arr = $af->getArtifacts();

if ($headers) {
	$s = 'artifact_id'.$sep.
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
		'last_modified_by'.$sep.
		'summary'.$sep.
		'details'.$sep.
		'_votes'.$sep.
		'_voters'.$sep.
		'_votage';

	//
	//	Show the extra fields
	//
	$ef = $ath->getExtraFields();
	$keys = array_keys($ef);
	for ($i = 0; $i < count($keys); $i++) {
		if ($ef[$keys[$i]]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_EFFORT) {
			$s .= $sep.'"'.'effort_unit for '.$ef[$keys[$i]]['field_name'].'"';
		}
		$s .= $sep.'"'.$ef[$keys[$i]]['field_name'].'"';
	}
	$s .= $sep.'comments';
	$s .= "\n";
}

for ($i = 0; $i < count($at_arr); $i++) {

	$open_date   = $at_arr[$i]->getOpenDate() ? date(_('Y-m-d H:i'),$at_arr[$i]->getOpenDate()) : '';
	$update_date = $at_arr[$i]->getLastModifiedDate() ? date(_('Y-m-d H:i'),$at_arr[$i]->getLastModifiedDate()) : '';
	$close_date  = $at_arr[$i]->getCloseDate()? date(_('Y-m-d H:i'),$at_arr[$i]->getCloseDate()): '';

	$votes = $at_arr[$i]->getVotes();
	$s .= $at_arr[$i]->getID().$sep.
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
		'"'.$at_arr[$i]->getLastModifiedRealName().'"'.$sep.
		'"'.fix4csv($at_arr[$i]->getSummary()).'"'.$sep.
		'"'.fix4csv($at_arr[$i]->getDetails()).'"'.$sep.
		$votes[0].$sep.
		$votes[1].$sep.
		$votes[2];

	// Show the extra fields
	$efd = $at_arr[$i]->getExtraFieldDataText();
	foreach ( $efd as $key => $efd_pair ) {
		if ($efd_pair['type'] == ARTIFACT_EXTRAFIELDTYPE_EFFORT) {
			if (!isset($effortUnitSet)) {
				$effortUnitSet = new EffortUnitSet($ath, $ath->getEffortUnitSet());
				$effortUnitFactory = new EffortUnitFactory($effortUnitSet);
			}
			$units = $effortUnitFactory->getUnits();
			$unitId = $effortUnitFactory->encodedToUnitId($efd_pair['value']);
			$unittexts = _('Unknown');
			foreach ($units as $unit) {
				if ($unit->getID() == $unitId) {
					$unittexts = $unit->getName();
				}
			}
			$s .= $sep.'"'.fix4csv($unittexts).'"';
			$value = $effortUnitFactory->encodedToValue($efd_pair['value']);
		} else {
			$value = $efd_pair["value"];
		}
		$s .= $sep.'"'.fix4csv($value).'"';
	}

	// Include comments
	$result = $at_arr[$i]->getMessages();
	$comments = '';
	while ($arr = db_fetch_array($result)) {
		$date = date(_('Y-m-d H:i'), $arr['adddate']);
		$realname = $arr['realname'];
		$body = $arr['body'];
		// replace all newline by ' ~ '
		$body = str_replace(array("\r\n", "\r", "\n", PHP_EOL, chr(10), chr(13), chr(10).chr(13)), " ~ ", $body);
		$comments .= ' *** '.$date.' --- '.$realname.' --- '.$body;
	}
	$s .= $sep.'"'.fix4csv($comments).'"';
	$s .= "\n";
}

if ($bom) {
	if ($encoding == 'UTF-16LE') {
		echo "\xFF\xFE";
	} elseif ($encoding == 'UTF-16BE') {
		echo "\xFE\xFF";
	} elseif ($encoding == 'UTF-8') {
		echo "\xEF\xBB\xBF";
	}
}

echo mb_convert_encoding($s, $encoding, "UTF-8");

function fix4csv($value) {
	$value = util_unconvert_htmlspecialchars($value);
	$value = str_replace("\r\n", "\n", $value);
	$value = str_replace('"', '""', $value);
	return $value;
}
