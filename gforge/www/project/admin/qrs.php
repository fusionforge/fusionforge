<?php
/**
  *
  * Project Admin: Quick Release System (QRS)
  *
  * This page allows one-step release of a file.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: qrs.php,v 1.17 2001/07/09 21:26:28 pfalcon Exp $
  *
  */


require_once('pre.php');    
require_once('www/project/admin/project_admin_utils.php');
require_once('frs.class');

/*
	Quick file release system , Darrell Brogdon, SourceForge, Aug, 2000

	With much code horked from editreleases.php
*/

session_require(array('group'=>$group_id));

$project =& group_get_object($group_id);

exit_assert_object($project, 'Project');

$perm =& $project->getPermission(session_get_user());

if (!$perm->isReleaseTechnician()) {
	exit_permission_denied();
}

project_admin_header(array('title'=>'Release New File Version','group'=>$group_id,'pagename'=>'project_admin_qrs','sectionvals'=>array(group_getname($group_id))));

if( $submit ) {
	if (!util_check_fileupload($userfile)) {
		$feedback .= ' Invalid filename';
	} else if (!$release_name) {
		$feedback .= ' Must define a release name. ';
		echo db_error();
	} else 	if (!$package_id) {
		$feedback .= ' Must select a package. ';
	} else {
		//create a new release of this package

		//see if this package belongs to this project
		$res1=db_query("SELECT * FROM frs_package WHERE package_id='$package_id' AND group_id='$group_id'");
		if (!$res1 || db_numrows($res1) < 1) {
		  $feedback .= ' | Package Doesn\'t Exist Or Isn\'t Yours ';
		  echo db_error();
		} else {
		  //package_id was fine - now insert the release
		  $res=db_query("INSERT INTO frs_release (package_id,name,notes,changes,status_id,preformatted,release_date,released_by) ".
				"VALUES ('$package_id','$release_name','$release_notes','$release_changes','$status_id','$preformatted','". time() ."','". user_getid() ."')");
		  if (!$res) {
		    $feedback .= ' | Adding Release Failed ';
		    echo db_error();
		    //insert failed - go back to definition screen
		  } else {
		    //release added - now show the detail page for this new release
		    $release_id=db_insertid($res,'frs_release','release_id');
		    $feedback .= ' Added Release <BR>';
		  }
		}

		/*
			Add a file to this release

			First, make sure this release belongs to this group

			iterate the following for each file:

			Second see if the filename is legal
			Third see if they already have a file by the same name
			Fourth if file actually exists, physically move the file on garbage to the new location
			Fifth insert it into the database
		*/
		$group_unix_name=group_getunixname($group_id);
		$project_files_dir=ereg_replace("<GROUP>",$group_unix_name,$FTPFILES_DIR);
		$user_unix_name=user_getname();
		$user_incoming_dir=ereg_replace("<USER>",$user_unix_name,$FTPINCOMING_DIR);

		if ($file_name) {
			// Check to see if the user uploaded a file instead of selecting an existing one.
			// If so then move it to the 'incoming' dir where we proceed as usual.
			if( $file_name == "qrs_newfile" ) {
				$file_name = $userfile_name;

				if (is_file($userfile) && file_exists($userfile)) {
					$new_userfile = explode("tmp/", $userfile);
					$userfile = $new_userfile[1];
					// The following line is due to PHP braindeadness.
					// Don't you dare remove it, it'll break.
					// I know.  I tried it.
					// [RM]
					putenv ('sys_dbpasswd='.getenv ('sys_dbpasswd')) ;
					exec ("/usr/lib/sourceforge/bin/tmpfilemove.pl ".escapeshellarg($userfile). " ".escapeshellarg($userfile_name)." ".escapeshellarg($user_unix_name)." 2>&1",$exec_res);
					putenv ('sys_dbpasswd=') ;
					if ($exec_res[0]) {
						echo '<H3>' . $exec_res[0],$exec_res[1] . '</H3><P>';
					}
				}
			}
			$feedback .= ' Adding File ';
			//see if this release belongs to this project
			$res1=db_query("SELECT frs_package.package_id FROM frs_package,frs_release ".
						   "WHERE frs_package.group_id='$group_id' ".
						   "AND frs_release.release_id='$release_id' ".
						   "AND frs_release.package_id=frs_package.package_id");
			if (!$res1 || db_numrows($res1) < 1) {
				//release not found for this project
				$feedback .= " | Not Your Release Or Release Doesn't Exist ";
			} else {
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
						"AND frs_file.filename='$file_name'");
					if (!$res1 || db_numrows($res1) < 1) {

						/*
							move the file to the project's fileserver directory
						*/
						clearstatcache();
						if (is_file($user_incoming_dir.'/'.$file_name) && file_exists($user_incoming_dir.'/'.$file_name)) {
							//move the file to a its project page using a setuid program
						  // The following line is due to PHP braindeadness.
						  // Don't you dare remove it, it'll break.
						  // I know.  I tried it.
						  // [RM]
						  putenv ('sys_dbpasswd='.getenv ('sys_dbpasswd')) ;
						  exec ("/usr/lib/sourceforge/bin/fileforge.pl ".escapeshellarg($file_name)." ".escapeshellarg($user_unix_name)." ".escapeshellarg($group_unix_name)." 2>&1",$exec_res);
						  putenv ('sys_dbpasswd=') ;
							if ($exec_res[0]) {
								echo '<h3>'.$exec_res[0],$exec_res[1],$exec_res[2].'</H3><P>';
							}
							//add the file to the database
							$res=db_query("INSERT INTO frs_file ".
								"(release_time,filename,release_id,file_size,post_date, type_id, processor_id) ".
								"VALUES ('$now','$file_name','$release_id','"
								. filesize("$project_files_dir/$file_name") 
								. "','$now', '$type_id', '$processor_id') ");
							if (!$res) {
								$feedback .= " | Couldn't Add FileName: $file_name ";
								echo db_error();
							} else {
								// Finally, send out email notification
								$frs = new FRS($group_id);
						                $frs->frsSendNotice($group_id, $release_id, $package_id);
						                if( !$frs->isError() ) {
					                                $feedback .= '<br>Email Notice Sent';
								}
								?>
								<p>
								Please note that file(s) may not appear immediately
								on the <a href="/project/showfiles.php?group_id=<?php echo $group_id;?>">
								download page</a>. Allow several hours for propogation.
								</p>
								<?php
							}
						} else {
							$feedback .= " | FileName Invalid Or Does Not Exist: $file_name ";
						}
					} else {
						$feedback .= " | FileName Already Exists For This Project: $file_name ";
					}
				}
			}
		} else {
			//do nothing
			$feedback .= ' No Files Selected ';
		}
	}
} else {
?>

<FORM ENCTYPE="multipart/form-data" METHOD="POST" ACTION="<?php echo $PHP_SELF; ?>">
<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="1000000">
	<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="2">
	<TR>
		<TD>
			<H4>Package ID:</H4>
		</TD>
		<TD>
<?php
	$sql="SELECT * FROM frs_package WHERE group_id='$group_id' AND status_id='1'";
	$res=db_query($sql);
	$rows=db_numrows($res);
	if (!$res || $rows < 1) {
		echo '<H4>No File Types Available</H4>';
	} else {
		echo '<SELECT NAME="package_id">';
		echo '<OPTION VALUE="">(select)</OPTION>';
		for ($i=0; $i<$rows; $i++) {
			echo '<OPTION VALUE="' . db_result($res,$i,'package_id') . '">' . db_result($res,$i,'name') . '</OPTION>';
		}
		echo '</SELECT>';
	}
?>
			&nbsp;&nbsp;Or, <a href="editpackages.php?group_id=<?php echo $group_id; ?>">create a new package</a>.
		</TD>
	</TR>
	<TR>
		<TD>
			<H4>Release Name:</H4>
		</TD>
		<TD>
			<INPUT TYPE="TEXT" name="release_name">
		</TD>
	</TR>
	<TR>
		<TD>
			<H4>Release Date:</H4>
		</TD>
		<TD>
			<INPUT TYPE="TEXT" NAME="release_date" VALUE="<?php echo date('Y-m-d H:i'); ?>" SIZE="16" MAXLENGTH="16">
		</TD>
	</TR>
	<TR>
		<TD>
			<H4>Status:</H4>
		</TD>
		<TD>
<?php print frs_show_status_popup ($name='status_id') . "<br>"; ?>
		</TD>
	</TR>
	<TR>
		<TD>
			<H4>File Name:</H4>
		</TD>
		<TD>
<font color="red"><b>NOTE: In some browsers you must select the file in the file-upload dialog and click "OK".  Double-clicking doesn't register the file.</b></font><br>
<?php
	
	$user_unix_name=user_getname();
	$user_incoming_dir=ereg_replace("<USER>",$user_unix_name,$FTPINCOMING_DIR);
	if(is_dir($user_incoming_dir)){
	$dirhandle = opendir($user_incoming_dir);

	echo '<SELECT NAME="file_name">';
	echo '	<OPTION VALUE="qrs_newfile">Select a file</OPTION>';
	//iterate and show the files in the upload directory
	while ($file = readdir($dirhandle)) {
		if (!ereg('^\.',$file[0])) {
			$atleastone = 1;
			print '<OPTION value="'.$file.'">'.$file.'</OPTION>';
		}
	}
	}
	echo '</SELECT> Or, upload a new file: <input type="file" name="userfile"  size="30">';
	if (!$atleastone) {
		print '<h3>No available files</H3>
			<P>
			You can upload files using FTP to <B>'.$GLOBALS['sys_upload_host'].'</B> 
			in the <B>/incoming</B> directory, then hit <B>Refresh View</B>.';
	}
?>

		</TD>
	</TR>
	<TR>
		<TD>
			<H4>File Type:</H4>
		</TD>
		<TD>
<?php
	print frs_show_filetype_popup ($name='type_id') . "<br>";
?>
		</TD>
	</TR>
	<TR>
		<TD>
			<H4>Processor Type:</H4>
		</TD>
		<TD>
<?php
	print frs_show_processor_popup ($name='processor_id');
?>		
		</TD>
	</TR>
	<TR>
		<TD VALIGN="TOP">
			<H4>Release Notes:</H4>
		</TD>
		<TD>
			<TEXTAREA NAME="release_notes" ROWS="7" COLS="50"></TEXTAREA>
		</TD>
	</TR>
	<TR>
		<TD VALIGN="TOP">
			<H4>Change Log:</H4>
		</TD>
		<TD>
			<TEXTAREA NAME="release_changes" ROWS="7" COLS="50"></TEXTAREA>
		</TD>
	</TR>
	<TR>
		<TD COLSPAN="2" ALIGN="CENTER">
			<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="<?php echo $group_id; ?>">
			<input type="checkbox" name="preformatted" value="1" <?php echo ((db_result($result,0,'preformatted'))?'checked':''); ?>> Preserve my pre-formatted text.
			<p>
			<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Release File">
		</TD>
	</TR>
	</TABLE>
</FORM>

<?php
}

project_admin_footer(array());
?>
