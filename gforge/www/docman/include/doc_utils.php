<?php
/**
 * GForge Documentaion Manager
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/*
	by Quentin Cregan, SourceForge 06/2000
*/


function display_groups_option($group_id=false,$checkedval='xzxz') {

	if (!$group_id) {
		exit_no_group();
	} else {
		$query = "select doc_group, groupname "
		."from doc_groups "
		."where group_id = '$group_id' "
		."order by groupname";
		$result = db_query($query);

		echo html_build_select_box ($result,'doc_group',$checkedval,false);

	} //end else

} //end display_groups_option


function docman_header($title,$pagehead,$pagename,$titleval,$sectionval,$style='xyz') {
	
	global $group_id, $Language, $HTML, $sys_use_docman;

	if (!$sys_use_docman) {
		exit_disabled();
	}

	$project =& group_get_object($group_id);
	if (!$project || !is_object($project)) {
		exit_no_group();
	}

	if (!$project->usesDocman()) {
		exit_error($Language->getText('general','error'),$Language->getText('docman','turned_off'));
	}

	site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'docman','pagename'=>$pagename,'titlevals'=>array($titleval),'sectionvals'=>array($sectionval)));

	$menu_text=array();
	$menu_links=array();

	$menu_text[]=$Language->getText('docman','submit_new');
	$menu_links[]='/docman/new.php?group_id='.$group_id;
	$menu_text[]=$Language->getText('docman','view_doc');
	$menu_links[]='/docman/index.php?group_id='.$group_id;

	if (session_loggedin()) {
		$perm =& $project->getPermission(session_get_user());
		if ($perm && is_object($perm) && !$perm->isError() && $perm->isDocEditor()) {
			$menu_text[]=$Language->getText('docman','admin');
			$menu_links[]='/docman/admin/index.php?group_id='.$group_id;
		}
	}
	echo $HTML->subMenu(
		$menu_text,
		$menu_links
	);
}

function doc_droplist_count($l_group_id, $language_id, $g) {
	global $Language;

	if (session_loggedin()) {
		$perm =& $g->getPermission( session_get_user() );
		if (!$perm || !is_object($perm) || !$perm->isMember()) {
			$public_flag='AND dd.stateid=1';
		} else {
			$public_flag='AND dd.stateid IN (1,4,5)';
		}
	} else {
		$public_flag='AND dd.stateid=1';
	}

	$query = "select dd.language_id, sl.name, count(*) as count
		 from doc_groups as dg, doc_data as dd, supported_languages as sl
		 where dg.doc_group = dd.doc_group
		 and dg.group_id = '$l_group_id'
		 $public_flag
		 and sl.language_id = dd.language_id
		 group by dd.language_id,sl.name";

	$gresult = db_query($query);


	if (db_numrows($gresult) >= 1) {

		print "<form name=\"langchoice\" action=\"index.php?group_id=".$l_group_id."\" method=\"post\"><table border=\"0\">"
			." <tr><td valign=\"middle\"><strong>".$Language->getText('general','language')." </strong></td>"
			." <td valign=\"middle\"><select name=\"language_id\">\n\n";
		print "<option value=\"*\">".$Language->getText('docman_display_doc','all_languages')." </option>";
		while($grow = db_fetch_array($gresult)) {

			if ($language_id == $grow['language_id']) {

				print "<option value=\"".$grow['language_id']."\" selected=\"selected\">".$grow['name']." (".$grow['count'].") </option>";
			} else {
				print "<option value=\"".$grow['language_id']."\">".$grow['name']." (".$grow['count'].") </option>";
			}
		}
		print "</select></td><td valign=\"middle\"><input type=\"submit\" value=\"".$Language->getText('general','go')."\" /></td></tr></table></form>";
	} else {
		echo db_error();
	}


}

function doc_get_state_box($checkedval='xzxz') {
	$res_states=db_query("select * from doc_states;");
	echo html_build_select_box ($res_states,'stateid',$checkedval,false);

}

function docman_footer($params) {
	site_project_footer($params);
}

/**
 * docman_display_documents - Recursive function to show the documents inside the groups tree
 */
function docman_display_documents(&$nested_groups, &$document_factory, $is_editor, $stateid=0, $from_admin=false, $parent_group=0) {
	global $selected_doc_group_id,$Language;

	if (!is_array($nested_groups["$parent_group"])) {
		return;
	}
	
	echo "<ul style='list-style-type: none'>";
	$child_count = count($nested_groups["$parent_group"]);
	
	for ($i=0; $i < $child_count; $i++) {
		$doc_group =& $nested_groups["$parent_group"][$i];
		
		// Display group and subgroups only if it has associated documents
		if ($doc_group->hasDocuments(&$nested_groups, &$document_factory, $stateid)) {
			// Recursive call
			if (($doc_group->getID() == $selected_doc_group_id || $doc_group->hasSubgroup(&$nested_groups, $selected_doc_group_id)) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
				$icon = 'ofolder15.png';
			} else {
				$icon = 'cfolder15.png';
			}
			echo "<li>".html_image('ic/'.$icon,"15","13",array("border"=>"0"))." <a href='index.php?group_id=".$doc_group->Group->getID()."&selected_doc_group_id=".$doc_group->getID()."&amp;language_id=".$GLOBALS['selected_language'];
			if ($from_admin && $stateid) {	// if we're sorting by the state, pass the state as a variable
				echo "&amp;selected_stateid=".$stateid;
			}
			echo "'>".$doc_group->getName()."</a>";
				
			// display link to add a document to the current group
			echo " &nbsp;&nbsp;&nbsp;&nbsp;<a href='".($from_admin ? "../" : "")."new.php?group_id=".$doc_group->Group->getID()."&amp;selected_doc_group=".$doc_group->getID()."'>";
			echo html_image('ic/adddoc12.png',"12","14",array("border"=>"0"))." ";
			echo $Language->getText('docman_admin', 'add_docs');
			echo "</a>";
			
			if (($doc_group->getID() == $selected_doc_group_id || $doc_group->hasSubgroup(&$nested_groups, $selected_doc_group_id)) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
				docman_display_documents(&$nested_groups, &$document_factory, $is_editor, $stateid, $from_admin, $doc_group->getID());
			}
		}
		
		// Display this group's documents
		if (($doc_group->hasSubgroup(&$nested_groups, $selected_doc_group_id) || $selected_doc_group_id == $doc_group->getID()) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
			// Retrieve all the docs from this category
			if ($stateid) {
				$document_factory->setStateID($stateid);
			}
			$document_factory->setDocGroupID($doc_group->getID());
			$docs = $document_factory->getDocuments();
			if (is_array($docs)) {
				$docs_count = count($docs);
				
				echo "<ul style='list-style-type: none'>";
				for ($j=0; $j < $docs_count; $j++) {
					if ($from_admin) {
						$link = "index.php?editdoc=1&amp;docid=".$docs[$j]->getID()."&amp;group_id=".$docs[$j]->Group->getID();
					} else {
						$link = (( $docs[$j]->isURL() ) ? $docs[$j]->getFileName() : "view.php/".$docs[$j]->Group->getID()."/".$docs[$j]->getID()."/".$docs[$j]->getFileName() );
					}
				
					echo "<li>".
							html_image('ic/docman16b.png',"20","20",array("border"=>"0")).
							" ".
							"<a href=\"".$link."\">".
							$docs[$j]->getName().
							"</a>";
				}
				echo "</ul>";
			}
		}
	}
	echo "</ul>\n";
}

?>
