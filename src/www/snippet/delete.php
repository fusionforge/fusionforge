<?php
/**
 * Code Snippets Repository
 *
 * Copyright 1999-2001 (c) VA Linux Systems - Tim Perdue
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
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
/*
	Delete items from packages, package versions, and snippet versions
*/

if (session_loggedin()) {
	snippet_header(array('title' => _('Delete Snippets')));
	$type = getStringFromRequest('type');
	$snippet_version_id = getIntFromRequest('snippet_version_id');
	$snippet_package_version_id = getIntFromRequest('snippet_package_version_id');

	if ($type=='frompackage' && $snippet_version_id && $snippet_package_version_id) {
		/*
			Delete an item from a package
		*/

		//Check to see if they are the creator of this package_version
		$result=db_query_params("SELECT * FROM snippet_package_version ".
					"WHERE snippet_package_version_id=$1",
					array($snippet_package_version_id));
		if (!$result || db_numrows($result) < 1) {
			echo $HTML->error_msg(_('Error: package version not found.'));
			snippet_footer();
			exit;
		} elseif (!forge_check_global_perm('forge_admin')
		           and db_result($result,0,'submitted_by') != user_getid()) {
			echo $HTML->error_msg(_('Error: Only the creator of a package version can delete snippets from it.'));
			snippet_footer();
			exit;
		} else {
			//Remove the item from the package
			$result=db_query_params ('DELETE FROM snippet_package_item
						WHERE snippet_version_id=$1
						AND snippet_package_version_id=$2',
						array($snippet_version_id,
							$snippet_package_version_id));
			if (!$result || db_affected_rows($result) < 1) {
				echo $HTML->error_msg(_('Error: That snippet does not exist in this package.'));
				snippet_footer();
				exit;
			} else {
				echo $HTML->feedback(_('Item Removed From Package'));
				snippet_footer();
				exit;
			}
		}

	} else  if ($type=='snippet' && $snippet_version_id) {
		/*
			Delete a snippet version
		*/

		//find this snippet id and make sure the current user created it
		$result=db_query_params("SELECT * FROM snippet_version ".
					"WHERE snippet_version_id=$1",
					array($snippet_version_id));
		if (!$result || db_numrows($result) < 1) {
			echo $HTML->error_msg(_('Error: That snippet does not exist.'));
			snippet_footer();
			exit;
		} elseif (!forge_check_global_perm('forge_admin')
			and db_result($result,0,'submitted_by') != user_getid()) {
			echo $HTML->error_msg(_('Error: Only the creator of a snippet can delete it.'));
			snippet_footer();
			exit;
		} else {
			$snippet_id=db_result($result,0,'snippet_id');

			//do the delete
			$result=db_query_params("DELETE FROM snippet_version ".
						"WHERE snippet_version_id=$1 AND submitted_by=$2",
						array($snippet_version_id, user_getid()));

			//see if any versions of this snippet are left
			$result=db_query_params("SELECT * FROM snippet_version WHERE snippet_id=$1", array($snippet_id));
			if (!$result || db_numrows($result) < 1) {
				//since no version of this snippet exist, delete the main snippet entry,
				//even if this person is not the creator of the original snippet
				$result=db_query_params("DELETE FROM snippet WHERE snippet_id=$1",array($snippet_id));
			}

			echo $HTML->feedback(_('Snippet Removed'));
			snippet_footer();
			exit;
		}

	} else  if ($type=='package' && $snippet_package_version_id) {
		/*
			Delete a package version

		*/

		//make sure they own this version of the package
		$result=db_query_params("SELECT * FROM snippet_package_version ".
					"WHERE snippet_package_version_id=$1",
					array($snippet_package_version_id));
		if (!$result || db_numrows($result) < 1) {
			echo $HTML->error_msg(_('Error: package version not found.'));
			snippet_footer();
			exit;
		} elseif (!forge_check_global_perm('forge_admin')
		           and db_result($result,0,'submitted_by') != user_getid()) {
			echo $HTML->error_msg(_('Error: Only the creator of a package version can delete it.'));
			snippet_footer();
			exit;
		} else {
			$snippet_package_id=db_result($result,0,'snippet_package_id');

			//do the version delete
			$result=db_query_params("DELETE FROM snippet_package_version ".
						"WHERE snippet_package_version_id=$1",
						array($snippet_package_version_id));

			//delete snippet_package_items
			$result=db_query_params("DELETE FROM snippet_package_item ".
						"WHERE snippet_package_version_id=$1",
						array($snippet_package_version_id));

			//see if any versions of this package remain
			$result=db_query_params("SELECT * FROM snippet_package_version ".
						"WHERE snippet_package_id=$1",
						array($snippet_package_id));
			if (!$result || db_numrows($result) < 1) {
				//since no versions of this package remain,
				//delete the main package even if the user didn't create it
				$result=db_query_params("DELETE FROM snippet_package WHERE snippet_package_id=$1", array($snippet_package_id));
			}
			echo $HTML->feedback(_('Package Removed'));
			snippet_footer();
			exit;
		}
	} else {
		exit_error(_('Error: mangled URL?'));
	}
} else {
	exit_not_logged_in();
}
