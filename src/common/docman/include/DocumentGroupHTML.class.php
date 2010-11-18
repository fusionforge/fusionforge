<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2002 GForge, LLC
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

require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/note.php';

/**
 * Wrap many group display related functions
 */
class DocumentGroupHTML extends Error {
	var $Group;

	function DocumentGroupHTML(&$Group) {
		$this->Error();

		if (!$Group || !is_object($Group)) {
			$this->setError(_('DocumentGroupHTML:: Invalid Project'));
			return false;
		}
		if ($Group->isError()) {
			$this->setError('DocumentGroupHTML:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		return true;
	}

	/**
	 * showSelectNestedGroups - Display the tree of document groups inside a <select> tag
	 *
	 * @param	array	Array of groups.
	 * @param	string	The name that will be assigned to the input
	 * @param	bool	Allow selection of "None"
	 * @param	int	The ID of the group that should be selected by default (if any)
	 * @param	array	Array of IDs of groups that should not be displayed
	 * @return	string	html select box code
	 */
	function showSelectNestedGroups ($group_arr, $select_name, $allow_none = true, $selected_id = 0, $dont_display = array()) {
		// Build arrays for calling html_build_select_box_from_arrays()
		$id_array = array();
		$text_array = array();

		if ($allow_none) {
			// First option to be displayed
			$id_array[] = 0;
			$text_array[] = "(None)";
		}

		// Recursively build the document group tree
		$this->buildArrays($group_arr, $id_array, $text_array, $dont_display);
		
		echo html_build_select_box_from_arrays($id_array, $text_array, $select_name, $selected_id, false);
	}

	/**
	 * buildArrays - Build the arrays to call html_build_select_box_from_arrays()
	 *
	 * @param	array	Array of groups.
	 * @param	array	Reference to the array of ids that will be build
	 * @param	array	Reference to the array of group names
	 * @param	array	Array of IDs of groups that should not be displayed
	 * @param	int	The ID of the parent whose childs are being showed (0 for root groups)
	 * @param	int	The current level
	 */
	function buildArrays($group_arr, &$id_array, &$text_array, &$dont_display, $parent = 0, $level = 0) {
		if (!is_array($group_arr) || !array_key_exists("$parent", $group_arr)) return;

		$child_count = count($group_arr["$parent"]);
		for ($i = 0; $i < $child_count; $i++) {
			$doc_group =& $group_arr["$parent"][$i];

			// Should we display this element?
			if (in_array($doc_group->getID(), $dont_display)) continue;

			$margin = str_repeat("--", $level);
			
			$id_array[] = $doc_group->getID();
			$text_array[] = $margin.$doc_group->getName();
			
			// Show childs (if any)
			$this->buildArrays($group_arr, $id_array, $text_array, $dont_display, $doc_group->getID(), $level+1);
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
