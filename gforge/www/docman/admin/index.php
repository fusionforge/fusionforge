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
		Docmentation Manager
		by Quentin Cregan, SourceForge 06/2000
*/


require_once('../doc_utils.php');
require_once('pre.php');

if (!($group_id)) {
	exit_no_group();
}

if (!(user_ismember($group_id,"D1"))) {
	exit_permission_denied();
}

function main_page($group_id) {
		docman_header('Document Admin Page','Document Manager Admin','docman_admin','admin',group_getname($group_id),'admin');
		echo '<p><b>Pending Submissions:</b>  <p>';
		display_docs('3',$group_id);
		// doc_group 3 == pending
		echo '<p>';
		echo '<b>Active Submissions:</b>  <p>';
		display_docs('1',$group_id);
		//doc_group 1 == active
		docman_footer($params);

}//end function main_page($group_id);

//begin to seek out what this page has been called to do.

	if (strstr($mode,"docedit")) {
		$query = "select * from doc_data,doc_groups "
			."where docid='$docid' "
			."and doc_groups.doc_group = doc_data.doc_group "
			."and doc_groups.group_id = '$group_id'";
		$result = db_query($query);
		$row = db_fetch_array($result);
	
		docman_header('Edit Document','Edit Document','docman_admin_docedit','admin',group_getname($group_id),'');

		echo '
	
			<form name="editdata" action="index.php?mode=docdoedit&group_id='.$group_id.'" method="POST" enctype="multipart/form-data">

			<table border="0" width="75%">

			<tr>
					<th>Document Title:</th>
					<td><input type="text" name="title" size="40" maxlength="255" value="'.$row['title'].'"></td>
					<td class="example">(e.g. How to use the download server)</td>

			</tr>
			<tr>
			</tr>
			<tr>
					<th>Short Description:</td>
					<td><input type="text" name="description" size="20" maxlength="255" value="'.$row['description'].'"></td>
					<td class="example">(e.g. http://www.linux.com/)</td>

			</tr>
			<tr>
				<th>File:</th>
				<td><a target="_blank" href="../display_doc.php/'.$row['docid'].'/'.$row['filename'].'">'.$row['title'].'</A>
			</tr>
			<tr>
				<th>Language:</th>
				<td>';

		echo html_get_language_popup($Language,'language_id',$row['language_id']);

		echo	'
			<tr>
					<th>Group doc belongs in:</th>
					<td>';

		display_groups_option($group_id,$row['doc_group']);

		echo '			</td>
				</tr>

				<tr>
						<th>State:</th>
						<td>';

		doc_get_state_box($row['stateid']);

		echo '
	   				</td>
			</tr>';

		//	if this is a text/html doc, display an edit box
		if (strstr($row['filetype'],'ext')) {

			echo	'
				<tr>
					<th>Document Contents:</th>
					<td><textarea cols="80" rows="20" name="data">'. htmlspecialchars(base64_decode($row['data'])).'</textarea></td>
				</tr>';
		}

		echo '
		<tr>
			<th>OPTIONAL: Upload New File:</th>
			<td><input type="file" name="uploaded_data" size="30"></td>
			</tr>
		</table>

		<input type="hidden" name="docid" value="'.$row['docid'].'">
		<input type="submit" value="Submit Edit">

		</form>';

		docman_footer($params);
	} elseif (strstr($mode,"groupdelete")) {
		$query = "select docid "
			."from doc_data "
			."where doc_group = '$doc_group'";
		$result = db_query($query);
		if (db_numrows($result) < 1) {
			$query = "delete from doc_groups "
				."where doc_group = '$doc_group' "
				."and group_id = '$group_id'";
			db_query($query);
			docman_header("Group Delete","Group Delete",'docman_admin_groupdelete','admin',group_getname($group_id),'');
			print "<p><b>Group deleted. (GroupID : ".$doc_group.")</b>";	
			docman_footer($params);	

		} else {
		
			docman_header("Group Delete","Group Delete Failed",'docman_admin_groupdelete','admin',group_getname($group_id),'');
			print "Group was not deleted.  Cannot delete groups that still have documents grouped under them."; 
			docman_footer($params);
		}
		
	} elseif (strstr($mode,"groupedit")) {
			docman_header('Group Edit','Group Edit','docman_admin_groupedit','admin',group_getname($group_id),'');
			$query = "select * "
				."from doc_groups "
				."where doc_group = '$doc_group' "
				."and group_id='$group_id'";
			$result = db_query($query);
			$row = db_fetch_array($result);
			echo '
			<b> Edit a group:</b>

			<form name="editgroup" action="index.php?mode=groupdoedit&group_id='.$group_id.'" method="POST">
			<table>
			<tr><th>Name:</th>  <td><input type="text" name="groupname" value="'.$row['groupname'].'"></td></tr>
			<input type="hidden" name="doc_group" value="'.$row['doc_group'].'">
			<tr><td> <input type="submit"></td></tr></table>	
			</form>	
			';
			docman_footer($params);

	} elseif (strstr($mode,"groupdoedit")) {
		$query = "update doc_groups "
			."set groupname='".htmlspecialchars($groupname)."' "
			."where doc_group='$doc_group' "
			."and group_id = '$group_id'";
		db_query($query);
		$feedback .= "Document Group Edited.";
		main_page($group_id);

	} elseif (strstr($mode,"docdoedit")) {
		//Page security - checks someone isnt updating a doc
		//that isnt theirs.

		$query = "select dd.docid "
			."from doc_data dd, doc_groups dg "
			."where dd.doc_group = dg.doc_group "
			."and dg.group_id = '$group_id' "
			."and dd.docid = '$docid'"; 
		
		$result = db_query($query);
	
		if (db_numrows($result) == 1) {	

			if ($data) {
				$datastring = "data = '". base64_encode($data) ."',";
			}
			if ($uploaded_data_name) {
				if (!is_uploaded_file($uploaded_data)) {
					exit_error("Error","Invalid file attack attempt $uploaded_data");
				}
				$data = fread(fopen($uploaded_data, 'r'), filesize($uploaded_data));
				$datastring = "data = '". base64_encode($data) ."',
					filename='$uploaded_data_name',
					filetype='$uploaded_data_type',";
			}
			// data in DB stored in htmlspecialchars()-encoded form
			$query = "update doc_data "
				."set title = '".htmlspecialchars($title)."', "
				.$datastring
				."updatedate = '".time()."', "
				."doc_group = '".$doc_group."', "
				."stateid = '".$stateid."', "
				."language_id = '".$language_id."', "
				."description = '".htmlspecialchars($description)."' "
				."where docid = '$docid'"; 
		
			$res = db_query($query);
			if (!$res || db_affected_rows($res)<1) {
				$feedback .= 'Could not update document<br>';
			} else {
				$feedback .= "Document \" ".htmlspecialchars($title)." \" updated";
			}
			main_page($group_id);

		} else {

			exit_error("Error","Unable to update - Document does not exist, or document's group not the same as that to which your account belongs.");

		}

	} elseif (strstr($mode,"groupadd")) {
		$query = "insert into doc_groups(groupname,group_id) " 
			."values ('"
			."".htmlspecialchars($groupname)."',"
			."'$group_id')";
		
		db_query($query);
		$feedback .= "Group ".htmlspecialchars($groupname)." added.";
		main_page($group_id);
	
	} elseif (strstr($mode,"editgroups")) {
		docman_header('Group Edit', 'Group Edit','docman_admin_editgroups','admin',group_getname($group_id),'');
		echo '
			<p><b> Add a group:</b>
			<form name="addgroup" action="index.php?mode=groupadd&group_id='.$group_id.'" method="POST">
			<table>
			<tr><th>New Group Name:</th>  <td><input type="text" name="groupname"></td><td><input type="submit" value="Add"></td></tr></table>	
			<p>
			Group name will be used as a title, so it should be
			formatted correspondingly.
			</p>
			</form>	
		';
		display_groups($group_id);

	} elseif (strstr($mode,"editdocs")) {

		docman_header('Edit documents list','Edit documents','docman_admin_editdocs','admin',group_getname($group_id),'');
		
		print "<p><b>Active Documents:</b><p>";	
		display_docs('1',$group_id);
		print "<p><b>Pending Documents:</b><p>";	
		display_docs('3',$group_id);
		print "<p><b>Hidden Documents:</b><p>";	
		display_docs('4',$group_id);
		print "<p><b>Deleted Documents:</b><p>";	
		display_docs('2',$group_id);
		print "<p><b>Private Documents:</b><p>";	
		display_docs('5',$group_id);
		docman_footer($params);	

	} else {
		main_page($group_id);
	} //end else

?>
