<?php
/**
 * Project Admin: Edit Releases of Packages
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../../env.inc.php');
require_once('pre.php');
require_once('common/frs/FRSPackage.class.php');
require_once('common/frs/FRSRelease.class.php');
require_once('common/frs/FRSFile.class.php');
require_once('www/frs/include/frs_utils.php');

$group_id = getIntFromRequest('group_id');
$package_id = getIntFromRequest('package_id');
$release_id = getIntFromRequest('release_id');
if (!$group_id) {
	exit_no_group();
}
if (!$package_id || !$release_id) {
	header("Location: /frs/admin/?group_id=$group_id");
	exit;
}

$g =& group_get_object($group_id);
if (!$g || $g->isError()) {
	exit_error('Error',$g->getErrorMessage());
}
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

//we make sure we are not receiving $sys_ftp_upload_dir by POST or GET, to prevent security problems
global $sys_ftp_upload_dir;
if (!$sys_ftp_upload_dir) {
	exit_error('Error','External sys_ftp_upload_dir detected');
}
$upload_dir = $sys_ftp_upload_dir . "/" . $g->getUnixName();


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
			exit_error('Error','Attempted File Upload Attack');
		}
		$notes = addslashes(fread(fopen($uploaded_notes['tmp_name'],'r'),$uploaded_notes['size']));
		if (strlen($notes) < 20) {
			$feedback .= _('Release Notes Are Too Small');
			$exec_changes = false;
		}
	} else {
		$notes = $release_notes;
	}

	// Check for uploaded change logs
	if ($uploaded_changes['tmp_name']) {
		if (!is_uploaded_file($uploaded_changes['tmp_name'])) {
			exit_error('Error','Attempted File Upload Attack');
		}
		$changes = addslashes(fread(fopen($uploaded_changes['tmp_name'],'r'), $uploaded_changes['size']));
		if (strlen($changes) < 20) {
			$feedback .= _('Change Log Is Too Small');
			$exec_changes = false;
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
			exit_error('Error',$frsr->getErrorMessage());
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
	$processor_id = getStringFromRequest('processor_id');
	// Build a Unix time value from the supplied Y-m-d value
	$group_unix_name=group_getunixname($group_id);
	$ftp_filename = getStringFromRequest('ftp_filename');

	if (($userfile && is_uploaded_file($userfile['tmp_name'])) || ($sys_use_ftpuploads && $ftp_filename)){
		if ($sys_use_ftpuploads && $ftp_filename && util_is_valid_filename($ftp_filename) && is_file($upload_dir.'/'.$ftp_filename)) {
			//file was uploaded already via ftp
			//use setuid prog to chown it
			//$cmd = escapeshellcmd("$sys_ftp_upload_chowner $ftp_filename");
			//exec($cmd,$output);
			$userfile_name=$ftp_filename;
			$userfile=$upload_dir.'/'.$ftp_filename;
			//echo $cmd.'***'.$output.'***'.$userfile;
		}
		//
		//  Now create the new FRSFile in the db
		//
		$frsf = new FRSFile($frsr);
		if (!$frsf || !is_object($frsf)) {
			exit_error('Error','Could Not Get FRSFile');
		} elseif ($frsf->isError()) {
			exit_error('Error',$frsf->getErrorMessage());
		} else {
			if (!$frsf->create($userfile_name,$userfile['tmp_name'],$type_id,$processor_id,$release_date)) {
				db_rollback();
				exit_error('Error',$frsf->getErrorMessage());
			}
			$feedback=_('File Released');
		}
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
				exit_error('Error','Could Not Get FRSFile');
			} elseif ($frsf->isError()) {
				exit_error('Error',$frsf->getErrorMessage());
			} else {
				if (!$frsf->delete()) {
					exit_error('Error',$frsf->getErrorMessage());
				} else {
					$feedback .= _('File Deleted');
				}
			}
		} else {
			exit_error('Error',_('Missing Parameters'));
		}
	// Otherwise update the file information
	} else {
		$frsf = new FRSFile($frsr,$file_id);
		if (!$frsf || !is_object($frsf)) {
			exit_error('Error','Could Not Get FRSFile');
		} elseif ($frsf->isError()) {
			exit_error('Error',$frsf->getErrorMessage());
		} else {
			//$date_list = split('[- :]',$release_time,5);
			//$release_time = mktime($date_list[3],$date_list[4],0,$date_list[1],$date_list[2],$date_list[0]);
			$release_time = strtotime($release_time);
			if (!$frsf->update($type_id,$processor_id,$release_time,$new_release_id)) {
				exit_error('Error',$frsf->getErrorMessage());
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

<h3><?php echo _('Step 1:&nbsp;&nbsp; Edit Release') ?></h3>

<form enctype="multipart/form-data" method="post" action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&release_id=$release_id&package_id=$package_id"; ?>">
<input type="hidden" name="step1" value="1" />
<table border="0" cellpadding="1" cellspacing="1">
<tr>
	<td width="10%"><strong><?php echo _('Release date') ?>:<strong></td>
	<td><input type="text" name="release_date" value="<?php echo date('Y-m-d H:i',$frsr->getReleaseDate()) ?>" size="16" maxlength="16" /></td>
</tr>
<tr>
	<td><strong><?php echo _('Release name') ?>:<strong></td>
	<td><input type="text" name="release_name" value="<?php echo htmlspecialchars($frsr->getName()); ?>" /></td>
</tr>
<tr>
	<td><strong><?php echo _('Status') ?>:</strong></td>
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
		<textarea name="release_notes" rows="10" cols="60" wrap="soft"><?php echo $frsr->getNotes(); ?></textarea>
	</td>
</tr>
<tr>
	<td colspan="2">
		<strong><?php echo _('Paste The Change Log In') ?>:</strong><br />
		<textarea name="release_changes" rows="10" cols="60" wrap="soft"><?php echo $frsr->getChanges(); ?></textarea>
	</td>
</tr>
<tr>
	<td>
		<br />
		<input type="checkbox" name="preformatted" value="1" <?php echo (($frsr->getPreformatted())?'checked="checked"':''); ?> /> <?php echo _('Preserve my pre-formatted text.') ?>
		<p>
		<input type="submit" name="submit" value="<?php echo _('Submit/Refresh') ?>"/></p>
	</td>
</tr>
</table>
</form>
<p>&nbsp;</p>
<hr />
<h3><?php echo _('Step 2: Add Files To This Release</h3><p>Now, choose a file to upload into the system. The maximum file size is determined by the site administrator, but defaults to 2MB. If you need to upload large files, contact your site administrator.</p>') ?></h3>
<p>
<form enctype="multipart/form-data" method="post" action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&release_id=$release_id&package_id=$package_id"; ?>">
<input type="hidden" name="step2" value="1" />
<span class="important">
<?php echo _('NOTE: In some browsers you must select the file in the file-upload dialog and click "OK".  Double-clicking doesn\'t register the file.') ?></span>
</span><br />
<?php echo _('Upload a new file') ?>: <input type="file" name="userfile"  size="30" />
<?php if ($sys_use_ftpuploads) {

	echo '<p>';
	printf(_('Alternatively, you can use FTP to upload a new file at %1$s'), $sys_ftp_upload_host).'<br />';
	echo _('Choose an FTP file instead of uploading:').'<br />';
	$arr[]='';
	$ftp_files_arr=array_merge($arr,ls($upload_dir,true));
	echo html_build_select_box_from_arrays($ftp_files_arr,$ftp_files_arr,'ftp_filename','',false); ?>
	</p>
<?php } ?>
<table width="60%">
<tr>
<td>
<h4><?php echo _('File Type') ?>:</h4>
<?php
	print frs_show_filetype_popup ('type_id');
?>
</td>
<td>
<h4><?php echo _('Processor Type') ?>:</h4>
<?php
	print frs_show_processor_popup ('processor_id');
?>
</td>
</tr>
</table>
<p>
<input type="submit" name="submit" value="<?php echo _('Add This File') ?>" /></p>
</form></p>
<p>&nbsp;</p>
<hr />
<p>&nbsp;</p>
<h3><?php echo _('Step 3: Edit Files In This Release') ?></h3>

<?php
	// Get a list of files associated with this release
	$res=db_query("SELECT * FROM frs_file WHERE release_id='$release_id'");
	$rows=db_numrows($res);
	if($rows < 1) {
		print("<span class=\"error\">"._('No Files In This Release')."</span>\n");
	} else {
		print(_('Once you have added files to this release you <strong>must</strong> update each of these files with the correct information or they will not appear on your download summary page.')."\n");
		$title_arr[]=_('Filename<br />Release').'<br />';
		$title_arr[]=_('Processor<br />Release Date').'<br />';
		$title_arr[]=_('File Type<br />Update').'<br />';

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		for($x=0; $x<$rows; $x++) {
?>
			<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&release_id=$release_id&package_id=$package_id"; ?>" method="post">
				<input type="hidden" name="file_id" value="<?php echo db_result($res,$x,'file_id'); ?>" />
				<input type="hidden" name="step3" value="1" />
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
					<td nowrap="nowrap"><?php echo db_result($res,$x,'filename'); ?></td>
					<td><?php echo frs_show_processor_popup ('processor_id', db_result($res,$x,'processor_id')); ?></td>
					<td><?php echo frs_show_filetype_popup ('type_id', db_result($res,$x,'type_id')); ?></td>
				</tr>
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
				file:///usr/share/ubuntu-artwork/home/index.html	<td>
						
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

echo '<br />'.sprintf(_('There are %1$s users monitoring this package.'), $frsp->getMonitorCount());
echo '<hr />';

frs_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
