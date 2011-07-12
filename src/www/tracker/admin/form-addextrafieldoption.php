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
//  FORM TO ADD ELEMENTS TO EXTRA FIELD
//
	$boxid = getIntFromRequest('boxid');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		exit_error(_('Unable to create ArtifactExtraField Object'),'tracker');
	} elseif ($ac->isError()) {
		exit_error($ac->getErrorMessage(),'tracker');
	} else {
	  	$efearr=$ath->getExtraFieldElements($boxid);
		$title = sprintf(_('Add/Update Custom Field Elements in %s'), $ath->getName());
		$ath->adminHeader(array ('title'=>$title));

		$rows=count($efearr);
		if ($rows > 0) {

			?>
			<form action="<?php echo 'index.php?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;boxid='.$boxid; ?>" method="post">
			<?php
			$title_arr=array();
			$title_arr[]=_('Current / New positions');
			$title_arr[]=_('Up/Down positions');
			$title_arr[]=_('Elements Defined');
			$title_arr[]='';

			echo $GLOBALS['HTML']->listTableTop ($title_arr,false, ' ');

			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td align="right">'.
					($i + 1).'&nbsp;--&gt;&nbsp;<input type="text" name="order['. $efearr[$i]['element_id'] .']" value="" size="3" maxlength="3" />'.
					'</td>'."\n".'<td align="center">'.'&nbsp;&nbsp;&nbsp;'.
					'<a href="index.php?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;boxid='.$boxid.'&amp;id='.$efearr[$i]['element_id'].
					'&amp;updownorder_opt=1&amp;new_pos='.(($i == 0)? $i + 1 : $i).'">'.html_image('ic/btn_up.png','19','18',array('alt'=>"Up")).'</a>'.
					'&nbsp;&nbsp;'.
					'<a href="index.php?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;boxid='.$boxid.'&amp;id='.$efearr[$i]['element_id'].
					'&amp;updownorder_opt=1&amp;new_pos='.(($i == $rows - 1)? $i + 1 : $i + 2).'">'.html_image('ic/btn_down.png','19','18',array('alt'=>"Down")).'</a>'.
					'</td>'."\n".'<td>'.'&nbsp;&nbsp;&nbsp;'.$efearr[$i]['element_name'].
					'</td>'."\n".'<td align="center">'.
					'<a href="'.getStringFromServer('PHP_SELF').'?update_opt=1&amp;id='.
					$efearr[$i]['element_id'].'&amp;boxid='.
					$boxid.'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
					html_image('ic/forum_edit.gif','37','15',array('alt'=>"Edit")).'</a>'.
					'</td></tr>'."\n";
			}
//			echo $GLOBALS['HTML']->listTableBottom();
			?>
			<tr class="noborder">
			<td align="right">
			<input type="submit" name="post_changes_order" value="<?php echo _('Reorder') ?>" />
			</td>
			<td>
			</td>
			<td align="left">
			<input type="submit" name="post_changes_alphaorder" value="<?php echo _('Alphabetical order') ?>" />
			<br />&nbsp;
			</td>
			</tr>
			<?php echo $GLOBALS['HTML']->listTableBottom(); ?>
			</form>
			<?php

		} else {
			echo "\n<strong>"._('You have not defined any elements')."</strong>";
		}
		?>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;boxid='.$boxid.'&amp;atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_opt" value="y" />
		<br /><br />
		<strong><?php echo _('Add New Element') ?>:</strong>
		<input type="text" name="name" value="" size="15" maxlength="30" />
		<!--
		Show a pop-up box to choose the possible statuses that this element will map to
		-->
		<?php if ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) { ?>
		<strong>&nbsp;&nbsp;<?php echo _('Status'); ?></strong>
		<?php echo $ath->statusBox('status_id',1,false,false); ?>
		<?php } ?>
		&nbsp;&nbsp;<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
		</form>
		<?php
		$ath->footer(array());
	}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
