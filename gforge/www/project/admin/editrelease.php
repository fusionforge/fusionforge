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
	if ($uploaded_notes != "none") {
		if (!is_uploaded_file($uploaded_notes)) {
			exit_error('Error','Attempted File Upload Attack');
		}
		$notes = addslashes(fread(fopen($HTTP_POST_FILES['uploaded_notes']['tmp_name'],'r'),filesize($HTTP_POST_FILES['uploaded_notes']['tmp_name'])));
		if (strlen($notes) < 20) {
			$feedback .= " Release Notes Are Too Small ";
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
		if (strlen($changes) < 20) {
			$feedback .= " Change Log Is Too Small ";
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
			$feedback .= " Data Saved ";
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
			$feedback='File Released';
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
				$feedback .= " File Deleted ";
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
				$feedback .= " File Updated ";
			}
		}
	}
}

project_admin_header(array('title'=>'Release New File Version','group'=>$group_id,'pagename'=>'project_admin_editreleases','sectionvals'=>array(group_getname($group_id))));
/*
 * Show the forms for each step
 */
?>

<h3>
Step 1:&nbsp;&nbsp; Edit Release
</h3>

<form enctype="multipart/form-data" method="post" action="<?php echo $PHP_SELF."?group_id=$group_id&release_id=$release_id&package_id=$package_id"; ?>">
<input type="hidden" name="step1" value="1">
<table border="0" cellpadding="1" cellspacing="1">
<tr>
	<td width="10%"><b>Release Date:<b></td>
	<td><input type="text" name="release_date" value="<?php echo date('Y-m-d',$frsr->getReleaseDate()) ?>" size="10" maxlength="10"></td>
</tr>
<tr>
	<td><b>Release Name:<b></td>
	<td><input type="text" name="release_name" value="<?php echo htmlspecialchars($frsr->getName()); ?>"></td>
</tr>
<tr>
	<td><b>Status:</b></td>
	<td>
		<?php 
			echo frs_show_status_popup('status_id',$frsr->getStatus()); 
		?>
	</td>
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
		<textarea name="release_notes" rows="10" cols="60" wrap="soft"><?php echo htmlspecialchars($frsr->getNotes()); ?></textarea>
	</td>
</TR>
<TR>
	<td COLSPAN=2>
		<b>Paste The Change Log In:</b><br>
		<textarea name="release_changes" rows="10" cols="60" wrap="soft"><?php echo htmlspecialchars($frsr->getChanges()); ?></textarea>
	</td>
</tr>
<TR>
	<TD>
		<br>
		<input type="checkbox" name="preformatted" value="1" <?php echo (($frsr->getPreformatted())?'checked':''); ?>> Preserve my pre-formatted text.
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
<FORM ENCTYPE="multipart/form-data" METHOD="POST" ACTION="<?php echo $PHP_SELF."?group_id=$group_id&release_id=$release_id&package_id=$package_id"; ?>">
<input type="hidden" name="step2" value="1">
<font color="red"><b>NOTE: In some browsers you must select the file in
the file-upload dialog and click "OK".  Double-clicking doesn't register the file.</b></font><br>
Upload a new file: <input type="file" name="userfile"  size="30">
<P>
<H4>File Type:</H4>
<P>
<?php
	print frs_show_filetype_popup ('type_id') . "<br>";
?>
<P>
<H4>Processor Type:</H4>
<P>
<?php
	print frs_show_processor_popup ('processor_id');
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
	$res=db_query("SELECT * FROM frs_file WHERE release_id='$release_id'");
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
			<form action="<?php echo $PHP_SELF."?group_id=$group_id&release_id=$release_id&package_id=$package_id"; ?>" method="post">
				<input type="hidden" name="file_id" value="<?php echo db_result($res,$x,'file_id'); ?>">
				<input type="hidden" name="step3" value="1">
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
					<td nowrap><font size="-1"><?php echo db_result($res,$x,'filename'); ?></td>
					<td><font size="-1"><?php echo frs_show_processor_popup ('processor_id', db_result($res,$x,'processor_id')); ?></td>
					<td><font size="-1"><?php echo frs_show_filetype_popup ('type_id', db_result($res,$x,'type_id')); ?></td>
				</tr>
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
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
				<tr <?php echo $HTML->boxGetAltRowStyle($x); ?>>
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

project_admin_footer(array());

?>
