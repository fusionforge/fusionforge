<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2000, Quentin Cregan/SourceForge
 * Copyright 2002-2004, GForge Team
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

/**
 * tooling library
 */

function doc_get_state_box($checkedval = 'xzxz', $removedval = '') {
	if (!empty($removedval)) {
		$res_states = db_query_params('select * from doc_states where stateid not in ($1)', array($removedval));
	} else {
		$res_states = db_query_params('select * from doc_states', array());
	}
	echo html_build_select_box($res_states, 'stateid', $checkedval, false);
}

/**
 * docman_fill_zip - Recursive function to add docgroup and documents inside zip for backup
 *
 * @param	$object	zip
 * @param	$array	nested groups
 * @param	$object	documentfactory
 * @param	$int	documentgroup id : default value = 0
 * @param	$string	documentgroup parent name : default value = empty
 * @return	boolean	success or not
 * @access	public
 */
function docman_fill_zip($zip, $nested_groups, $document_factory, $docgroup = 0, $parent_docname = '') {
	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			if (!$zip->addEmptyDir(iconv("UTF-8", "ASCII//TRANSLIT", $parent_docname).'/'.iconv("UTF-8", "ASCII//TRANSLIT", $dg->getName())))
				return false;

			$document_factory->setDocGroupID($dg->getID());
			$docs = $document_factory->getDocuments(1);	// no caching
			if (is_array($docs) && count($docs) > 0) {	// this group has documents
				foreach ($docs as $doc) {
					if (!$zip->addFromString(iconv("UTF-8", "ASCII//TRANSLIT", $parent_docname).'/'.iconv("UTF-8", "ASCII//TRANSLIT", $dg->getName()).'/'.iconv("UTF-8", "ASCII//TRANSLIT", $doc->getFileName()), $doc->getFileData()))
						return false;
				}
			}
			if (!docman_fill_zip($zip, $nested_groups, $document_factory, $dg->getID(), $parent_docname.'/'.$dg->getName())) {
				return false;
			}
		}
	}
	return true;
}

function docman_recursive_stateid($docgroup, $nested_groups, $nested_docs, $stateid = 2) {
	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			$dg->setStateID($stateid);
		}
	}
	if (isset($nested_docs[$docgroup]) && is_array($nested_docs[$docgroup])) {
		foreach ($nested_docs[$docgroup] as $d) {
			$d->setState($stateid);
		}
	}
}

function get_path_document($groupsarr, $doc_group, $group_id) {
	$rep = "";
	foreach ($groupsarr as $group) {
		if ($group["doc_group"] == $doc_group) {
			if ($group["parent_doc_group"] == 0) {
				$href = util_make_uri("docman/?group_id=$group_id&amp;view=listfile&amp;dirid=$group[doc_group]");
				$rep .= "<a href=\"$href\" style=\"color:#00610A;\">$group[groupname]</a>";
				break;
			}
			$s = get_path_document($groupsarr, $group["parent_doc_group"], $group_id);
			$href = util_make_uri("docman/?group_id=$group_id&amp;view=listfile&amp;dirid=$group[doc_group]");
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

?>
