<?php
/**
 * Code Snippets Repository
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014,2016, Franck Villaume - TrivialDev
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
require_once $gfwww.'snippet/snippet_utils.php';

global $HTML;
$suppress_nav = getStringFromRequest('suppress_nav');

function handle_add_exit() {
	global $suppress_nav;
	if ($suppress_nav) {
		echo '</body></html>';
	} else {
		snippet_footer();
	}
	exit;
}

if (session_loggedin()) {
	$snippet_package_version_id = getIntFromRequest('snippet_package_version_id');
	$snippet_version_id = getIntFromRequest('snippet_version_id');

	if ($suppress_nav) {
		echo '
		<html>
		<body>';
	} else {
		snippet_header(array('title'=>_('Submit A New Snippet')));
	}

	if (!$snippet_package_version_id) {
		//make sure the package id was passed in
		echo $HTML->error_msg(_('Error')._(': ')._('snippet_package_version_id missing'));
		handle_add_exit();
	}

	if (getStringFromRequest('post_changes')) {
		/*
			Create a new snippet entry, then create a new snippet version entry
		*/
		if ($snippet_package_version_id && $snippet_version_id) {
			/*
				check to see if they are the creator of this version
			*/
			$result=db_query_params("SELECT * FROM snippet_package_version ".
						"WHERE submitted_by=$1 AND ".
						"snippet_package_version_id=$2",
						array(user_getid(), $snippet_package_version_id));
			if (!$result || db_numrows($result) < 1) {
				echo $HTML->error_msg(_('Error: Only the creator of a package version can add snippets to it.'));
				handle_add_exit();
			}

			/*
				make sure the snippet_version_id exists
			*/
			$result=db_query_params('SELECT * FROM snippet_version WHERE snippet_version_id=$1',
						array($snippet_version_id));
			if (!$result || db_numrows($result) < 1) {
				echo $HTML->error_msg(_('Error: snippet does not exist'));
				echo util_make_link('/snippet/add_snippet_to_package.php?snippet_package_version_id='.$snippet_package_version_id,_('Back To Add Page'));
				handle_add_exit();
			}

			/*
				make sure the snippet_version_id isn't already in this package
			*/
			$result=db_query_params('SELECT * FROM snippet_package_item
						WHERE snippet_package_version_id=$1
						AND snippet_version_id=$2',
						array($snippet_package_version_id,
							$snippet_version_id));
			if ($result && db_numrows($result) > 0) {
				echo $HTML->error_msg(_('Error: That snippet was already added to this package.'));
				echo util_make_link('/snippet/add_snippet_to_package.php?snippet_package_version_id='.$snippet_package_version_id,_('Back To Add Page'));
				handle_add_exit();
			}

			/*
				create the snippet version
			*/
			$result=db_query_params("INSERT INTO snippet_package_item (snippet_package_version_id,snippet_version_id)
						VALUES ($1, $2)", array($snippet_package_version_id, $snippet_version_id));

			if (!$result) {
				$error_msg .= _('Error doing snippet version insert').' '.db_error();
			} else {
				$feedback .= _('Snippet Version Added Successfully.');
			}
		} else {
			echo $HTML->error_msg(_('Error')._(': ')._('Go back and fill in all the information'));
			echo util_make_link('/snippet/add_snippet_to_package.php?snippet_package_version_id='.$snippet_package_version_id,_('Back To Add Page'));
			handle_add_exit();
		}
	}

	$result=db_query_params ('SELECT snippet_package.name,snippet_package_version.version
				FROM snippet_package,snippet_package_version
				WHERE snippet_package.snippet_package_id=snippet_package_version.snippet_package_id
				AND snippet_package_version.snippet_package_version_id=$1',
				array($snippet_package_version_id));

	?>
	<p>
	<strong><?php echo _('Package')._(':'); ?></strong><br />
	<?php echo db_result($result,0,'name') . ' -  ' . db_result($result,0,'version'); ?></p>
	<p><?php echo _('You can use this form repeatedly to keep adding snippets to your package.'); ?></p>
	<p><?php echo _('The “Snippet Version ID” is the unique ID number that is shown next to a specific version of a snippet on the browse pages.'); ?></p>
	<p>
	<?php echo $HTML->openForm(array('action' => getStringFromServer('PHP_SELF'), 'method' => 'post')); ?>
	<input type="hidden" name="post_changes" value="y" />
	<input type="hidden" name="snippet_package_version_id" value="<?php echo $snippet_package_version_id; ?>" />
	<input type="hidden" name="suppress_nav" value="<?php echo $suppress_nav; ?>" />

	<table>
	<tr><td colspan="2" class="align-center">
		<strong><?php echo _('Add This Snippet Version ID')._(':'); ?></strong><br />
		<select name="snippet_version_id">
<?php

$combolistresult = db_query_params ('SELECT myname,snippet_version.snippet_version_id
					FROM ( SELECT MAX(post_date) AS
					mydate,name AS myname,snippet.snippet_id AS myid
					FROM
					snippet,snippet_version
					WHERE
					snippet.snippet_id=snippet_version.snippet_id
					GROUP BY
					name,snippet.snippet_id ) AS foo,snippet_version
					WHERE
					snippet_version.post_date=mydate',array());
$combolistrows=db_numrows($combolistresult);
for ($i=0; $i<$combolistrows; $i++)
{
	print '<option value='.db_result($combolistresult,$i,'snippet_version_id').'>'.db_result($combolistresult,$i,'myname').'</option>';
}
?>
</select>
	</td></tr>
	<tr><td colspan="2" class="align-center">
		<strong><?php echo _('Make sure all info is complete and accurate'); ?></strong>
		<br />
		<input type="submit" name="submit" value="<?php echo _('Submit'); ?>" />
	</td></tr>
	</table>
	<?php echo $HTML->closeForm(); ?>
	</p>
	<?php
	/*
		Show the snippets in this package
	*/
	$result=db_query_params('SELECT snippet_package_item.snippet_version_id, snippet_version.version, snippet.name
				FROM snippet,snippet_version,snippet_package_item
				WHERE snippet.snippet_id=snippet_version.snippet_id
				AND snippet_version.snippet_version_id=snippet_package_item.snippet_version_id
				AND snippet_package_item.snippet_package_version_id=$1',
				array($snippet_package_version_id));
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo db_error();
		echo $HTML->information(_('No Snippets Are In This Package Yet'));
	} else {
		echo $HTML->boxTop(_('Snippets In This Package'));
		echo $HTML->listTableTop();
		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td class="align-center">'.
				util_make_link('/snippet/delete.php?type=frompackage&snippet_version_id='.db_result($result,$i,'snippet_version_id').'&snippet_package_version_id='.$snippet_package_version_id,
						html_image("ic/trash.png", 16, 16)).'</td><td style="width:99%">'.
				db_result($result,$i,'name').' '.db_result($result,$i,'version')."</td></tr>";

			$last_group=db_result($result,$i,'group_id');
		}
		echo $HTML->listTableBottom();
		echo $HTML->boxBottom();
	}
	handle_add_exit();
} else {
	exit_not_logged_in();
}
