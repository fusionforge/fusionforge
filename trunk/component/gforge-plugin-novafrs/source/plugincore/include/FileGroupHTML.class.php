<?php
/**
 * GForge Fr Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: FileGroupHTML.class.php,v 1.4 2006/10/27 17:42:55 pascal Exp $
 */

require_once ("common/include/Error.class.php");

/**
 *	Wrap many group display related functions
 */
class FileGroupHTML extends Error {
	var $Group;

	function FileGroupHTML(&$Group) {
		$this->Error();
		
		if (!$Group || !is_object($Group)) {
			$this->setError("FileGroupHTML:: Invalid Group");
			return false;
		}
		if ($Group->isError()) {
			$this->setError('FileGroupHTML:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;


		return true;
	}

	/**
	 * showTableNestedGroups - Display the tree of file groups
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
			$fr_group =& $group_arr["$parent"][$i];
			
			$margin = str_repeat("&nbsp;&nbsp;&nbsp;", $level);
			
			$img = "cfolder15.png";
			echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($rowno) .'>'.
				'<td>'.$fr_group->getID().'</td>'.
				'<td>'.$margin.html_image('ic/'.$img,"15","13",array("border"=>"0")).' '.
				'<a href="index.php?editgroup=1&amp;fr_group='.
					$fr_group->getID().'&amp;group_id='.$fr_group->Group->getID().'">'.
					$fr_group->getName().'</a></td></tr>';
			// Show childs (if any)
			$this->showTableNestedGroups($group_arr, $rowno, $fr_group->getID(), $level+1);
		}
	}
	
	/**
	 * showSelectNestedGroups - Display the tree of file groups inside a <select> tag
	 *
	 * @param array	Array of groups.
	 * @param string	The name that will be assigned to the input
	 * @param bool	Allow selection of "None"
	 * @param int	The ID of the group that should be selected by default (if any)
	 * @param array	Array of IDs of groups that should not be displayed
	 * @param levelMax level max to be displayed (-1 for all)
	 */
	function showSelectNestedGroups (&$group_arr, $select_name, $allow_none=true, $selected_id=0, $dont_display=array(), $levelMax=-1) {
		// Build arrays for calling html_build_select_box_from_arrays()
		$id_array = array();
		$text_array = array();
		
		if ($allow_none) {
			// First option to be displayed
			$id_array[] = 0;
			$text_array[] = "(None)";
		}
		// Recursively build the file group tree
		$this->buildArrays($group_arr, $id_array, $text_array, $dont_display,0,0,$levelMax);
		
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
	 * @param levelMax level max to be displayed (-1 for all)
	 */
	function buildArrays(&$group_arr, &$id_array, &$text_array, &$dont_display, $parent=0, $level=0,$levelMax=-1) {
		if (!is_array($group_arr) || !array_key_exists("$parent", $group_arr)) return;

		$child_count = count($group_arr["$parent"]);
		for ($i = 0; $i < $child_count; $i++) {
			$fr_group =& $group_arr["$parent"][$i];
			
			// Should we display this element?
			if (in_array($fr_group->getID(), $dont_display)) continue;

			$margin = str_repeat("&nbsp;|&nbsp;&nbsp;", $level);
			
			$id_array[] = $fr_group->getID();
			$text_array[] = $margin.$fr_group->getName();
			
			// Show childs (if any)
    	    if( $levelMax==-1 || $level > $levelMax ){
	    		$this->buildArrays($group_arr, $id_array, $text_array, $dont_display, $fr_group->getID(), $level+1,$levelMax);
	    	}
		}

	}
}

?>
