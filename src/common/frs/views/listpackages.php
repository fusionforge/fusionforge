<?php
/**
 * FusionForge FRS: List packages view
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2013-2014,2016-2017,2020, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $HTML; // html object
global $group_id; // id of group
global $g; // group object
global $fpFactory; // frs package factory package

$FRSPackages = $fpFactory->getFRSs(true);
if (count($FRSPackages) < 1) {
	$localInformation = _('There are no file packages defined for this project.');
}

$child_has_p = false;
if ($g->usesPlugin('projects-hierarchy')) {
	$projectsHierarchy = plugin_get_object('projects-hierarchy');
	$projectIDsArray = $projectsHierarchy->getFamily($group_id, 'child', true, 'validated');
	foreach ($projectIDsArray as $projectid) {
		$childGroupObject = group_get_object($projectid);
		if ($childGroupObject && is_object($childGroupObject) && !$childGroupObject->isError()) {
			if ($childGroupObject->usesFRS() && $projectsHierarchy->getFRSStatus($childGroupObject->getID())) {
				$childfpf = new FRSPackageFactory($childGroupObject);
				$child_frsp = $childfpf->getFRSs(true);
				if (count($child_frsp) > 0) {
					$FRSPackages = array_merge($FRSPackages, $child_frsp);
					$child_has_p = true;
				}
			}
		}
		unset($childGroupObject);
	}
}

if (count($FRSPackages) < 1) {
	echo $HTML->information(_('There are no file packages defined for this project.'));
} else {
	$javascript = <<<'EOS'
	var controllerFRS;
	jQuery(document).ready(function() {
		controllerFRS = new FRSController();
	});
EOS;
	echo html_e('script', array( 'type'=>'text/javascript'), '//<![CDATA['."\n".'jQuery(function(){'.$javascript.'});'."\n".'//]]>');

	echo html_ao('div', array('id' => 'forge-frs', 'class' => 'underline-link'));

	$content = _('Below is a list of all files of the project.').' ';
	if ($release_id) {
		$content = _('The release you have chosen is <span class="selected">highlighted</span>.').' ';
	}
	$content .= _('Before downloading, you may want to read Release Notes and ChangeLog (accessible by clicking on release version).');
	echo html_e('div', array('class' => 'blue-box'), $content);

	// check the permissions and see if this user is a release manager.
	// If so, offer the opportunity to create a release
	if (forge_check_perm('frs_admin', $group_id, 'admin')) {
		echo html_e('p', array(), util_make_link('/frs/?view=qrs&group_id='.$group_id, _('To create a new release click here.')));
	}

	// Iterate and show the packages
	$current_groupid = $group_id;
	foreach ($FRSPackages as $FRSPackage) {

		if ($FRSPackage->Group->getID() != $current_groupid) {
			echo html_e('h2', array(), sprintf(_('Child Project %s Packages'), util_make_link('/frs/?group_id='.$FRSPackage->Group->getID(), $FRSPackage->Group->getPublicName())));
			$current_groupid = $FRSPackage->Group->getID();
		}
		$package_id = $FRSPackage->getID();
		$package_name = $FRSPackage->getName();
		$url = '/frs/?group_id='.$FRSPackage->Group->getID().'&package_id='.$package_id.'&action=monitor';
		if (session_loggedin()) {
			if($FRSPackage->isMonitoring()) {
				$title = html_entity_decode($package_name).' - '._('Stop monitoring this package');
				$url .= '&status=0';
				$image = $HTML->getStopMonitoringPic($title);
			} else {
				$title = html_entity_decode($package_name).' - '._('Start monitoring this package');
				$url .= '&status=1';
				$image = $HTML->getStartMonitoringPic($title);
			}
			$errorMessage = _('Unable to set monitoring');
			$package_monitor = html_e('span', array('class' => 'frs-monitor-package'), util_make_link('#', $image, array('id' => 'pkgid'.$package_id, 'onclick' => 'javascript:controllerFRS.doAction({action:\''.util_make_uri($url).'\', id:\'pkgid'.$package_id.'\'})'), true));
		} else {
			$package_monitor = '';
		}

		// get the releases of the package
		$FRSPackageReleases = $FRSPackage->getReleases(false);
		$num_releases = count($FRSPackageReleases);

		$package_name_protected = $HTML->toSlug($package_name);
		$package_ziplink = '';
		if ($FRSPackageReleases && $num_releases >= 1 && class_exists('ZipArchive') && file_exists($FRSPackage->getReleaseZipPath($FRSPackage->getNewestReleaseID()))) {
			// display link to latest-release-as-zip
			$package_ziplink = html_e('span', array('class' => 'frs-zip-package'), util_make_link('/frs/download.php/latestzip/'.$FRSPackage->getID().'/'.$FRSPackage->getNewestReleaseZipName(),
																$HTML->getZipPic(_('Download the newest release as ZIP.')
																.' '._('This link always points to the newest release as a ZIP file.'))));
		}
		echo html_e('h2', array('id' => 'title_'. $package_name_protected), html_entity_decode($package_name).$package_monitor.$package_ziplink);

		if ( !$FRSPackageReleases || $num_releases < 1 ) {
			echo $HTML->warning_msg(_('No releases'));
		} else {
			// iterate and show the releases of the package
			foreach ($FRSPackageReleases as $FRSPackageRelease) {
				$package_release_id = $FRSPackageRelease->getID();
				$ziplink = '';
				if (class_exists('ZipArchive')) {
					if (file_exists($FRSPackage->getReleaseZipPath($package_release_id))) {
						$ziplink .= html_e('span', array('class' => 'frs-zip-release'), util_make_link('/frs/download.php/zip/'.$FRSPackageRelease->getID().'/'.urlencode($FRSPackage->getReleaseZipName($FRSPackageRelease->getID())),
						  $HTML->getZipPic(_('Download this release as ZIP.').' '._('This link always points to this release as a ZIP file.'))), false);
					}
				}
				// Switch whether release_id exists and/or release_id is current one
				if ( ! $release_id || $release_id == $package_release_id ) {
					// no release_id OR release_id is current one
					$release_title = util_make_link('/frs/?view=shownotes&group_id='.$group_id.'&release_id='.$package_release_id, $package_name.' '.$FRSPackageRelease->getName().' ('.date(_('Y-m-d H:i'), $FRSPackageRelease->getReleaseDate()).')');
					echo $HTML->boxTop($release_title.$ziplink, $package_name.' '.$FRSPackageRelease->getName());
				} elseif ( $release_id != $package_release_id ) {
					// release_id but not current one
					$t_url_anchor = $HTML->toSlug($package_name).'-'.$HTML->toSlug($FRSPackageRelease->getName()).'-title-content';
					$t_url = '/frs/?group_id='.$group_id.'&release_id='.$package_release_id.'#'.$t_url_anchor;
					$release_title = util_make_link($t_url, $package_name.' '.$FRSPackageRelease->getName());
					echo html_e('div', array('class' => 'frs_release_name_version'), $release_title.$ziplink);
				}

				// display linked roadmaps if any
				if ($g->usesTracker()) {
					$linkedRoadmaps = $FRSPackageRelease->getLinkedRoadmaps();
					if (count($linkedRoadmaps)) {
						$urls = '';
						foreach ($linkedRoadmaps as $linkedRoadmapID => $linkedRoadmap) {
							$roadmapObject = new Roadmap($g, $linkedRoadmapID);
							$rnum = 0;
							foreach ($linkedRoadmap as $linkedRoadmapRelease) {
								if ($rnum) {
									$urls .= ' || ';
								}
								$urls .= util_make_link('/tracker/roadmap.php?group_id='.$group_id.'&release='.urlencode($linkedRoadmapRelease), $roadmapObject->getName().' - '.$linkedRoadmapRelease);
								$rnum++;
							}
						}
						echo html_e('span', array(), html_e('b', array(), _('Linked Roadmaps')._(': ')).$urls, false);
					}
				}

				// get the files in this release....
				$res_files = $FRSPackageRelease->getFiles();

				// Switch whether release_id exists and/or release_id == package_release['release_id']
				if (!$release_id || $release_id == $package_release_id) {
					// no release_id OR no release_id OR release_id is current one
					if ( !$res_files || count($res_files) < 1 ) {
						echo $HTML->information(_('No files'));
					} else {
						$cell_data = array();
						$cell_data[] = _('File Name');
						$cell_data[] = _('Date');
						$cell_data[] = _('Size');
						$cell_data[] = _('D/L');
						$cell_data[] = _('Arch');
						$cell_data[] = _('Type');
						$cell_data[] = _('Latest');
						echo $HTML->listTableTop($cell_data);
						// now iterate and show the files in this release....
						foreach ($res_files as $res_file) {
							$cells = array();
							$cells[][] = util_make_link('/frs/download.php/file/'.$res_file->getID().'/'.urlencode($res_file->getName()), $res_file->getName());
							$cells[][] = date(_('Y-m-d H:i'), $res_file->getReleaseTime());
							$cells[][] = human_readable_bytes($res_file->getSize());
							$cells[][] = ($res_file->getDownloads() ? number_format($res_file->getDownloads(), 0) : '0');
							$cells[][] = $res_file->getProcessor();
							$cells[][] = $res_file->getFileType();
							$cells[][] = util_make_link('/frs/download.php/latestfile/'.$FRSPackage->getID().'/'.urlencode($res_file->getName()), _('Latest version'));
							echo $HTML->multiTableRow(array(), $cells);
						}
						echo $HTML->listTableBottom();
					}
					echo $HTML->boxBottom();
				}
			} //for: release(s)
		} //if: release(s) available
	}
	echo html_ac(html_ap() -1);
}
