<?php
//
//  FORM TO DELETE POP-UP CHOICES FOR A BOX
//
	/*
		Allow deletion of a Choice for a Pop-up Box
	*/
	$boxid = getIntFromRequest('boxid');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		exit_error('Error','Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		exit_error('Error',$ac->getErrorMessage());
	} else {
		$id = getStringFromRequest('id');
		$ao = new ArtifactExtraFieldElement($ac,$id);
		if (!$ao || !is_object($ao)) {
			exit_error('Error','Unable to create ArtifactExtraFieldElement Object');
		} elseif ($ao->isError()) {
			exit_error('Error',$ao->getErrorMessage());
		} else {
			$title = sprintf(_('Remove a custom field element in %s'), $ath->getName()) ;
			$ath->adminHeader(array('title'=>$title));

			echo '
				<h2>'.$title.'</h2>';

			?>
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="delete_opt" value="y" />
			<input type="hidden" name="id" value="<?php echo $ao->getID(); ?>" />
			<input type="hidden" name="boxid" value="<?php echo $boxid; ?>" />

			<p>
			<strong><?php echo _('Element') ?>:</strong>&nbsp;
			<?php echo $ao->getName(); ?></p>
			<p>
			<input type="checkbox" name="sure" value="1" /><?php echo _("I'm Sure.") ?><br />
			<input type="checkbox" name="really_sure" value="1" /><?php echo _("I'm Really Sure.") ?>
			</p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
			</form>
			<?php
			$ath->footer(array());
		}
	}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
