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
		exit_error($Language->getText('general','error'),$Language->getText('general','no_valid_group',array('Document')));
	}

	if (!$title || !$description) { 
		exit_missing_param();
	}

	if (!$uploaded_data) {
		exit_missing_param();
	}

	if (!is_uploaded_file($uploaded_data)) {
		exit_error($Language->getText('general','error'),$Language->getText('general','invalid_filename'));
	}
	$d = new Document($g);
	if (!$d || !is_object($d)) {
		exit_error($Language->getText('general','error'),$Language->getText('docman_new','error_blank_document'));
	} elseif ($d->isError()) {
		exit_error($Language->getText('general','error'),$d->getErrorMessage());
	}

	$data = addslashes(fread(fopen($uploaded_data, 'r'), filesize($uploaded_data)));
	if (!$d->create($uploaded_data_name,$uploaded_data_type,$data,$doc_group,$title,$language_id,$description)) {
		exit_error($Language->getText('general','error'),$d->getErrorMessage());
	} else {
		Header("Location: /docman/?group_id=$group_id&feedback=".$Language->getText('general','submitted_successfully','Document'));
		exit;
	}

} else {
	docman_header($Language->getText('docman_new','title'),$Language->getText('docman_new','section'),'docman','',$g->getPublicName());
	?>
	<p>
	<?php echo $Language->getText('docman_new','intro') ?> 
	<br />

	<form name="adddata" action="<?php echo "$PHP_SELF?group_id=$group_id"; ?>" method="POST" enctype="multipart/form-data">

	<table border="0" width="75%">

	<tr>
		<td>
		<strong>	<?php echo $Language->getText('docman_new','doc_title') ?></strong> <?php echo utils_requiredField(); ?> <br />
		<input type="text" name="title" size="40" maxlength="255">
		</td>
	</tr>

	<tr>
		<td>
		<strong>	<?php echo $Language->getText('docman_new','description') ?> :</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="description" size="50" maxlength="255">
		</td>
	</tr>

	<tr>
		<td>
		<strong>	<?php echo $Language->getText('docman_new','upload_file') ?> :</strong><?php echo utils_requiredField(); ?><br />
		<input type="file" name="uploaded_data" size="30">
		</td>
	</tr>

	<tr>
		<td>
		<strong>	<?php echo $Language->getText('docman_new','language') ?> :</strong><br />
		<?php
			echo html_get_language_popup($Language,'language_id',1);
		?>
		</td>
	</tr>

	<tr>
		<td>
		<strong>	<?php echo $Language->getText('docman_new','group') ?> :</strong><br />
		<?php
			display_groups_option($group_id);
		?>
		</td>
	</tr>

	</table>
	<input type="submit" name="submit" value="	<?php echo $Language->getText('docman_new','submit') ?> ">
	</form>
	<?php
	docman_footer(array());
}

?>
