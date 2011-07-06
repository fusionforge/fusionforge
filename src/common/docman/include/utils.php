<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2000, Quentin Cregan/SourceForge
 * Copyright 2002-2004, GForge Team
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
 * docman_recursive_display - Recursive function to show the documents inside the groups tree : javascript enabled function
 *
 * @param	int	doc_group_id
 */
function docman_recursive_display($docgroup) {
	global $nested_groups, $group_id;
	global $idExposeTreeIndex, $dirid, $idhtml, $linkmenu;

	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			$idhtml++;

			if ($dirid == $dg->getID())
				$idExposeTreeIndex = $idhtml;

			echo "
				['".'<span class="JSCookTreeFolderClosed"><i><img alt="" src="\' + ctThemeXPBase + \'folder1.gif" /></i></span><span class="JSCookTreeFolderOpen"><i><img alt="" src="\' + ctThemeXPBase + \'folderopen1.gif" /></i></span>'."', '".addslashes($dg->getName())."', '?group_id=".$group_id."&amp;view=".$linkmenu."&amp;dirid=".$dg->getID()."', '', '',";
					docman_recursive_display($dg->getID());
			echo ",
				],";
		}
	}
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
			if (!$zip->addEmptyDir($parent_docname.'/'.$dg->getName()))
				return false;

			$document_factory->setDocGroupID($dg->getID());
			$docs = $document_factory->getDocuments(1);	// no caching
			if (is_array($docs) && count($docs) > 0) {	// this group has documents
				foreach ($docs as $doc) {
					if ( !$zip->addFromString($parent_docname.'/'.$dg->getName().'/'.$doc->getFileName(),$doc->getFileData()))
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

?>
