<?php
/**
 * Project File Information/Download Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2013-2014, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'frs/include/frs_utils.php';
require_once $gfcommon.'frs/FRSPackageFactory.class.php';

global $HTML;
/* are we using frs ? */
if (!forge_get_config('use_frs'))
	exit_disabled('home');

$group_id = getIntFromRequest('group_id');
/* validate group */
if (!$group_id)
	exit_no_group();

$g = group_get_object($group_id);
if (!$g || !is_object($g))
	exit_no_group();

/* is this group using FRS ? */
if (!$g->usesFRS())
	exit_disabled();

if ($g->isError())
	exit_error($g->getErrorMessage(), 'frs');

session_require_perm('frs', $group_id, 'read_public');

$release_id = getIntFromRequest('release_id');

// Allow alternate content-type rendering by hook
$default_content_type = 'text/html';

$script = 'frs_index';
$content_type = util_negociate_alternate_content_types($script, $default_content_type);

if($content_type != $default_content_type) {
	$hook_params = array();
	$hook_params['accept'] = $content_type;
	$hook_params['group_id'] = $group_id;
	$hook_params['release_id'] = $release_id;
	$hook_params['return'] = '';
	$hook_params['content_type'] = '';
	plugin_hook_by_reference('content_negociated_frs_index', $hook_params);
	if($hook_params['content_type'] != ''){
		header('Content-type: '. $hook_params['content_type']);
		echo $hook_params['content'];
	} else {
		header('HTTP/1.1 406 Not Acceptable',true,406);
	}
	exit(0);
}


$fpFactory = new FRSPackageFactory($g);
if (!$fpFactory || !is_object($fpFactory)) {
	exit_error(_('Could Not Get FRSPackageFactory'), 'frs');
} elseif ($fpFactory->isError()) {
	exit_error($fpFactory->getErrorMessage(), 'frs');
}

$FRSPackages = $fpFactory->getFRSs();

frs_header(array('title'=>_('Project Filelist'),'group'=>$group_id));

plugin_hook("blocks", "files index");

if ( count($FRSPackages) < 1) {
	echo "<h1>"._('No File Packages')."</h1>";
	echo $HTML->warning_msg(_('There are no file packages defined for this project.'));
} else {
	echo '<div id="forge-frs" class="underline-link">'."\n";

	echo '<div class="blue-box">'._('Below is a list of all files of the project.').' ';
	if ($release_id) {
		echo _('The release you have chosen is <span class="selected">highlighted</span>.').' ';
	}
	echo _('Before downloading, you may want to read Release Notes and ChangeLog (accessible by clicking on release version).').'
	</div><!-- class="blue-box" -->
    '."\n";

	// check the permissions and see if this user is a release manager.
	// If so, offer the opportunity to create a release

	if (forge_check_perm ('frs', $group_id, 'write')) {
		echo '<p>'.util_make_link('/frs/admin/qrs.php?group_id='.$group_id, _('To create a new release click here.'));
		echo '</p>';
	}


	$proj_stats['packages'] = count($FRSPackages);
	$proj_stats['releases'] = 0;
	$proj_stats['size']     = 0;

	// Iterate and show the packages
	foreach ($FRSPackages as $FRSPackage) {

		$package_id = $FRSPackage->getID();

		$package_name = $FRSPackage->getName();

		if($FRSPackage->isMonitoring()) {
			$title = $package_name . " - " . _('Stop monitoring this package');
			$url = '/frs/monitor.php?filemodule_id='. $package_id .'&group_id='.$FRSPackage->Group->getID().'&stop=1';
			$package_monitor = util_make_link($url, $HTML->getMonitorPic($title));
		} else {
			$title = $package_name . " - " . _('Monitor this package');
			$url = '/frs/monitor.php?filemodule_id='. $package_id .'&group_id='.$FRSPackage->Group->getID().'&start=1';
			$package_monitor = util_make_link($url, $HTML->getMonitorPic($title));
		}

		$package_name_protected = $HTML->toSlug($package_name);
		echo "\n".'<h2 id="title_'. $package_name_protected .'">' . $package_name . ' <span class="frs-monitor-package">' . $package_monitor . '</span></h2>'."\n";

		// get the releases of the package
		$FRSPackageReleases = $FRSPackage->getReleases();
		$num_releases = count($FRSPackageReleases);

		$proj_stats['releases'] += $num_releases;

		if ( !$FRSPackageReleases || $num_releases < 1 ) {
			echo $HTML->warning_msg(_('No releases'));
		} else {
			if (class_exists('ZipArchive')) {
				// display link to latest-release-as-zip
				print '<p><em>'._('Download latest release as ZIP:').' ';
				print util_make_link ('/frs/download.php/latestzip/'.$FRSPackage->getID().'/'.$FRSPackage->getNewestReleaseZipName(),
						$FRSPackage->getNewestReleaseZipName(),
						array('title' => _('This link always points to the newest release as a ZIP file.')));
				print '</em></p>';
			}

			// iterate and show the releases of the package
			foreach ($FRSPackageReleases as $FRSPackageRelease) {
				$package_release_id = $FRSPackageRelease->getID();

				// Switch whether release_id exists and/or release_id is current one
				if ( ! $release_id || $release_id == $package_release_id ) {
					// no release_id OR release_id is current one
					$release_title = util_make_link('/frs/shownotes.php?release_id=' . $package_release_id, $package_name.' '.$FRSPackageRelease->getName().' ('.date(_('Y-m-d H:i'), $FRSPackageRelease->getReleaseDate()).')');
					echo $HTML->boxTop($release_title, $package_name . '_' . $FRSPackageRelease->getName())."\n";
				} elseif ( $release_id!=$package_release_id ) {
					// release_id but not current one
					$t_url_anchor = $HTML->toSlug($package_name)."-".$HTML->toSlug($FRSPackageRelease->getName())."-title-content";
					$t_url = '/frs/?group_id='.$group_id.'&release_id=' . $package_release_id . "#" . $t_url_anchor;
					$release_title = util_make_link( $t_url, $package_name.' '.$FRSPackageRelease->getName());
					echo '<div class="frs_release_name_version">'.$release_title."</div>"."\n";
				}

				// get the files in this release....
				$res_files = $FRSPackageRelease->getFiles();
				$num_files = count($FRSPackageRelease);

				@$proj_stats['files'] += $num_files;

				$cell_data = array();
				$cell_data[] = _('File Name');
				$cell_data[] = _('Date');
				$cell_data[] = _('Size');
				$cell_data[] = _('D/L');
				$cell_data[] = _('Arch');
				$cell_data[] = _('Type');
				$cell_data[] = _('Latest');

				// Switch whether release_id exists and/or release_id == package_release['release_id']
				if ( ! $release_id ) {
					// no release_id
					echo $HTML->listTableTop($cell_data,'',false);
				} elseif ( $release_id==$package_release_id ) {
					// release_id is current one
					echo $HTML->listTableTop($cell_data,'',true);
				} else {
				// release_id but not current one => dont print anything here
				}

				if ( ! $release_id || $release_id == $package_release_id ) {
					// no release_id OR no release_id OR release_id is current one
					if ( !$res_files || $num_files < 1 ) {
						echo '<tr><td colspan="7">&nbsp;&nbsp;<em>'._('No releases').'</em></td></tr>
						';
					} else {
						// now iterate and show the files in this release....
						foreach ($res_files as $res_file) {

						$tmp_col1 = util_make_link('/frs/download.php/file/'.$res_file->getID().'/'.$res_file->getName(), $res_file->getName());
						$tmp_col2 = date(_('Y-m-d H:i'), $res_file->getReleaseTime());
						$tmp_col3 = human_readable_bytes($res_file->getSize());
						$tmp_col4 = ($res_file->getDownloads() ? number_format($res_file->getDownloads(), 0) : '0');
						$tmp_col5 = $res_file->getProcessor();
						$tmp_col6 = $res_file->getFileType();
						$tmp_col7 = util_make_link('/frs/download.php/latestfile/'.$FRSPackage->getID().'/'.$res_file->getName(), _('Latest version'));

						$proj_stats['size'] += $res_file->getSize();
						@$proj_stats['downloads'] += $res_file->getDownloads();

						echo '<tr ' . ">\n";
						echo ' <td>' . $tmp_col1 . '</td>'."\n";
						echo ' <td>' . $tmp_col2 . '</td>'."\n";
						echo ' <td>' . $tmp_col3 . '</td>'."\n";
						echo ' <td>' . $tmp_col4 . '</td>'."\n";
						echo ' <td>' . $tmp_col5 . '</td>'."\n";
						echo ' <td>' . $tmp_col6 . '</td>'."\n";
						echo ' <td>' . $tmp_col7 . '</td>'."\n";
						echo '</tr>'."\n";
						}
					}
					echo $HTML->listTableBottom();
				} else {
					// release_id but not current one
					// nothing to print here
				}

				if ( ! $release_id || $release_id==$package_release_id ) {
					echo $HTML->boxBottom();
				}
			} //for: release(s)
		} //if: release(s) available
	}
echo '</div><!-- id="forge-frs" -->';

}

frs_footer();
