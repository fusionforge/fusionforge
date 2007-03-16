<?php
/**
 * GForge News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../../env.inc.php');
require_once('pre.php');
require_once('note.php');
require_once('news_admin_utils.php');
require_once('www/news/news_utils.php');
//common forum tools which are used during the creation/editing of news items
require_once('common/forum/Forum.class');
require_once('common/include/TextSanitizer.class'); // to make the HTML input by the user safe to store

$group_id = getIntFromRequest('group_id');
$post_changes = getStringFromRequest('post_changes');
$approve = getStringFromRequest('approve');
$status = getIntFromRequest('status');
$summary = getStringFromRequest('summary');
$details = getStringFromRequest('details');
$id = getIntFromRequest('id');

if ($group_id && $group_id != $sys_news_group && user_ismember($group_id,'A')) {
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
		if ($approve) {
			/*
				Update the db so the item shows on the home page
			*/
			if ($status != 0 && $status != 4) {
				//may have tampered with HTML to get their item on the home page
				$status=0;
			}

			//foundry stuff - remove this news from the foundry so it has to be re-approved by the admin
			db_query("DELETE FROM foundry_news WHERE news_id='$id'");

			if (!$summary) {
				$summary='(none)';
			}
			if (!$details) {
				$details='(none)';
			}
			
			$sanitizer = new TextSanitizer();
			$details = $sanitizer->SanitizeHtml($details);
			$sql="UPDATE news_bytes SET is_approved='$status', summary='".htmlspecialchars($summary)."', ".
				"details='".$details."' WHERE id='$id' AND group_id='$group_id'";
			$result=db_query($sql);

			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= _('Error On Update:');
			} else {
				$feedback .= _('NewsByte Updated.');
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

		$sql="SELECT * FROM news_bytes WHERE id='$id' AND group_id='$group_id'";
		$result=db_query($sql);
		if (db_numrows($result) < 1) {
			exit_error(_('Error'), _('NewsByte not found'));
		}
		
		$group =& group_get_object($group_id);
		
		echo notepad_func();
		echo '
		<h3>'.sprintf(_('Approve a NewsByte For Project: %1$s'), $group->getPublicName()).'</h3>
		<p />
		<form action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="group_id" value="'.db_result($result,0,'group_id').'" />
		<input type="hidden" name="id" value="'.db_result($result,0,'id').'" />';

		$user =& user_get_object(db_result($result,0,'submitted_by'));

		echo '
		<strong>'._('Submitted by').':</strong> '.$user->getRealName().'<br />
		<input type="hidden" name="approve" value="y" />
		<input type="hidden" name="post_changes" value="y" />

		<strong>'._('Status').':</strong><br />
		<input type="radio" name="status" value="0" checked="checked" /> '._('Displayed').'<br />
		<input type="radio" name="status" value="4" /> '._('Delete').'<br />

		<strong>'._('Subject').':</strong><br />
		<input type="text" name="summary" value="'.db_result($result,0,'summary').'" size="30" maxlength="60"><br />
		<strong>'._('Details').':</strong>'.notepad_button('document.forms[1].details').'<br />';
			
		$params['name'] = 'details';
		$params['width'] = "600";
		$params['height'] = "300";
		$params['group'] = $group_id;
		$params['body'] = db_result($result,0,'details');
		plugin_hook("text_editor",$params);
		if (!$GLOBALS['editor_was_set_up']) {
			//if we don�t have any plugin for text editor, display a simple textarea edit box
			echo '<textarea name="details" rows="5" cols="50" wrap="soft">'.db_result($result,0,'details').'</textarea><br />';
		}
		unset($GLOBALS['editor_was_set_up']);
		
		echo '<p>
		<strong>'.sprintf(_('If this item is on the %1$s home page and you edit it, it will be removed from the home page.'), $GLOBALS['sys_name']).'</strong><br /></p>
		<input type="submit" name="submit" value="'._('Submit').'" />
		</form>';

	} else {
		/*
			Show list of waiting news items
		*/

		$sql="SELECT * FROM news_bytes WHERE is_approved <> 4 AND group_id='$group_id'";
		$result=db_query($sql);
		$rows=db_numrows($result);
		$group =& group_get_object($group_id);
		
		if ($rows < 1) {
			echo '
				<h4>'._('No Queued Items Found').': '.$group->getPublicName().'</h4>';
		} else {
			echo '
				<h4>'._('List of News Submitted for Project').': '.$group->getPublicName().'</h4>
				<ul>';
			for ($i=0; $i<$rows; $i++) {
				echo '
				<li><a href="'.$GLOBALS['sys_urlprefix'].'/news/admin/?approve=1&id='.db_result($result,$i,'id').'&amp;group_id='.
					db_result($result,$i,'group_id').'">'.
					db_result($result,$i,'summary').'</a></li>';
			}
			echo '</ul>';
		}

	}
	news_footer(array());

} else if (user_ismember($sys_news_group,'A')) {
	/*

		News uber-user admin pages

		Show all waiting news items except those already rejected.

		Admin members of $sys_news_group (news project) can edit/change/approve news items

	*/
	if ($post_changes) {
		if ($approve) {
			if ($status==1) {
				/*
					Update the db so the item shows on the home page
				*/
				$sanitizer = new TextSanitizer();
				$details = $sanitizer->SanitizeHtml($details);
				$sql="UPDATE news_bytes SET is_approved='1', post_date='".time()."', ".
					"summary='".htmlspecialchars($summary)."', details='".$details."' WHERE id='$id'";
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					$feedback .= _('Error On Update:');
				} else {
					$feedback .= _('NewsByte Updated.');
				}
			} else if ($status==2) {
				/*
					Move msg to deleted status
				*/
				$sql="UPDATE news_bytes SET is_approved='2' WHERE id='$id'";
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					$feedback .= _('Error On Update:');
					$feedback .= db_error();
				} else {
					$feedback .= _('NewsByte Deleted.');
				}
			}

			/*
				Show the list_queue
			*/
			$approve='';
			$list_queue='y';
		} else if (getStringFromRequest('mass_reject')) {
			/*
				Move msg to rejected status
			*/
			$news_id = getArrayFromRequest('news_id');
			$sql="UPDATE news_bytes "
			     ."SET is_approved='2' "
			     ."WHERE id IN ('".implode("','",$news_id)."')";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= _('Error On Update:');
				$feedback .= db_error();
			} else {
				$feedback .= _('NewsBytes Rejected.');
			}
		}
	}

	news_header(array('title'=>_('News Admin')));

	if ($approve) {
		/*
			Show the submit form
		*/

		$sql="SELECT groups.unix_group_name,news_bytes.* ".
			"FROM news_bytes,groups WHERE id='$id' ".
			"AND news_bytes.group_id=groups.group_id ";
		$result=db_query($sql);
		if (db_numrows($result) < 1) {
			exit_error(_('Error'), _('NewsByte not found'));
		}
		
		$group =& group_get_object(db_result($result,0,'group_id'));
		$user =& user_get_object(db_result($result,0,'submitted_by'));

		echo '
		<h3>'.sprintf(_('Approve a NewsByte For Project: %1$s'), $group->getPublicName()).'</h3>
		<p />
		<form action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="for_group" value="'.db_result($result,0,'group_id').'" />
		<input type="hidden" name="id" value="'.db_result($result,0,'id').'" />
		<strong>'._('Submitted for group').':</strong> <a href="'.$GLOBALS['sys_urlprefix'].'/projects/'.strtolower(db_result($result,0,'unix_group_name')).'/">'.$group->getPublicName().'</a><br />
		<strong>'._('Submitted by').':</strong> '.$user->getRealName().'<br />
		<input type="hidden" name="approve" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		<input type="radio" name="status" value="1" /> '._('Approve For Front Page').'<br />
		<input type="radio" name="status" value="0" /> '._('Do Nothing').'<br />
		<input type="radio" name="status" value="2" checked="checked" /> '._('Reject').'<br />
		<strong>'._('Subject').':</strong><br />
		<input type="text" name="summary" value="'.db_result($result,0,'summary').'" size="30" maxlength="60" /><br />
		<strong>'._('Details').':</strong><br />';
		
		$params['name'] = 'details';
		$params['width'] = "600";
		$params['height'] = "300";
		$params['group'] = db_result($result,0,'group_id');
		$params['body'] = db_result($result,0,'details');
		plugin_hook("text_editor",$params);
		if (!$GLOBALS['editor_was_set_up']) {
			//if we don�t have any plugin for text editor, display a simple textarea edit box
			echo '<textarea name="details" rows="5" cols="50" wrap="soft">'.db_result($result,0,'details').'</textarea><br />';
		}
		unset($GLOBALS['editor_was_set_up']);		
		
		
		echo '<br />
		<input type="submit" name="submit" value="'._('Submit').'" />
		</form>';

	} else {

		/*
			Show list of waiting news items
		*/

		$old_date = time()-60*60*24*30;
		$sql_pending= "
			SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=0
			AND news_bytes.group_id=groups.group_id
			AND post_date > '$old_date'
			AND groups.is_public=1
			AND groups.status='A'
			ORDER BY post_date
		";

		$old_date = time()-(60*60*24*7);
		$sql_rejected = "
			SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=2
			AND news_bytes.group_id=groups.group_id
			AND post_date > '$old_date'
			ORDER BY post_date
		";

		$sql_approved = "
			SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=1
			AND news_bytes.group_id=groups.group_id
			AND post_date > '$old_date'
			ORDER BY post_date
		";
		show_news_approve_form(
			$sql_pending,
			$sql_rejected,
			$sql_approved
		);

	}
	news_footer(array());

} else {

	exit_error(_('Permission Denied.'),sprintf(_('You have to be an admin on the project you are editing or a member of the %s News team.'), $GLOBALS['sys_name']));

}
?>
