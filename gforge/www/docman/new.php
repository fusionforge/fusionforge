<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/


require_once('pre.php');
require_once('common/docman/Document.class');
require_once('include/doc_utils.php');

if (!$group_id) {
	exit_no_group();
}
$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

if ($submit){

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

	if (!is_uploaded_file($uploaded_data)) {
		exit_error("Error","Invalid filename");
	}
	$d = new Document($g);
	if (!$d || !is_object($d)) {
		exit_error('Error','Error getting blank document');
	} elseif ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	$data = addslashes(fread(fopen($uploaded_data, 'r'), filesize($uploaded_data)));
	if (!$d->create($uploaded_data_name,$uploaded_data_type,$data,$doc_group,$title,$language_id,$description)) {
		exit_error('Error',$d->getErrorMessage());
	} else {
		Header("Location: /docman/?group_id=$group_id&feedback=Document+Submitted+Successfully");
		exit;
	}

} else {
	docman_header('Add documentation','Add documentation','docman_new','',$g->getPublicName());
	?>
	<p>
	<b> Document Title: </b> Refers to the relatively brief title of the document 
	(e.g. How to use the download server)
	<br>
	<b> Description: </b> A brief description to be placed just under the title.<br>

	<form name="adddata" action="<?php echo "$PHP_SELF?group_id=$group_id"; ?>" method="POST" enctype="multipart/form-data">

	<table border="0" width="75%">

	<tr>
		<td>
		<b>Document Title:</b><br>
		<input type="text" name="title" size="40" maxlength="255">
		</td>
	</tr>

	<tr>
		<td>
		<b>Description:</b><br>
		<input type="text" name="description" size="50" maxlength="255">
		</td>
	</tr>

	<tr>
		<td>
		<b>Upload File:</b><br>
		<input type="file" name="uploaded_data" size="30">
		</td>
	</tr>

	<tr>
		<td>
		<b>Language:</b><br>
		<?php
			echo html_get_language_popup($Language,'language_id',1);
		?>
		</td>
	</tr>

	<tr>
		<td>
		<b>Group that document belongs in:</b><br>
		<?php
			display_groups_option($group_id);
		?>
		</td>
	</tr>

	</table>
	<input type="submit" name="submit" value="Submit Information">
	</form>
	<?php
	docman_footer(array());
}

?>
