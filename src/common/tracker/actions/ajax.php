<?php
/**
 * Tracker Facility
 *
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2016 Stéphane-Eymeric Bredthauer - TrivialDev
 * http://fusionforge.org/
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

require_once $gfwww.'include/expression.php';

global $group;
global $atid;

$sysdebug_enable = false;

$function = getStringFromRequest('function');

switch ($function) {
	case 'get_canned_response':
		$canned_response_id = getIntFromRequest('canned_response_id');
		echo get_canned_response($canned_response_id);
		break;
	case 'get_formulas_results':
		$extra_fields = getArrayFromRequest('extra_fields');
		echo get_formulas_results($group, $atid, $extra_fields);
		break;
	default:
		echo '';
		break;
}

function get_canned_response($id) {
	$result = db_query_params('SELECT body FROM artifact_canned_responses WHERE id=$1',
		array ($id));
	if (! $result || db_numrows($result) < 1) {
		return '';
	}
	else {
		return db_result($result, 0, 'body');
	}
}

function get_formulas_results($group, $atid, $extra_fields=array()){
	$ret = array('messages' => '');
	$at = new ArtifactType($group, $atid);
	if (!$at || !is_object($at)) {
		$ret['messages'] = _('ArtifactType could not be created');
		return json_encode($ret);
		exit();
	}
	if ($at->isError()) {
		$ret['messages'] = $at->getErrorMessage();
		return json_encode($ret);
		exit();
	}

	$expr = new Expression();

	// Variable assignment
	$extraFields = $at->getExtraFields();
	foreach ($extraFields as $extraField) {
		if (isset($extra_fields[$extraField['extra_field_id']])) {
			if ($extraField['field_type']==ARTIFACT_EXTRAFIELDTYPE_INTEGER) {
				$varAss = $extraField['alias'].'='.$extra_fields[$extraField['extra_field_id']];
				$expr->evaluate($varAss);
			} elseif ($extraField['field_type']==ARTIFACT_EXTRAFIELDTYPE_TEXT) {
				$varAss = $extraField['alias'].'="'.$extra_fields[$extraField['extra_field_id']].'"';
				$expr->evaluate($varAss);
			}
		}
	}

	// formula
	$result = array();
	foreach ($extraFields as $extraField) {
		$ef = new ArtifactExtraField($at,$extraField['extra_field_id']);
		if (!$ef || !is_object($ef)) {
			$ret['messages'] = _('ArtifactExtraField could not be created');
			return json_encode($ret);
			exit();
		}
		if ($ef->isError()) {
			$ret['messages'] = $ef->getErrorMessage();
			return json_encode($ret);
			exit();
		}
		$formula = $ef->getFormula();
		if (in_array($extraField['field_type'], unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_VALUE))) {
			if (!empty($formula)) {
				$value = $expr->evaluate($formula);
				$result [] = array( 'id'=>$extraField['extra_field_id'], 'value'=>$value, 'error'=>$expr->last_error );
			}
		} elseif (in_array($extraField['field_type'], unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_CHOICE))) {
			if (is_array($formula)) {
				$formulas = $formula;
				$valueArr = array();
				foreach ($formulas as $key=>$formula) {
					$value = $expr->evaluate($formula);
					if ($value) {
						$valueArr[]=$key;
						if (in_array($extraField['field_type'], unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
							breack;
						}
					}
				}
				$result [] = array( 'id'=>$extraField['extra_field_id'], 'value'=>$valueArr, 'error'=>$expr->last_error);
			}
		}
	}
	$ret['fields'] = $result;
	return json_encode($ret);
}
