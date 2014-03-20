<?php
/**
 * User's bookmark editing Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2014, Stéphane-Eymeric Bredthauer
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
require_once $gfwww.'include/bookmarks.php';

$bookmark_id = getIntFromRequest('bookmark_id');
if (!$bookmark_id) {
	exit_missing_param('',array(_('Bookmark ID')),'my');
}

if (getStringFromRequest('submit')) {
	$bookmark_url = getStringFromRequest('bookmark_url');
	$bookmark_title = getStringFromRequest('bookmark_title');

	if ($bookmark_url && $bookmark_title && bookmark_edit($bookmark_id, $bookmark_url, $bookmark_title)) {
		$feedback = _('Bookmark Updated');
	} else {
		$error_msg = _('Failed to update bookmark.');
	}
}

site_user_header(array('title'=>_('Edit Bookmark')));

$result = db_query_params ('SELECT * from user_bookmarks where
bookmark_id=$1 and user_id=$2',
			array($bookmark_id,
				user_getid()));
if ($result) {
	$bookmark_url = db_result($result,0,'bookmark_url');
	$bookmark_title = db_result($result,0,'bookmark_title');
}

echo html_ao('form', array('action' => util_make_uri('/my/bookmark_edit.php'), 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'bookmark_id', 'value' => $bookmark_id));
echo html_ao('p');
echo html_e('label', array('for' => 'bookmark_url'), _('Bookmark URL')._(':').html_e('br'));
echo html_e('input', array('id' => 'bookmark_url', 'required' => 'required', 'type' => 'url', 'name' => 'bookmark_url', 'value' => $bookmark_url));
echo html_ac(html_ap()-1);
echo html_ao('p');
echo html_e('label', array('for' => 'bookmark_title'), _('Bookmark Title')._(':').html_e('br'));
echo html_e('input', array('id' => 'bookmark_title', 'required' => 'required', 'type' => 'text', 'name' => 'bookmark_title', 'value' => $bookmark_title));
echo html_ac(html_ap()-1);
echo html_ao('p');
echo html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Submit')));
echo html_ac(html_ap()-2);
echo html_e('p', array(), util_make_link('/my/', _('Return')));

site_user_footer();
