<?php
/**
 * Commit user's email change
 *
 * This page should be accessed with confirmation URL sent to user in email
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010,2014, Franck Villaume - TrivialDev
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
require_once $gfcommon.'include/account.php';

$confirm_hash = getStringFromRequest('confirm_hash');

if (!$confirm_hash) {
	$confirm_hash = getStringFromRequest('ch');
}
if (!$confirm_hash) {
	exit_missing_param('',array(_('Confirm Hash')),'my');
}

$confirm_hash = html_clean_hash_string($confirm_hash);

$res_user = db_query_params ('SELECT * FROM users WHERE confirm_hash=$1',
			array($confirm_hash)) ;

if (db_numrows($res_user) > 1) {
	exit_error(_('This confirm hash exists more than once.'),'my');
}
if (db_numrows($res_user) < 1) {
	exit_error(_('Invalid confirmation hash.'),'my');
}
$u = user_get_object(db_result($res_user, 0, 'user_id'), $res_user);
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'),'home');
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
} elseif (($u->getStatus() == 'S') || ($u->getStatus() == 'D')) {
	exit_error(_('Account is suspended or deleted'),'my');
}

if (!$u->setEmail($u->getNewEmail())) {
	exit_error($u->getErrorMessage(),'my');
}

site_user_header(array('title'=>_('Email Change Complete')));
echo html_e('p', array(), sprintf(_('Welcome, %1$s. Your email change is complete. Your new email address on file is <strong>%2$s</strong>. Mail sent to &lt;%3$s&gt; will now be forwarded to this account.'), $u->getUnixName(), $u->getEmail(), $u->getUnixName().'@'.forge_get_config('users_host')));
echo html_e('p', array(), util_make_link('/', _('Return')));
site_user_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
