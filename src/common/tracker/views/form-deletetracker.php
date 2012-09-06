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

$ath->adminHeader(array ('title'=>sprintf(_('Permanently Delete Tracker %s'), $ath->getName())));
?>
		<fieldset>
		<legend><?php echo _("Delete Tracker") ?></legend>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="delete" value="y" /><br />
		<?php echo _('You are about to permanently and irretrievably delete this tracker and all its contents!'); ?>
		<p>
		<input type="checkbox" name="sure" value="1" /><?php echo _("I'm Sure") ?><br />
		<input type="checkbox" name="really_sure" value="1" /><?php echo _("I'm Really Sure") ?></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo _('Delete') ?>" /></p>
		</form>
		</fieldset>
<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
