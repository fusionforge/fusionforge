<?php
/**
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
//  FORM TO DELETE POP-UP CHOICES FOR A BOX
//
	/*
		Allow deletion of a Choice for a Pop-up Box
	*/
	$boxid = getIntFromRequest('boxid');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		exit_error(_('Unable to create ArtifactExtraField Object'),'tracker');
	} elseif ($ac->isError()) {
		exit_error($ac->getErrorMessage(),'tracker');
	} else {
		$id = getStringFromRequest('id');
		$ao = new ArtifactExtraFieldElement($ac,$id);
		if (!$ao || !is_object($ao)) {
			exit_error(_('Unable to create ArtifactExtraFieldElement Object'),'tracker');
		} elseif ($ao->isError()) {
			exit_error($ao->getErrorMessage(),'tracker');
		} else {

			$ath->adminHeader(array('title'=>sprintf(_("Delete a custom field element in: %s"), $ath->getName())));

			?>
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="delete_opt" value="y" />
			<input type="hidden" name="id" value="<?php echo $ao->getID(); ?>" />
			<input type="hidden" name="boxid" value="<?php echo $boxid; ?>" />

			<p>
			<strong><?php echo _('Element') ?>:</strong>&nbsp;
			<?php echo $ao->getName(); ?></p>
			<p>
			<input type="checkbox" name="sure" value="1" /><?php echo _("I'm Sure.") ?><br />
			<input type="checkbox" name="really_sure" value="1" /><?php echo _("I'm Really Sure.") ?>
			</p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
			</form>
			<?php
			$ath->footer(array());
		}
	}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
