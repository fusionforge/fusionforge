<?php
/**
 * FusionForge News Facility
 *
 * Copyright (C) 1999-2001 VA Linux Systems
 * Copyright (C) 2002-2004 GForge Team
 * Copyright (C) 2008-2009 Alcatel-Lucent
 *
 * http://fusionforge.org/
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Style Sheet ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'include/note.php';
require_once $gfwww.'news/news_utils.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'include/TextSanitizer.class.php'; // to make the HTML input by the user safe to store

$group_id = getIntFromRequest('group_id');

if (session_loggedin()) {

	if (!user_ismember($group_id,'A')) {
		exit_permission_denied(_('You cannot submit news for a project unless you are an admin on that project'));
	}

	$group_id = getIntFromRequest('group_id');

	if ($group_id == $sys_news_group) {
		exit_permission_denied(_('Submitting news from the news group is not allowed.'));
	}

	if (getStringFromRequest('post_changes')) {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}
		$summary = getStringFromRequest('summary');
		$details = getStringFromRequest('details');

		//check to make sure both fields are there
		if ($summary && $details) {
			$sanitizer = new TextSanitizer();
			$details = $sanitizer->purify($details);

			/*
				Insert the row into the db if it's a generic message
				OR this person is an admin for the group involved
			*/

	   			/*
	   				create a new discussion forum without a default msg
	   				if one isn't already there
	   			*/

				db_begin();
				$f=new Forum(group_get_object($sys_news_group));
				if (!$f->create(ereg_replace('[^_\.0-9a-z-]','-', strtolower($summary)),$details,1,'',0,0)) {
					db_rollback();
					exit_error('Error',$f->getErrorMessage());
				}
	   			$new_id=$f->getID();
				$sql='INSERT INTO news_bytes (group_id,submitted_by,is_approved,post_date,forum_id,summary,details) 
 VALUES ($1, $2, $3, $4, $5, $6, $7)';
				$result=db_query_params($sql,
					array($group_id, user_getid(), 0, time(), $new_id, htmlspecialchars($summary), $details));
	   			if (!$result) {
					db_rollback();
					form_release_key(getStringFromRequest('form_key'));
	   				$feedback = ' '._('ERROR doing insert').' ';
	   			} else {
					db_commit();
	   				$feedback = ' '._('News Added.').' ';
	   			}
		} else {
			form_release_key(getStringFromRequest('form_key'));
			$feedback = ' '._('ERROR - both subject and body are required').' ';
		}
	}

	//news must now be submitted from a project page - 

	if (!$group_id) {
		exit_no_group();
	}
	/*
		Show the submit form
	*/
	news_header(array('title'=>_('Submit News')));
	echo '<h1>' . _('Submit News') . '</h1>';

	$jsfunc = notepad_func();
	$group = group_get_object($group_id);
	echo '
		<p>
		'. sprintf(_('You can post news about your project if you are an admin on your project. You may also post "help wanted" notes if your project needs help.</p><p>All posts <b>for your project</b> will appear instantly on your project summary page. Posts that are of special interest to the community will have to be approved by a member of the %1$s news team before they will appear on the %1$s home page.</p><p>You may include URLs, but not HTML in your submissions.</p><p>URLs that start with http:// are made clickable.'), forge_get_config ('forge_name')) .'</p>' . $jsfunc . 
		'
		<form id="newssubmitform" action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<input type="hidden" name="post_changes" value="y" />
		<input type="hidden" name="form_key" value="'. form_generate_key() .'" />
		<p><strong>'._('For project').' '.$group->getPublicName().'</strong></p>
		<p>
		<strong>'._('Subject').':</strong>'.utils_requiredField().'<br />
		<input type="text" name="summary" value="" size="60" maxlength="60" /></p>
		<p>
		<strong>'._('Details').':</strong>'.notepad_button('document.forms.newssubmitform.details').utils_requiredField().'</p>';
	
	$GLOBALS['editor_was_set_up']=false;
	$params = array() ;
	$params['name'] = 'details';
	$params['width'] = "800";
	$params['height'] = "500";
	$params['body'] = "";
	$params['group'] = $group_id;
	plugin_hook("text_editor",$params);
	if (!$GLOBALS['editor_was_set_up']) {
		//if we don't have any plugin for text editor, display a simple textarea edit box
		echo '<textarea name="details" rows="5" cols="50"></textarea><br />';
	}
	unset($GLOBALS['editor_was_set_up']);
	echo '<input type="submit" name="submit" value="'._('Submit').'" />
		</p></form>';

	news_footer(array());

} else {

	exit_not_logged_in();

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
