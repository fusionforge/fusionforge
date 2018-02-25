<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2016, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; // Group object
global $group_id; // id of group
global $HTML;

$sysdebug_enable = false;
$result = array();
$result['status'] = 0;

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$result['html'] = $HTML->error_msg(_('Document Manager Action Denied.'));
	echo json_encode($result);
	exit;
}

$docid = getIntFromRequest('docid');
$review = getIntFromRequest('review');

if ($docid && $review) {
	$documentObject = document_get_object($docid, $group_id);
	if ($documentObject && !$documentObject->isError()) {
		$dr = new DocumentReview($documentObject, $review);
		if ($dr && !$dr->isError()) {
			if ($dr->delete()) {
				$result['html'] = $HTML->feedback(_('Review deleted successfully.'));
				$result['status'] = 1;
			} else {
				$result['html'] = $HTML->error_msg(_('Cannot delete review ID')._(': ').$review.' '.$dr->getErrorMessage());
			}
		} else {
			$result['html'] = $HTML->error_msg(_('Cannot create object documentreview'));
		}
	} else {
		$result['html'] = $HTML->error_msg(_('Cannot retrieve document')._(': ').$docid);
	}
} else {
	$result['html'] = $HTML->warning_msg(_('No document ID. Cannot retrieve review.'));
}

echo json_encode($result);
exit;
