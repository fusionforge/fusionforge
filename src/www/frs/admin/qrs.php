<?php
/**
 * FusionForge File Release Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems , Darrell Brogdon
 * Copyright 2002 (c) GForge, LLC
 * Copyright 2010 (c), FusionForge Team
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';
require_once $gfcommon.'frs/FRSFile.class.php';
require_once $gfcommon.'frs/include/frs_utils.php';

$group_id = getIntFromRequest('group_id');
$package_id = getIntFromRequest('package_id');

if (!$group_id)
	exit_no_group();

$g = group_get_object($group_id);

if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error('Error', $g->getErrorMessage(), 'frs');
}

// check the permissions and see if this user is a release manager.
// If so, he can create a release
session_require_perm('frs', $group_id, 'write');

$packages = get_frs_packages($g);

$upload_dir = forge_get_config('ftp_upload_dir') . "/" . $g->getUnixName();

if (getStringFromRequest('submit')) {
	$release_name = trim(getStringFromRequest('release_name'));
	$userfile = getUploadedFile('userfile');
	$userfile_name = $userfile['name'];
	$type_id = getIntFromRequest('type_id');
	$processor_id = getIntFromRequest('processor_id');
	$release_date = getStringFromRequest('release_date');
	// Build a Unix time value from the supplied Y-m-d value
	$release_date = strtotime($release_date);
	$release_notes = getStringFromRequest('release_notes');
	$release_changes = getStringFromRequest('release_changes');
	$preformatted = getStringFromRequest('preformatted');
	$ftp_filename = getStringFromRequest('ftp_filename');
	$manual_filename = getStringFromRequest('manual_filename');
	$group_unix_name=group_getunixname($group_id);

	$warning_msg = '' ;
	if (!$release_name) {
		$warning_msg .= _('Must define a release name.');
	} else 	if (!$package_id) {
		$warning_msg .= _('Must select a package.');

	} else {

		//
		//	Get the package
		//
		$frsp = new FRSPackage($g,$package_id);
		if (!$frsp || !is_object($frsp)) {
			exit_error(_('Could Not Get FRS Package'),'frs');
		} elseif ($frsp->isError()) {
			exit_error($frsp->getErrorMessage(),'frs');
		} else {
			//
			//	Create a new FRSRelease in the db
			//
			$frsr = new FRSRelease($frsp);
			if (!$frsr || !is_object($frsr)) {
				exit_error(_('Could Not Get FRS Release'),'frs');
			} elseif ($frsr->isError()) {
				exit_error($frsr->getErrorMessage(),'frs');
			} else {
				db_begin();
				if (!$frsr->create($release_name,$release_notes,$release_changes,
						   $preformatted,$release_date)) {
					db_rollback();
					exit_error($frsr->getErrorMessage(),'frs');
				}

				$ret = frs_add_file_from_form ($frsr, $type_id, $processor_id, $release_date,
							       $userfile, $ftp_filename, $manual_filename) ;

				if ($ret !== true) {
					db_rollback() ;
					exit_error ($ret,'frs') ;
				}
				$frsr->sendNotice();

				frs_admin_header(array('title'=>_('Quick Release System'),'group'=>$group_id));
				echo '<p>';
				printf (_('You can now <a href="%1$s"><strong>add files to this release</strong></a> if you wish, or edit the release. Please note that file(s) may not appear immediately on the <a href="%2$s">download page</a>. Allow several hours for propagation.'),
					util_make_url ('/frs/admin/editrelease.php?release_id='.$frsr->getID().'&amp;group_id='.$group_id.'&amp;package_id='.$package_id),
					util_make_url ('/frs/?group_id='.$group_id)
					) ;
				echo '</p>';
				db_commit();
				frs_admin_footer(array());
				exit();
			}
		}
	}
} else {
	$release_name = '';
	$userfile = '';
	$userfile_name = '';
	$type_id = '';
	$processor_id = '';
	$release_date = '';
	$release_notes = '';
	$release_changes = '';
	$preformatted = '';
	$ftp_filename = '';
	$manual_filename = '';
}

frs_admin_header(array('title'=>_('Quick Release System'),'group'=>$group_id));

?>

<form enctype="multipart/form-data" method="post" action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id"; ?>">
	<table>
	<tr>
		<td>
			<strong><?php echo _('Package ID')._(':'); ?></strong>
		</td>
		<td>
<?php
	$res=db_query_params("SELECT * FROM frs_package WHERE group_id=$1 AND status_id='1'", array($group_id));
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

			<?php printf(_('Or %1$s create a new package %2$s'), '<a href="'.util_make_url ('/frs/admin/?group_id='.$group_id).'">', '</a>') ?>
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo _('Release Name')._(':'); ?><?php echo utils_requiredField();?></strong>
		</td>
		<td>
			<input type="text" required="required" name="release_name" value="<?php echo htmlspecialchars($release_name) ?>" pattern=".{3,}" title="<?php echo  _('At least 3 characters') ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo _('Release Date')._(':'); ?></strong>
		</td>
		<td>
			<input type="text" name="release_date" value="<?php echo date('Y-m-d H:i'); ?>" size="16" maxlength="16" />
		</td>
	</tr>
	<tr>
		<td>
			<h4><?php echo _('File Name')._(':'); ?><?php echo utils_requiredField();?></h4>
		</td>
		<td>
		<div class="important">
		<?php printf(_('You can probably not upload files larger than about %.2f MiB in size.'), util_get_maxuploadfilesize() / 1048576); ?><br />
		</div>
		<?php echo _('Upload a new file')._(': '); ?><input type="file" name="userfile"  size="30" />
		<?php if (forge_get_config('use_ftp_uploads')) {
			echo '<p>';
			printf(_('Alternatively, you can use FTP to upload a new file at %s.'), forge_get_config('ftp_upload_host'));
			echo '<br />';
			echo _('Choose an FTP file instead of uploading:').'<br />';
			$ftp_files_arr=frs_filterfiles(ls($upload_dir,true));
			echo html_build_select_box_from_arrays($ftp_files_arr,$ftp_files_arr,'ftp_filename','');
			echo '</p>';
		}
		if (forge_get_config('use_manual_uploads')) {
			$incoming = forge_get_config('groupdir_prefix')."/".$g->getUnixName()."/incoming" ;
			echo '<p>';
			printf(_('Alternatively, you can use a file you already uploaded (by SFTP or SCP) to the <a href="%2$s">project\'s incoming directory</a> (%1$s).'),
				$incoming, "sftp://" . forge_get_config ('shell_host') . $incoming . "/");
			echo ' ' . _('This direct <tt>sftp://</tt> link only works with some browsers, such as Konqueror.') . '<br />';
			echo _('Choose an already uploaded file:').'<br />';
			$manual_files_arr=frs_filterfiles(ls($incoming,true));
			echo html_build_select_box_from_arrays($manual_files_arr,$manual_files_arr,'manual_filename','');
			echo '</p>';
		}
?>

		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo _('File Type')._(':'); ?></strong>
		</td>
		<td>
<?php
	print frs_show_filetype_popup ('type_id',$type_id);
?>
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo _('Processor Type')._(':'); ?></strong>
		</td>
		<td>
<?php
	print frs_show_processor_popup ('processor_id',$processor_id);
?>
		</td>
	</tr>
	<tr>
		<td class="top">
			<strong><?php echo _('Release Notes')._(':'); ?></strong>
		</td>
		<td>
			<textarea name="release_notes" rows="7" cols="50"><?php echo htmlspecialchars($release_notes); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="top">
			<strong><?php echo _('Change Log')._(':'); ?></strong>
		</td>
		<td>
			<textarea name="release_changes" rows="7" cols="50"><?php echo htmlspecialchars($release_changes); ?></textarea>
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
