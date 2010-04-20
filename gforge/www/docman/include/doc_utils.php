<?php
/**
 * GForge Documentaion Manager
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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
		$result = db_query_params ('SELECT doc_group, groupname
FROM doc_groups
WHERE group_id = $1
ORDER BY groupname',
					   array ($group_id)) ;
		echo html_build_select_box ($result,'doc_group',$checkedval,false);

	} //end else

} //end display_groups_option


function docman_header($title,$pagehead,$style='xyz') {
	
	global $group_id, $HTML;

	if (!forge_get_config('use_docman')) {
		exit_disabled();
	}

	$project =& group_get_object($group_id);
	if (!$project || !is_object($project)) {
		exit_no_group();
	}

	if (!$project->usesDocman()) {
		exit_error(_('Error'),_('This project has turned off the Doc Manager.'));
	}

	site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'docman'));

	$menu_text=array();
	$menu_links=array();

	if (session_loggedin()) {
		$menu_text[]=_('Submit new documentation');
		$menu_links[]='/docman/new.php?group_id='.$group_id;
	}

	$menu_text[]=_('View Documentation');
	$menu_links[]='/docman/index.php?group_id='.$group_id;
	$menu_text[]=_('Search in documents');
	$menu_links[]='/docman/search.php?group_id='.$group_id;
	

	if (session_loggedin()) {
		$perm =& $project->getPermission(session_get_user());
		if ($perm && is_object($perm) && !$perm->isError() && $perm->isDocEditor()) {
			$menu_text[]=_('Admin');
			$menu_links[]='/docman/admin/index.php?group_id='.$group_id;
		}
	}
	echo $HTML->subMenu(
		$menu_text,
		$menu_links
	);

	plugin_hook ("blocks", "doc index");

}

function doc_droplist_count($l_group_id, $language_id, $g) {
	$pub = array () ;
	$pub[] = 1 ;
	if (session_loggedin()) {
		$perm =& $g->getPermission( session_get_user() );
		if ($perm && is_object($perm) && $perm->isMember()) {
			$pub[] = 4 ;
			$pub[] = 5 ;
		}
	}
	$gresult = db_query_params ('SELECT dd.language_id, sl.name, COUNT(*) AS count
		 FROM doc_groups AS dg, doc_data AS dd, supported_languages AS sl
		 WHERE dg.doc_group = dd.doc_group
		 AND dg.group_id = $1
		 AND dd.stateid = ANY ($2)
		 AND sl.language_id = dd.language_id
		 GROUP BY dd.language_id, sl.name',
				    array ($l_group_id,
					   db_int_array_to_any_clause ($pub))) ;
	if (db_numrows($gresult) >= 1) {

		print "<form name=\"langchoice\" action=\"index.php?group_id=".$l_group_id."\" method=\"post\"><table border=\"0\">
 <tr><td valign=\"middle\"><strong>"._('Language')." </strong></td>
 <td valign=\"middle\"><select name=\"language_id\">\n\n";
		print "<option value=\"*\">"._('All Languages')." </option>";
		while($grow = db_fetch_array($gresult)) {

			if ($language_id == $grow['language_id']) {

				print "<option value=\"".$grow['language_id']."\" selected=\"selected\">".$grow['name']." (".$grow['count'].") </option>";
			} else {
				print "<option value=\"".$grow['language_id']."\">".$grow['name']." (".$grow['count'].") </option>";
			}
		}
		print "</select></td><td valign=\"middle\"><input type=\"submit\" value=\""._('Go')."\" /></td></tr></table></form>";
	} else {
		echo db_error();
	}


}

function doc_get_state_box($checkedval='xzxz') {
	$res_states=db_query_params ('select * from doc_states;',
			array()) ;

	echo html_build_select_box ($res_states,'stateid',$checkedval,false);

}

function docman_footer($params) {
	site_project_footer($params);
}

/**
 * docman_display_documents - Recursive function to show the documents inside the groups tree
 */
function docman_display_documents(&$nested_groups, &$document_factory, $is_editor, $stateid=0, $from_admin=false, $parent_group=0) {
	global $selected_doc_group_id;

	$selected_stateid = getIntFromRequest('selected_stateid');
	$selected_doc_group_id=getIntFromRequest('selected_doc_group_id');
	
	

	
	if (!array_key_exists("$parent_group",$nested_groups) || !is_array($nested_groups["$parent_group"])) {
		return;
	}
	
	echo "<ul style='list-style-type: none'>\n";
	$child_count = count($nested_groups["$parent_group"]);
	
	for ($i=0; $i < $child_count; $i++) {		
		$doc_group =& $nested_groups["$parent_group"][$i];
		
		// Display group and subgroups only if it has associated documents
		if ($doc_group->hasDocuments($nested_groups, $document_factory, $stateid)) {
			// Recursive call			
			if (($doc_group->getID() == $selected_doc_group_id || $doc_group->hasSubgroup($nested_groups, $selected_doc_group_id)) && (!$stateid || $stateid == @$selected_stateid)) {
				$icon = 'ofolder15.png';
			} else {
				$icon = 'cfolder15.png';
			}
			echo "<li>".html_image('ic/'.$icon,"15","13",array("border"=>"0"))." <a href='index.php?group_id=".$doc_group->Group->getID()."&amp;selected_doc_group_id=".$doc_group->getID()."&amp;language_id=".@$GLOBALS['selected_language'];
			if ($from_admin && $stateid) {	// if we're sorting by the state, pass the state as a variable
				echo "&amp;selected_stateid=".$stateid;
			}
			echo "'>".$doc_group->getName()."</a>";
				
			// display link to add a document to the current group
			echo " &nbsp;&nbsp;&nbsp;&nbsp;<a href='".($from_admin ? "../" : "")."new.php?group_id=".$doc_group->Group->getID()."&amp;selected_doc_group=".$doc_group->getID()."'>";
			echo html_image('ic/adddoc12.png',"12","14",array("border"=>"0", 'align'=>'bottom'))." ";
			echo _('[Add document here]');
			echo "</a></li>\n";

			if (($doc_group->getID() == $selected_doc_group_id || $doc_group->hasSubgroup($nested_groups, $selected_doc_group_id)) && (!$stateid || $stateid == @$selected_stateid)) {
				docman_display_documents($nested_groups, $document_factory, $is_editor, $stateid, $from_admin, $doc_group->getID());
			}
		}
		
		// Display this group's documents
		if (($doc_group->hasSubgroup($nested_groups, $selected_doc_group_id) || $selected_doc_group_id == $doc_group->getID()) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
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
					$tooltip = $docs[$j]->getFileName() . " (" .
								($docs[$j]->getUpdated() ?
								date(_('Y-m-d H:i'), $docs[$j]->getUpdated()) :
								date(_('Y-m-d H:i'),$docs[$j]->getCreated()))  .
								") ";
					if ($docs[$j]->getFilesize() > 1024) {
						$tooltip .= floor($docs[$j]->getFilesize()/1024) . "KB";
					} else {
						$tooltip .= $docs[$j]->getFilesize() . "B";
					}
					$tooltip = htmlspecialchars($tooltip);					
					echo "<li>".
							html_image('ic/docman16b.png',"20","20",array("border"=>"0")).
							" 
<a href=\"".$link."\" title=\"$tooltip\">".
							$docs[$j]->getName().
							"</a> - " . $tooltip . "</li>
(".$docs[$j]->getFileSize()." "._('bytes').")";
							//add description
							echo "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							echo "<i>".$docs[$j]->getDescription()."</i>";
				}
				echo "</ul>";
			}
		}
	}
	echo "</ul>\n";
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
