<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 *
 */

$ath->header(array ('title'=>_('Delete artifact').': '.$ah->getID(). ' - ' . $ah->getSummary(),'atid'=>$ath->getID()));

// $atid, $aid and $group_id are set in tracker.php

?>

<form action="<?php echo getStringFromServer('PHP_SELF')."?aid=$aid&amp;group_id=$group_id"; ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>">
<input type="hidden" name="func" value="postdeleteartifact" />
<input type="hidden" name="atid" value="<?php echo $atid; ?>" />

<table border="0" align="center">

	<tr>
		<td class="veryimportant"><?php echo _('Are you sure you want to delete this artifact?'); ?>
			<h3>&quot;<?php echo $ah->getSummary(); ?>&quot;</h3></td>
	</tr>
	<tr align="center">
		<td align="center"><input type="checkbox" value="1" name="confirm_delete"> <?php echo _('Yes, I want to delete this artifact'); ?></td>
	</tr>
	<tr>
		<td align="center"><input type="submit" value="<?php echo _('Submit'); ?>" name="submit" /></td>
	</tr>

</table>
</form>

<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
