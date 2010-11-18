<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2010, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* NEED A REAL REWRITE */
require_once $gfcommon.'docman/include/vtemplate.class.php';

$is_editor = forge_check_perm('docman', $g->getID(), 'admin');

$vtp = new VTemplate;
if (empty($gfcommon)) {
	$templates_dir = '../../common';
} else {
	$templates_dir = $gfcommon;
}
$handle = $vtp->Open($templates_dir."/docman/templates/search.tpl.html");
$vtp->NewSession($handle,"MAIN");

$allchecked = ""; $onechecked = "";
if (getStringFromPost('search_type') == "one") {$onechecked = "checked";}
else {$allchecked = "checked";}
$vtp->AddSession($handle,"FORMSEARCH");
$vtp->SetVar($handle,"FORMSEARCH.TITLE",_('Search in documents'));
$vtp->SetVar($handle,"FORMSEARCH.GROUP_ID",$_GET["group_id"]);
$vtp->SetVar($handle,"FORMSEARCH.TEXTSEARCH",getStringFromPost("textsearch"));
$vtp->SetVar($handle,"FORMSEARCH.ALLCHECKED",$allchecked);
$vtp->SetVar($handle,"FORMSEARCH.ONECHECKED",$onechecked);
$vtp->SetVar($handle,"FORMSEARCH.SUBMIT_PROMPT",_('Search'));
$vtp->SetVar($handle,"FORMSEARCH.SEARCH_ALL_WORDS",_('With all the words'));
$vtp->SetVar($handle,"FORMSEARCH.SEARCH_ONE_WORD",_('With at least one of words'));
$vtp->CloseSession($handle,"FORMSEARCH");

if (getStringFromPost('cmd') == "search") {
	$textsearch = getStringFromPost("textsearch");
	$textsearch = prepare_search_text ($textsearch);
	$mots = preg_split("/[\s,]+/",$textsearch);
	$qpa = db_construct_qpa (false, 'SELECT filename, filetype, docid, doc_data.stateid as stateid, doc_states.name as statename, title, description, createdate, updatedate, doc_group, group_id FROM doc_data JOIN doc_states ON doc_data.stateid = doc_states.stateid') ;
	if (getStringFromPost('search_type') == "one") {
		if (count($mots) > 0) {
			$qpa = db_construct_qpa ($qpa, ' AND (FALSE');
			foreach ($mots as $mot) {
				$mot = strtolower($mot);
				$qpa = db_construct_qpa ($qpa, ' OR title LIKE $1 OR description LIKE $1 OR data_words LIKE $1',
							 array("%$mot%"));
			}
			$qpa = db_construct_qpa($qpa, ')');
		}
	} else {
		// search_type = all
		if (count($mots) > 0) {
			$qpa = db_construct_qpa($qpa, ' AND (TRUE');
			foreach ($mots as $mot) {
				$mot = strtolower($mot);
				$qpa = db_construct_qpa($qpa, ' AND (title LIKE $1 OR description LIKE $1 OR data_words LIKE $1)',
							array("%$mot%"));
			}
			$qpa = db_construct_qpa($qpa, ')');
		}
	}

	if (!$is_editor) {
		$qpa = db_construct_qpa($qpa, ' AND doc_data.stateid = 1');
	} else {
		$qpa = db_construct_qpa($qpa, ' AND doc_data.stateid != 2');
	}

	$qpa = db_construct_qpa($qpa, 'AND group_id = $1',
				array(getIntFromRequest('group_id')));
	$qpa = db_construct_qpa($qpa, 'ORDER BY updatedate, createdate');
	$resarr = array();
	$result = db_query_qpa($qpa);
	if (!$result) {
		$vtp->AddSession($handle, "MESSAGE");
		$vtp->SetVar($handle, "MESSAGE.TEXT", _('Database query error'));
		$vtp->CloseSession($handle, "MESSAGE");
	} elseif (db_numrows($result) < 1) {
		$vtp->AddSession($handle, "MESSAGE");
		$vtp->SetVar($handle, "MESSAGE.TEXT", _('Your search did not match any documents'));
		$vtp->CloseSession($handle, "MESSAGE");
	} else {
		while ($arr = db_fetch_array($result)) {
			$resarr[] = $arr;
		}
	}
	db_free_result($result);
 	// print_debug ($sql);
	// need groups infos
	$groupsarr = array();
	$result = db_query_params('SELECT doc_group, groupname, parent_doc_group FROM doc_groups WHERE group_id=$1',
					array(getIntFromRequest('group_id')));
	if ($result && db_numrows($result) > 0) {
		while ($arr = db_fetch_array($result)) {
			$groupsarr[] = $arr;
		}
	}
	db_free_result($result);

	$vtp->AddSession($handle, "RESULTSEARCH");
	$count = 0;
	foreach ($resarr as $item) {
		$count++;
		$vtp->AddSession($handle, "RESULT");
		$vtp->SetVar($handle, "RESULT.N", $count);
		$vtp->SetVar($handle, "RESULT.SEARCHTITLE", $item["title"]);
		$vtp->SetVar($handle, "RESULT.SEARCHCOMMENT", $item["description"]);
		$s = get_path_document($groupsarr, $item["doc_group"], "$_GET[group_id]");
		$vtp->SetVar($handle, "RESULT.SEARCHPATH", $s);
		if ($item['filetype'] == 'URL') {
			$vtp->SetVar($handle, "RESULT.FILE_NAME", $item["filename"]);
		} else {
			$vtp->SetVar($handle, "RESULT.FILE_NAME", '/docman/view.php/'.$_GET["group_id"].'/'.$item["docid"].'/'.urlencode($item["filename"]));
		}
		if ($is_editor) $vtp->SetVar($handle, "RESULT.STATE", $item["statename"]);
		$vtp->CloseSession($handle, "RESULT");
	}
	$vtp->CloseSession($handle, "RESULTSEARCH");
}

$vtp->CloseSession($handle, "MAIN");
$vtp->Display();

// print_debug (print_r($_POST,true));
// print_debug (print_r($groupsarr,true));

function print_debug($text) {
	echo "<pre>$text</pre>";
}

function get_path_document($groupsarr, $doc_group, $group_id) {
	$rep = "";
	foreach ($groupsarr as $group) {
		if ($group["doc_group"] == $doc_group) {
			if ($group["parent_doc_group"] == 0) {
				$href = util_make_url("docman/?group_id=$group_id&view=listfile&dirid=$group[doc_group]");
				$rep .= "<a href=\"$href\" style=\"color:#00610A;\">$group[groupname]</a>";
				break;
			}
			$s = get_path_document($groupsarr, $group["parent_doc_group"], $group_id);
			$href = util_make_url ("docman/?group_id=$group_id&view=listfile&dirid=$group[doc_group]");
			$rep .= "$s / <a href=\"$href\" style=\"color:#00610A;\">$group[groupname]</a>";
			break;
		}
	}
	return $rep;
}

function prepare_search_text($text) {
	$rep = $text;
	$rep = utf8_decode($rep);
	$rep = preg_replace("/é/", "/e/", $rep);
	$rep = preg_replace("/è/", "/e/", $rep);
	$rep = preg_replace("/ê/", "/e/", $rep);
	$rep = preg_replace("/à/", "/a/", $rep);
	$rep = preg_replace("/ù/", "/u/", $rep);
	$rep = preg_replace("/ç/", "/c/", $rep);
	$rep = preg_replace("/é/", "/e/", $rep);
	$rep = strtolower($rep);
	return $rep;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
