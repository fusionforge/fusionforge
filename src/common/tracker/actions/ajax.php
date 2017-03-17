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
		$status = getStringFromRequest('status');
		$assigned_to = getStringFromRequest('assigned_to');
		$priority = getStringFromRequest('priority');
		$summary = getStringFromRequest('summary');
		$description = getStringFromRequest('description');
		echo get_formulas_results($group, $atid, $extra_fields, $status, $assigned_to, $priority, $summary, $description);
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

function get_formulas_results($group, $atid, $extra_fields=array(), $status='', $assigned_to='', $priority=0, $summary='', $description=''){
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

	// Constants assignment
	// Internal Fields
	if (!$at->usesCustomStatuses()) {
		if (!$status) {
			$status = $at->getStatusName(1);
		}
		$expr->setConstant('status', $status);
		if ($expr->isError()) {
			$ret['message'] = $expr->getErrorMessage()._(':').' status=\''.$status.'\'';
			return json_encode($ret);
			exit();
		}
	}
	$expr->setConstant('assigned_to', $assigned_to);
	if ($expr->isError()) {
		$ret['message'] = $expr->getErrorMessage()._(':').' assigned_to=\''.$assigned_to.'\'';
		return json_encode($ret);
		exit();
	}
	$expr->setConstant('priority', $priority);
	if ($expr->isError()) {
		$ret['message'] = $expr->getErrorMessage()._(':').' priority=\''.$priority.'\'';
		return json_encode($ret);
		exit();
	}
	$expr->setConstant('summary', $summary);
	if ($expr->isError()) {
		$ret['message'] = $expr->getErrorMessage()._(':').' summary=\''.$summary.'\'';
		return json_encode($ret);
		exit();
	}
	$expr->setConstant('description', $description);
	if ($expr->isError()) {
		$ret['message'] = $expr->getErrorMessage()._(':').' description=\''.$description.'\'';
		return json_encode($ret);
		exit();
	}

	// Extra Fields
	$extraFields = $at->getExtraFields();
	foreach ($extraFields as $extraField) {
		if (isset($extra_fields[$extraField['extra_field_id']])) {
			$value = '';
			$type = $extraField['field_type'];
			if ($type==ARTIFACT_EXTRAFIELDTYPE_INTEGER) {
				$value = (integer)$extra_fields[$extraField['extra_field_id']];
			} elseif ($type==ARTIFACT_EXTRAFIELDTYPE_RELATION ||
					$type==ARTIFACT_EXTRAFIELDTYPE_DATETIME) {
				$value = addslashes($extra_fields[$extraField['extra_field_id']]);
			} elseif ($type==ARTIFACT_EXTRAFIELDTYPE_TEXT ||
					$type==ARTIFACT_EXTRAFIELDTYPE_TEXTAREA ) {
				$value = $extra_fields[$extraField['extra_field_id']];
			} elseif (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
				$ef = new ArtifactExtraField($at, $extraField['extra_field_id']);
				$efe = new ArtifactExtraFieldElement($ef,$extra_fields[$extraField['extra_field_id']] );
				$value = addslashes($efe->getName());
			} elseif (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_MULTICHOICE))) {
				$var = array();
				$ef = new ArtifactExtraField($at, $extraField['extra_field_id']);
				foreach ($extra_fields[$extraField['extra_field_id']] as $element_id) {
					$efe = new ArtifactExtraFieldElement($ef,$element_id);
					$var[]=  $efe->getName();
				}
				$value = json_encode($var);
			}
			$expr->setConstant($extraField['alias'], $value);
			if ($expr->isError()) {
				$ret['message'] = $expr->getErrorMessage()._(':').' '.$extraField['alias'].'='.($type==ARTIFACT_EXTRAFIELDTYPE_INTEGER?'':'\'').$value.($type==ARTIFACT_EXTRAFIELDTYPE_INTEGER?'':'\'');
				return json_encode($ret);
				exit();
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
