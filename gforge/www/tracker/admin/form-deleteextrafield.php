<?php

$ath->adminHeader(array ('title'=>sprintf(_('Delete a custom field for %s'),$ath->getName())));

		$id = getStringFromRequest('id');

		?>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
		<p>
		<input type="hidden" name="deleteextrafield" value="y" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<?php echo _('You are about to permanently and irretrievably delete this custom field and all its contents!'); ?>
		</p>
		<p>
		<input type="checkbox" name="sure" value="1" /><?php echo _("I'm Sure.") ?><br />
		<input type="checkbox" name="really_sure" value="1" /><?php echo _("I'm Really Sure.") ?>
		</p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo _('Delete') ?>" /></p>
		</form>
		<?php

		$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
