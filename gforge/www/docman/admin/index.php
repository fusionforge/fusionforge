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
require_once('www/docman/include/doc_utils.php');
require_once('common/docman/DocumentFactory.class');
require_once('common/docman/DocumentGroup.class');

if (!$group_id) {
	exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$perm =& $g->getPermission( session_get_user() );
if (!$perm || $perm->isError() || !$perm->isDocEditor()) {
	exit_permission_denied();
}

//
//
//	Submit the changes to the database
//
//

if ($submit) {

	if ($editdoc) {

		$d= new Document($g,$docid);
		if ($d->isError()) {
			exit_error($Language->getText('general','error'),$d->getErrorMessage());
		}
		if ($uploaded_data_name) {
			if (!is_uploaded_file($uploaded_data)) {
				exit_error($Language->getText('general','error'),$Language->getText('docman','error_invalid_file_attack', $uploaded_data));
			}
			$data = addslashes(fread(fopen($uploaded_data, 'r'), filesize($uploaded_data)));
			$filename=$uploaded_data_name;
			$filetype=$uploaded_data_type;
		} else {
			$filename=$d->getFileName();
			$filetype=$d->getFileType();
		}
		if (!$d->update($filename,$filetype,$data,$doc_group,$title,$language_id,$description,$stateid)) {
			exit_error('Error',$d->getErrorMessage());
		}
		$feedback = $Language->getText('general','update_successful');

	} elseif ($editgroup) {

		$dg = new DocumentGroup($g,$doc_group);
		if ($dg->isError()) {
			exit_error('Error',$dg->getErrorMessage());
		}
		if (!$dg->update($groupname)) {
			exit_error('Error',$dg->getErrorMessage());
		}
		$feedback = $Language->getText('general','update_successful');
		

	} elseif ($addgroup) {

		$dg = new DocumentGroup($g);
		if ($dg->isError()) {
			exit_error('Error',$dg->getErrorMessage());
		}
		if (!$dg->create($groupname)) {
			exit_error('Error',$dg->getErrorMessage());
		}
		$feedback = $Language->getText('general','create_successful');
	}

}

//
//
//	Edit a specific document
//
//
if ($editdoc && $docid) {
	
	$d= new Document($g,$docid);
	if ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	docman_header($Language->getText('docman_admin_editdocs','title'),'Edit Document','docman_admin_docedit','admin',$g->getPublicName(),'');

	?>
		<br />
		<?php echo $Language->getText('docman_new','intro') ?>
	<form name="editdata" action="index.php?editdoc=1&group_id=<?php echo $group_id; ?>" method="POST" enctype="multipart/form-data">

	<table border="0">

	<tr>
		<td>
		<strong><?php echo $Language->getText('docman_new','doc_title') ?>:</strong><br />
		<input type="text" name="title" size="40" maxlength="255" value="<?php echo $d->getName(); ?>">
		<br /></td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('docman_new','description') ?></strong><br />
		<input type="text" name="description" size="20" maxlength="255" value="<?php echo $d->getDescription(); ?>">
		<br /></td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('docman_new','file')?></strong><br />
		<a target="_blank" href="../view.php/<?php echo $group_id.'/'.$d->getID().'/'.$d->getFileName() ?>"><?php echo $d->getName(); ?></a>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('docman_new','language') ?></strong><br />
		<?php

			echo html_get_language_popup($Language,'language_id',$d->getLanguageID());

		?></td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('docman_new','group') ?>:</strong><br />
		<?php

			echo display_groups_option($group_id,$d->getDocGroupID());

		?></td>
	</tr>

	<tr>
		<td>
		<br /><?php echo $Language->getText('docman_admin_editdocs','state') ?>:</strong><br />
		<?php

			doc_get_state_box($d->getStateID());

		?></td>
	</tr>

	<?php

	//	if this is a text/html doc, display an edit box
	if (strstr($d->getFileType(),'ext')) {

		echo	'
	<tr>
		<td>
		<strong>'.$Language->getText('docman_admin_editdocs','doc_contents').'</strong><br />
		<textarea cols="80" rows="20" name="data">'. htmlspecialchars( $d->getFileData() ).'</textarea>
		</td>
	</tr>';
	}

	?>
	<tr>
		<td>
		<strong><?php echo $Language->getText('docman_admin_editdocs','upload') ?></strong><br />
		<input type="file" name="uploaded_data" size="30">
		</td>
	</tr>
	</table>

	<input type="hidden" name="docid" value="<?php echo $d->getID(); ?>">
	<input type="submit" value="<?php echo $Language->getText('general','submit_edit') ?>" name="submit">

	</form>
	<?php

	docman_footer(array());

//
//
//	Add a document group / view existing groups list
//
//
} elseif ($addgroup) {

	docman_header($Language->getText('docman_admin_editgroups','title'),$Language->getText('docman_admin_editgroups','section'),'docman_admin_editgroups','admin',$g->getPublicName(),'');

	echo "<h1>".$Language->getText('docman_admin_addgroups','title')."</h1>";

	/*
		List of possible categories for this ArtifactType
	*/
	$result=db_query("SELECT * FROM doc_groups WHERE group_id='$group_id'");
	echo "<p>";
	$rows=db_numrows($result);
	if ($result && $rows > 0) {
		$title_arr=array();
		$title_arr[]='ID';
		$title_arr[]='Title';

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		for ($i=0; $i < $rows; $i++) {
			echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
				'<td>'.db_result($result, $i, 'doc_group').'</td>'.
				'<td><a href="index.php?editgroup=1&doc_group='.
					db_result($result, $i, 'doc_group').'&group_id='.$group_id.'">'.
					db_result($result, $i, 'groupname').'</a></td></tr>';
		}

		echo $GLOBALS['HTML']->listTableBottom();

	} else {
		echo "\n<h1>No Document Groups Defined</h1>";
	}

	?>
	<p><strong><?php echo $Language->getText('docman_admin_editgroups','add_group') ?>:</strong>
	<form name="addgroup" action="index.php?addgroup=1&group_id=<?php echo $group_id; ?>" method="POST">
	<table>
		<tr>
			<th><?php echo $Language->getText('docman_admin_editgroups','new_group_name') ?>:</th>
			<td><input type="text" name="groupname"></td>
			<td><input type="submit" value="Add" name="submit"></td>
		</tr>
	</table>	
	<p>
		 <?php echo $Language->getText('docman_admin_editgroups','description') ?>
	</p>
	</form>	
	<?php

	docman_footer(array());

//
//
//	Edit a specific doc group
//
//
} elseif ($editgroup && $doc_group) {

	$dg = new DocumentGroup($g,$doc_group);
	if ($dg->isError()) {
		exit_error('Error',$dg->getErrorMessage());
	}

	docman_header($Language->getText('docman_admin_editgroups','title'),$Language->getText('docman_admin_editgroups','section'),'docman_admin_editgroups','admin',$g->getPublicName(),'');
	?>
	<p><strong><?php echo $Language->getText('docman_admin_editgroups','edit_group') ?></strong>
	<form name="editgroup" action="index.php?editgroup=1&group_id=<?php echo $group_id; ?>" method="POST">
	<input type="hidden" name="doc_group" value="<?php echo $doc_group; ?>">
	<table>
		<tr>
			<th><?php echo $Language->getText('docman_admin_groupedit','group_name') ?>:</th>
			<td><input type="text" name="groupname" value="<?php echo $dg->getName(); ?>"></td>
			<td><input type="submit" value="<?php echo $Language->getText('general','edit') ?>" name="submit"></td>
		</tr>
	</table>	
	<p>
		 <?php echo $Language->getText('docman_admin_editgroups','description') ?>

	</p>
	</form>	
	<?php
	docman_footer(array());

//
//
//	Display the main admin page
//
//
} else {

	$df = new DocumentFactory($g);
	if ($df->isError()) {
		exit_error($Language->getText('general','error'),$df->getErrorMessage());
	}
	$df->setStateID('ALL');
	$df->setSort('stateid');
	$d_arr =& $df->getDocuments();

	docman_header($Language->getText('docman_admin','title'),$Language->getText('docman_admin','section'),'docman_admin','admin',$g->getPublicName(),'admin');

	?>
	<h3><?php echo $Language->getText('docman_admin','title') ?></h3>
	<p>
	<a href="index.php?group_id=<?php echo $group_id; ?>&addgroup=1"><?php echo $Language->getText('docman_admin','add_edit_docgroups') ?></a>
	<p>
	<?php

	if (!$d_arr || count($d_arr) < 1) {
		print "<strong>".$Language->getText('docman','error_no_docs').".</strong><p>";
	} else {
	//	  doc_droplist_count($group_id, $language_id);

		print "\n<ul>";
		for ($i=0; $i<count($d_arr); $i++) {

			//
			//  If we're starting a new "group" of docs, put in the
			//  docGroupName and start a new <ul>
			//
			if ($d_arr[$i]->getStateID() != $last_state) {
				print (($i==0) ? '' : '</ul>');
				print "\n\n<li><strong>". $d_arr[$i]->getStateName() ."</strong></li><ul>";
				$last_state=$d_arr[$i]->getStateID();
			}
			print "\n<li><a href=\"index.php?editdoc=1&docid=".$d_arr[$i]->getID()."&group_id=$group_id\">".
				$d_arr[$i]->getName()." [ ".$d_arr[$i]->getFileName()." ]</a>".
				"\n<br /><em><?php echo $Language->getText('docman','description') ?>:</em> ".$d_arr[$i]->getDescription();

		}
		print "\n</ul>\n";
	}

	docman_footer(array());

}

?>
