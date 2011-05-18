<?php

/*
 * Copyright (C) 2006 Alain Peyrat, Alcatel-Lucent
 * Copyright (C) 2010 Alain Peyrat <aljeux@free.fr>
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The provided file ("Contribution") has not been tested and/or
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

require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once $gfconfig.'plugins/blocks/config.php' ;

require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'mail/MailingListFactory.class.php';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';
require_once $gfcommon.'survey/SurveyFactory.class.php';

function getAvailableBlocks($group) {
	$blocks = array(
		'summary_description' =>
			_("Block to replace the default project description with an enhanced one."),
//	To be reworked to play nice with the widgets page.
//		'summary_right' =>
//			_("Block in the summary page (right)"),
		'request_join' =>
			_("Block to list informations requested to ask to join a project"),
	);

	if ($group->usesForum()) {
		// Get the blocks in the forums.
		$blocks['forum index'] = _("Display block at the top of the listing");
		$ff = new ForumFactory($group);
		foreach ( $ff->getForums() as $f) {
			$blocks['forum_'.$f->getName()] = _("Display block at the top");
		}
	}

	if ($group->usesTracker()) {
		// Get the blocks in the trackers.
		$blocks['tracker index'] = _("Display block at the top of the listing");
		$ff = new ArtifactTypeFactory($group);
		foreach ( $ff->getArtifactTypes() as $f) {
			$blocks['tracker_'.$f->getName()] = _("Display block at the top");
		}
	}

	if ($group->usesMail()) {
		// Get the blocks in the mailing lists.
		$blocks['mail index'] = _("Display block at the top of the listing");
		$ff = new MailingListFactory($group);
		foreach ( $ff->getMailingLists() as $f) {
			$blocks['mail_'.$f->getName()] = _("Display block at the top");
		}
	}

	if ($group->usesPM()) {
		// Get the blocks in the tasks.
		$blocks['tasks index'] = _("Display block at the top of the listing");
		$ff = new ProjectGroupFactory($group);
		foreach ( $ff->getProjectGroups() as $f) {
			$blocks['tasks_'.$f->getName()] = _("Display block at the top");
		}
	}

	if ($group->usesDocman()) {
		// Get the blocks in the doc.
		$blocks['doc index'] = _("Display block at the top of the listing");
		$blocks['doc help'] = _("Display block at the top of the main page");
	}

	if ($group->usesSurvey()) {
		// Get the blocks in the survey.
		$blocks['survey index'] = _("Display block at the top of the listing");
		$ff = new SurveyFactory($group);
		foreach ( $ff->getSurveys() as $f) {
			$blocks['survey_'.$f->getTitle()] = _("Display block at the top");
		}
	}

	if ($group->usesNews()) {
		// Get the blocks in the news.
		$blocks['news index'] = _("Display block at the top of the listing");
	}

	if ($group->usesSCM()) {
		// Get the blocks in the scm.
		$blocks['scm index'] = _("Display block at the top of the listing");
	}

	if ($group->usesFRS()) {
		// Get the blocks in the files.
		$blocks['files index'] = _("Display block at the top of the listing");
	}

	return $blocks;
}

// the header that displays for the user portion of the plugin
function blocks_Project_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$id;
	$params['toptab']='blocks';
	$params['group']=$id;
	/*
		Show horizontal links
	*/
	site_project_header($params);
}

$user = session_get_user(); // get the session user

if (!$user || !is_object($user) ) {
	exit_error(_('Invalid User'),'home');
} else if ( $user->isError() ) {
	exit_error($user->getErrorMessage(),'home');
} else if ( !$user->isActive()) {
	exit_error(_('Invalid User : Not active'),'home');
}


$type = getStringFromRequest('type');
$id = getStringFromRequest('id');
$pluginname = getStringFromRequest('pluginname');
$name = getStringFromRequest('name');
$body = getStringFromRequest('body');
$activate = getArrayFromRequest('activate');

$blocks_text = array(
	'forum' => _('Forums'),
	'tracker' => _('Trackers'),
	'mail' => _('Lists'),
	'tasks' => _('Tasks'),
	'doc' => _('Docs'),
	'survey' => _('Surveys'),
	'news' => _('News'),
	'scm' => _('SCM'),
	'files' => _('Files')
);

if (!$type) {
	exit_error(_('Cannot Process your request : No TYPE specified'),'home'); // you can create items in Base.tab and customize this messages
} elseif (!$id) {
	exit_error(_('Cannot Process your request : No ID specified'),'home');
} else {
	if ($type == 'group') {
		$group = group_get_object($id);
		if ( !$group) {
			exit_no_group();
		}
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the blocks plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
		}

		session_require_perm ('project_admin', $id) ;

		blocks_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
		// DO THE STUFF FOR THE PROJECT PART HERE
		echo "We are in the Project blocks plugin <br />";
		echo "Greetings from planet " . $world; // $world comes from the config file in /etc
	} elseif ($type == 'admin') {
		$group = group_get_object($id);
		if ( !$group) {
			exit_no_group();
		}
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the blocks plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
		}
		session_require_perm ('project_admin', $id) ;

		blocks_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
		// DO THE STUFF FOR THE PROJECT ADMINISTRATION PART HERE

		$res = db_query_params('SELECT name, status FROM plugin_blocks WHERE group_id=$1',
				       array($id));
		while ($row = db_fetch_array($res)) {
			$status[ $row['name'] ] = $row['status'];
		}

		print _("Blocks are customizable HTML boxes in the left or right side of the pages the web site. They are created manually.");

		print "<form action=\"/plugins/blocks/\" method=\"post\">";
		print "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
		print "<input type=\"hidden\" name=\"pluginname\" value=\"$pluginname\" />\n";
		print "<input type=\"hidden\" name=\"type\" value=\"admin_post\" />\n";

		print "<table class=\"listing\" align=\"center\">";
		print "<thead><tr><th>".
			_("Name").
			"</th>" .
			"<th>".
			_("Active").
			"</th>" .
			"<th>" .
			_("Description").
			"</th>" .
			"<th>" .
			_("Operation") .
			"</th>" .
			"</tr></thead>";
		$blocks = getAvailableBlocks($group);
		foreach ($blocks as $b => $help) {

			$class = (! isset($class) || $class == 'bgcolor-white') ? "bgcolor-grey" : "bgcolor-white";

			$match = '';
			if (preg_match('/(.*) index$/', $b, $match)) {
				print '<tr><td colspan="4"><b>'.$blocks_text[$match[1]].'</b></td></tr>';
			}

			$checked = (isset($status[$b]) && $status[$b] == 1) ? ' checked="checked"' : '';

			print "<tr class=\"$class\"><td>$b</td>\n" .
				"<td align=\"center\">" .
				"<input type=\"checkbox\" name=\"activate[$b]\" value=\"1\"$checked /></td>\n" .
				"<td>$help</td>\n" .
				"<td><a href=\"/plugins/blocks/index.php?id=$id&amp;type=configure&amp;pluginname=blocks&amp;name=".urlencode($b)."\">configure</a></td>\n</tr>\n";
			}
		print "</table>";
		print "<p align=\"center\"><input type=\"submit\" value=\"" .
			_("Save Blocks") .
			"\" /></p>";
		print "</form><p />";
	} elseif ($type == 'admin_post') {
		$group = group_get_object($id);
		if ( !$group) {
			exit_no_group();
		}
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the blocks plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
		}
		session_require_perm ('project_admin', $id) ;

		$res = db_query_params('SELECT name, status FROM plugin_blocks WHERE group_id=$1',
				       array($id));
		while ($row = db_fetch_array($res)) {
			$present[ $row['name'] ] = true;
			$status[ $row['name'] ] = $row['status'];
		}
		$blocks = getAvailableBlocks($group);

		// Workaround when a block has a name with a &amp; inside.
		// It seems sadly converted by the form (or php?).
		foreach ($activate as $k => $v) {
			$nk = str_replace("&","&amp;", $k);
			if ($nk !== $k) {
				$activate[$nk] = $v;
				unset($activate[$k]);
			}
		}

		foreach ($blocks as $b => $help) {

			if (!$activate[$b])
				$activate[$b] = 0;

			if ((!isset($status[$b]) && $activate[$b]) ||
			    (isset($status[$b]) && $activate[$b] !== $status[$b]))
				// Must be updated.
				if (!isset($present[$b])) {
					db_query_params('INSERT INTO plugin_blocks (group_id, name, status)
							VALUES ($1, $2, $3)',
							array($id, $b, $activate[$b]));
				} else {
					db_query_params('UPDATE plugin_blocks SET status=$1
							WHERE group_id=$2 AND name=$3',
							array($activate[$b], $id, $b));
				}
		}
        $msg = _('Block Saved');
		session_redirect('/plugins/blocks/index.php?id='.$id.'&type=admin&pluginname=blocks&feedback='.urlencode($msg));
	} elseif ($type == 'configure') {
		$group = group_get_object($id);
		if ( !$group) {
			exit_no_group();
		}
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the blocks plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginnname),'home');
		}
		session_require_perm ('project_admin', $id) ;

		blocks_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
		// DO THE STUFF FOR THE PROJECT ADMINISTRATION PART HERE

		$blocks = getAvailableBlocks($group);
		$res = db_query_params('SELECT content FROM plugin_blocks WHERE group_id=$1 AND name=$2',
				       array($id, $name));
		$body = db_result($res,0,"content");

		print _("Edit the block as you want. If you activate the HTML editor, you will be able to use WYSIWYG formatting (bold, colors...)");

		print "<center>";
		print "<b>$blocks[$name]</b> ($name)";
		print "<form action=\"/plugins/blocks/\" method=\"post\">";
		print "<input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
		print "<input type=\"hidden\" name=\"pluginname\" value=\"$pluginname\" />\n";
		print "<input type=\"hidden\" name=\"type\" value=\"configure_post\" />\n";
		print "<input type=\"hidden\" name=\"name\" value=\"$name\" />\n";

		// Get default page from the templates defined in the config file.
		if (!$body && function_exists ('json_decode')) {
			$templates=json_decode(file_get_contents(forge_get_config('templates_file','blocks'))) ;
			if (isset($templates[$name])) {
				$body = $templates[$name];
			} else {
				$body = $templates['*'];
			}
		}

		$params['body'] = $body;
		$params['width'] = "800";
		$params['height'] = "500";
		$params['group'] = $id;
		plugin_hook("text_editor",$params);
		if (!$GLOBALS['editor_was_set_up']) {
			//if we don't have any plugin for text editor, display a simple textarea edit box
			echo '<textarea name="body"  rows="20" cols="80">' . $body . '</textarea>';
		}
		unset($GLOBALS['editor_was_set_up']);

		print "<br /><input type=\"submit\" value=\"" .
			_("Save") .
			"\" />";
		print "</form>";
		print "</center>";

		print "<fieldset><legend>".
			_("Tips").
			"</legend>" .
			_("<p>You can create boxes like the ones on the right site of summary page, by inserting the following sentences in the content:</p><ul><li>{boxTop Hello}: will create the top part of the box using Hello as title.</li><li>{boxMiddle Here}: will create a middle part of a box using Here as title (optional).</li><li>{boxBottom}: will create the end part of a box.</li></ul><p /><ul><li>{boxHeader}: will create a header before a text.</li><li>{boxFooter}: will create a footer after a text.</li></ul><p>You can create as many boxes as you want, but a boxTop has to be closed by a boxBottom and a boxHeader has to be closed by a boxFooter.</p>").
			"</fieldset>";
	} elseif ($type == 'configure_post') {
		$group = group_get_object($id);
		if ( !$group) {
			exit_no_group();
		}
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the blocks plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
		}
		session_require_perm ('project_admin', $id) ;

		$res = db_query_params('SELECT id FROM plugin_blocks WHERE group_id=$1 AND name=$2',
				       array($id,$name));
		if (db_numrows($res)== 0) {
			db_query_params('INSERT INTO plugin_blocks (group_id, name, content)
					VALUES ($1, $2, $3)',
					array($id, $name, $body));
		} else {
			db_query_params('UPDATE plugin_blocks SET content=$1
					WHERE group_id=$2 AND name=$3',
					array($body, $id, $name));
		}
        $msg = $name .' : '. _('Block configuration saved');
		session_redirect('/plugins/blocks/index.php?id='.$id.'&type=admin&pluginname=blocks&feedback='.urlencode($msg));
	}
}

site_project_footer(array());

?>
