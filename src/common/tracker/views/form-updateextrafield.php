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
//  FORM TO UPDATE POP-UP BOXES
//
/*
	Allow modification of a artifact Selection Box
*/
$title = sprintf(_('Modify a custom field in %s'),$ath->getName());
$ath->adminHeader(array('title'=>$title));

$id = getStringFromRequest('id');
$ac = new ArtifactExtraField($ath,$id);
if (!$ac || !is_object($ac)) {
	$error_msg .= _('Unable to create ArtifactExtraField Object');
} elseif ($ac->isError()) {
	$error_msg .= $ac->getErrorMessage();
} else {
?>
<p>
<strong><?php echo _('Type of custom field')._(': ').$ac->getTypeName(); ?></strong></p>

<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;id='.$id.'&amp;atid='.$ath->getID(); ?>" method="post">
	<input type="hidden" name="update_box" value="y" />
	<input type="hidden" name="id" value="<?php echo $ac->getID(); ?>" />
	<p>
		<label for="name">
		<strong><?php echo _('Custom Field Name')._(':'); ?></strong><br />
		</label>
		<input id="name" type="text" name="name" value="<?php echo $ac->getName(); ?>" required="required" />
	</p>
	<p>
		<?php if ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA) { ?>
		<label for="attribute1">
		<b><?php echo _('Text Area Rows'); ?></b><br />
		</label>
		<input id="attribute1" type="text" name="attribute1" value="<?php echo $ac->getAttribute1(); ?>" size="2" maxlength="2" />
	</p>
	<p>
		<label for="attribute2">
		<b><?php echo _('Text Area Columns'); ?></b><br />
		</label>
		<input id="attribute2" type="text" name="attribute2" value="<?php echo $ac->getAttribute2(); ?>" size="2" maxlength="2" />
		<?php } elseif ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_TEXT ||
					$ac->getType() == ARTIFACT_EXTRAFIELDTYPE_RELATION) {?>
		<label for="attribute1">
		<b><?php echo _('Text Field Size'); ?></b><br />
		</label>
		<input id="attribute1" type="text" name="attribute1" value="<?php echo $ac->getAttribute1(); ?>" size="2" maxlength="2" />
		</p>
		<p>
		<label for="attribute2">
		<b><?php echo _('Text Field Maxlength'); ?></b><br />
		<input id="attribute2" type="text" name="attribute2" value="<?php echo $ac->getAttribute2(); ?>" size="2" maxlength="2" />
		<?php } else { ?>
		<input type="hidden" name="attribute1" value="0" />
		<input type="hidden" name="attribute2" value="0" />
		<?php } ?>
	</p>
	<p>
		<label for="alias">
		<strong><?php echo _('Field alias')._(':'); ?></strong><br />
		</label>
		<input id="alias" type="text" name="alias" value="<?php echo $ac->getAlias(); ?>" />
	</p>
	<p><input id="is_required" type="checkbox" name="is_required" <?php if ($ac->isRequired()) echo 'checked="checked"'; ?> />
	<?php echo _('Field is mandatory')?></p>
	<label for="is_required">
	<p class="warning"><?php echo _('It is not recommended that you change the custom field name because other things are dependent upon it. When you change the custom field name, all related items will be changed to the new name.') ?>
	</label>
	</p>
	<p>
	<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
	</p>
</form>
<?php
}

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
