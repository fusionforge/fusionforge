<?php
/**
 * Show Release Notes/ChangeLog Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $group_id; // id of group
global $HTML; // html object

$release_id = getIntFromRequest('release_id');

$frsr = frsrelease_get_object($release_id);
if (!$frsr || !is_object($frsr)) {
	exit_error(_('That Release Was Not Found'), 'frs');
} elseif ($frsr->isError()) {
	exit_error($frsr->getErrorMessage(), 'frs');
}

//  Members of projects can see all packages
//  Non-members can only see public packages
if(!$frsr->getFRSPackage()->isPublic()) {
	if (!session_loggedin() || (!user_ismember($group_id) &&
		!forge_check_global_perm('forge_admin'))) {
		exit_permission_denied();
	}
}

echo html_e('h2', array(), _('File Release Notes and Changelog'));
echo html_e('h3', array(), _('Release Name')._(': ').util_make_link('/frs/?group_id='.$group_id.'&release_id='.$release_id, $frsr->getName()));

if (forge_get_config('use_object_associations')) {
	echo html_ao('div', array('id' => 'tabber'));
	$elementsLi = array();
	$elementsLi[] = array('content' => util_make_link('#tabber-changelog', _('Change Log & Notes'), array('title' => _('View Changelog & Notes.')), true));
	$anf = '';
	if ($frsr->getAssociationCounter()) {
		$anf = ' ('.$frsr->getAssociationCounter().')';
	}
	$elementsLi[] = array('content' => util_make_link('#tabber-association', _('Associations').$anf, array('title' => _('View Associated Objects.')), true));
	echo $HTML->html_list($elementsLi);
	echo html_ao('div', array('id' => 'tabber-changelog', 'class' => 'tabbertab'));
}
// Show preformatted or plain notes/changes
if ($frsr->getPreformatted()) {
	$htmltag = 'pre';
} else {
	$htmltag = 'p';
}

if (strlen($frsr->getNotes())) {
	echo $HTML->boxTop(_('Release Notes'));
	echo html_e($htmltag, array(), $frsr->getNotes());
	echo $HTML->boxBottom();
} else {
	echo $HTML->information(_('No release notes'));
}

if (strlen($frsr->getChanges())) {
	echo $HTML->boxTop(_('Change Log'));
	echo html_e($htmltag, array(), $frsr->getChanges());
	echo $HTML->boxBottom();
} else {
	echo $HTML->information(_('No change log'));
}

if (forge_get_config('use_object_associations')) {
	echo html_ac(html_ap() -1);
	echo html_e('div', array('id' => 'tabber-association', 'class' => 'tabbertab'), $frsr->showAssociations());
	echo html_ac(html_ap() -1);
}
