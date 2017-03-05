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

require_once $gfcommon.'tracker/ArtifactExpression.class.php';

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
	$ret = array('message' => '');
	$at = new ArtifactType($group, $atid);
	if (!$at || !is_object($at)) {
		$ret['message'] = _('ArtifactType could not be created');
		return json_encode($ret);
		exit();
	}
	if ($at->isError()) {
		$ret['message'] = $at->getErrorMessage();
		return json_encode($ret);
		exit();
	}

	$expr = new ArtifactExpression();

	// Variable assignment
	$extraFields = $at->getExtraFields();
	foreach ($extraFields as $extraField) {
		if (isset($extra_fields[$extraField['extra_field_id']])) {
			$varAss = false;
			$type = $extraField['field_type'];
			if ($type==ARTIFACT_EXTRAFIELDTYPE_INTEGER) {
				$varAss = $extraField['alias'].'='.$extra_fields[$extraField['extra_field_id']];
			} elseif ($type==ARTIFACT_EXTRAFIELDTYPE_TEXT ||
					$type==ARTIFACT_EXTRAFIELDTYPE_TEXTAREA ||
					$type==ARTIFACT_EXTRAFIELDTYPE_RELATION) {
				$varAss = $extraField['alias'].'="'.addslashes($extra_fields[$extraField['extra_field_id']]).'"';
			} elseif (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
				$ef = new ArtifactExtraField($at, $extraField['extra_field_id']);
				$efe = new ArtifactExtraFieldElement($ef,$extra_fields[$extraField['extra_field_id']] );
				$varAss =  $extraField['alias'].'="'.addslashes($efe->getName()).'"';
			} elseif (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_MULTICHOICE))) {
				$var = array();
				$ef = new ArtifactExtraField($at, $extraField['extra_field_id']);
				foreach ($extra_fields[$extraField['extra_field_id']] as $element_id) {
					$efe = new ArtifactExtraFieldElement($ef,$element_id);
					$var[]=  $efe->getName();
				}
				$varAss =  $extraField['alias'].'=\''. json_encode($var).'\'';
			}
			if ($varAss) {
				$expr->evaluate($varAss);
				if ($expr->isError()) {
					$ret['message'] = $expr->getErrorMessage()._(':').' '.$varAss;
					return json_encode($ret);
					exit();
				}
			}
		}
	}

	// formula
	$result = array();
	foreach ($extraFields as $extraField) {
		$ef = new ArtifactExtraField($at,$extraField['extra_field_id']);
		if (!$ef || !is_object($ef)) {
			$ret['message'] = _('ArtifactExtraField could not be created');
			return json_encode($ret);
			exit();
		}
		if ($ef->isError()) {
			$ret['message'] = $ef->getErrorMessage();
			return json_encode($ret);
			exit();
		}
		$formula = $ef->getFormula();
		if ($formula) {
			if (in_array($extraField['field_type'], unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_VALUE))) {
				if (!empty($formula)) {
					$value = $expr->evaluate($formula);
					if ($expr->isError()) {
						$ret['message'] = $expr->getErrorMessage();
						return json_encode($ret);
						exit();
					}
					$result [] = array( 'id'=>$extraField['extra_field_id'], 'value'=>$value, 'error'=>($expr->isError()?$expr->getErrorMessage():null));
				}
			} elseif (in_array($extraField['field_type'], unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_CHOICE))) {
				if (is_array($formula)) {
					$formulas = $formula;
					$valueArr = array();
					foreach ($formulas as $key=>$formula) {
						$value = $expr->evaluate($formula);
						if ($expr->isError()) {
							$ret['message'] = $expr->getErrorMessage();
							return json_encode($ret);
							exit();
						}
						if ($value) {
							$valueArr[]=$key;
							if (in_array($extraField['field_type'], unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
								break;
							}
						}
					}
					$result [] = array( 'id'=>$extraField['extra_field_id'], 'value'=>$valueArr, 'error'=>($expr->isError()?$expr->getErrorMessage():null));
				}
			}
		}
	}
	$ret['fields'] = $result;
	return json_encode($ret);
}
