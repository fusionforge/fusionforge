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
if (!$g || !is_object($g)) {
	exit_error('Error','Could Not Get Group');
} elseif ($g->isError()) {
	exit_error('Error',$g->getErrorMessage());
}

if ($submit){

	if (!$doc_group || $doc_group ==100) {
		//cannot add a doc unless an appropriate group is provided
		exit_error($Language->getText('general','error'),$Language->getText('docman_new','no_valid_group'));
	}

	if (!$title || !$description || (!$uploaded_data && !$file_url)) {
		exit_missing_param();
	}

	$d = new Document($g);
	if (!$d || !is_object($d)) {
		exit_error($Language->getText('general','error'),$Language->getText('docman_new','error_blank_document'));
	} elseif ($d->isError()) {
		exit_error($Language->getText('general','error'),$d->getErrorMessage());
	}

	if ($uploaded_data) {
		if (!is_uploaded_file($uploaded_data)) {
			exit_error($Language->getText('general','error'),$Language->getText('general','invalid_filename'));
		}
		$data = addslashes(fread(fopen($uploaded_data, 'r'), filesize($uploaded_data)));
		$file_url='';
	} elseif ($file_url) {
		$data = '';
		$uploaded_data_name=$file_url;
		$uploaded_data_type='URL';
	}
	if (!$d->create($uploaded_data_name,$uploaded_data_type,$data,$doc_group,$title,$language_id,$description)) {
		exit_error($Language->getText('general','error'),$d->getErrorMessage());
	} else {
		Header("Location: /docman/?group_id=$group_id&feedback=".$Language->getText('docman_new','submitted_successfully'));
		exit;
	}

} else {
	docman_header($Language->getText('docman_new','title'),$Language->getText('docman_new','section'),'docman','',$g->getPublicName());
	?>
	<p>
	<?php echo $Language->getText('docman_new','intro') ?>
	</p>

	<form name="adddata" action="<?php echo "$PHP_SELF?group_id=$group_id"; ?>" method="post" enctype="multipart/form-data">

	<table border="0" width="75%">

	<tr>
		<td>
		<strong>	<?php echo $Language->getText('docman_new','doc_title') ?></strong> <?php echo utils_requiredField(); ?> (min 5 chars) <br />
		<input type="text" name="title" size="40" maxlength="255" />
		</td>
	</tr>

	<tr>
		<td>
		<strong>	<?php echo $Language->getText('docman_new','description') ?> :</strong><?php echo utils_requiredField(); ?> (min 10 chars) <br />
		<input type="text" name="description" size="50" maxlength="255" />
		</td>
	</tr>

	<tr>
		<td>
		<strong>	<?php echo $Language->getText('docman_new','upload_file') ?> :</strong><?php echo utils_requiredField(); ?><br />
		<input type="file" name="uploaded_data" size="30" /><br /><br />
		<strong>	<?php echo $Language->getText('docman_new','upload_url') ?> :</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="file_url" size="50" />
		</td>
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
	<input type="submit" name="submit" value="	<?php echo $Language->getText('docman_new','submit') ?> " />
	</form>
	<?php
	docman_footer(array());
}

?>
