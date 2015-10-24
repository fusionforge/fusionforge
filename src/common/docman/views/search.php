<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2015, Franck Villaume - TrivialDev
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

global $g;
global $group_id;
global $gfcommon;
global $HTML;

if (!forge_check_perm('docman', $group_id, 'read')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id);
}

$is_editor = forge_check_perm('docman', $g->getID(), 'approve');
$searchString = trim(getStringFromPost('textsearch'));
$insideDocuments = getStringFromPost('insideDocuments');
$subprojectsIncluded = getStringFromPost('includesubprojects');
$limitNbSearchDocs = getStringFromPost('limitNbSearchDocs');
$limitByStartDate = getIntFromPost('limitByStartDate', 0);
$limitByEndDate = getIntFromPost('limitByEndDate', 0);
$received_begin = getStringFromRequest('start_date', 0);
$received_end = getStringFromRequest('end_date', 0);
$allchecked = '';
$onechecked = '';
$insideDocumentsCheckbox = '';
$attrsInputSearchAll = array('type' => 'radio', 'name' => 'search_type', 'required' => 'required', 'value' => 'all', 'title' => _('All searched words are mandatory'));
$attrsInputSearchOne = array('type' => 'radio', 'name' => 'search_type', 'required' => 'required', 'value' => 'one', 'title' => _('At least one word must be found'));
$date_format_js = _('yy-mm-dd');
$date_format = _('Y-m-d');

if (getStringFromPost('search_type') == 'one') {
	$attrsInputSearchOne['checked'] = 'checked';
} else {
	$attrsInputSearchAll['checked'] = 'checked';
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
echo $HTML->openForm(array('method' => 'post', 'action' => util_make_uri('/docman/?group_id='.$group_id.'&view=search')));
echo html_e('div', array('id' => 'docman_search_query_words'),
		html_e('span', array('id' => 'docman_search_query_label'), _('Query').utils_requiredField()._(': ')).
		html_e('input', array('type' => 'text', 'name' => 'textsearch', 'id' => 'textsearch', 'size' => 48, 'value' => $searchString, 'required' => 'required', 'placeholder' => _('Searched words'))).
		html_e('input', array('type' => 'submit', 'value' => _('Search'))));
echo html_ao('div', array('id' => 'docman_search_query_ckeckbox'));
echo html_e('input', $attrsInputSearchAll)._('With all the words');
echo html_e('input', $attrsInputSearchOne)._('With at least one of words');
if ($g->useDocmanSearch()) {
	$attrsInputInsideDocs = array('type' => 'checkbox', 'name'  => 'insideDocuments', 'value' => 1, 'title' => _('Filename and contents are used to match searched words'));
	if ($insideDocuments)
		$attrsInputInsideDocs['checked'] = 'checked';
	echo html_e('input', $attrsInputInsideDocs)._('Inside documents');
}
$attrsFieldSet = array('id' => 'fieldset1_closed', 'class' => 'coolfieldset');
if ($limitByStartDate || $limitByEndDate || is_integer($limitNbSearchDocs)) {
	$attrsFieldSet['id'] = 'fieldset1';
}
echo html_ao('fieldset', $attrsFieldSet);
echo html_e('legend', array(), _('Advanced Options'));
echo html_ao('div');
if ($g->usesPlugin('projects-hierarchy')) {
	$projectsHierarchy = plugin_get_object('projects-hierarchy');
	$projectIDsArray = $projectsHierarchy->getFamily($group_id, 'child', true, 'validated');
	if (is_array($projectIDsArray)) {
		$attrsInputIncludeSubprojects = array('type' => 'checkbox', 'name'  => 'includesubprojects', 'value' => 1, 'title' => _('search into childs following project hierarchy'));
		if ($subprojectsIncluded)
			$attrsInputIncludeSubprojects['checked'] = 'checked';
		echo html_e('p', array(), html_e('input', $attrsInputIncludeSubprojects)._('Include child projects'));
	}
}
echo html_e('p', array(), _('limit search results to').html_build_select_box_from_array(array(_('All'), '10', '25', '50', '100'), 'limitNbSearchDocs', _('All'), 1)._('documents'));
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
		$attrsDatePickerLimitByStartDate['value'] = util_html_encode($received_end);
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
if ($searchString) {
	$mots = preg_split("/[\s,]+/",$searchString);
	$qpa = db_construct_qpa(false, 'SELECT filename, filetype, docid, doc_data.stateid as stateid, doc_states.name as statename, title, description, createdate, updatedate, doc_group, group_id FROM doc_data, doc_states WHERE doc_data.stateid = doc_states.stateid');
	if (getStringFromPost('search_type') == 'one') {
		if (count($mots) > 0) {
			$qpa = db_construct_qpa($qpa, ' AND (FALSE');
			foreach ($mots as $mot) {
				$mot = strtolower($mot);
				$qpa = db_construct_qpa($qpa, ' OR title LIKE $1 OR description LIKE $1 ',
							 array("%$mot%"));
				if ($insideDocuments)
					$qpa = db_construct_qpa($qpa, ' OR data_words LIKE $1 ', array("%$mot%"));
			}
			$qpa = db_construct_qpa($qpa, ')');
		}
	} else {
		// search_type = all
		if (count($mots) > 0) {
			$qpa = db_construct_qpa($qpa, ' AND (TRUE');
			foreach ($mots as $mot) {
				$mot = strtolower($mot);
				$qpa = db_construct_qpa($qpa, ' AND (title LIKE $1 OR description LIKE $1 ',
							array("%$mot%"));
				if ($insideDocuments)
					$qpa = db_construct_qpa($qpa, ' OR data_words LIKE $1 ', array("%$mot%"));
				$qpa = db_construct_qpa($qpa, ' ) ', array());
			}
			$qpa = db_construct_qpa($qpa, ')');
		}
	}

	if (!$is_editor) {
		$qpa = db_construct_qpa($qpa, ' AND doc_data.stateid = 1');
	} else {
		$qpa = db_construct_qpa($qpa, ' AND doc_data.stateid != 2');
	}

	$qpa = db_construct_qpa($qpa, ' AND ( group_id = $1', array($group_id));
	$params['group_id'] = $group_id;
	$params['qpa'] = &$qpa;
	$params['includesubprojects'] = $subprojectsIncluded;
	plugin_hook('docmansearch_has_hierarchy', $params);
	$qpa = db_construct_qpa($qpa, ' ) ', array());
	if ($received_begin) {
		$arrDateBegin = DateTime::createFromFormat($date_format, $received_begin);
	}
	if ($received_end) {
		$arrDateEnd = DateTime::createFromFormat($date_format, $received_end);
	}

	if (isset($arrDateBegin) && !isset($arrDateEnd)) {
		$qpa = db_construct_qpa($qpa, ' AND doc_data.createdate >= $1', array($arrDateBegin->getTimestamp()));
	} elseif (!isset($arrDateBegin) && isset($arrDateEnd)) {
		$qpa = db_construct_qpa($qpa, ' AND doc_data.createdate <= $1', array($arrDateEnd->getTimestamp()));
	} elseif (isset($arrDateBegin) && isset($arrDateEnd)) {
		$qpa = db_construct_qpa($qpa, ' AND doc_data.createdate between $1 and $2', array($arrDateBegin->getTimestamp(), $arrDateEnd->getTimestamp()));
	}

	$qpa = db_construct_qpa($qpa, ' ORDER BY updatedate, createdate');
	if (is_integer($limitNbSearchDocs)) {
		$qpa = db_construct_qpa($qpa, ' LIMIT $1', array($limitNbSearchDocs));
	}

	$result = db_query_qpa($qpa);
	if (!$result) {
		echo $HTML->error_msg(_('Database query error'));
		db_free_result($result);
	} elseif (db_numrows($result) < 1) {
		echo $HTML->warning_msg(_('Your search did not match any documents.'));
		db_free_result($result);
	} else {
		$resarr = array();
		while ($arr = db_fetch_array($result)) {
			$resarr[] = $arr;
		}
		db_free_result($result);
		$count = 0;
		$tabletop = array(_('Order'), _('Document'), _('Description'), _('Status'), _('Path'));
		$classth = array('', '', '', '', '');
		echo $HTML->listTableTop($tabletop, false, 'sortable_docman_searchfile', 'sortable', $classth);
		foreach ($resarr as $item) {
			$cells = array();
			$count++;
			$cells[][] = html_e('strong', array(), $count, false);
			if ($item['filetype'] == 'URL') {
				$cells[][] = util_make_link($item["filename"], $item["title"], array(), true);
			} else {
				$cells[][] = util_make_link('/docman/view.php/'.$item["group_id"].'/'.$item["docid"].'/'.urlencode($item["filename"]), $item["filename"]).' ('.$item["title"].')';
			}
			$cells[][] = $item["description"];
			$localProject = group_get_object($item['group_id']);
			$docGroupObject = new DocumentGroup($localProject, $item['doc_group']);
			$cells[][] = $item["statename"];
			$nextcell = '';
			if ($localProject->getUnixName() != $g->getUnixName()) {
				$nextcell .= util_make_link('/docman/?group_id='.$localProject->getID(), $localProject->getPublicName(), array('title' => _('Browse document manager for this project.'))).'::';
			}
			$nextcell .= html_e('i', array(), $docGroupObject->getPath(true, true), false);
			$cells[][] = $nextcell;
			echo $HTML->multiTableRow(array(), $cells);
		}
		echo $HTML->listTableBottom();
	}
} elseif (getStringFromServer('REQUEST_METHOD') === 'POST') {
	echo $HTML->warning_msg(_('Your search is empty.'));
}
echo html_ac(html_ap() -2);
