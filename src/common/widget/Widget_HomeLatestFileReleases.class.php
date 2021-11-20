<?php
/**
 * Copyright 2017, Franck Villaume - TrivialDev
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';

class Widget_HomeLatestFileReleases extends Widget {
	function __construct() {
		parent::__construct('homelatestfilereleases');
		if (forge_get_config('use_frs')) {
			$this->content['title'] = _('Latest 5 File Releases');
		}
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getCategory() {
		return _('File Release System');
	}

	function isAvailable() {
		return isset($this->content['title']);
	}

	function getContent() {
		global $HTML;
		$content = '';
		$this->cached_results = array();
		$this->fetchData();
		while ((count($this->cached_results) < 5) && ($row = db_fetch_array($this->data_res))) {
			if (forge_check_perm('frs', $row['package_id'], 'read')) {
				$this->cached_results[] = $row;
			}
		}
		db_free_result($this->data_res);
		if (count($this->cached_results)) {
			$content .= $HTML->listTableTop();
			$seen = array();
			foreach ($this->cached_results as $result) {
				if (!isset($seen[$result['group_id']])) {
					$cells = array();
					$cells[] = array(util_make_link_g($result['unix_group_name'], $result['group_id'], html_e('strong', array(), $result['group_name'])), 'colspan' => 4);
					$content .= $HTML->multiTableRow(array('class' => 'top'), $cells);
					$seen[$result['group_id']] = 1;
				}
				$cells = array();
				$cells[][] = _('Module')._(': ').$result['module_name'];
				$cells[][] = _('Version')._(': ').$result['release_version'];
				$cells[][] = _('Released by')._(': ').util_make_link_u($result['user_name'], $result['user_name']);
				$cells[][] = date(_('Y-m-d H:i'), $result['release_date']);
				$content .= $HTML->multiTableRow(array(), $cells);
				$cells = array();
				$cells[] = array($result['short_description'], 'colspan' => 4);
				$content .= $HTML->multiTableRow(array(), $cells);
				$cells = array();
				$cells[] = array(util_make_link('/frs/?group_id='.$result['group_id'].'&release_id='.$result['release_id'],_('Download')).
					' ('._('Project Total:') .$result['downloads'].') | '.
					util_make_link('/frs/?view=shownotes&group_id='.$result['group_id'].'&release_id='.$result['release_id'],_('Notes and Changes')), 'colspan' => 4);
				$content .= $HTML->multiTableRow(array(), $cells);
			}
			$content .= $HTML->listTableBottom();
		} else {
			$content .= $HTML->warning_msg(_('No file releases found.'));
		}
		$content .= util_make_link('/new/', _('Browse all file releases'));
		return $content;
	}

	function getDescription() {
		return _('Display last 5 published file releases. Permission settings apply to filter information.');
	}

	function fetchData() {
		$this->data_res = db_query_params('SELECT groups.group_name,
						groups.group_id,
						groups.unix_group_name,
						groups.short_description,
						users.user_name,
						users.user_id,
						frs_release.release_id,
						frs_package.package_id,
						frs_release.name AS release_version,
						frs_release.release_date,
						frs_release.released_by,
						frs_package.name AS module_name,
						frs_dlstats_grouptotal_vw.downloads
						FROM groups,users,frs_package,frs_release,frs_dlstats_grouptotal_vw
						WHERE ( frs_release.package_id = frs_package.package_id
						AND frs_package.group_id = groups.group_id
						AND frs_release.released_by = users.user_id
						AND frs_package.group_id = frs_dlstats_grouptotal_vw.group_id
						AND frs_release.status_id = 1 )
						ORDER BY frs_release.release_date DESC',
						array());
		return true;
	}
}
