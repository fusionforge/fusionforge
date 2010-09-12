<?php
/**
 * FusionForge Tracker Cloning Form
 *
 * Copyright 2010, FusionForge Team
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

$g =& group_get_object(forge_get_config('template_group'));
if (!$g || !is_object($g)) {
	exit_error('Error','Unable to Create Template Group Object');
} elseif ($g->isError()) {
	exit_error('Error',$g->getErrorMessage());
} else {
	$atf = new ArtifactTypeFactory($g);
	if (!$atf || !is_object($atf)) {
		exit_error('Error','Unable to Create Template Group Object');
	} elseif ($atf->isError()) {
		exit_error('Error',$atf->atfetErrorMessaatfe());
	} else {
		$ata = & $atf->getArtifactTypes();
		$ids = array();
		$titles = array();
		for ($i=0; $i<count($ata); $i++) {
			if (!$ata[$i] || $ata[$i]->isError()) {
//skip it
			} else {
				$ids[]=$ata[$i]->getID();
				$titles[]=$g->getPublicName().'::'.$ata[$i]->getName();
			}
		}

		$ath->adminHeader(array ('title'=>_('Clone Tracker')));

		echo "<h1>"._('Clone Tracker')."</h1>";

		?>
		<p><?php echo _('Choose the template tracker to clone. The site administrator will have to set up trackers with default values and set permissions properly so you can access them.') ?></p>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="clone_tracker" value="y" />
		<div class="warning" ><?php echo _('WARNING!!! Cloning this tracker will duplicate all the fields and all the elements from those fields into this tracker. There is nothing to prevent you from cloning multiple times or making a huge mess. You have been warned!') ?></div>
		<p><?php echo html_build_select_box_from_arrays($ids,$titles,'clone_id','',false); ?></p>
		<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
		</form>
<?php
		$ath->footer(array());
	}
}
?>
