<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012, Franck Villaume - TrivialDev
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

if (!forge_check_perm('docman', $group_id, 'read')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

$is_editor = forge_check_perm('docman', $g->getID(), 'approve');
$searchString = trim(getStringFromPost("textsearch"));
$subprojectsIncluded = getStringFromPost('includesubprojects');
$insideDocuments = getStringFromPost('insideDocuments');
$allchecked = "";
$onechecked = "";
$includesubprojects = "";
$insideDocumentsCheckbox = "";
if (getStringFromPost('search_type') == "one") {
	$onechecked = 'checked="checked"';
} else {
	$allchecked = 'checked="checked"';
}

if ($subprojectsIncluded)
	$includesubprojects = 'checked="checked"';

if ($insideDocuments)
	$insideDocumentsCheckbox = 'checked="checked"';

echo '<div class="docmanDivIncluded">';
echo '<form method="post" action="?group_id='.$group_id.'&view=search" >';
echo '<table width="98%" cellpadding="2" cellspacing="0" border="0">';
echo '<tr><td><b>'._('Query: ').'</b>';
echo '<input type="text" name="textsearch" id="textsearch" size="48" value="'.$searchString.'" />';
echo '<input type="submit" value="'._('Search').'" />';
echo '</td></tr><tr><td>';
echo '<input type="radio" name="search_type" value="all" '.$allchecked.' class="tabtitle-nw" title="'._('All searched words are mandatory').'" />'._('With all the words');
echo '<input type="radio" name="search_type" value="one" '.$onechecked.' class="tabtitle" title="'._('At least one word must be found').'" />'._('With at least one of words');
if ($g->useDocmanSearch()) {
	echo '<input type="checkbox" name="insideDocuments" value="1" '.$insideDocumentsCheckbox.' class="tabtitle" title="'._('Filename and contents are used to match searched words').'" />'._('Inside documents');
}
if ($g->usesPlugin('projects-hierarchy')) {
	$projectsHierarchy = plugin_get_object('projects-hierarchy');
	$projectIDsArray = $projectsHierarchy->getFamily($group_id, 'child', true, 'validated');
}
if (isset($projectIDsArray) && is_array($projectIDsArray))
	echo '<input type="checkbox" name="includesubprojects" value="1" '.$includesubprojects.' class="tabtitle" title="'._('search into childs following project hierarchy').'" />'._('Include child projects');

echo '</td></tr>';
echo '</table>';
echo '</form>';
if ($searchString) {
	$mots = preg_split("/[\s,]+/",$searchString);
	$qpa = db_construct_qpa(false, 'SELECT filename, filetype, docid, doc_data.stateid as stateid, doc_states.name as statename, title, description, createdate, updatedate, doc_group, group_id FROM doc_data, doc_states WHERE doc_data.stateid = doc_states.stateid');
	if (getStringFromPost('search_type') == "one") {
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
	$qpa = db_construct_qpa($qpa, ' ORDER BY updatedate, createdate');
	$result = db_query_qpa($qpa);
	if (!$result) {
		echo '<p class="error">'._('Database query error').'</p>';
		db_free_result($result);
	} elseif (db_numrows($result) < 1) {
		echo '<p class="warning_msg">'._('Your search did not match any documents.').'</p>';
		db_free_result($result);
	} else {
		$resarr = array();
		while ($arr = db_fetch_array($result)) {
			$resarr[] = $arr;
		}
		db_free_result($result);
		// need groups infos
		$groupsarr = array();
		$qpa = db_construct_qpa(false, 'SELECT doc_group, groupname, parent_doc_group FROM doc_groups WHERE group_id=$1', array($group_id));
		$params['group_id'] = $group_id;
		$params['qpa'] = &$qpa;
		$params['includesubprojects'] = $subprojectsIncluded;
		plugin_hook('docmansearch_has_hierarchy', $params);
		$result = db_query_qpa($qpa);
		if ($result && db_numrows($result) > 0) {
			while ($arr = db_fetch_array($result)) {
				$groupsarr[] = $arr;
			}
		}
		db_free_result($result);
		$count = 0;
		echo '<table width="98%" cellpadding="0" cellspacing="0" border="0">';
		foreach ($resarr as $item) {
			$count++;
			if ($item['filetype'] == 'URL') {
				$fileurl = $item["filename"];
			} else {
				$fileurl = '/docman/view.php/'.$item["group_id"].'/'.$item["docid"].'/'.urlencode($item["filename"]);
			}
			echo '<tr><td width="20px" align="right"><b>'.$count.'.</b></td><td><b>'.$item["title"].'</b>&nbsp;(<a href="'.$fileurl.'">'.$item["filename"].'</a>)</td></tr>';
			echo '<tr><td colspan="2">'.$item["description"].'</td></tr>';
			echo '<tr><td colspan="2"><b>'.$item["statename"].'</b>&nbsp;&nbsp;<i>'.get_path_document($groupsarr, $item["doc_group"], $item["group_id"]).'</i></td></tr>';
			echo '<tr><td colspan="2">&nbsp;</td></tr>';
		}
		echo '</table>';
	}
} else {
	echo '<p class="warning_msg">'._('Your search is empty.').'</p>';
}
echo '</div>';
?>
