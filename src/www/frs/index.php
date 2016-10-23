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
require_once $gfcommon.'frs/FRSFile.class.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSPackageFactory.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';
require_once $gfcommon.'frs/FRSReleaseFactory.class.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/ReportDownloads.class.php';
require_once $gfcommon.'docman/DocumentManager.class.php';

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

session_require_perm('frs_admin', $group_id, 'read');

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

/* everything sounds ok, now let do the job */
$action = getStringFromRequest('action');
if (file_exists(forge_get_config('source_path').'/common/frs/actions/'.$action.'.php')) {
	include(forge_get_config('source_path').'/common/frs/actions/'.$action.'.php');
}

html_use_jqueryui();
html_use_coolfieldset();
html_use_tablesorter();
use_javascript('/frs/scripts/FRSController.js');

site_project_header(array('title' => _('Project Filelist for ').$g->getPublicName(), 'group' => $group_id, 'toptab' => 'frs'));

echo html_ao('div', array('id' => 'menu'));
include ($gfcommon.'frs/views/menu.php');
echo html_ac(html_ap() - 1);

echo html_ao('div', array('id' => 'views'));
include ($gfcommon.'frs/views/views.php');
echo html_ac(html_ap() - 1);

site_project_footer();
