<?php
/**
 * SourceForge Documentaion Manager
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
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

	global $group_id;
	global $Language;

	$project =& group_get_object($group_id);
	if (!$project || !is_object($project)) {
		exit_no_group();
	}   

	if (!$project->usesDocman()) {
		exit_error($Language->getText('general','error'),$Language->getText('general','turned_off',array('Doc Manager')));
	}

	site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'docman','pagename'=>$pagename,'titlevals'=>array($titleval),'sectionvals'=>array($sectionval)));

	print "<p><strong><a href=\"/docman/new.php?group_id=".$group_id."\">".$Language->getText('docman','submit_new')."</a> | ".
		"<a href=\"/docman/index.php?group_id=".$group_id."\">".$Language->getText('docman','view_doc')."</a> | ".
		"<a href=\"/docman/admin/index.php?group_id=".$group_id."\">".$Language->getText('docman','admin')."</a></strong>"; 
	
	print("<br />");
}

function doc_droplist_count($l_group_id, $language_id) {
	global $Language;

	$query = "select dd.language_id, sl.name, count(*) as count
		 from doc_groups as dg, doc_data as dd, supported_languages as sl
		 where dg.doc_group = dd.doc_group 
		 and dg.group_id = '$l_group_id' 
		 and dd.stateid = '1' 
		 and sl.language_id = dd.language_id 
		 group by dd.language_id,sl.name";

	$gresult = db_query($query);
	

	if (db_numrows($gresult) >= 1) {

		print "<form name=\"langchoice\" action=\"index.php?group_id=".$l_group_id."\" method=\"POST\"><table border=\"0\">"
			." <tr><td valign=\"center\"><strong>".$Language->getText('general','language').":</strong></td>"
			." <td valign=\"center\"><select name=\"language_id\">\n\n"; 
		print "<option value=\"0\">".$Language->getText('docman_display_doc','all_languages')." </option>";
		while($grow = db_fetch_array($gresult)) {

			if ($language_id == $grow['language_id']) {

				print "<option value=\"".$grow['language_id']."\" selected>".$grow['name']." (".$grow['count'].") </option>";
			} else {
				print "<option value=\"".$grow['language_id']."\">".$grow['name']." (".$grow['count'].") </option>";
			}	
		}	
		print "</select></td><td valign=\"center\"><input type=\"submit\" value=\"Go\"></td></tr></table></form>"; 
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

?>
