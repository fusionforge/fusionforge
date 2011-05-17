<?php
/**
 * Project Admin: Edit Releases of Packages
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */


require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';
require_once $gfcommon.'frs/FRSFile.class.php';
require_once $gfwww.'frs/include/frs_utils.php';

$group_id = getIntFromRequest('group_id');
$package_id = getIntFromRequest('package_id');
$release_id = getIntFromRequest('release_id');
if (!$group_id) {
	exit_no_group();
}
if (!$package_id || !$release_id) {
	session_redirect('/frs/admin/?group_id='.$group_id);
}

$group=group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(),'frs');
}
session_require_perm ('frs', $group_id, 'write') ;

//
//  Get the package
//
$frsp = new FRSPackage($group,$package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error(_('Could Not Get FRSPackage'),'frs');
} elseif ($frsp->isError()) {
	exit_error($frsp->getErrorMessage(),'frs');
}

//
//  Get the release
//
$frsr = new FRSRelease($frsp,$release_id);
if (!$frsr || !is_object($frsr)) {
	exit_error(_('Could Not Get FRSRelease'),'frs');
} elseif ($frsr->isError()) {
	exit_error($frsr->getErrorMessage(),'frs');
}

$upload_dir = forge_get_config('ftp_upload_dir') . "/" . $group->getUnixName();


/*
 * Here's where we do the dirty work based on the step the user has chosen
 */

// Edit release info
if (getStringFromRequest('step1')) {
	$release_date = getStringFromRequest('release_date');
	$release_name = getStringFromRequest('release_name');
	$status_id = getIntFromRequest('status_id');
	$uploaded_notes = getUploadedFile('uploaded_notes');
	$uploaded_changes = getUploadedFile('uploaded_changes');
	$release_notes = getStringFromRequest('release_notes');
	$release_changes = getStringFromRequest('release_changes');
	$preformatted = getStringFromRequest('preformatted');
	$exec_changes = true;

	// Check for uploaded release notes
	if ($uploaded_notes["tmp_name"]) {
		if (!is_uploaded_file($uploaded_notes['tmp_name'])) {
			exit_error(_('Attempted File Upload Attack'),'frs');
		}
		if ($uploaded_notes['type'] !== 'text/plain') {
			$error_msg .= _('Release Notes Are not in Text').'<br />';
			$exec_changes = false;
		} else {
			$notes = fread(fopen($uploaded_notes['tmp_name'],'r'),$uploaded_notes['size']);
			if (strlen($notes) < 20) {
				$error_msg .= _('Release Notes Are Too Small').'<br />';
				$exec_changes = false;
			}
		}
	} else {
		$notes = $release_notes;
	}

	// Check for uploaded change logs
	if ($uploaded_changes['tmp_name']) {
		if (!is_uploaded_file($uploaded_changes['tmp_name'])) {
			exit_error(_('Attempted File Upload Attack'),'frs');
		}
		if ($uploaded_changes['type'] !== 'text/plain') {
			$error_msg .= _('Change Log Is not in Text').'<br />';
			$exec_changes = false;
		} else {
			$changes = fread(fopen($uploaded_changes['tmp_name'],'r'), $uploaded_changes['size']);
			if (strlen($changes) < 20) {
				$error_msg .= _('Change Log Is Too Small').'<br />';
				$exec_changes = false;
			}
		}
	} else {
		$changes = $release_changes;
	}

	// If we haven't encountered any problems so far then save the changes
	if ($exec_changes == true) {
		//$date_list = split('[- :]',$release_date,5);
		//$release_date = mktime($date_list[3],$date_list[4],0,$date_list[1],$date_list[2],$date_list[0]);
		$release_date = strtotime($release_date);
		if (!$frsr->update($status_id,$release_name,$notes,$changes,$preformatted,$release_date)) {
			exit_error($frsr->getErrorMessage(),'frs');
		} else {
			$feedback .= _('Data Saved');
		}
	}
}

// Add file(s) to the release
if (getStringFromRequest('step2')) {
	$userfile = getUploadedFile('userfile');
	$userfile_name = $userfile['name'];
	$type_id = getIntFromRequest('type_id');
	$release_date = getStringFromRequest('release_date');
	// Build a Unix time value from the supplied Y-m-d value
	$release_date = strtotime($release_date);
	$processor_id = getIntFromRequest('processor_id');
	$group_unix_name=group_getunixname($group_id);
	$ftp_filename = getStringFromRequest('ftp_filename');
	$manual_filename = getStringFromRequest('manual_filename');

	$ret = frs_add_file_from_form ($frsr, $type_id, $processor_id, $release_date,
				       $userfile, $ftp_filename, $manual_filename) ;
	if ($ret == true) {
		$feedback = _('File Released') ;
	} else {
		$error_msg .= $ret ;
	}
}

// Edit/Delete files in a release
if (getStringFromRequest('step3')) {
	$step3 = getStringFromRequest('step3');
	$file_id = getIntFromRequest('file_id');
	$processor_id = getIntFromRequest('processor_id');
	$type_id = getIntFromRequest('type_id');
	$new_release_id = getIntFromRequest('new_release_id');
	$release_time = getStringFromRequest('release_time');
	$group_id = getIntFromRequest('group_id');
	$release_id = getIntFromRequest('release_id');
	$package_id = getIntFromRequest('package_id');
	$file_id = getIntFromRequest('file_id');
	$im_sure = getStringFromRequest('im_sure');

	// If the user chose to delete the file and he's sure then delete the file
	if( $step3 == "Delete File" ) {
		if ($im_sure) {
			$frsf = new FRSFile($frsr,$file_id);
			if (!$frsf || !is_object($frsf)) {
				exit_error(_('Could Not Get FRSFile'),'frs');
			} elseif ($frsf->isError()) {
				exit_error($frsf->getErrorMessage(),'frs');
			} else {
				if (!$frsf->delete()) {
					exit_error($frsf->getErrorMessage(),'frs');
				} else {
					$feedback .= _('File Deleted');
				}
			}
		} else {
			$error_msg .= _('File not deleted: you did not check "I\'m Sure"');
		}
	// Otherwise update the file information
	} else {
		$frsf = new FRSFile($frsr,$file_id);
		if (!$frsf || !is_object($frsf)) {
			exit_error(_('Could Not Get FRSFile'),'frs');
		} elseif ($frsf->isError()) {
			exit_error($frsf->getErrorMessage(),'frs');
		} else {
			//$date_list = split('[- :]',$release_time,5);
			//$release_time = mktime($date_list[3],$date_list[4],0,$date_list[1],$date_list[2],$date_list[0]);
			$release_time = strtotime($release_time);
			if (!$frsf->update($type_id,$processor_id,$release_time,$new_release_id)) {
				exit_error($frsf->getErrorMessage(),'frs');
			} else {
				$feedback .= _('File Updated');
			}
		}
	}
}

frs_admin_header(array('title'=>_('Edit Releases'),'group'=>$group_id));
/*
 * Show the forms for each step
 */
?>

<h2><?php echo _('Edit Release') ?></h2>

<form enctype="multipart/form-data" method="post" action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;release_id=$release_id&amp;package_id=$package_id"; ?>">
<input type="hidden" name="step1" value="1" />
<table border="0" cellpadding="1" cellspacing="1">
<tr>
	<td width="10%"><strong><?php echo _('Release date') ?>:</strong></td>
	<td><input type="text" name="release_date" value="<?php echo date('Y-m-d H:i',$frsr->getReleaseDate()) ?>" size="16" maxlength="16" /></td>
</tr>
<tr>
	<td><strong><?php echo _('Release name') ?>:</strong></td>
	<td><input type="text" name="release_name" value="<?php echo htmlspecialchars($frsr->getName()); ?>" /></td>
</tr>
<tr>
	<td><strong><?php echo _('Status') ?></strong></td>
	<td>
		<?php
			echo frs_show_status_popup('status_id',$frsr->getStatus());
		?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<br />
		<?php echo _('Edit the Release Notes or Change Log for this release of this package. These changes will apply to all files attached to this release.<br />You can either upload the release notes and change log individually, or paste them in together below.') ?>
	</td>
</tr>
<tr>
	<td><strong><?php echo _('Upload Release Notes') ?>:</strong></td>
	<td><input type="file" name="uploaded_notes" size="30" /></td>
</tr>
<tr>
	<td><strong><?php echo _('Upload Change Log') ?>:</strong></td>
	<td><input type="file" name="uploaded_changes" size="30" /></td>
</tr>
<tr>
	<td colspan="2">
		<strong><?php echo _('Paste The Notes In') ?>:</strong><br />
		<textarea name="release_notes" rows="10" cols="60"><?php echo $frsr->getNotes(); ?></textarea>
	</td>
</tr>
<tr>
	<td colspan="2">
		<strong><?php echo _('Paste The Change Log In') ?>:</strong><br />
		<textarea name="release_changes" rows="10" cols="60"><?php echo $frsr->getChanges(); ?></textarea>
	</td>
</tr>
<tr>
	<td colspan="2">
		<br />
		<input type="checkbox" name="preformatted" value="1" <?php echo (($frsr->getPreformatted())?'checked="checked"':''); ?> /> <?php echo _('Preserve my pre-formatted text.') ?>
		<p>
		<input type="submit" name="submit" value="<?php echo _('Submit/Refresh') ?>"/>
		</p>
	</td>
</tr>
</table>
</form>
<hr />

<h2><?php echo _('Add Files To This Release') ?></h2>
<p><?php echo _('Now, choose a file to upload into the system.') ?></p>

<form enctype="multipart/form-data" method="post" action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;release_id=$release_id&amp;package_id=$package_id"; ?>">
<input type="hidden" name="step2" value="1" />
<fieldset><legend><strong><?php echo _("File Name") ?></strong></legend>
<?php echo _("Upload a new file") ?>: <input type="file" name="userfile"  size="30" />
<?php if (forge_get_config('use_ftp_uploads')) {
	echo '<p>';
	  printf(_('Alternatively, you can use FTP to upload a new file at %1$s.'), forge_get_config('ftp_upload_host'));
	echo '<br />';
	echo _('Choose an already uploaded file:').'<br />';
	$ftp_files_arr=ls($upload_dir,true);
	echo html_build_select_box_from_arrays($ftp_files_arr,$ftp_files_arr,'ftp_filename',''); ?>
	</p>
<?php } ?>

<?php if (forge_get_config('use_manual_uploads')) {
	$incoming = forge_get_config('groupdir_prefix')."/".$group->getUnixName()."/incoming" ;

	echo '<p>';
	printf(_('Alternatively, you can use a file you already uploaded (by SFTP or SCP) to the <a href="%2$s">project\'s incoming directory</a> (%1$s).'),
	       $incoming, "sftp://" . forge_get_config ('web_host') . $incoming . "/");
	echo ' ' . _('This direct <tt>sftp://</tt> link only works with some browsers, such as Konqueror.') . '<br />';
	echo _('Choose an already uploaded file:').'<br />';
	$manual_files_arr=ls($incoming,true);
	echo html_build_select_box_from_arrays($manual_files_arr,$manual_files_arr,'manual_filename',''); ?>
	</p>
<?php } ?>
</fieldset>
<table width="60%">
<tr>
<td>
<strong><?php echo _('File Type') ?>:</strong>
<?php
	print frs_show_filetype_popup ('type_id');
?>
</td>
<td>
<strong><?php echo _('Processor Type') ?>:</strong>
<?php
	print frs_show_processor_popup ('processor_id');
?>
</td>
</tr>
</table>
<p>
<input type="submit" name="submit" value="<?php echo _('Add This File') ?>" /></p>
</form>

<?php
	// Get a list of files associated with this release
	$res=db_query_params ('SELECT * FROM frs_file WHERE release_id=$1',
			array($release_id));
	$rows=db_numrows($res);
	if($rows > 0) {
		echo '<hr />';
		echo '<h2>'._('Edit Files In This Release').'</h2>';
		print(_('Once you have added files to this release you <strong>must</strong> update each of these files with the correct information or they will not appear on your download summary page.')."\n");
		$title_arr[]=_('Filename<br />Release').'<br />';
		$title_arr[]=_('Processor<br />Release Date').'<br />';
		$title_arr[]=_('File Type<br />Update').'<br />';

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		for($x=0; $x<$rows; $x++) {
?>
			<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;release_id=$release_id&amp;package_id=$package_id"; ?>" method="post">
				<input type="hidden" name="file_id" value="<?php echo db_result($res,$x,'file_id'); ?>" />
				<input type="hidden" name="step3" value="1" />
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
					<td style="white-space: nowrap;"><?php echo db_result($res,$x,'filename'); ?></td>
					<td><?php echo frs_show_processor_popup ('processor_id', db_result($res,$x,'processor_id')); ?></td>
					<td><?php echo frs_show_filetype_popup ('type_id', db_result($res,$x,'type_id')); ?></td>
				</tr>
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
					<td>
						
							<?php echo frs_show_release_popup ($group_id, $name='new_release_id',db_result($res,$x,'release_id')); ?>
						
					</td>
					<td>
						
							<input type="text" name="release_time" value="<?php echo date('Y-m-d',db_result($res,$x,'release_time')); ?>" size="10" maxlength="10" />
						
					</td>
					<td><input type="submit" name="submit" value="<?php echo _('Update/Refresh') ?> " /></td>
				</tr>
				</form>

			<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
				<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
				<input type="hidden" name="release_id" value="<?php echo $release_id; ?>" />
				<input type="hidden" name="package_id" value="<?php echo $package_id; ?>" />
				<input type="hidden" name="file_id" value="<?php echo db_result($res,$x,'file_id'); ?>" />
				<input type="hidden" name="step3" value="Delete File" />
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>
						
							<input type="submit" name="submit" value="<?php echo _('Delete File') ?> " /> <input type="checkbox" name="im_sure" value="1" /> <?php echo _('I\'m Sure') ?> 
						
					</td>
				</tr>
			</form>
<?php
		}
		echo $GLOBALS['HTML']->listTableBottom();
	}

echo '<p>' . sprintf(ngettext('There is %1$s user monitoring this package.', 'There are %1$s users monitoring this package.', $frsp->getMonitorCount()), $frsp->getMonitorCount()) . '</p>';

frs_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
