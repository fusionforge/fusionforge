<?php
/*
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
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

//
//	FORM TO UPDATE CANNED MESSAGES
//
$title = sprintf(_('Modify Canned Responses In %s'),$ath->getName());
$ath->adminHeader(array('title'=>$title));

		$id = getStringFromRequest('id');
		$acr = new ArtifactCanned($ath,$id);
		if (!$acr || !is_object($acr)) {
			$feedback .= 'Unable to create ArtifactCanned Object';
		} elseif ($acr->isError()) {
			$feedback .= $acr->getErrorMessage();
		} else {
			?>
			<p><?php echo _('Creating useful generic messages can save you a lot of time when handling common artifact requests.') ?></p>
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_canned" value="y" />
			<input type="hidden" name="id" value="<?php echo $acr->getID(); ?>" />
			<label for="title">
			<strong><?php echo _('Title') . _(':') ?></strong><br />
			</label>
			<input id="title" type="text" name="title" value="<?php echo $acr->getTitle(); ?>" size="80" maxlength="80" />
			<p>
			<label for="body">
			<strong><?php echo _('Message Body') . _(':') ?></strong><br />
			</label>
			<textarea id="body" name="body" rows="30" cols="80"><?php echo $acr->getBody(); ?></textarea></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
			</form>
			<?php
		}
		$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
