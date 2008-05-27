<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


require_once $gfwww.'include/pre.php';
require_once $gfwww.'include/note.php';

/**
 *	Wrap many group display related functions
 */
class DocumentGroupHTML extends Error {
	var $Group;

	function DocumentGroupHTML(&$Group) {
		$this->Error();
		
		if (!$Group || !is_object($Group)) {
			$this->setError("DocumentGroupHTML:: Invalid Group");
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
	 * showTableNestedGroups - Display the tree of document groups
	 *
	 * This is a recursive function that is used to display the tree
	 *
	 * @param array Array of groups. This array contains information about the groups and their childs.
	 * @param int The number of row that is currently being showed. It is used for formatting purposes
	 * @param int The ID of the parent whose childs are being showed (0 for root groups)
	 * @param int The current level
	 */
	function showTableNestedGroups (&$group_arr, &$rowno, $parent=0, $level=0) {
		// No childs to display
		if (!is_array($group_arr) || !array_key_exists("$parent", $group_arr)) return;

		$child_count = count($group_arr["$parent"]);
		for ($i = 0; $i < $child_count; $i++) {
			$rowno++;
			$doc_group =& $group_arr["$parent"][$i];
			
			$margin = str_repeat("&nbsp;&nbsp;&nbsp;", $level);
			
			$img = "cfolder15.png";
/*
			// Display the folder icon opened or closed?
			if (array_key_exists("".$doc_group->getID(),$group_arr)) $img = "ofolder15.png";
			else $img = "cfolder15.png";
*/

			echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($rowno) .'>'.
				'<td>'.$doc_group->getID().'</td>'.
				'<td>'.$margin.html_image('ic/'.$img,"15","13",array("border"=>"0")).' '.
				'<a href="index.php?editgroup=1&amp;doc_group='.
					$doc_group->getID().'&amp;group_id='.$doc_group->Group->getID().'">'.
					$doc_group->getName().'</a></td></tr>';
			// Show childs (if any)
			$this->showTableNestedGroups($group_arr, $rowno, $doc_group->getID(), $level+1);
		}
	}
	
	/**
	 * showSelectNestedGroups - Display the tree of document groups inside a <select> tag
	 *
	 * @param array	Array of groups.
	 * @param string	The name that will be assigned to the input
	 * @param bool	Allow selection of "None"
	 * @param int	The ID of the group that should be selected by default (if any)
	 * @param array	Array of IDs of groups that should not be displayed
	 */
	function showSelectNestedGroups (&$group_arr, $select_name, $allow_none=true, $selected_id=0, $dont_display=array()) {
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
		
		echo html_build_select_box_from_arrays($id_array,$text_array,$select_name,$selected_id,false);
	}
	
	/**
	 * buildArrays - Build the arrays to call html_build_select_box_from_arrays()
	 *
	 * @param array Array of groups.
	 * @param array Reference to the array of ids that will be build
	 * @param array Reference to the array of group names
	 * @param array	Array of IDs of groups that should not be displayed
	 * @param int The ID of the parent whose childs are being showed (0 for root groups)
	 * @param int The current level
	 */
	function buildArrays(&$group_arr, &$id_array, &$text_array, &$dont_display, $parent=0, $level=0) {
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
