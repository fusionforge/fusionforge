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

$ath->header(array ('title'=>$Language->getText('tracker_mod','delete_title').': '.$ah->getID(). ' - ' . $ah->getSummary(),'atid'=>$ath->getID()));
?>

<form action="<?php echo getStringFromServer('PHP_SELF')."?aid=$aid&amp;group_id=$group_id"; ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>">
<input type="hidden" name="func" value="postdeleteartifact" />
<input type="hidden" name="atid" value="<?php echo $atid; ?>" />

<table border="0" align="center">

	<tr>
		<td align="center"><h3><font color="#FF0000"><?php echo $Language->getText('tracker_artifact','are_you_sure_delete'); ?></font></h3>
			<h3>&quot;<?php echo $ah->getSummary(); ?>&quot;</h3></td>
	</tr>
	<tr align="center">
		<td align="center"><input type="checkbox" value="1" name="confirm_delete"> <?php echo $Language->getText('tracker_artifact','confirm_delete'); ?></td>
	</tr>
	<tr>
		<td align="center"><input type="submit" value="<?php echo $Language->getText('general','submit'); ?>" name="submit" /></td>
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
