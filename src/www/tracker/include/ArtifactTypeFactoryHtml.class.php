<?php
/**
 * FusionForge Generic Tracker facility
 *
 * Copyright 2011 (C) Alain Peyrat, Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
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

require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactExtraField.class.php';
require_once $gfcommon.'tracker/ArtifactExtraFieldElement.class.php';
require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';
require_once $gfcommon.'include/utils_crossref.php';

class ArtifactTypeFactoryHtml extends ArtifactTypeFactory {
	function header($params=array()) {
		global $HTML;

		if (!forge_get_config('use_tracker')) {
			exit_disabled();
		}

		$group_id= $this->Group->getID();

		$params['group']=$group_id;
		if (!isset($params['title'])) {
			$params['title']=sprintf(_('Trackers for %1$s'), $this->Group->getPublicName());
		}
		$params['toptab']='tracker';

		$labels = array(_('View Trackers'));
		$links  = array('/tracker/?group_id='.$group_id);
		$attr   = array(array('title' => _('Get the list of available trackers'), 'class' => 'tabtitle-nw'));
		if (session_loggedin()) {
			$labels[] = _('Reporting');
			$links[]  = '/tracker/reporting/?group_id='.$group_id;
			$attr[]   = array('title' => _('Various graph of statistics.'), 'class' => 'tabtitle');
			$perm = $this->Group->getPermission(session_get_user());
			if ($perm && is_object($perm) && !$perm->isError() && $perm->isPMAdmin()) {
				$labels[] = _('Administration');
				$links[]  = '/tracker/admin/?group_id='.$group_id;
				$attr[]   = array('title' => _('Global administration for trackers. Create, clone, workflow, fields ...'), 'class' => 'tabtitle');
			}
		}

		$params['submenu'] = $HTML->subMenu($labels, $links, $attr);

		site_project_header($params);
	}

	function footer($params=array()) {
		site_project_footer($params);
	}

//     function adminHeader($params=array()) {
//             return $this->header($params);
//     }
//
//     function adminFooter($params=array()) {
//             return $this->footer($params);
//     }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>