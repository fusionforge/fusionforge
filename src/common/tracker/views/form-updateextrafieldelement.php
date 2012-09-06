<?php
/**
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
//  FORM TO UPDATE POP-UP CHOICES FOR A BOX
//
/*
	Allow modification of a Choice for a Pop-up Box
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
		$title = sprintf(_('Modify a custom field element in %s'), $ath->getName()) ;
		$ath->adminHeader(array('title'=>$title));

?>
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_opt" value="y" />
			<input type="hidden" name="id" value="<?php echo $ao->getID(); ?>" />
			<input type="hidden" name="boxid" value="<?php echo $boxid; ?>" />

			<p>
			<strong><?php echo _('Element') ?>:</strong><br />
			<input type="text" name="name" value="<?php echo $ao->getName(); ?>" /></p>
			<!--
			Show a pop-up box to choose the possible statuses that this element will map to
			-->
			<?php if ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) { ?>
			<strong><?php echo _('Status'); ?></strong><br />
			<?php echo $ath->statusBox('status_id',$ao->getStatusID(),false,false); ?>
			<?php } ?>

			<div class="warning"><?php echo _('It is not recommended that you change the custom field name because other things are dependent upon it. When you change the custom field name, all related items will be changed to the new name') ?>
			</div>
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
