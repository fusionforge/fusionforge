<?php
/**
  *
  * Project Admin: Edit Releases of Packages
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


/* Updated rewrite of the File Release System to clean up the UI 
 * a little and incorporate FRS.class.		-Darrell
 */

require_once('pre.php');	
require_once('frs.class');
require_once('www/project/admin/project_admin_utils.php');

if (!$group_id) {
	exit_no_group();
}
if (!$package_id || !$release_id) {
	header("Location: ./editpackages.php?group_id=$group_id");
	exit;
}

session_require(array('group'=>$group_id));

$project =& group_get_object($group_id);

exit_assert_object($project,'Project');

$perm =& $project->getPermission(session_get_user());

if (!$perm->isReleaseTechnician()) {
	exit_permission_denied();
}

project_admin_header(array('title'=>'Release New File Version','group'=>$group_id,'pagename'=>'project_admin_editreleases','sectionvals'=>array(group_getname($group_id))));

// Create a new FRS object
$frs = new FRS($group_id);

/*
 * Here's where we do the dirty work based on the step the user has chosen
 */

// Edit release info
if ($step1) {	
	$exec_changes = true;

	// Check for uploaded release notes
	if ($uploaded_notes != "none") {
		if (!is_uploaded_file($uploaded_notes)) {
			exit_error('Error','Attempted File Upload Attack');
		}
		$notes = addslashes(fread(fopen($HTTP_POST_FILES['uploaded_notes']['tmp_name'],'r'),filesize($HTTP_POST_FILES['uploaded_notes']['tmp_name'])));
		if ((strlen($notes) < 20) || (strlen($notes) > 256000)) {
			$feedback .= " Release Notes Are Either Too Small Or Too Large ";
			$exec_changes = false;
		}
	} else {
		$notes = $release_notes;
	}

	// Check for uplaoded change logs
	if ($uploaded_changes != "none") {
		if (!is_uploaded_file($uploaded_changes)) {
			exit_error('Error','Attempted File Upload Attack');
		}
		$changes = addslashes(fread(fopen($HTTP_POST_FILES['uploaded_changes']['tmp_name'],'r'), filesize($HTTP_POST_FILES['uploaded_changes']['tmp_name'])));
		if ((strlen($changes) < 20) || (strlen($changes) > 256000)) {
			$feedback .= " Change Log Is Either Too Small Or Too Large ";
			$exec_changes = false;
		}
	} else {
		$changes = $release_changes;
	}

	// If we haven't encountered any problems so far then save the changes
	if ($exec_changes == true) {
		if ($frs->frsChangeRelease($release_date, $release_name, $preformatted, $status_id, $notes, $changes, $package_id, $release_id)) {
			$feedback .= " Data Saved ";
		} else {
			$feedback .= $frs->getErrorMessage();
		}
	}
} 

// Add file(s) to the release
if ($step2) {	
	// Build a Unix time value from the supplied Y-m-d value
	$group_unix_name=group_getunixname($group_id);

	if ($userfile && is_uploaded_file($userfile)) {
		// Check to see if the user uploaded a file instead of selecting an existing one.
		// If so then move it to the 'incoming' dir where we proceed as usual.
		$file_name = $userfile_name;
		$feedback .= ' Adding File ';
		$now=time();
		//see if filename is legal before adding it
		if (!util_is_valid_filename ($file_name)) {
			$feedback .= " | Illegal FileName: $file_name ";
		} else {
			//see if they already have a file by this name

			$res1=db_query("SELECT frs_package.package_id FROM frs_package,frs_release,frs_file ".
				"WHERE frs_package.group_id='$group_id' ".
				"AND frs_release.release_id=frs_file.release_id ".
				"AND frs_release.package_id=frs_package.package_id ".
				"AND frs_release.release_id='$release_id' ".
				"AND frs_file.filename='$file_name'");
			echo db_error();
			if (!$res1 || db_numrows($res1) < 1) {
				/*
					Move the file into place
				*/
				$new_file=$sys_upload_dir.$group_unix_name.'/'.$userfile_name;
				system("/bin/mkdir $sys_upload_dir$group_unix_name/");
				if (!move_uploaded_file($userfile, $new_file)) {
					$feedback .= ' | Could Not Move Uploaded File ';
					db_rollback();
				} else {
					//add the file to the database
					$res=db_query("INSERT INTO frs_file ".
						"(release_time,filename,release_id,file_size,post_date, type_id, processor_id) ".
						"VALUES ('$now','$file_name','$release_id','"
						. filesize($new_file)
						. "','$now', '$type_id', '$processor_id') ");
					if (!$res) {
						$feedback .= " | Couldn't Add FileName: $file_name ";
						echo db_error();
					}
				}
			} else {
				$feedback .= " | FileName Already Exists For This Project: $file_name ";
			}
		}
	}
}

// Edit/Delete files in a release
if ($step3) {	
	// If the user chose to delete the file and he's sure then delete the file
	if( $step3 == "Delete File" && $im_sure ) {
		// delete the file from the database
		$frs->frsDeleteFile($file_id, $group_id);
		if( !$frs->isError() ) {
			$feedback .= " File Deleted ";
		}
	// Otherwise update the file information
	} else {
		$frs->frsChangeFile($release_time, $type_id, $processor_id, $file_id, $new_release_id, $package_id);
		if( !$frs->isError() ) {
			$feedback .= " File Updated ";
		}
	}
}

// Send email notice
if ($step4) {
	$frs->frsSendNotice($group_id, $release_id, $package_id);
	if( !$frs->isError() ) {
		$feedback .= " Email Notice Sent ";
	}
}

/*
 * Show the forms for each step
 */
?>

<h3>
Step 1:&nbsp;&nbsp; Edit Release
</h3>

<form enctype="multipart/form-data" method="post" action="<?php echo $PHP_SELF; ?>">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
<input type="hidden" name="release_id" value="<?php echo $release_id; ?>">
<input type="hidden" name="step1" value="1">
<table border="0" cellpadding="1" cellspacing="1">
<?php
	if(!($result = $frs->frsGetRelease($release_id))) {
		$feedback .= $frs->getErrorMessage();
	}
?>
<tr>
	<td width="10%"><b>Release Date:<b></td>
	<td><input type="text" name="release_date" value="<?php echo date('Y-m-d',db_result($result,0,'release_date')) ?>" size="10" maxlength="10"></td>
</tr>
<tr>
	<td><b>Release Name:<b></td>
	<td><input type="text" name="release_name" value="<?php echo htmlspecialchars(db_result($result,0,'release_name')); ?>"></td>
</tr>
<tr>
	<td><b>Status:</b></td>
	<td>
		<?php 
			echo frs_show_status_popup('status_id',db_result($result,0,'status_id')); 
		?>
	</td>
</tr>
<tr>
	<td><b>Of Package:</b></td>
	<td><?php echo frs_show_package_popup($group_id,'new_package_id',db_result($result,0,'package_id')); ?></td>
</tr>
<tr>
	<td colspan="2">
		<br>
		Edit the Release Notes or Change Log for this release of this package. These changes will apply to all files attached to this release.<br>
		You can either upload the release notes and change log individually, or paste them in together below.<br>
	</td>
</tr>
<tr>
	<td><b>Upload Release Notes:</b></td>
	<td><input type="file" name="uploaded_notes" size="30"></td>
</tr>
<tr>
	<td><b>Upload Change Log:</b></td>
	<td><input type="file" name="uploaded_changes" size="30"></td>
</tr>
<tr>
	<td COLSPAN=2>
		<b>Paste The Notes In:</b><br>
		<textarea name="release_notes" rows="10" cols="60" wrap="soft"><?php echo htmlspecialchars(db_result($result,0,'notes')); ?></textarea>
	</td>
</TR>
<TR>
	<td COLSPAN=2>
		<b>Paste The Change Log In:</b><br>
		<textarea name="release_changes" rows="10" cols="60" wrap="soft"><?php echo htmlspecialchars(db_result($result,0,'changes')); ?></textarea>
	</td>
</tr>
<TR>
	<TD>
		<br>
		<input type="checkbox" name="preformatted" value="1" <?php echo ((db_result($result,0,'preformatted'))?'checked':''); ?>> Preserve my pre-formatted text.
		<p>
		<input type="submit" name="submit" value="Submit/Refresh">
	</td>
</tr>
</table>
</form>
<P>
<hr noshade>
<P>
<h3>Step 2: Add Files To This Release</h3>
<P>
Now, choose a file to upload into the system. The maximum file size is determined by
the site administrator, but defaults to 2MB. If you need to upload large files, 
contact your site administrator.
<P>
<FORM ENCTYPE="multipart/form-data" METHOD="POST" ACTION="<?php echo $PHP_SELF; ?>">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
<input type="hidden" name="release_id" value="<?php echo $release_id; ?>">
<input type="hidden" name="step2" value="1">
<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="2000000">
<font color="red"><b>NOTE: In some browsers you must select the file in
the file-upload dialog and click "OK".  Double-clicking doesn't register the file.</b></font><br>
Upload a new file: <input type="file" name="userfile"  size="30">
<P>
<H4>File Type:</H4>
<P>
<?php
	print frs_show_filetype_popup ($name='type_id') . "<br>";
?>
<P>
<H4>Processor Type:</H4>
<P>
<?php
	print frs_show_processor_popup ($name='processor_id');
?>
<P>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Add This File">
</FORM>
<P>
<hr noshade>
<P>
<h3>Step 3: Edit Files In This Release</h3>

<?php
	// Get a list of files associated with this release
	$res=$frs->frsGetReleaseFiles($release_id);
	if( !$frs->isError() ) {
		$rows=db_numrows($res);
		if($rows < 1) {
			print("<H4>No Files In This Release</H4>\n");
		} else {
			print("Once you have added files to this release you <b>must</b> update each of these files with the correct information or they will not appear on your download summary page.\n");
			$title_arr[]='Filename<BR>Release';
			$title_arr[]='Processor<BR>Release Date';
			$title_arr[]='File Type<BR>Update';
	
			echo $GLOBALS['HTML']->listTableTop ($title_arr);
	
		for($x=0; $x<$rows; $x++) {
?>
			<form action="<?php echo $PHP_SELF; ?>" method="post">
				<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
				<input type="hidden" name="release_id" value="<?php echo $release_id; ?>">
				<input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
				<input type="hidden" name="file_id" value="<?php echo db_result($res,$x,'file_id'); ?>">
				<input type="hidden" name="step3" value="1">
				<tr bgcolor="<?php echo html_get_alt_row_color($x); ?>">
					<td nowrap><font size="-1"><?php echo db_result($res,$x,'filename'); ?></td>
					<td><font size="-1"><?php echo frs_show_processor_popup ('processor_id', db_result($res,$x,'processor_id')); ?></td>
					<td><font size="-1"><?php echo frs_show_filetype_popup ('type_id', db_result($res,$x,'type_id')); ?></td>
				</tr>
				<tr bgcolor="<?php echo html_get_alt_row_color($x); ?>">
					<td>
						<font size="-1">
							<?php echo frs_show_release_popup ($group_id, $name='new_release_id',db_result($res,$x,'release_id')); ?>
						</font>
					</td>
					<td>
						<font size="-1">
							<input type="text" name="release_time" value="<?php echo date('Y-m-d',db_result($res,$x,'release_time')); ?>" size="10" maxlength="10">
						</font>
					</td>
					<td><font size="-1"><input type="submit" name="submit" value="Update/Refresh"></td>
				</tr>
				</form>

			<form action="<?php echo $PHP_SELF; ?>" method="post">
				<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
				<input type="hidden" name="release_id" value="<?php echo $release_id; ?>">
				<input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
				<input type="hidden" name="file_id" value="<?php echo db_result($res,$x,'file_id'); ?>">
				<input type="hidden" name="step3" value="Delete File">
				<tr bgcolor="<?php echo html_get_alt_row_color($x); ?>">
					<td><font size="-1">&nbsp;</td>
					<td><font size="-1">&nbsp;</td>
					<td>
						<font size="-1">
							<input type="submit" name="submit" value="Delete File"> <input type="checkbox" name="im_sure" value="1"> I'm Sure
						</font>
					</td>
				</tr>
			</form>
<?php
			}

			echo $GLOBALS['HTML']->listTableBottom();

		}
	} else {
		$feedback .= $frs->getErrorMessage();
	}
?>

<P>
<hr noshade>
<P>
<h3>Step 4: Email Release Notice</h3>
<P>
<?php 
	$mons = $frs->frsGetReleaseMonitors($package_id); 
	if( $mons > 0 ) {
?>

<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
	<input type="hidden" name="release_id" value="<?php echo $release_id; ?>">
	<input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
	<input type="hidden" name="step4" value="Email Release">
	<?php echo $mons; ?> users(s) are monitoring this package.  You should send a notice of your file release.<br>
	<input type="submit" value="Send Notice"><input type="checkbox" value="sure"> I'm sure.
</form>

<?php

	} else {

		echo 'Nobody is monitoring this package at this time.';

	}

project_admin_footer(array());

?>
