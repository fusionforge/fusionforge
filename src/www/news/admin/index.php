<?php
/**
 * News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/note.php';
require_once $gfwww.'news/admin/news_admin_utils.php';
require_once $gfwww.'news/news_utils.php';
//common forum tools which are used during the creation/editing of news items
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'include/TextSanitizer.class.php'; // to make the HTML input by the user safe to store

$group_id = getIntFromRequest('group_id');
$post_changes = getStringFromRequest('post_changes');
$approve = getStringFromRequest('approve');
$status = getIntFromRequest('status');
$summary = getStringFromRequest('summary');
$details = getStringFromRequest('details');
$id = getIntFromRequest('id');
$for_group = getIntFromRequest('for_group');

$feedback = htmlspecialchars(getStringFromRequest('feedback'));

if ($group_id && $group_id != forge_get_config('news_group')) {
	session_require_perm ('project_admin', $group_id) ;

	$status = getIntFromRequest('status');
	$summary = getStringFromRequest('summary');
	$details = getStringFromRequest('details');

	/*

		Per-project admin pages.

		Shows their own news items so they can edit/update.

		If their news is on the homepage, and they edit, it is removed from
			sf.net homepage.

	*/
	if ($post_changes) {
		$result = db_query_params("SELECT forum_id FROM news_bytes WHERE id=$1 AND group_id=$2", array($id, $group_id));
		if (db_numrows($result) < 1) {
			exit_error(_('Newsbyte not found'),'news');
		}
		
		$forum_id = db_result($result,0,'forum_id');
		$old_group_id = db_result($result,0,'group_id');
	
		if ($approve) {
			/*
				Update the db so the item shows on the home page
			*/
			if ($status != 0 && $status != 4) {
				//may have tampered with HTML to get their item on the home page
				$status=0;
			}

			if (!$summary) {
				$summary='(none)';
			}
			if (!$details) {
				$details='(none)';
			}
			
			if (getStringFromRequest('_details_content_type') == 'html') {
				$details = TextSanitizer::purify($details);
			} else {
				$details = htmlspecialchars($details);
			}

			$result = db_query_params("UPDATE news_bytes SET is_approved=$1, summary=$2, 
details=$3 WHERE id=$4 AND group_id=$5", array($status, htmlspecialchars($summary), $details, $id, $group_id));
			
			if (!$result || db_affected_rows($result) < 1) {
				$error_msg .= _('Error On Update:');
				$error_msg .= db_error();
			} else {
				$feedback .= _('Newsbyte Updated.');
			}
			/*
				Show the list_queue
			*/
			$approve='';
			$list_queue='y';
		}
	}

	news_header(array('title'=>_('News admin')));

	if ($approve) {
		/*
			Show the submit form
		*/

		$result=db_query_params("SELECT * FROM news_bytes WHERE id=$1 AND group_id=$2", array($id, $group_id));
		if (db_numrows($result) < 1) {
			exit_error(_('Newsbyte not found'),'news');
		}
		
		$group = group_get_object($group_id);
		
		echo notepad_func();
		echo '
		<p />
		<form id="newsadminform" action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="group_id" value="'.db_result($result,0,'group_id').'" />
		<input type="hidden" name="id" value="'.db_result($result,0,'id').'" />';

		$user =& user_get_object(db_result($result,0,'submitted_by'));

		echo '
		<strong>'._('Submitted by').':</strong> '.$user->getRealName().'<br />
		<input type="hidden" name="approve" value="y" />
		<input type="hidden" name="post_changes" value="y" />

		<strong>'._('Status').'</strong><br />
		<input type="radio" name="status" value="0" checked="checked" /> '._('Displayed').'<br />
		<input type="radio" name="status" value="4" /> '._('Delete').'<br />

		<strong>'._('Subject').'</strong><br />
		<input type="text" name="summary" value="'.db_result($result,0,'summary').'" size="60" maxlength="60" /><br />
		<strong>'._('Details').'</strong>'.notepad_button('document.forms.newsadminform.details').'<br />';
		
		$GLOBALS['editor_was_set_up']=false;
		$params = array () ;
		$params['name'] = 'details';
		$params['width'] = "600";
		$params['height'] = "300";
		$params['group'] = $group_id;
		$params['body'] = db_result($result,0,'details');
		plugin_hook("text_editor",$params);
		if (!$GLOBALS['editor_was_set_up']) {
			//if we don't have any plugin for text editor, display a simple textarea edit box
			echo '<textarea name="details" rows="5" cols="50">'.db_result($result,0,'details').'</textarea><br />';
		}
		unset($GLOBALS['editor_was_set_up']);
		
		echo '<p>
		<strong>'.sprintf(_('If this item is on the %1$s home page and you edit it, it will be removed from the home page.'), forge_get_config ('forge_name')).'</strong><br /></p>
		<input type="submit" name="submit" value="'._('Submit').'" />
		</form>';

	} else {
		/*
			Show list of waiting news items
		*/

		$result=db_query_params("SELECT * FROM news_bytes WHERE is_approved <> 4 AND group_id=$1", array($group_id));
		$rows=db_numrows($result);
		$group = group_get_object($group_id);
		
		if ($rows < 1) {
			echo '
				<p class="warning_msg">'._('No Queued Items Found').'</p>';
		} else {
			echo '
				<ul>';
			for ($i=0; $i<$rows; $i++) {
				echo '
				<li>'.util_make_link ('/news/admin/?approve=1&amp;id='.db_result($result,$i,'id').'&amp;group_id='.db_result($result,$i,'group_id'),db_result($result,$i,'summary')).'</li>';
			}
			echo '</ul>';
		}

	}
	news_footer(array());

} else { // No group, or newsadmin group
	session_redirect('/admin/pending-news.php');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
