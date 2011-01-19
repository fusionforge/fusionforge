<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'docman/Document.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';
require_once $gfcommon.'docman/include/DocumentGroupHTML.class.php';
require_once $gfcommon.'docman/include/utils.php';
require_once $gfcommon.'include/TextSanitizer.class.php'; // to make the HTML input by the user safe to store

/* are we using docman ? */
if (!forge_get_config('use_docman'))
	exit_disabled('home');

/* get informations from request or $_POST */
$group_id = getIntFromRequest('group_id');
$feedback = htmlspecialchars(getStringFromRequest('feedback'));
$error_msg = htmlspecialchars(getStringFromRequest('error_msg'));
$warning_msg = htmlspecialchars(getStringFromRequest('warning_msg'));

/* validate group */
if (!$group_id)
	exit_no_group();

$g = group_get_object($group_id);
if (!$g || !is_object($g))
	exit_no_group();

/* is this group using docman ? */
if (!$g->usesDocman())
	exit_disabled();

if ($g->isError())
	exit_error($g->getErrorMessage(),'docman');

$dirid = getIntFromRequest('dirid');
if (empty($dirid))
	$dirid = 0;

/* everything sounds ok, now let do the job */
$action = getStringFromRequest('action');
switch ($action) {
	case "addfile":
	case "addsubdocgroup":
	case "deldir":
	case "editdocgroup":
	case "editfile":
	case "emptytrash":
	case "enforcereserve":
	case "forcereindexenginesearch":
	case "injectzip":
	case "lockfile":
	case "monitorfile":
	case "releasefile":
	case "reservefile":
	case "trashdir":
	case "trashfile":
	case "updatecreateonline":
	case "updateenginesearch":
	case "updatewebdavinterface": {
		include ($gfcommon."docman/actions/$action.php");
		break;
	}
}

$use_tooltips = 1;

if (session_loggedin()) {
	$u =& user_get_object(user_getid());
	if (!$u || !is_object($u)) {
		exit_error(_('Could Not Get User'));
	} elseif ($u->isError()) {
		exit_error($u->getErrorMessage(),'my');
	}
	if (!$u->usesTooltips()) {
		$use_tooltips = 0;
	}
}

$df = new DocumentFactory($g);
if ($df->isError())
	exit_error($df->getErrorMessage(),'docman');

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError())
	exit_error($dgf->getErrorMessage(),'docman');

$nested_groups = $dgf->getNested();

$dgh = new DocumentGroupHTML($g);
if ($dgh->isError())
	exit_error($dgh->getErrorMessage(),'docman');

$df->setDocGroupID($dirid);
$d_arr =& $df->getDocuments();

html_use_tooltips();
use_javascript('scripts/DocManController.js');
use_javascript('/js/sortable.js');

$title = _('Document Manager: Display Document');

site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'docman'));

echo '<div width:100%;">';
include ($gfcommon.'docman/views/menu.php');
echo '</div>';
echo '<div style="float:left; width:17%;">';
include ($gfcommon.'docman/views/tree.php');
echo '</div>';

echo '<div style="float:right; width:82%;">';
include ($gfcommon.'docman/views/views.php');
echo '</div>';

echo '<div style="clear:both; margin-bottom:5px;" />';
site_project_footer(array());

?>
