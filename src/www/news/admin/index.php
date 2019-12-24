<?php
/**
 * News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2015,2019, Franck Villaume
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/note.php';
require_once $gfwww.'news/admin/news_admin_utils.php';
require_once $gfwww.'news/news_utils.php';
//common forum tools which are used during the creation/editing of news items
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'include/TextSanitizer.class.php'; // to make the HTML input by the user safe to store

global $HTML;

$group_id = getIntFromRequest('group_id');
$post_changes = getStringFromRequest('post_changes');
$approve = getStringFromRequest('approve');
$status = getIntFromRequest('status');
$summary = getStringFromRequest('summary');
$details = getHtmlTextFromRequest('details');
$id = getIntFromRequest('id');
$for_group = getIntFromRequest('for_group');

if ($group_id && $group_id != GROUP_IS_NEWS) {
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

			$result = db_query_params("UPDATE news_bytes SET is_approved=$1, summary=$2,
						details=$3 WHERE id=$4 AND group_id=$5",
						array($status, htmlspecialchars($summary), $details, $id, $group_id));

			if (!$result || db_affected_rows($result) < 1) {
				$error_msg .= _('Error On Update')._(': ').db_error();
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

	news_header(array('title'=>_('News Admin')));

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
		echo $HTML->openForm(array('id' => 'newsadminform', 'action' => '/news/admin/', 'method' => 'post'));
		echo '<input type="hidden" name="group_id" value="'.db_result($result,0,'group_id').'" />
		<input type="hidden" name="id" value="'.db_result($result,0,'id').'" />';

		$user = user_get_object(db_result($result,0,'submitted_by'));

		echo '
		<p>
		<strong>'._('Submitted by')._(': ').'</strong> '.$user->getRealName().'
		<input type="hidden" name="approve" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		</p>

		<p>
		<strong>'._('Status')._(': ').'</strong><br />
		<input type="radio" id="status_displayed" name="status" value="0" checked="checked" /> <label for="status_displayed">'._('Displayed').'</label><br />
		<input type="radio" id="status_delete" name="status" value="4" /> <label for="status_delete">'._('Delete').'</label>
		</p>

		<p>
		<label for="summary"><strong>'._('Subject')._(': ').'</strong></label>
		<input type="text" id="summary" name="summary" value="'.db_result($result,0,'summary').'" size="80" />
		</p>
		<p>
		<label for="details"><strong>'._('Details')._(': ').'</strong></label>'.notepad_button('document.forms.newsadminform.details');

		$params = array();
		$params['name'] = 'details';
		$params['width'] = "600";
		$params['height'] = "300";
		$params['group'] = $group_id;
		$params['body'] = db_result($result,0,'details');
		$params['content'] = '<textarea id="details" name="details" rows="5" cols="50">'.$params['body'].'</textarea>';
		plugin_hook_by_reference("text_editor",$params);

		echo $params['content'];
		echo '</p>
		<p>
		<strong>'.sprintf(_('If this item is on the %s home page and you edit it, it will be removed from the home page.'), forge_get_config('forge_name')).'</strong></p>';
		echo '<p>';
		echo '<label for="ask_frontpage">'._('Check this box if you request frontpage publication. Only public project can be published to frontpage')._(':').'</label>';
		echo '<input type="checkbox" id="ask_frontpage" name="ask_frontpage" value="1" checked="checked" />';
		echo '</p>';
		echo '<p>
		<input type="submit" name="submit" value="'._('Submit').'" />
		</p>';
		echo $HTML->closeForm();

	} else {
		/*
			Show list of waiting news items
		*/

		$result=db_query_params("SELECT * FROM news_bytes WHERE is_approved <> 4 AND group_id=$1", array($group_id));
		$rows=db_numrows($result);
		$group = group_get_object($group_id);

		if ($rows < 1) {
			echo $HTML->information(_('No Queued Items Found'));
		} else {
			echo '
				<ul>';
			for ($i=0; $i<$rows; $i++) {
				echo '
				<li>'.util_make_link('/news/admin/?approve=1&id='.db_result($result,$i,'id').'&group_id='.db_result($result,$i,'group_id'),db_result($result,$i,'summary')).'</li>';
			}
			echo '</ul>';
		}

	}
	$HTML->footer();

} else { // No group, or newsadmin group
	session_redirect('/admin/pending-news.php');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
