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
require_once('www/docman/include/DocumentGroupHTML.class');
require_once('common/docman/DocumentFactory.class');
require_once('common/docman/DocumentGroup.class');
require_once('common/docman/DocumentGroupFactory.class');

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

$upload_dir = $sys_ftp_upload_dir . "/" . $g->getUnixName();

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
		if ($uploaded_data) {
			if (!is_uploaded_file($uploaded_data)) {
				exit_error($Language->getText('general','error'),$Language->getText('docman','error_invalid_file_attack', $uploaded_data));
			}
			$data = addslashes(fread(fopen($uploaded_data, 'r'), filesize($uploaded_data)));
			$filename=$uploaded_data_name;
			$filetype=$uploaded_data_type;
		} elseif ($file_url) {
			$data = '';
			$filename=$file_url;
			$filetype='URL';
		} elseif ($ftp_filename) {
			$filename=$upload_dir.'/'.$ftp_filename;
			$data = addslashes(fread(fopen($filename, 'r'), filesize($filename)));
			$filetype=$uploaded_data_type;
		} else {
			$filename=addslashes($d->getFileName());
			$filetype=addslashes($d->getFileType());
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
		if (!$dg->update($groupname,$parent_doc_group)) {
			exit_error('Error',$dg->getErrorMessage());
		}
		$feedback = $Language->getText('general','update_successful');


	} elseif ($addgroup) {

		$dg = new DocumentGroup($g);
		if ($dg->isError()) {
			exit_error('Error',$dg->getErrorMessage());
		}
		if (!$dg->create($groupname, $parent_doc_group)) {
			exit_error('Error',$dg->getErrorMessage());
		}
		$feedback = $Language->getText('general','create_successful');
	
	} elseif ($deletedoc && $docid && $sure && $really_sure) {
		$d= new Document($g,$docid);
		if ($d->isError()) {
			exit_error('Error',$d->getErrorMessage());
		}
		
		if (!$d->delete()) {
			exit_error('Error',$d->getErrorMessage());
		}
		
		$feedback = $Language->getText('general','deleted');
		header('Location: index.php?group_id='.$d->Group->getID().'&feedback='.urlencode($feedback));
		die();	// End parsing file and redirect
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

	$dgf = new DocumentGroupFactory($g);
	if ($dgf->isError()) {
		exit_error('Error',$dgf->getErrorMessage());
	}
	
	$dgh = new DocumentGroupHTML($g);
	if ($dgh->isError()) {
		exit_error('Error',$dgh->getErrorMessage());
	}

	
	docman_header($Language->getText('docman_admin_editdocs','section'),$Language->getText('docman_admin_editdocs','title'),'docman_admin_docedit','admin',$g->getPublicName(),'');

	?>
		<br />
		<?php echo $Language->getText('docman_new','intro') ?>
	<form name="editdata" action="index.php?editdoc=1&amp;group_id=<?php echo $group_id; ?>" method="post" enctype="multipart/form-data">

	<table border="0">

	<tr>
		<td>
		<strong><?php echo $Language->getText('docman_new','doc_title') ?>:</strong><br />
		<input type="text" name="title" size="40" maxlength="255" value="<?php echo $d->getName(); ?>" />
		<br /></td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('docman_new','description') ?></strong><br />
		<input type="text" name="description" size="20" maxlength="255" value="<?php echo $d->getDescription(); ?>" />
		<br /></td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('docman_new','file')?></strong><br />
		<?php if ($d->isURL()) {
			echo '<a href="'.$d->getFileName().'">[View File URL]</a>';
		} else { ?>
		<a target="_blank" href="../view.php/<?php echo $group_id.'/'.$d->getID().'/'.$d->getFileName() ?>"><?php echo $d->getName(); ?></a>
		<?php } ?>
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
		<strong><?php echo $Language->getText('docman_new','group') ?></strong><br />
		<?php

			//echo display_groups_option($group_id,$d->getDocGroupID());
			$dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $d->getDocGroupID());

		?></td>
	</tr>

	<tr>
		<td>
		<br /><strong><?php echo $Language->getText('docman_admin_editdocs','state') ?>:</strong><br />
		<?php

			doc_get_state_box($d->getStateID());

		?></td>
	</tr>

	<?php

/*
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
*/
	?>
	<tr>
		<td>
		<?php if ($d->isURL()) { ?>
		<strong><?php echo $Language->getText('docman_admin_editdocs','upload_url') ?> :</strong><?php echo utils_requiredField(); ?><br />
        <input type="text" name="file_url" size="50" value="<?php echo $d->getFileName() ?>" />
		<?php } else { ?>
		<strong><?php echo $Language->getText('docman_admin_editdocs','upload') ?></strong><br />
		<input type="file" name="uploaded_data" size="30" /><br/><br />
			<?php if ($sys_use_ftpuploads) { ?>
			<strong><?php echo $Language->getText('docman_admin_editdocs','upload_ftp',array($sys_ftp_upload_host)) ?></strong><br />
			<?php
			$arr[]='';
			$ftp_files_arr=array_merge($arr,ls($upload_dir,true));
			echo html_build_select_box_from_arrays($ftp_files_arr,$ftp_files_arr,'ftp_filename','');
			echo '<br /><br />';			
			}
		}
		?>
		</td>
	</tr>
	</table>

	<input type="hidden" name="docid" value="<?php echo $d->getID(); ?>" />
	<input type="submit" value="<?php echo $Language->getText('general','submit_edit') ?>" name="submit" /><br /><br />
	<a href="index.php?deletedoc=1&amp;docid=<?php echo $d->getID() ?>&amp;group_id=<?php echo $d->Group->getID() ?>"><?php echo $Language->getText('docman_admin_editdocs', 'delete_doc') ?></a>

	</form>
	<?php

	docman_footer(array());

//
//
//	Add a document group / view existing groups list
//
//
} elseif ($addgroup) {

	docman_header($Language->getText('docman_admin_addgroups','section'),$Language->getText('docman_admin_addgroups','title'),'docman_admin_addgroups','admin',$g->getPublicName(),'');

	echo "<h1>".$Language->getText('docman_admin_addgroups','title')."</h1>";
	
	$dgf = new DocumentGroupFactory($g);
	if ($dgf->isError()) {
		exit_error('Error',$dgf->getErrorMessage());
	}
	
	$dgh = new DocumentGroupHTML($g);
	if ($dgh->isError()) {
		exit_error('Error',$dgh->getErrorMessage());
	}
	
	$nested_groups =& $dgf->getNested();
	
	if (count($nested_groups) > 0) {
		$title_arr=array();
		$title_arr[]=$Language->getText('docman_admin_editgroups','group_id');
		$title_arr[]=$Language->getText('docman_admin_editgroups','group_name');

		echo $GLOBALS['HTML']->listTableTop ($title_arr);
		
		$row = 0;
		$dgh->showTableNestedGroups(&$nested_groups, &$row);
		
		echo $GLOBALS['HTML']->listTableBottom();
		
	} else {
		echo "\n<h1>".$Language->getText('docman','error_no_groups_defined')."</h1>";
	}
	?>
	<p><strong><?php echo $Language->getText('docman_admin_editgroups','add_group') ?>:</strong></p>
	<form name="addgroup" action="index.php?addgroup=1&amp;group_id=<?php echo $group_id; ?>" method="post">
	<table>
		<tr>
			<th><?php echo $Language->getText('docman_admin_editgroups','new_group_name') ?>:</th>
			<td><input type="text" name="groupname" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th><?php echo $Language->getText('docman_admin_editgroups','new_group_parent') ?>:</th>
			<td>
				<?php echo $dgh->showSelectNestedGroups(&$nested_groups, 'parent_doc_group') ?>
			</td>

			<td><input type="submit" value="<?php echo $Language->getText('general','add') ?>" name="submit" /></td>
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
	
	$dgf = new DocumentGroupFactory($g);
	if ($dgf->isError()) {
		exit_error('Error',$dgf->getErrorMessage());
	}
	
	$dgh = new DocumentGroupHTML($g);
	if ($dgh->isError()) {
		exit_error('Error',$dgh->getErrorMessage());
	}

	docman_header($Language->getText('docman_admin_editgroups','section'),$Language->getText('docman_admin_editgroups','title'),'docman_admin_editgroups','admin',$g->getPublicName(),'');
	?>
	<p><strong><?php echo $Language->getText('docman_admin_editgroups','edit_group') ?></strong></p>
	<form name="editgroup" action="index.php?editgroup=1&amp;group_id=<?php echo $group_id; ?>" method="post">
	<input type="hidden" name="doc_group" value="<?php echo $doc_group; ?>" />
	<table>
		<tr>
			<th><?php echo $Language->getText('docman_admin_editgroups','group_name') ?>:</th>
			<td><input type="text" name="groupname" value="<?php echo $dg->getName(); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th><?php echo $Language->getText('docman_admin_editgroups','group_parent') ?>:</th>
			<td>
			<?php
				$dgh->showSelectNestedGroups($dgf->getNested(), "parent_doc_group", true, $dg->getParentId(), array($dg->getID()));
			?>
			</td>
			<td><input type="submit" value="<?php echo $Language->getText('general','edit') ?>" name="submit" /></td>
		</tr>
	</table>
	<p>
		 <?php echo $Language->getText('docman_admin_editgroups','description') ?>

	</p>
	</form>
	<?php
	docman_footer(array());
} else if ($deletedoc && $docid) {
	$d= new Document($g,$docid);
	if ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}
	
	docman_header($Language->getText('docman_admin_editgroups','section'),$Language->getText('docman_admin_editgroups','title'),'docman_admin_editgroups','admin',$g->getPublicName(),'');
?>
		<p>
		<form action="<?php echo $PHP_SELF.'?deletedoc=1&amp;docid='.$d->getID().'&amp;group_id='.$d->Group->getID() ?>" method="post">
		<input type="hidden" name="submit" value="1" /><br />
		<?php echo $Language->getText('docman_admin_deletedoc','delete_warning'); ?>
		<p>
		<input type="checkbox" name="sure" value="1"><?php echo $Language->getText('docman_admin_deletedoc','sure') ?><br />
		<input type="checkbox" name="really_sure" value="1"><?php echo $Language->getText('docman_admin_deletedoc','really_sure') ?><br />
		<p>
		<input type="submit" name="post_changes" value="<?php echo $Language->getText('docman_admin_deletedoc','delete') ?>" /></p>
		</form></p>
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
	
	$dgf = new DocumentGroupFactory($g);
	if ($dgf->isError()) {
		exit_error($Language->getText('general','error'),$dgf->getErrorMessage());
	}
	

	$df->setStateID('ALL');
//	$df->setSort('stateid');
	$d_arr =& $df->getDocuments();

	docman_header($Language->getText('docman_admin','section', $g->getPublicName()),$Language->getText('docman_admin','title'),'docman_admin','admin',$g->getPublicName(),'admin');

	?>
	<h3><?php echo $Language->getText('docman_admin','title') ?></h3>
	<p>
	<a href="index.php?group_id=<?php echo $group_id; ?>&amp;addgroup=1"><?php echo $Language->getText('docman_admin','add_edit_docgroups') ?></a>
	</p>
	<?php
	
	if (!$d_arr || count($d_arr) < 1) {
		print "<p><strong>".$Language->getText('docman','error_no_docs').".</strong></p>";
	} else {
		// get a list of used document states
		$states = $df->getUsedStates();
		$nested_groups =& $dgf->getNested();
		echo "<ul>";
		foreach ($states as $state) {
			echo "<li><strong>".$state["name"]."</strong>";
			docman_display_documents(&$nested_groups, &$df, true, $state["stateid"], true);
			echo "</li>";
		}
		echo "</ul>";
	}

	docman_footer(array());

}

?>
