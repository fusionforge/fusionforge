<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2010-2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2016,2018, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'docman/DocumentManager.class.php';
require_once $gfcommon.'docman/Document.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';
require_once $gfcommon.'docman/DocumentReview.class.php';
require_once $gfcommon.'docman/DocumentReviewFactory.class.php';
require_once $gfcommon.'docman/DocumentReviewComment.class.php';
require_once $gfcommon.'docman/DocumentReviewCommentFactory.class.php';
require_once $gfcommon.'docman/DocumentVersion.class.php';
require_once $gfcommon.'docman/DocumentVersionFactory.class.php';
require_once $gfcommon.'docman/include/utils.php';
require_once $gfcommon.'docman/include/constants.php';
require_once $gfcommon.'include/TextSanitizer.class.php'; // to make the HTML input by the user safe to store
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/ReportPerGroupDocmanDownloads.class.php';
require_once $gfcommon.'include/html.php';
require_once $gfwww.'search/include/renderers/DocsHtmlSearchRenderer.class.php';

/* are we using docman ? */
if (!forge_get_config('use_docman'))
	exit_disabled('home');

/* get informations from request or $_POST */
$group_id = getIntFromRequest('group_id');

/* validate group */
if (!$group_id)
	exit_no_group();

$g = group_get_object($group_id);
if (!$g || !is_object($g))
	exit_no_group();

session_require_perm('docman', $group_id, 'read');

/* is this group using docman ? */
if (!$g->usesDocman())
	exit_disabled();

if ($g->isError())
	exit_error($g->getErrorMessage(), 'docman');

$childgroup_id = getIntFromRequest('childgroup_id', 0);
$dirid = getIntFromRequest('dirid', 0);
if ($dirid) {
	if ($childgroup_id) {
		$chkdg = documentgroup_get_object($dirid, group_get_object($childgroup_id)->getID());
	} else {
		$chkdg = documentgroup_get_object($dirid, $g->getID());
	}
	if (!is_object($chkdg)) {
		session_redirect('/docman/?group_id='.$group_id);
	}
}

/* everything sounds ok, now let's do the job */
$action = getStringFromRequest('action');
if (file_exists(forge_get_config('source_path').'/common/docman/actions/'.$action.'.php')) {
	include(forge_get_config('source_path').'/common/docman/actions/'.$action.'.php');
}

$start = getIntFromRequest('start', 0);
if ($start < 0)
	$start = 0;

html_use_storage();
html_use_simplemenu();
html_use_jqueryui();
html_use_jquerysplitter();
html_use_jquerygentleselect();
use_javascript('/docman/scripts/DocManController.js');
html_use_tablesorter();

site_project_header(array('title'=> _('Documents for ').$g->getPublicName(), 'group'=>$group_id, 'toptab'=>'docman'));

echo html_ao('div', array('id' => 'menu'));
include ($gfcommon.'docman/views/menu.php');
echo html_ac(html_ap() - 1);

echo html_ao('div', array('id' => 'views'));
include ($gfcommon.'docman/views/views.php');
echo html_ac(html_ap() - 1);

site_project_footer();
