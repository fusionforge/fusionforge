<?php
/**
 * Formula Editor
*
* Copyright 2017, Stéphane-Eymeric Bredthauer - TrivialDev
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

require_once $gfcommon.'tracker/ArtifactExpression.class.php';

global $HTML;

html_use_tablesorter();

$title = sprintf(_('Manage Custom Fields for %s'), $ath->getName());
$ath->adminHeader(array('title'=>$title, 'modal'=>1));

$boxid = getIntFromRequest('boxid');
if ($boxid) {
	$ef_id = $boxid;
	$efe_id = getIntFromRequest('id');
} else {
	$ef_id = getIntFromRequest('id');;
	$efe_id = 0;
}

$ac = new ArtifactExtraField($ath,$ef_id);
if (!$ac || !is_object($ac)) {
	exit_error(_('Unable to create ArtifactExtraField Object'));
} elseif ($ac->isError()) {
	exit_error($ac->getErrorMessage());
}

if (!$efe_id) {
	$formula = $ac->getFormula();
} else {
	$ao = new ArtifactExtraFieldElement($ac,$id);
	if (!$ao || !is_object($ao)) {
		exit_error(_('Unable to create ArtifactExtraFieldElement Object'),'tracker');
	} elseif ($ao->isError()) {
		exit_error($ao->getErrorMessage(),'tracker');
	}
	$formula = $ao->getFormula();
}

$efarr = $ath->getExtraFields(array(),false,true);
$eftypes=ArtifactExtraField::getAvailableTypes();
$keys=array_keys($efarr);
$rows=count($keys);
echo html_ao('table',array('class'=>'fullwidth'));
echo html_ao('tr');

//variables
echo html_ao('td',array('class'=>'onethirdwidth top'));
echo html_e('p',array(),_('Variable'));
if ($rows > 0) {
	$title_arr = array();
	$classth = array();
	$title_arr[] = _('Custom Fields');
	$classth[]   = '';
	$title_arr[] = _('Variable');
	$classth[]   = '';
	$title_arr[] = _('Type');
	$classth[]   = '';
	$title_arr[] = _('Elements Defined');
	$classth[]   = 'unsortable';
	echo $HTML->listTableTop($title_arr, array(), 'full sortable', 'sortable_extrafields', $classth);
	$rownb = 0;
	for ($k=0; $k < $rows; $k++) {
		$i=$keys[$k];
		$rownb++;
		$row_attrs = array('class'=>$HTML->boxGetAltRowStyle($rownb,true));
		$cells = array();
		$cells[] = array($efarr[$i]['field_name'], 'class'=>'align-right');
		$cells[] = array(html_e('span',array('class'=>'insert'),$efarr[$i]['alias']), 'class'=>'align-right');
		$cells[] = array($eftypes[$efarr[$i]['field_type']], 'class'=>'align-right');

		//$id=str_replace('@','',$efarr[$i]['alias']);
		
				/*
				 List of possible options for a user built Selection Box
				 */

				$elearray = $ath->getExtraFieldElements($efarr[$i]['extra_field_id']);
/*
				if ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_USER && !isset($roles)) {
					$rolesarray = array();
					$roles = $ath->getGroup()->getRoles();
					foreach ($roles as $role) {
						$rolesarray[$role->getID()]=$role->getName();
					}
				}
				if ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RELEASE && !isset($packages)) {
					$packagesarray = array();
					$packages = $packages = get_frs_packages($ath->getGroup());
					foreach ($packages as $package) {
						$packagesarray[$package->getID()]=$package->getName();
					}
				}
*/
		
				$content = '';
				if (!empty($elearray)) {
					$optrows=count($elearray);

					for ($j=0; $j <$optrows; $j++) {
						switch ($efarr[$i]['field_type']) {
							case ARTIFACT_EXTRAFIELDTYPE_USER:
								$content .= $rolesarray[$elearray[$j]['element_name']];
								break;
							case ARTIFACT_EXTRAFIELDTYPE_RELEASE:
								$content .= $packagesarray[$elearray[$j]['element_name']];
								break;
							default:
								$content .= html_e('span',array('class'=>'insert'),$elearray[$j]['element_name']);
						}
						$content .= html_e('br');
					}
				} 
				
				$cells[] = array($content, 'class'=>'align-right');
				
				echo $HTML->multiTableRow($row_attrs, $cells);
	}
	echo $HTML->listTableBottom();
} else {
	echo $HTML->warning_msg(_('You have not defined any custom fields'));
}
echo html_ac(html_ap() - 1);

// Functions
echo html_ao('td',array('class'=>'onethirdwidth top'));
echo html_e('p',array(),_('Functions'));
$expression = new ArtifactExpression();
$functions =  $expression->getFunctions();
$title_arr = array();
$classth = array();
$title_arr[] = _('Fuction');
$classth[]   = '';
$title_arr[] = _('Description');
$classth[]   = 'unsortable';
echo $HTML->listTableTop($title_arr, array(), 'full sortable', 'sortable_fuction', $classth);

$rownb = 0;
foreach ($functions as $function) {
	$rownb++;
	$row_attrs = array('class'=>$HTML->boxGetAltRowStyle($rownb,true));
	$cells = array();
	$cells[] = array(html_e('span', array('class'=>'insert'), $function), 'class'=>'align-right');
	$cells[] = array($expression->getFunctionDescription($function), 'class'=>'align-right');
	echo $HTML->multiTableRow($row_attrs, $cells);
}
echo $HTML->listTableBottom();
echo html_ac(html_ap() - 1);

//Operators
echo html_ao('td',array('class'=>'onethirdwidth top'));
$operatorTypes = $expression->getOperators();
foreach ($operatorTypes as $operatorType) {
	echo html_e('p',array(),$operatorType[0]);
	$title_arr = array();
	$classth = array();
	$title_arr[] = _('Operator');
	$classth[]   = '';
	$title_arr[] = _('Description');
	$classth[]   = 'unsortable';
	$title_arr[] = _('Exemple');
	$classth[]   = 'unsortable';
	echo $HTML->listTableTop($title_arr, array(), 'full sortable', 'sortable_fuction', $classth);
	$operators = $operatorType[1];
	$rownb = 0;
	foreach ($operators as $operator) {
		$rownb++;
		$row_attrs = array('class'=>$HTML->boxGetAltRowStyle($rownb,true));
		$cells = array();
		$cells[] = array(html_e('span', array('class'=>'insert'), $operator[0]), 'class'=>'align-right');
		$cells[] = array($operator[1], 'class'=>'align-right');
		$cells[] = array($operator[2], 'class'=>'align-right');
		echo $HTML->multiTableRow($row_attrs, $cells);
	}
	echo $HTML->listTableBottom();
}

echo html_ac(html_ap() - 1);
echo html_ac(html_ap() - 1);
echo html_ac(html_ap() - 1);
$javascript = <<<'EOS'
$("span.insert").on('click', function(){
	var formula = $('#formula');
	var start = formula.prop('selectionStart');
	var end = formula.prop('selectionEnd');
	var value = formula.val();
	formula.val(value.substring(0, start)+$(this).text()+value.substring(end, value.length));
	formula.prop({'selectionStart' : start+$(this).text().length, 'selectionEnd': start+$(this).text().length});
	formula.focus();
});
$("textarea#formula").keydown(function(e) {
	if(e.keyCode === 9) {
		var start = $(this).prop('selectionStart');
		var end = $(this).prop('selectionEnd');
		var value = $(this).val();
		$(this).val(value.substring(0, start)+"\t"+value.substring(end));
		this.selectionStart = this.selectionEnd = start + 1;
		e.preventDefault();
	}
});
EOS;
echo html_e('script', array('type'=>'text/javascript'), '//<![CDATA['."\n".'$(function(){'.$javascript.'});'."\n".'//]]>');

echo html_e('h2', array(), _('Edit formula'));

if (!$efe_id) {
	echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&id='.$ef_id.'&atid='.$ath->getID(), 'method' => 'post'));
	echo html_e('input', array('type'=>'hidden', 'name'=>'update_box_formula', 'value'=>'y'));
}else {
	echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&boxid='.$ef_id.'&atid='.$ath->getID().'&id='.$efe_id, 'method' => 'post'));
	echo html_e('input', array('type'=>'hidden', 'name'=>'update_opt_formula', 'value'=>'y'));
}

echo html_e('textarea', array('id'=>'formula', 'name'=>'formula', 'class'=>'fullwidth', 'rows'=>10),$formula,false);

echo html_ao('p');
echo html_e('input', array('type'=>'submit', 'name'=>'post_changes', 'value'=>_('Update')));
if (!$efe_id) {
	echo html_e('input', array('type'=>'button', 'onclick'=>'location.href="/tracker/admin/?update_box=1&group_id='.$group_id.'&id='.$ef_id.'&atid='.$ath->getID().'"; return false;','value'=>_('Cancel')));
}else {
	echo html_e('input', array('type'=>'button', 'onclick'=>'location.href="/tracker/admin/?update_opt=1&id='&efe_id.'&group_id='.$group_id.'&atid='.$ath->getID().'&boxid='.$ef_id.'"; return false;','value'=>_('Cancel')));
}
echo html_ac(html_ap() - 1);

echo $HTML->closeForm();

echo html_e('p', array(), _('Use formula do define function, set variable, and calculate the value of the current field'));
echo html_e('p', array(), _('The instructions can contain tabs, spaces and carriage returns'));
echo html_e('p', array(), _('The instructions have to be terminated with a semicolon'));
echo html_e('p', array(), _('The instructions must be separated by carriage returns'));
echo html_e('p', array(), _('Each line that begins with a hash mark is a comment'));
echo html_e('p', array(), _('The last line must be the value to be calculated from the field'));

$ath->footer();
