<?php
/**
  *
  * Project Admin: Edit Releases of Packages
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: editreleases.php,v 1.56 2001/07/09 21:46:15 pfalcon Exp $
  *
  */


/* Updated rewrite of the File Release System to clean up the UI 
 * a little and incorporate FRS.class.		-Darrell
 */

require_once('pre.php');    
require_once('frs.class');
require_once('www/project/admin/project_admin_utils.php');

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
	if (!util_check_fileupload($uploaded_notes)
	    || !util_check_fileupload($uploaded_changes)) {
		$feedback .= ' Invalid filename';
		project_admin_footer(array());
		exit();

	}

	$exec_changes = true;

	// Check for uploaded release notes
	if ($uploaded_notes != "none") {
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
	$group_unix_name=group_getunixname($group_id);
	$user_unix_name=user_getname();
	$project_files_dir=$FTPFILES_DIR . '/' . $group_unix_name;
	$user_incoming_dir=ereg_replace("<USER>",$user_unix_name,$FTPINCOMING_DIR);

	// For every file selected add that file to this release
	for($x=0;$x<count($file_list);$x++) {
		$frs->frsAddFile(time(), $file_list[$x], $group_unix_name, $user_unix_name, filesize("$user_incoming_dir/$file_list[$x]"), time(), $release_id, $package_id);
		if( !$frs->isError() ) {
			$feedback .= " File(s) Added ";
		}
	}
}

// Edit/Delete files in a release
if ($step3) {	
	// If the user chose to delete the file and he's sure then delete the file
	if( $step3 == "Delete File") {
		if ( $im_sure ) {
			// delete the file from the database
			$frs->frsDeleteFile($file_id, $group_id);
			if( !$frs->isError() ) {
				$feedback .= " File Deleted ";
			}
		} else {
			$feedback .= " File Not Deleted, you must be sure ";
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

if ($package_id) {
	//narrow the list to just this package's releases
	$pkg_str = "AND frs_package.package_id='$package_id'";
}

if( !$release_id ) {
	$res=$frs->frsGetReleaseList($pkg_str);
	$rows=db_numrows($res);
	if (!$res || $rows < 1) {
		echo '<h4>You Have No Releases '.(($package_id)?'Of This Package ':'').'Defined</h4>';
		echo db_error();
	} else {
		/*
			Show a list of releases
			For this project or package
		*/
		$title_arr=array();
		$title_arr[]='Release Name';
		$title_arr[]='Package Name';
		$title_arr[]='Status';
	
		echo html_build_list_table_top ($title_arr);

		for ($i=0; $i<$rows; $i++) {
?>

<tr bgcolor="<?php echo html_get_alt_row_color($i); ?>">
	<td>
		<font size="-1">
			<?php echo db_result($res,$i,'release_name'); ?>
			<a href="editreleases.php?package_id=<?php echo $package_id; ?>&release_id=<?php echo db_result($res,$i,'release_id'); ?>&group_id=<?php echo $group_id; ?>">[Edit This Release]</a>
		</font>
	</td>
	<td>
		<font size="-1">
			<?php echo db_result($res,$i,'package_name'); ?>
			<a href="editpackages.php?group_id=<?php echo $group_id; ?>">[Edit This Package]</a>
		</font>
	</td>
	<td>
		<font size="-1"><?php echo db_result($res,$i,'status_name'); ?></font>
	</td>
</tr>
</form>

<?php
	}
}
	echo "</table>\n";
}

/*
 * Show the forms for each step
 */
if( $release_id ) {
?>

<h3>
Step 1:&nbsp;&nbsp;
Edit Existing Release
<!-- Edit release '<i><?php // echo $frs->frsResolveRelease("release_name", $release_id, $group_id); ?></i>'  -->
<!-- of package '<i><?php // echo $frs->frsResolveRelease("package_name", $release_id, $group_id); ?></i>' -->
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

<hr noshade>

<h3>Step 2:&nbsp;&nbsp; Add Files To This Release</h3>

<form method="post" action="<?php echo $PHP_SELF; ?>">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
<input type="hidden" name="release_id" value="<?php echo $release_id; ?>">
<input type="hidden" name="step2" value="1">

<?php $user_unix_name=user_getname(); ?>
Next, choose your files from the list below.<br>
You can upload new files using FTP to <b><?php echo "<a href=ftp://$user_unix_name@$sys_upload_host/incoming/>$sys_upload_host</a>"; ?></b> with the username <b><?php echo $user_unix_name ; ?></b> and your password in the <b>incoming</b> directory. 
When you are done uploading, just hit the refresh button to see the new files.
<br><br>
<table border="0" cellpadding="3" cellspacing="3">
<tr>
<TD>
<?php
	$atleastone = 0;
	$counter = 0;
	$user_incoming_dir=ereg_replace("<USER>",user_getname(),$FTPINCOMING_DIR);
	//echo "<b>$user_incoming_dir</b>";
	if(is_dir($user_incoming_dir)){
	$dirhandle = opendir($user_incoming_dir);
	
	// Iterate through each file in the upload dir and display it with a checkbox
	while ($file = readdir($dirhandle)) {
		// Make sure its not a dot file (.file)
		if (!ereg('^\.',$file[0])) {
			$atleastone = 1;

			if($counter < 8) {
				$counter++;
			} else {
				//print("</tr>\n<tr>\n");
				$counter = 0;
			}

			print("	<input type='checkbox' name='file_list[]' value='$file'>$file<BR>\n");
		}
	}
	}

	// If there aren't any files in the upload dir then say so
	if($atleastone == 0) {
		print("No Files Available\n");
	}
?>
</TD></tr>
</table>
<input type="submit" name="submit" value="Add Files and/or Refresh View">
</form>

<hr noshade>

<h3>Step 3:&nbsp;&nbsp; Edit Files In This Release</h3>

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
	
			echo html_build_list_table_top ($title_arr);
	
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
			echo '</table>';
		}
	} else {
		$feedback .= $frs->getErrorMessage();
	}
?>


<hr noshade>

<h3>Step 4:&nbsp;&nbsp; Email Release Notice</h3>

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
?>

Nobody is monitoring this package at this time.

<?php
	}
}
	project_admin_footer(array());
?>
