<?php
/**
 * GForge File Release Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: forum_utils.php.patched,v 1.1.2.1 2002/11/30 09:57:57 cbayle Exp $
 */

require_once('pre.php');	
require_once('www/project/admin/project_admin_utils.php');
require_once('common/frs/FRSPackage.class');
require_once('common/frs/FRSRelease.class');
require_once('common/frs/FRSFile.class');

/*
	Quick file release system , Darrell Brogdon, SourceForge, Aug, 2000

	With much code horked from editreleases.php
*/

session_require(array('group'=>$group_id));

$g =& group_get_object($group_id);

exit_assert_object($g, 'Project');

$perm =& $g->getPermission(session_get_user());

if (!$perm->isReleaseTechnician()) {
	exit_permission_denied();
}


if( $submit ) {
	if (!$release_name) {
		$feedback .= ' Must define a release name. ';
		echo db_error();
	} else 	if (!$package_id) {
		$feedback .= ' Must select a package. ';
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
			if ($userfile && is_uploaded_file($userfile)) {
				//
				//	Create a new FRSRelease in the db
				//
				$frsr = new FRSRelease($frsp);
				if (!$frsr || !is_object($frsr)) {
					exit_error('Error','Could Not Get FRSRelease');
				} elseif ($frsr->isError()) {
					exit_error('Error',$frsr->getErrorMessage());
				} else {
					$date_list = split('[- :]',$release_date,5);
					$release_date = mktime($date_list[3],$date_list[4],0,$date_list[1],$date_list[2],$date_list[0]);
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
						if (!$frsf->create($userfile_name,$userfile,$type_id,$processor_id,$release_date)) {
							db_rollback();
							exit_error('Error',$frsf->getErrorMessage());
						}
						$frsr->sendNotice();
						$feedback='File Released: You May Choose To Edit the Release Now';

						project_admin_header(array('title'=>'Release New File Version','group'=>$group_id,'pagename'=>'project_admin_qrs','sectionvals'=>array(group_getname($group_id))));
						?>
						<p>
						You can now <a href="/project/admin/editrelease.php?release_id=<?php echo $frsr->getID()."&amp;group_id=$group_id&amp;package_id=$package_id"; ?>"><strong>Add Files To This Release</strong></a> if you wish,
						or edit the release.</p>
						<p>
						Please note that file(s) may not appear immediately
						on the <a href="/project/showfiles.php?group_id=<?php echo $group_id;?>">
						download page</a>. Allow several hours for propogation.
						</p>
						<?php
						db_commit();
						
					}

				}

			} else {

			}
		}
	}
} else {

project_admin_header(array('title'=>'Release New File Version','group'=>$group_id,'pagename'=>'project_admin_qrs','sectionvals'=>array(group_getname($group_id))));

?>

<form enctype="multipart/form-data" method="post" action="<?php echo $PHP_SELF."?group_id=$group_id"; ?>">
	<table border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td>
			<h4>Package ID:</h4>
		</td>
		<td>
<?php
	$sql="SELECT * FROM frs_package WHERE group_id='$group_id' AND status_id='1'";
	$res=db_query($sql);
	$rows=db_numrows($res);
	if (!$res || $rows < 1) {
		echo '<h4>No File Types Available</h4>';
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
			&nbsp;&nbsp;Or, <a href="editpackages.php?group_id=<?php echo $group_id; ?>">create a new package</a>.
		</td>
	</tr>
	<tr>
		<td>
			<h4>Release Name:</h4>
		</td>
		<td>
			<input type="text" name="release_name" />
		</td>
	</tr>
	<tr>
		<td>
			<h4>Release Date:</h4>
		</td>
		<td>
			<input type="text" name="release_date" value="<?php echo date('Y-m-d'); ?>" size="10" maxlength="10" />
		</td>
	</tr>
	<tr>
		<td>
			<h4>File Name:</h4>
		</td>
		<td>
		<span style="color:red"><strong>NOTE: In some browsers you must select the file in
		the file-upload dialog and click "OK".  Double-clicking doesn't register the file.</strong></span><br />
		Upload a new file: <input type="file" name="userfile"  size="30" />
		</td>
	</tr>
	<tr>
		<td>
			<h4>File Type:</h4>
		</td>
		<td>
<?php
	print frs_show_filetype_popup ($name='type_id') . "<br />";
?>
		</td>
	</tr>
	<tr>
		<td>
			<h4>Processor Type:</h4>
		</td>
		<td>
<?php
	print frs_show_processor_popup ($name='processor_id');
?>		
		</td>
	</tr>
	<tr>
		<td valign="top">
			<h4>Release Notes:</h4>
		</td>
		<td>
			<textarea name="release_notes" rows="7" cols="50"></textarea>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<h4>Change Log:</h4>
		</td>
		<td>
			<textarea name="release_changes" rows="7" cols="50"></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="checkbox" name="preformatted" value="1" /> Preserve my pre-formatted text.
			<p><input type="submit" name="submit" value="Release File" /></p>
		</td>
	</tr>
	</table>
</form>

<?php
}

project_admin_footer(array());
?>
