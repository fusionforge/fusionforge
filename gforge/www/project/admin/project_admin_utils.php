<?php
/**
  *
  * Project Admin: Module of common functions
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


/*

	Standard header to be used on all /project/admin/* pages

*/

function project_admin_header($params) {
	global $DOCUMENT_ROOT,$group_id,$feedback;

	$params['toptab']='admin';
	$params['group']=$group_id;
	site_project_header($params);
	site_project_menu($params);
}

/*

	Standard footer to be used on all /project/admin/* pages

*/

function project_admin_footer($params) {
	site_project_footer($params);
}


/*


	The following functions are for the FRS (File Release System)


*/


/*

	pop-up box of supported frs statuses

*/

function frs_show_status_popup ($name='status_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of statuses
	*/
	global $FRS_STATUS_RES;
	if (!isset($FRS_STATUS_RES)) {
		$FRS_STATUS_RES=db_query("SELECT * FROM frs_status");
	}
	return html_build_select_box ($FRS_STATUS_RES,$name,$checked_val,false);
}

/*

	pop-up box of supported frs filetypes

*/

function frs_show_filetype_popup ($name='type_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of the available filetypes
	*/
	global $FRS_FILETYPE_RES;
	if (!isset($FRS_FILETYPE_RES)) {
		$FRS_FILETYPE_RES=db_query("SELECT * FROM frs_filetype");
	}
	return html_build_select_box ($FRS_FILETYPE_RES,$name,$checked_val,true,'Must Choose One');
}

/*

	pop-up box of supported frs processor options

*/

function frs_show_processor_popup ($name='processor_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of the available processors 
	*/
	global $FRS_PROCESSOR_RES;
	if (!isset($FRS_PROCESSOR_RES)) {
		$FRS_PROCESSOR_RES=db_query("SELECT * FROM frs_processor");
	}
	return html_build_select_box ($FRS_PROCESSOR_RES,$name,$checked_val,true,'Must Choose One');
}

/*

	pop-up box of packages:releases for this group

*/


function frs_show_release_popup ($group_id, $name='release_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of releases for the project
	*/
	global $FRS_RELEASE_RES;
	if (!$group_id) {
		return 'ERROR - GROUP ID REQUIRED';
	} else {
		if (!isset($FRS_RELEASE_RES)) {
			$FRS_RELEASE_RES=db_query("SELECT frs_release.release_id,(frs_package.name || ' : ' || frs_release.name) ".
				"FROM frs_release,frs_package ".
				"WHERE frs_package.group_id='$group_id' ".
				"AND frs_release.package_id=frs_package.package_id");
			echo db_error();
		}
		return html_build_select_box ($FRS_RELEASE_RES,$name,$checked_val,false);
	}
}

/*

	pop-up box of packages for this group

*/

function frs_show_package_popup ($group_id, $name='package_id', $checked_val="xzxz") {
	/*
		return a pop-up select box of packages for this project
	*/
	global $FRS_PACKAGE_RES;
	if (!$group_id) {
		return 'ERROR - GROUP ID REQUIRED';
	} else {
		if (!isset($FRS_PACKAGE_RES)) {
			$FRS_PACKAGE_RES=db_query("SELECT package_id,name 
				FROM frs_package WHERE group_id='$group_id'");
			echo db_error();
		}
		return html_build_select_box ($FRS_PACKAGE_RES,$name,$checked_val,false);
	}
}

/*

	The following three functions are for group
	audit trail

	When changes like adduser/rmuser/change status
	are made to a group, a row is added to audit trail
	using group_add_history()

*/

function group_get_history ($group_id=false) {
	$sql="SELECT group_history.field_name,group_history.old_value,group_history.date,users.user_name ".
		 "FROM group_history,users ".
		 "WHERE group_history.mod_by=users.user_id ".
		 "AND group_id='$group_id' ORDER BY group_history.date DESC";
	return db_query($sql);
}	       
	
function group_add_history ($field_name,$old_value,$group_id) {
	$group=group_get_object($group_id);
	$group->addHistory($field_name,$old_value);
}	       

/*

	Nicely html-formatted output of this group's audit trail

*/

function show_grouphistory ($group_id) {
	/*      
		show the group_history rows that are relevant to 
		this group_id
	*/
	global $sys_datefmt;
	$result=group_get_history($group_id);
	$rows=db_numrows($result);
	
	if ($rows > 0) {
	
		echo '
		<H3>Group Change History</H3>
		<P>';
		$title_arr=array();
		$title_arr[]='Field';
		$title_arr[]='Old Value';
		$title_arr[]='Date';
		$title_arr[]='By';
		
		echo $GLOBALS['HTML']->listTableTop ($title_arr);
		
		for ($i=0; $i < $rows; $i++) { 
			$field=db_result($result, $i, 'field_name');
			echo '
			<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD>'.$field.'</TD><TD>';
			
			if ($field=='removed user') {
				echo user_getname(db_result($result, $i, 'old_value'));
			} else {
				echo db_result($result, $i, 'old_value');
			}			
			echo '</TD>'.
				'<TD>'.date($sys_datefmt,db_result($result, $i, 'date')).'</TD>'.
				'<TD>'.db_result($result, $i, 'user_name').'</TD></TR>';
		}	       

		echo $GLOBALS['HTML']->listTableBottom();

	} else {
		echo '  
		<H3>No Changes Have Been Made to This Group</H3>';
	}       
}       

/*
	prdb_namespace_seek - check that a projects' potential db name hasn't
	already been used.  If it has - add a 1..20 to the end of it.  If it 
	iterates through twenty times and still fails - namespace depletion - 
	throw an error.

 */
function prdb_namespace_seek($namecheck) {

	$query = "select * "
		."from prdb_dbs "
		."where dbname = '$namecheck'";

	$res_dbl = db_query($query);

	if (db_numrows($res_dbl) > 0) {
		//crap, we're going to have issues
		$curr_num = 1;

		while ((db_numrows($res_dbl) > 0) && ($curr_num < 20)) {
			
			$curr_num++;
			$namecheck .= "$namecheck"."$curr_num";
					
			$query = "select * "
				."from prdb_dbs "
				."where dbname = '$namecheck'";

			$res_dbl = db_query($query);
		}

		// if we reached 20, then the namespace is depleted - eject eject
		if ($curr_num == 20) {
			exit_error("Namespace Failure","Failed to find namespace for database");
		}

	}
	return $namecheck;

} //end prdb_namespace_seek()

function random_pwgen() {

	srand ( (double) microtime()*10000000); 
	$rnpw = "";

	for ($i = 0; $i < 10; $i++) {

		$rn = rand(1,2);

		if ($rn == 1) {
			$rnpw .= rand(1,9);
		} else {
			$rnpw .= chr(rand(65,122));
		}

	}
	return rnpw;
}

function permissions_blurb() {
	return '
	<B>NOTE:</B>

	<dl>
	<dt><B>Project Admins (bold)</B></dt>
	<dd>can access this page and other project administration pages</dd>

	<dt><B>Release Technicians</B></dt>
	<dd>can make the file releases (any project admin also a release technician)</dd>
	'.

	/*
	'<dt><B>CVS Admins</B></dt>
	<dd><!-- can --> <i>will</i> be able to access repository files directly (in addition to standard write access)</dd>
	'.
	*/

	'<dt><B>Tool Technicians (T)</B></dt>
	<dd>can be assigned Bugs/Tasks/Patches</dd>

	<dt><B>Tool Admins (A)</B></dt>
	<dd>can make changes to Bugs/Tasks/Patches as well as use the /toolname/admin/ pages</dd>

	<dt><B>Tool No Permission (N/A)</B></dt>
	<dd>Developer doesn\'t have specific permission (currently
	equivalent to \'-\')</dd>

	<dt><B>Moderators</B> (forums)</dt>
	<dd>can delete messages from the project forums</dd>

	<dt><B>Editors</B> (doc. manager)</dt>
	<dd>can update/edit/remove documentation from the project.</dd>
	</dl>
	';
}

?>
