<?php
/**
 * Project Admin: Edit Releases of Packages
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */


require_once('pre.php');
require_once('common/frs/FRSPackage.class');
require_once('common/frs/FRSRelease.class');
require_once('common/frs/FRSFile.class');
require_once('www/project/admin/project_admin_utils.php');

if (!$group_id) {
	exit_no_group();
}
if (!$package_id || !$release_id) {
	header("Location: ./editpackages.php?group_id=$group_id");
	exit;
}

session_require(array('group'=>$group_id));

$g =& group_get_object($group_id);

exit_assert_object($g,'Project');

$perm =& $g->getPermission(session_get_user());

if (!$perm->isReleaseTechnician()) {
	exit_permission_denied();
}

//
//  Get the package
//
$frsp = new FRSPackage($g,$package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error('Error','Could Not Get FRSPackage');
} elseif ($frsp->isError()) {
	exit_error('Error',$frsp->getErrorMessage());
}

//
//  Get the release
//
$frsr = new FRSRelease($frsp,$release_id);
if (!$frsr || !is_object($frsr)) {
	exit_error('Error','Could Not Get FRSRelease');
} elseif ($frsr->isError()) {
	exit_error('Error',$frsr->getErrorMessage());
}


/*
 * Here's where we do the dirty work based on the step the user has chosen
 */

// Edit release info
if ($step1) {
	$exec_changes = true;

	// Check for uploaded release notes
	if ($uploaded_notes != "" && $uploaded_notes != "none") {
		if (!is_uploaded_file($uploaded_notes)) {
			exit_error('Error','Attempted File Upload Attack');
		}
		$notes = addslashes(fread(fopen($HTTP_POST_FILES['uploaded_notes']['tmp_name'],'r'),filesize($HTTP_POST_FILES['uploaded_notes']['tmp_name'])));
		if (strlen($notes) < 20) {
			$feedback .= $Language->getText('project_admin_editrelease','release_notes_too_small');
			$exec_changes = false;
		}
	} else {
		$notes = $release_notes;
	}

	// Check for uploaded change logs
	if ($uploaded_changes != "" && $uploaded_changes != "none") {
		if (!is_uploaded_file($uploaded_changes)) {
			exit_error('Error','Attempted File Upload Attack');
		}
		$changes = addslashes(fread(fopen($HTTP_POST_FILES['uploaded_changes']['tmp_name'],'r'), filesize($HTTP_POST_FILES['uploaded_changes']['tmp_name'])));
		if (strlen($changes) < 20) {
			$feedback .= $Language->getText('project_admin_editrelease','changelog_too_small');
			$exec_changes = false;
		}
	} else {
		$changes = $release_changes;
	}

	// If we haven't encountered any problems so far then save the changes
	if ($exec_changes == true) {
		$date_list = split('[- :]',$release_date,5);
		$release_date = mktime($date_list[3],$date_list[4],0,$date_list[1],$date_list[2],$date_list[0]);
		if (!$frsr->update($status_id,$release_name,$notes,$changes,$preformatted,$release_date)) {
			exit_error('Error',$frsr->getErrorMessage());
		} else {
			$feedback .= $Language->getText('project_admin_editrelease','data_saved');
		}
	}
}

// Add file(s) to the release
if ($step2) {
	// Build a Unix time value from the supplied Y-m-d value
	$group_unix_name=group_getunixname($group_id);

	if ($userfile && is_uploaded_file($userfile)) {
		//
		//  Now create the new FRSFile in the db
		//
		$frsf = new FRSFile($frsr);
		if (!$frsf || !is_object($frsf)) {
			exit_error('Error','Could Not Get FRSFile');
		} elseif ($frsf->isError()) {
			exit_error('Error',$frsf->getErrorMessage());
		} else {
			if (!$frsf->create($userfile_name,$userfile,$type_id,$processor_id,$release_date)) {
				db_rollback();
				exit_error('Error',$frsf->getErrorMessage());
			}
			$feedback=$Language->getText('project_admin_editrelease','file_released');
		}
	}
}

// Edit/Delete files in a release
if ($step3) {
	// If the user chose to delete the file and he's sure then delete the file
	if( $step3 == "Delete File" && $im_sure ) {
		$frsf = new FRSFile($frsr,$file_id);
		if (!$frsf || !is_object($frsf)) {
			exit_error('Error','Could Not Get FRSFile');
		} elseif ($frsf->isError()) {
			exit_error('Error',$frsf->getErrorMessage());
		} else {
			if (!$frsf->delete()) {
				exit_error('Error',$frsf->getErrorMessage());
			} else {
				$feedback .= $Language->getText('project_admin_editrelease','file_deleted');
			}
		}
	// Otherwise update the file information
	} else {
		$frsf = new FRSFile($frsr,$file_id);
		if (!$frsf || !is_object($frsf)) {
			exit_error('Error','Could Not Get FRSFile');
		} elseif ($frsf->isError()) {
			exit_error('Error',$frsf->getErrorMessage());
		} else {
			$date_list = split('[- :]',$release_time,5);
			$release_time = mktime($date_list[3],$date_list[4],0,$date_list[1],$date_list[2],$date_list[0]);
			if (!$frsf->update($type_id,$processor_id,$release_time)) {
				exit_error('Error',$frsf->getErrorMessage());
			} else {
				$feedback .= $Language->getText('project_admin_editrelease','file_updated');
			}
		}
	}
}

project_admin_header(array('title'=>$Language->getText('project_admin_editrelease','title'),'group'=>$group_id,'pagename'=>'project_admin_editreleases','sectionvals'=>array(group_getname($group_id))));
/*
 * Show the forms for each step
 */
?>

<h3><?php echo $Language->getText('project_admin_editrelease','step_1') ?></h3>

<form enctype="multipart/form-data" method="post" action="<?php echo $PHP_SELF."?group_id=$group_id&release_id=$release_id&package_id=$package_id"; ?>">
<input type="hidden" name="step1" value="1" />
<table border="0" cellpadding="1" cellspacing="1">
<tr>
	<td width="10%"><strong><?php echo $Language->getText('project_admin_editrelease','release_date') ?>:<strong></td>
	<td><input type="text" name="release_date" value="<?php echo date('Y-m-d',$frsr->getReleaseDate()) ?>" size="10" maxlength="10" /></td>
</tr>
<tr>
	<td><strong><?php echo $Language->getText('project_admin_editrelease','release_name') ?>:<strong></td>
	<td><input type="text" name="release_name" value="<?php echo htmlspecialchars($frsr->getName()); ?>" /></td>
</tr>
<tr>
	<td><strong><?php echo $Language->getText('project_admin_editrelease','status') ?>:</strong></td>
	<td>
		<?php
			echo frs_show_status_popup('status_id',$frsr->getStatus());
		?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<br />
		<?php echo $Language->getText('project_admin_editrelease','note') ?>
	</td>
</tr>
<tr>
	<td><strong><?php echo $Language->getText('project_admin_editrelease','upload_release_notes') ?>:</strong></td>
	<td><input type="file" name="uploaded_notes" size="30" /></td>
</tr>
<tr>
	<td><strong><?php echo $Language->getText('project_admin_editrelease','upload_change_log') ?>:</strong></td>
	<td><input type="file" name="uploaded_changes" size="30" /></td>
</tr>
<tr>
	<td colspan="2">
		<strong><?php echo $Language->getText('project_admin_editrelease','paste_release_notes') ?>:</strong><br />
		<textarea name="release_notes" rows="10" cols="60" wrap="soft"><?php echo $frsr->getNotes(); ?></textarea>
	</td>
</tr>
<tr>
	<td colspan="2">
		<strong><?php echo $Language->getText('project_admin_editrelease','paste_changelog') ?>:</strong><br />
		<textarea name="release_changes" rows="10" cols="60" wrap="soft"><?php echo $frsr->getChanges(); ?></textarea>
	</td>
</tr>
<tr>
	<td>
		<br />
		<input type="checkbox" name="preformatted" value="1" <?php echo (($frsr->getPreformatted())?'checked="checked"':''); ?> /> <?php echo $Language->getText('project_admin_editrelease','preserve_preformatted') ?>
		<p>
		<input type="submit" name="submit" value="<?php echo $Language->getText('project_admin_editrelease','submit_refresh') ?>"/></p>
	</td>
</tr>
</table>
</form>
<p>&nbsp;</p>
<hr noshade="noshade" />
<h3><?php echo $Language->getText('project_admin_editrelease','step_2') ?></h3>
<p>
<form enctype="multipart/form-data" method="post" action="<?php echo $PHP_SELF."?group_id=$group_id&release_id=$release_id&package_id=$package_id"; ?>">
<input type="hidden" name="step2" value="1" />
<span style="color:red"><strong>
<?php echo $Language->getText('project_admin_editrelease','add_files_note') ?>
</strong></span><br />
<?php echo $Language->getText('project_admin_editrelease','upload_new_file') ?>: <input type="file" name="userfile"  size="30" />
<table width="60%">
<tr>
<td>
<h4><?php echo $Language->getText('project_admin_editrelease','file_type') ?>:</h4>
<?php
	print frs_show_filetype_popup ('type_id');
?>
</td>
<td>
<h4><?php echo $Language->getText('project_admin_editrelease','processor_type') ?>:</h4>
<?php
	print frs_show_processor_popup ('processor_id');
?>
</td>
</tr>
</table>
<p>
<input type="submit" name="submit" value="<?php echo $Language->getText('project_admin_editrelease','add_file') ?>" /></p>
</form></p>
<p>&nbsp;</p>
<hr noshade="noshade" />
<p>&nbsp;</p>
<h3><?php echo $Language->getText('project_admin_editrelease','step_3') ?></h3>

<?php
	// Get a list of files associated with this release
	$res=db_query("SELECT * FROM frs_file WHERE release_id='$release_id'");
	$rows=db_numrows($res);
	if($rows < 1) {
		print("<h4>".$Language->getText('project_admin_editrelease','no_files_in_release')."</h4>\n");
	} else {
		print($Language->getText('project_admin_editrelease','file_list_note')."\n");
		$title_arr[]=$Language->getText('project_admin_editrelease','filename_release').'<br />';
		$title_arr[]=$Language->getText('project_admin_editrelease','processor_update').'<br />';
		$title_arr[]=$Language->getText('project_admin_editrelease','file_type_update').'<br />';

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		for($x=0; $x<$rows; $x++) {
?>
			<form action="<?php echo $PHP_SELF."?group_id=$group_id&release_id=$release_id&package_id=$package_id"; ?>" method="post">
				<input type="hidden" name="file_id" value="<?php echo db_result($res,$x,'file_id'); ?>" />
				<input type="hidden" name="step3" value="1" />
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
					<td nowrap="nowrap"><span style="font-size:smaller"><?php echo db_result($res,$x,'filename'); ?></span></td>
					<td><span style="font-size:smaller"><?php echo frs_show_processor_popup ('processor_id', db_result($res,$x,'processor_id')); ?></span></td>
					<td><span style="font-size:smaller"><?php echo frs_show_filetype_popup ('type_id', db_result($res,$x,'type_id')); ?></span></td>
				</tr>
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
					<td>
						<span style="font-size:smaller">
							<?php echo frs_show_release_popup ($group_id, $name='new_release_id',db_result($res,$x,'release_id')); ?>
						</span>
					</td>
					<td>
						<span style="font-size:smaller">
							<input type="text" name="release_time" value="<?php echo date('Y-m-d',db_result($res,$x,'release_time')); ?>" size="10" maxlength="10" />
						</span>
					</td>
					<td><input type="submit" name="submit" value="<?php echo $Language->getText('project_admin_editrelease','update_refresh') ?> " /></td>
				</tr>
				</form>

			<form action="<?php echo $PHP_SELF; ?>" method="post">
				<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
				<input type="hidden" name="release_id" value="<?php echo $release_id; ?>" />
				<input type="hidden" name="package_id" value="<?php echo $package_id; ?>" />
				<input type="hidden" name="file_id" value="<?php echo db_result($res,$x,'file_id'); ?>" />
				<input type="hidden" name="step3" value="Delete File" />
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>
						<span style="font-size:smaller">
							<input type="submit" name="submit" value="<?php echo $Language->getText('project_admin_editrelease','delete_file') ?> " /> <input type="checkbox" name="im_sure" value="1" /> <?php echo $Language->getText('project_admin_editrelease','i_am_sure') ?> 
						</span>
					</td>
				</tr>
			</form>
<?php
		}
		echo $GLOBALS['HTML']->listTableBottom();
	}

project_admin_footer(array());

?>
