<?php
/**
 * GForge File Release Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */

require_once('../../env.inc.php');
require_once('pre.php');	
require_once('common/frs/FRSPackage.class');
require_once('common/frs/FRSRelease.class');
require_once('common/frs/FRSFile.class');
require_once('www/frs/include/frs_utils.php');

$group_id = getIntFromRequest('group_id');
$package_id = getIntFromRequest('package_id');

if (!$group_id) {
	exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || $g->isError()) {
	exit_error('Error',$g->getErrorMessage());
}
$perm =& $g->getPermission(session_get_user());
if (!$perm->isReleaseTechnician()) {
	exit_permission_denied();
}

$upload_dir = $sys_ftp_upload_dir . "/" . $g->getUnixName();

/*
	Quick file release system , Darrell Brogdon, SourceForge, Aug, 2000

	With much code horked from editreleases.php
*/

if (getStringFromRequest('submit')) {
	$release_name = getStringFromRequest('release_name');
	$userfile = getUploadedFile('userfile');
	$userfile_name = $userfile['name'];
	$type_id = getIntFromRequest('type_id');
	$processor_id = getIntFromRequest('processor_id');
	$release_date = getStringFromRequest('release_date');
	$release_notes = getStringFromRequest('release_notes');
	$release_changes = getStringFromRequest('release_changes');
	$preformatted = getStringFromRequest('preformatted');
	$ftp_filename = getStringFromRequest('ftp_filename');
	if ($sys_use_ftpuploads && $ftp_filename && util_is_valid_filename($ftp_filename) && is_file($upload_dir.'/'.$ftp_filename)) {
		//file was uploaded already via ftp
		//use setuid prog to chown it
		//$cmd = escapeshellcmd("$sys_ftp_upload_chowner $ftp_filename");
		//exec($cmd,$output);
		$userfile_name=$ftp_filename;
		$userfile=$upload_dir.'/'.$ftp_filename;
		//echo $cmd.'***'.$output.'***'.$userfile;
	}
	if (!$release_name) {
		$feedback .= _('Must define a release name.');
	} else 	if (!$package_id) {
		$feedback .= _('Must select a package.');
	} else 	if (!$userfile['tmp_name'] && !$ftp_filename) {
		// Check errors
		switch($userfile['error']) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$feedback .= _('The uploaded file exceeds the maximum file size. Contact to the site admin to upload this big file.');
			break;
			case UPLOAD_ERR_PARTIAL:
				$feedback .= _('The uploaded file was only partially uploaded.');
			break;
			case UPLOAD_ERR_NO_FILE:
				$feedback .= _('Must select a file.');
			break;
			default:
				$feedback .= _('Unknown file upload error.');
			break;
		}
	} else 	if (!$type_id || $type_id == "100") {
		$feedback .= _('Must select a file type.');
	} else 	if (!$processor_id || $processor_id == "100")  {
		$feedback .= _('Must select a processor type.');
	} else {

		//
		//	Get the package
		//
		$frsp = new FRSPackage($g,$package_id);
		if (!$frsp || !is_object($frsp)) {
			exit_error('Error','Could Not Get FRSPackage');
		} elseif ($frsp->isError()) {
			exit_error('Error',$frsp->getErrorMessage());
		} else {
			if ($userfile && (is_uploaded_file($userfile['tmp_name']) || ($sys_use_ftpuploads && $ftp_filename))) {
				//
				//	Create a new FRSRelease in the db
				//
				$frsr = new FRSRelease($frsp);
				if (!$frsr || !is_object($frsr)) {
					exit_error('Error','Could Not Get FRSRelease');
				} elseif ($frsr->isError()) {
					exit_error('Error',$frsr->getErrorMessage());
				} else {
//					$date_list = split('[- :]',$release_date,5);
//					$release_date = mktime($date_list[3],$date_list[4],0,$date_list[1],$date_list[2],$date_list[0]);
					$release_date = strtotime($release_date);
					db_begin();
					if (!$frsr->create($release_name,$release_notes,$release_changes,
						$preformatted,$release_date)) {
						db_rollback();
						exit_error('Error',$frsr->getErrorMessage());
					}

					//
					//	Now create the new FRSFile in the db
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
						$frsr->sendNotice();
						$feedback .= _('File Released: You May Choose To Edit the Release Now');

						frs_admin_header(array('title'=>_('Quick Release System'),'group'=>$group_id));
						?>
						<p>
						<?php echo $Language->getText('project_admin_qrs','qrs_info',
							array('<a href="'.$GLOBALS['sys_urlprefix'].'/frs/admin/editrelease.php?release_id='.$frsr->getID().'&amp;group_id='.$group_id.'&amp;package_id='.$package_id.' "><strong>',
							'</strong></a>',
							'<a href="'.$GLOBALS['sys_urlprefix'].'/frs/?group_id='.$group_id.'">','</a>')) ?>
						<?php
						db_commit();
						frs_admin_footer(array());
						exit(); //quite dirty but less that a buggy output like before
						
					}

				}

			} else {
				exit_error('Error','Could Not Upload User File: '.$userfile['name']);
			}

		}

	}

}

frs_admin_header(array('title'=>_('Quick Release System'),'group'=>$group_id));

?>

<form enctype="multipart/form-data" method="post" action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id"; ?>">
	<table border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td>
			<h4><?php echo _('Package ID') ?>:</h4>
		</td>
		<td>
<?php
	$sql="SELECT * FROM frs_package WHERE group_id='$group_id' AND status_id='1'";
	$res=db_query($sql);
	$rows=db_numrows($res);
	if (!$res || $rows < 1) {
		echo '<h4>'._('No File Types Available').'</h4>';
	} else {
		
		echo '<select name="package_id">';
		for ($i=0; $i<$rows; $i++) {
			echo '<option value="' . db_result($res,$i,'package_id') .
				((db_result($res,$i,'package_id') ==$package_id) ? '" selected="selected"' : '"').'>' .
				db_result($res,$i,'name') . '</option>';
		}
		echo '</select>';
	}
?>
			&nbsp;&nbsp;
			
			<?php printf(_('Or %1$s create a new package %2$s'), '<a href="'.$GLOBALS['sys_urlprefix'].'/frs/admin/?group_id='.$group_id.'">', '</a>') ?>
		</td>
	</tr>
	<tr>
		<td>
			<h4><?php echo _('Release name') ?>:<?php echo utils_requiredField();?></h4>
		</td>
		<td>
			<input type="text" name="release_name" value="<?php echo htmlspecialchars(stripslashes($release_name)) ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<h4><?php echo _('Release date') ?>:</h4>
		</td>
		<td>
			<input type="text" name="release_date" value="<?php echo date('Y-m-d H:i'); ?>" size="16" maxlength="16" />
		</td>
	</tr>
	<tr>
		<td>
			<h4><?php echo _('File Name') ?>:<?php echo utils_requiredField();?></h4>
		</td>
		<td>
		<span class="important">
		<?php echo _('NOTE: In some browsers you must select the file in the file-upload dialog and click "OK".  Double-clicking doesn\'t register the file.')?>)</span><br />
		<?php echo _('Upload a new file') ?>: <input type="file" name="userfile"  size="30" />
		<?php if ($sys_use_ftpuploads) { 

			echo '<p>';
			printf(_('Alternatively, you can use FTP to upload a new file at %1$s'), $sys_ftp_upload_host).'<br />';
			echo _('Choose an FTP file instead of uploading:').'<br />';
			$arr[]='';
			$ftp_files_arr=array_merge($arr,ls($upload_dir,true));
			echo html_build_select_box_from_arrays($ftp_files_arr,$ftp_files_arr,'ftp_filename',''); ?>
		
		</p>
		<?php } ?>
		</td>
	</tr>
	<tr>
		<td>
			<h4><?php echo _('File Type') ?>:<?php echo utils_requiredField();?></h4>
		</td>
		<td>
<?php
	print frs_show_filetype_popup ('type_id',$type_id) . "<br />";
?>
		</td>
	</tr>
	<tr>
		<td>
			<h4><?php echo _('Processor Type') ?>:<?php echo utils_requiredField();?></h4>
		</td>
		<td>
<?php
	print frs_show_processor_popup ('processor_id',$processor_id);
?>		
		</td>
	</tr>
	<tr>
		<td valign="top">
			<h4><?php echo _('Release Notes') ?>:</h4>
		</td>
		<td>
			<textarea name="release_notes" rows="7" cols="50"><?php echo htmlspecialchars(stripslashes($release_notes)); ?></textarea>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<h4><?php echo _('Change Log') ?>:</h4>
		</td>
		<td>
			<textarea name="release_changes" rows="7" cols="50"><?php echo htmlspecialchars(stripslashes($release_changes)); ?></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:center">
			<input type="checkbox" name="preformatted" value="1" /> <?php echo _('Preserve my pre-formatted text') ?>
			<p><input type="submit" name="submit" value="<?php echo _('Release File') ?>" /></p>
		</td>
	</tr>
	</table>
</form>

<?php

frs_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
