<?php
/**
 * FusionForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
 *
 */

$ath->header(array ('title'=>_('Delete artifact').': [#'. $ah->getID(). '] ' . $ah->getSummary(), 'atid'=>$ath->getID()));

// $atid, $aid and $group_id are set in tracker.php

?>

<table>
<tr>
<td>
<fieldset>
<legend><?php echo _('Confirm Deletion'); ?></legend>
<form action="<?php echo getStringFromServer('PHP_SELF')."?aid=$aid&amp;group_id=$group_id"; ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
<input type="hidden" name="func" value="postdeleteartifact" />
<input type="hidden" name="atid" value="<?php echo $atid; ?>" />

<table border="0" align="center">

	<tr>
		<td class="veryimportant"><?php echo _('Are you sure you want to delete this artifact?'); ?></td>
	</tr>
	<tr align="center">
		<td align="center"><input type="checkbox" value="1" name="confirm_delete" /> <?php echo _('Yes, I want to delete this artifact'); ?></td>
	</tr>
	<tr>
		<td style="text-align:center"><input type="submit" value="<?php echo _('Delete'); ?>" name="submit" /></td>
	</tr>

</table>
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
