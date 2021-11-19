<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2000, Quentin Cregan/SourceForge
 * Copyright 2002-2004, GForge Team
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2014,2016 Franck Villaume - TrivialDev
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

/**
 * doc_get_state_box
 *
 * @param string $checkedval
 * @param array $removedval
 * @return string
 */

function doc_get_state_box($checkedval = 'xzxz', $removedval = array()) {
	if (count($removedval)) {
		//TODO: find an easier way to get != ANY($1)
		$res_states = db_query_params('select * from doc_states where stateid NOT IN (select stateid from doc_states where stateid = ANY($1))', array(db_int_array_to_any_clause($removedval)));
	} else {
		$res_states = db_query_params('select * from doc_states', array());
	}
	return html_build_select_box($res_states, 'stateid', $checkedval, false);
}

/**
 * docman_fill_zip - Recursive function to add docgroup and documents inside zip for backup
 *
 * @param	object	$zip
 * @param	array	$nested_groups
 * @param	object	$document_factory
 * @param	int	$docgroup id : default value = 0
 * @param	string	$parent_docname parent name : default value = empty
 * @return	bool	success or not
 * @access	public
 */
function docman_fill_zip($zip, $nested_groups, $document_factory, $docgroup = 0, $parent_docname = '') {
	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			if ($parent_docname != '') {
				$path = iconv('UTF-8', 'ASCII//TRANSLIT', $parent_docname).'/'.iconv('UTF-8', 'ASCII//TRANSLIT', $dg->getName());
			} else {
				$path = iconv('UTF-8', 'ASCII//TRANSLIT', $dg->getName());
			}

			if (!$zip->addEmptyDir($path)) {
				return false;
			}

			$stateidArr = array(1);
			$stateIdDg = 1;
			if (forge_check_perm('docman', $document_factory->Group->getID(), 'approve')) {
				$stateidArr = array(1, 4, 5);
				$stateIdDg = 5;
			}
			$document_factory->setDocGroupID($dg->getID());
			$document_factory->setStateID($stateidArr);
			$document_factory->setDocGroupState($stateIdDg);
			$docs = $document_factory->getDocuments(1); // no caching
			if (is_array($docs) && count($docs)) {
				foreach ($docs as $doc) {
					if (!$doc->isURL() && !$zip->addFromString($path.'/'.iconv('UTF-8', 'ASCII//TRANSLIT', $doc->getFileName()), $doc->getFileData())) {
						return false;
					}
				}
			}
			if (!docman_fill_zip($zip, $nested_groups, $document_factory, $dg->getID(), $path)) {
				return false;
			}
		}
	}
	return true;
}
