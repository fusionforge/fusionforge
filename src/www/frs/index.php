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
require_once $gfcommon.'frs/FRSPackage.class.php';

global $HTML;

$group_id = getIntFromRequest('group_id');
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
	}
	else {
		header('HTTP/1.1 406 Not Acceptable',true,406);
	}
	exit(0);
}

$cur_group = group_get_object($group_id);

if (!$cur_group) {
	exit_no_group();
}

//
//	Members of projects can see all packages
//	Non-members can only see public packages
//
if (session_loggedin()) {
	if (user_ismember($group_id) || forge_check_global_perm('forge_admin')) {
		$pub_sql='';
	} else {
		$pub_sql=' AND is_public=1 ';
	}
} else {
	$pub_sql=' AND is_public=1 ';
}

$sql = "SELECT *
	FROM frs_package
	WHERE group_id=$1
	AND status_id='1'
	$pub_sql
	ORDER BY name";
$res_package = db_query_params( $sql, array($group_id));
$num_packages = db_numrows( $res_package );

frs_header(array('title'=>_('Project Filelist'),'group'=>$group_id));

plugin_hook("blocks", "files index");

if ( $num_packages < 1) {
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

	// get unix group name for path
	$group_unix_name=group_getunixname($group_id);

	$proj_stats['packages'] = $num_packages;
	$proj_stats['releases'] = 0;
	$proj_stats['size']     = 0;

	// Iterate and show the packages
	for ( $p = 0; $p < $num_packages; $p++ ) {

		$package_id = db_result($res_package, $p, 'package_id');

		$frsPackage = new FRSPackage($cur_group, $package_id);

		$package_name = db_result($res_package, $p, 'name');

		if($frsPackage->isMonitoring()) {
			$title = $package_name . " - " . _('Stop monitoring this package');
			$url = '/frs/monitor.php?filemodule_id='. $package_id .'&amp;group_id='.db_result($res_package,$p,'group_id').'&amp;stop=1';
			$package_monitor = util_make_link ( $url, $HTML->getMonitorPic($title));
		} else {
			$title = $package_name . " - " . _('Monitor this package');
			$url = '/frs/monitor.php?filemodule_id='. $package_id .'&amp;group_id='.db_result($res_package,$p,'group_id').'&amp;start=1';
			$package_monitor = util_make_link ( $url, $HTML->getMonitorPic($title));
		}

		$package_name_protected = $HTML->toSlug($package_name);
		echo "\n".'<h2 id="title_'. $package_name_protected .'">' . $package_name . ' <span class="frs-monitor-package">' . $package_monitor . '</span></h2>'."\n";

		// get the releases of the package
		$res_release = db_query_params ('SELECT * FROM frs_release
		WHERE package_id=$1
		AND status_id=1 ORDER BY release_date DESC, name ASC',
			array ($package_id));
		$num_releases = db_numrows( $res_release );

		$proj_stats['releases'] += $num_releases;

		if ( !$res_release || $num_releases < 1 ) {
			echo $HTML->warning_msg(_('No releases'));
		} else {
			if (class_exists('ZipArchive')) {
				// display link to latest-release-as-zip
				print '<p><em>'._('Download latest release as ZIP:').' ';
				print util_make_link ('/frs/download.php/latestzip/'.$frsPackage->getID().'/'.$frsPackage->getNewestReleaseZipName(),
						$frsPackage->getNewestReleaseZipName(),
						array('title' => _('This link always points to the newest release as a ZIP file.')));
				print '</em></p>';
			}

			// iterate and show the releases of the package
			for ( $r = 0; $r < $num_releases; $r++ ) {
                $package_release = db_fetch_array( $res_release );

                $package_release_id = $package_release['release_id'];

                // Switch whether release_id exists and/or release_id is current one
                if ( ! $release_id || $release_id==$package_release_id ) {
                    // no release_id OR release_id is current one
                    $release_title = util_make_link('/frs/shownotes.php?release_id=' . $package_release_id, $package_name.' '.$package_release['name'].' ('.date(_('Y-m-d H:i'),$package_release['release_date']).')');
                    echo $HTML->boxTop($release_title, $package_name . '_' . $package_release['name'])."\n";
                } elseif ( $release_id!=$package_release_id ) {
                    // release_id but not current one
                    $t_url_anchor = $HTML->toSlug($package_name)."-".$HTML->toSlug($package_release['name'])."-title-content";
                    $t_url = '/frs/?group_id='.$group_id.'&release_id=' . $package_release_id . "#" . $t_url_anchor;
                    $release_title = util_make_link ( $t_url, $package_name.' '.$package_release['name']);
                    echo '<div class="frs_release_name_version">'.$release_title."</div>"."\n";
                }

				// get the files in this release....
				$res_file = db_query_params("SELECT frs_file.filename AS filename,
				frs_file.file_size AS file_size,
				frs_file.file_id AS file_id,
				frs_file.release_time AS release_time,
				frs_filetype.name AS type,
				frs_processor.name AS processor,
				frs_dlstats_filetotal_agg.downloads AS downloads
				FROM frs_filetype,frs_processor,
				frs_file LEFT JOIN frs_dlstats_filetotal_agg ON frs_dlstats_filetotal_agg.file_id=frs_file.file_id
				WHERE release_id=$1
				AND frs_filetype.type_id=frs_file.type_id
				AND frs_processor.processor_id=frs_file.processor_id
				ORDER BY filename", array($package_release_id));
				$num_files = db_numrows( $res_file );

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

                if ( ! $release_id || $release_id==$package_release_id ) {
                    // no release_id OR no release_id OR release_id is current one
                    if ( !$res_file || $num_files < 1 ) {
                        echo '<tr><td colspan="7">&nbsp;&nbsp;<em>'._('No releases').'</em></td></tr>
                        ';
                    } else {
                        // now iterate and show the files in this release....
                        for ( $f = 0; $f < $num_files; $f++ ) {
                            $file_release = db_fetch_array( $res_file );

                            $tmp_col1 = util_make_link('/frs/download.php/file/'.$file_release['file_id'].'/'.$file_release['filename'], $file_release['filename']);
                            $tmp_col2 = date(_('Y-m-d H:i'), $file_release['release_time'] );
                            $tmp_col3 = human_readable_bytes($file_release['file_size']);
                            $tmp_col4 = ($file_release['downloads'] ? number_format($file_release['downloads'], 0) : '0');
                            $tmp_col5 = $file_release['processor'];
                            $tmp_col6 = $file_release['type'];
                            $tmp_col7 = util_make_link('/frs/download.php/latestfile/'.$frsPackage->getID().'/'.$file_release['filename'], _('Latest version'));

                            $proj_stats['size'] += $file_release['file_size'];
                            @$proj_stats['downloads'] += $file_release['downloads'];

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
