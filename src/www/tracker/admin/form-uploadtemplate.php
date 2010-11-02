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


$title = sprintf(_('Add/Update template for %s'), $ath->getName()) ;

$ath->adminHeader(array('title'=>$title));

?>
<table>
<tr>
<td>
<fieldset>
<legend><?php echo _('Select Template'); ?></legend>
<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post" enctype="multipart/form-data">
<p>
    <input type="hidden" name="uploadtemplate" value="1" />
    <input type="file" name="input_file" size="30" />
    <input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
</p>
</form>
</fieldset>
</td>
</tr>
</table>
<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
