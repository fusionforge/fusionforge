<?php
/**
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2015, Franck Villaume - TrivialDev
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

$ath->adminHeader(array('title'=>sprintf(_('Delete a custom field for %s'),
	$ath->getName())));

$id = getStringFromRequest('id');
?>

<table class="centered">
<tr>
<td>
<fieldset>
<legend><?php echo _('Confirm Delete') ?></legend>
<?php
echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post'));
?>
<p>
<input type="hidden" name="deleteextrafield" value="y" />
<input type="hidden" name="id" value="<?php echo $id; ?>" />
<?php echo _('You are about to permanently and irretrievably delete this custom field and all its contents!'); ?>
</p>
<p>
<input id="sure" type="checkbox" name="sure" value="1" />
<label for="sure">
<?php echo _("I am Sure") ?><br />
</label>
<input id="really_sure" type="checkbox" name="really_sure" value="1" />
<label for="really_sure">
<?php echo _("I am Really Sure") ?>
</label>
</p>
<p>
<input type="submit" name="post_changes" value="<?php echo _('Delete') ?>" /></p>
<?php
echo $HTML->closeForm();
?>
</fieldset>
</td>
</tr>
</table>
<?php

$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
