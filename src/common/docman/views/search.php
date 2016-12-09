<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2016, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $group_id; // id of the group
global $g; // the group object
global $dirid; // id of doc_group
global $HTML; // Layout object
global $warning_msg;

$redirect_url = '/docman/?group_id='.$group_id;
if (!forge_check_perm('docman', $group_id, 'read')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect($redirect_url);
}

$searchString = trim(getStringFromRequest('textsearch', null));
$insideDocuments = getIntFromRequest('insideDocuments', 0);
$subprojectsIncluded = getIntFromRequest('includesubprojects', 0);
$limitByStartDate = getIntFromRequest('limitByStartDate', 0);
$limitByEndDate = getIntFromRequest('limitByEndDate', 0);
$received_begin = getStringFromRequest('start_date', 0);
$received_end = getStringFromRequest('end_date', 0);
$search_type = getStringFromRequest('search_type', 'all');
$insideDocumentsCheckbox = '';
$attrsInputSearchAll = array('type' => 'radio', 'name' => 'search_type', 'required' => 'required', 'value' => 'all', 'title' => _('All searched words are mandatory.'));
$attrsInputSearchOne = array('type' => 'radio', 'name' => 'search_type', 'required' => 'required', 'value' => 'one', 'title' => _('At least one word must be found.'));
$date_format_js = _('yy-mm-dd');
$date_format = _('Y-m-d');

if ($search_type == 'one') {
	$attrsInputSearchOne['checked'] = 'checked';
	$isExact = false;
} else {
	$attrsInputSearchAll['checked'] = 'checked';
	$isExact = true;
}

echo html_ao('div', array('id' => 'docman_search', 'class' => 'docmanDivIncluded'));
echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var controllerSearch;

jQuery(document).ready(function() {
	controllerSearch = new DocManSearchController({
		buttonStartDate:	jQuery('#limitByStartDate'),
		buttonEndDate:		jQuery('#limitByEndDate'),
		datePickerStartDate:	jQuery('#datepicker_start'),
		datePickerEndDate:	jQuery('#datepicker_end'),
	});

	jQuery('#datepicker_start').datepicker({
		dateFormat: "<?php echo $date_format_js ?>"
	});
	jQuery('#datepicker_end').datepicker({
		dateFormat: "<?php echo $date_format_js ?>"
	});
});

//]]>
<?php
echo html_ac(html_ap() - 1);
echo $HTML->openForm(array('method' => 'post', 'action' => '/docman/?group_id='.$group_id.'&view=search'));
echo html_e('div', array('id' => 'docman_search_query_words'),
		html_e('span', array('id' => 'docman_search_query_label'), _('Query').utils_requiredField()._(': ')).
html_e('input', array('type' => 'text', 'name' => 'textsearch', 'id' => 'textsearch', 'size' => 48, 'value' => stripslashes(htmlspecialchars($searchString)), 'required' => 'required', 'placeholder' => _('Searched words'))).
		html_e('input', array('type' => 'submit', 'value' => _('Search'))));
echo html_ao('div', array('id' => 'docman_search_query_ckeckbox'));
echo html_e('input', $attrsInputSearchAll)._('With all the words');
echo html_e('input', $attrsInputSearchOne)._('With at least one of words');
if ($g->useDocmanSearch()) {
	$attrsInputInsideDocs = array('type' => 'checkbox', 'name'  => 'insideDocuments', 'value' => 1, 'title' => _('Filename & contents are used to match searched words.'));
	if ($insideDocuments)
		$attrsInputInsideDocs['checked'] = 'checked';
	echo html_e('input', $attrsInputInsideDocs)._('Inside documents');
}
$attrsFieldSet = array('id' => 'fieldset1_closed', 'class' => 'coolfieldset');
if ($limitByStartDate || $limitByEndDate || $subprojectsIncluded) {
	$attrsFieldSet['id'] = 'fieldset1';
}
echo html_ao('fieldset', $attrsFieldSet);
echo html_e('legend', array(), _('Advanced Options'));
echo html_ao('div');
if ($g->usesPlugin('projects-hierarchy')) {
	$projectsHierarchy = plugin_get_object('projects-hierarchy');
	$projectIDsArray = $projectsHierarchy->getFamily($group_id, 'child', true, 'validated');
	if (is_array($projectIDsArray)) {
		$attrsInputIncludeSubprojects = array('type' => 'checkbox', 'name'  => 'includesubprojects', 'value' => 1, 'title' => _('Search into childs following project hierarchy.'));
		if ($subprojectsIncluded)
			$attrsInputIncludeSubprojects['checked'] = 'checked';
		echo html_e('p', array(), html_e('input', $attrsInputIncludeSubprojects)._('Include child projects'));
	}
}
$attrsInputLimitByStartDate = array('type' => 'checkbox', 'id' => 'limitByStartDate', 'name' => 'limitByStartDate', 'value' => 1, 'title' => _('Set created start date limitation for this search. If not enable, not limitation.'));
$attrsDatePickerLimitByStartDate = array('id' => 'datepicker_start', 'name' => 'start_date', 'size' => 10, 'maxlength' => 10, 'disabled' => 'disabled');
if ($limitByStartDate) {
	$attrsInputLimitByStartDate['checked'] = 'checked';
	unset($attrsDatePickerLimitByStartDate['disabled']);
	$attrsDatePickerLimitByStartDate['required'] = 'required';
	if ($received_begin) {
		$attrsDatePickerLimitByStartDate['value'] = util_html_encode($received_begin);
	}
}
$attrsInputLimitByEndDate = array('type' => 'checkbox', 'id' => 'limitByEndDate', 'name' => 'limitByEndDate', 'value' => 1, 'title' => _('Set created end date limitation for this search. If not enable, not limitation.'));
$attrsDatePickerLimitByEndDate = array('id' => 'datepicker_end', 'name' => 'end_date', 'size' => 10, 'maxlength' => 10, 'disabled' => 'disabled');
if ($limitByEndDate) {
	$attrsInputLimitByEndDate['checked'] = 'checked';
	unset($attrsDatePickerLimitByEndDate['disabled']);
	$attrsDatePickerLimitByEndDate['required'] = 'required';
	if ($received_end) {
		$attrsDatePickerLimitByEndDate['value'] = util_html_encode($received_end);
	}
}
echo html_e('p', array(), _('Set dates')._(': ').html_e('br').
			_('From')._(': ').html_e('input', $attrsInputLimitByStartDate).html_e('input', $attrsDatePickerLimitByStartDate).
			_('To')._(': ').html_e('input', $attrsInputLimitByEndDate).html_e('input', $attrsDatePickerLimitByEndDate));
echo html_ac(html_ap() - 2);
echo $HTML->addRequiredFieldsInfoBox();
echo $HTML->closeForm();
echo html_ac(html_ap() - 1);
echo html_ao('div', array('id' => 'docman_search_query_result'));
$search_options = array();
if ($received_begin) {
	$arrDateBegin = DateTime::createFromFormat($date_format, $received_begin);
	$search_options['date_begin'] = $arrDateBegin->getTimestamp();
}
if ($received_end) {
	$arrDateEnd = DateTime::createFromFormat($date_format, $received_end);
	$search_options['date_end'] = $arrDateEnd->getTimestamp();
}
$search_options['includesubprojects'] = $subprojectsIncluded;
$search_options['insideDocuments'] = $insideDocuments;

if (session_loggedin()) {
	if (getStringFromRequest('setpaging')) {
		/* store paging preferences */
		$paging = getIntFromRequest('nres');
		if (!$paging) {
			$paging = 25;
		}
		$LUSER->setPreference('paging', $paging);
	}
	/* logged in users get configurable paging */
	$paging = (int)$LUSER->getPreference('paging');
}

if(!isset($paging) || !$paging)
	$paging = 25;

if ($searchString) {
	$docsHtmlSearchRenderer = new DocsHtmlSearchRenderer($searchString, $start, $isExact, $group_id, SEARCH__ALL_SECTIONS, $paging, $search_options);
	$result = $docsHtmlSearchRenderer->searchQuery->getData($docsHtmlSearchRenderer->searchQuery->getRowsPerPage(),$docsHtmlSearchRenderer->searchQuery->getOffset());
	$nbDocs = count($result);
	$max = $docsHtmlSearchRenderer->searchQuery->getRowsTotalCount();
	echo $HTML->paging_top($start, $paging, $max, $nbDocs, $redirect_url.'&view=search&textsearch='.urlencode($searchString).'&insideDocuments='.$insideDocuments.'&search_type='.$search_type.'&includesubprojects='.$subprojectsIncluded.'&limitByStartDate='.$limitByStartDate.'&limitByEndDate='.$limitByEndDate.'&start_date='.$received_begin.'&end_date='.$received_end);
	$docsHtmlSearchRenderer->writeBody(false);
	echo $HTML->paging_bottom($start, $paging, $max, $redirect_url.'&view=search&textsearch='.urlencode($searchString).'&insideDocuments='.$insideDocuments.'&search_type='.$search_type.'&includesubprojects='.$subprojectsIncluded.'&limitByStartDate='.$limitByStartDate.'&limitByEndDate='.$limitByEndDate.'&start_date='.$received_begin.'&end_date='.$received_end);
}
echo html_ac(html_ap() -2);
