<?php
/**
  *
  * SourceForge Documentaion Manager
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


/*
	by Quentin Cregan, SourceForge 06/2000
*/


require_once('doc_utils.php');
require_once('pre.php');

if($group_id) {

	if ($mode == "add"){

		if (!$doc_group || $doc_group ==100) {
			//cannot add a doc unless an appropriate group is provided
			exit_error('Error','No Valid Document Group Was Selected');
		}

		if (!$title || !$description) { 
			exit_missing_param();
		}

		if (!$uploaded_data) {
			exit_missing_param();
		}

		if (!session_loggedin()) {
			$user_id=100;
		} else {
			$user_id=user_getid();
		}

		if (!util_check_fileupload($uploaded_data)) {
			exit_error("Error","Invalid filename");
		}
		$data = fread(fopen($uploaded_data, 'r'), filesize($uploaded_data));
		
		docman_header('Documentation - Add Information - Processing','Documentation - New submission','docman_new','',group_getname($group_id));

		$query = "insert into doc_data(stateid,title,data,filename,filetype,createdate,
			updatedate,created_by,doc_group,description,language_id) 
		values('3','". htmlspecialchars($title). "','". base64_encode($data) ."','$uploaded_data_name','$uploaded_data_type','".time()."',
		'".time()."', '$user_id', '$doc_group', '".htmlspecialchars($description)."', '$lang_id')";
	
		$res = db_query($query); 

		if (!$res || db_affected_rows($res)<1) {
			print '<p><b><font color="red">Error adding new document: '.db_error().'</font></b></p>';
		} else {
			print "<p><b>Thank You!  Your submission has been placed in the database for review before posting.</b> \n\n<p>\n <a href=\"/docman/index.php?group_id=".$group_id."\">Back</a>"; 
		}

		docman_footer($params);
	} else {
		docman_header('Add documentation','Add documentation','docman_new','',group_getname($group_id));
		if (get_group_count($group_id) > 0){
			if ($user == 100) {
  			print "<p>You are not logged in, and will not be given credit for this.<p>";
			}
			
			echo '
			<p>

			<b> Document Title: </b> Refers to the relatively brief title of the document (e.g. How to use the download server)
			<br>
			<b> Description: </b> A brief description to be placed just under the title.<br>

			<form name="adddata" action="new.php?mode=add&group_id='.$group_id.'" method="POST" enctype="multipart/form-data">

			<table border="0" width="75%">

			<tr>
			<th>Document Title:</th>
			<td><input type="text" name="title" size="40" maxlength="255"></td>

			</tr>
			<tr>
			<th>Description:</th> 
			<td><input type="text" name="description" size="50" maxlength="255"></td>
			</tr>

			<tr>
			<th><B>Upload File:</B></th>
			<td> <input type="file" name="uploaded_data" size="30"></td>
			</tr>

			<tr>
			<th> Language:</th>
			<td>';
			
			echo html_get_language_popup($Language,'lang_id',1);
			
			echo	'</td>
			</tr>

			<tr>
			<th>Group that document belongs in:</th>
			<td>';

			display_groups_option($group_id);

			echo '	</td> </tr> </table>

			<input type="submit" value="Submit Information">

			</form> '; 
		}	// end if (project has doc categories)
		else {
			echo("At least one documentation category must be defined before you can submit a document.<BR>");
			
			$group = new Group($group_id);
			$perm =& $group->getPermission( session_get_user() );

			// if an admin, prompt for adding a category
			if ( $perm->isDocEditor() || $perm->isAdmin() ) {
				echo("<a href=\"/docman/admin/index.php?mode=editgroups&group_id=" . $group_id . "\">Add a document group</a>");
			}
		}
		docman_footer($params);
	} // end else.

} else {
	exit_no_group();
}

?>
