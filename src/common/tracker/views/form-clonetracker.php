<?php
/**
 * FusionForge Tracker Cloning Form
 *
 * Copyright 2010, FusionForge Team
 * Copyright 2014-2015,2017, Franck Villaume - TrivialDev
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

global $HTML;
global $ath;
$arr_g = group_get_template_projects();
if (!$arr_g || !is_array($arr_g)) {
	exit_no_group();
}
$ids = array();
$titles = array();
foreach ($arr_g as $g) {
	if (!$g->isError()) {
		$atf = new ArtifactTypeFactory($g);
		if ($atf && is_object($atf) && !$atf->isError()) {
			$ata = $atf->getArtifactTypes();
			for ($i=0; $i<count($ata); $i++) {
				if (!$ata[$i] || $ata[$i]->isError()) {
	//skip it
				} else {
					$ids[]=$ata[$i]->getID();
					$titles[]=$g->getPublicName().'::'.$ata[$i]->getName();
				}
			}
		}
	}
}

$ath->adminHeader(array ('title'=>_('Clone Tracker'), 'modal'=>1));

if (count($ids) < 1) {
	echo $HTML->warning_msg(_('The site administrator must first set up template trackers in the template project with default values and set permissions properly so you can access them.'));
} else {
	?>
	<p><?php echo _('Choose the template tracker to clone.') ?></p>
	<?php
	echo $HTML->openForm(array('/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post'));
	?>
	<input type="hidden" name="clone_tracker" value="y" />
	<?php
	echo $HTML->warning_msg(_('WARNING!!! Cloning this tracker will duplicate all the fields and all the elements from those fields into this tracker. There is nothing to prevent you from cloning multiple times or making a huge mess. If you have preexisting extrafields with same name, they will be dropped. You have been warned!'));
	?>
	<p><?php echo html_build_select_box_from_arrays($ids,$titles,'clone_id','',false); ?></p>
	<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
	<?php
	echo $HTML->closeForm();
}
$ath->footer();
