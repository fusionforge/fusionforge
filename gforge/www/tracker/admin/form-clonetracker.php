<?php
//
//	FORM TO CLONE A TRACKER
//
		$g =& group_get_object($sys_template_group);
		if (!$g || !is_object($g)) {
			exit_error('Error','Unable to Create Template Group Object');
		} elseif ($g->isError()) {
			exit_error('Error',$g->getErrorMessage());
		} else {
			$atf = new ArtifactTypeFactory($g);
			if (!$atf || !is_object($atf)) {
				exit_error('Error','Unable to Create Template Group Object');
			} elseif ($atf->isError()) {
				exit_error('Error',$atf->atfetErrorMessaatfe());
			} else {
				$ata =& $atf->getArtifactTypes();
				for ($i=0; $i<count($ata); $i++) {
					if (!$ata[$i] || $ata[$i]->isError()) {
//skip it
					} else {
						$ids[]=$ata[$i]->getID();
						$titles[]=$g->getPublicName().'::'.$ata[$i]->getName();
					}
				}

				$ath->adminHeader(array ('title'=>_('Clone Tracker')));

				echo "<h1>"._('Clone Tracker')."</h1>";

				?>
				<p><?php echo _('Choose the template tracker to clone. The site administrator will have to set up trackers with default values and set permissions properly so you can access them.') ?></p>
				<p>
				<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
				<input type="hidden" name="clone_tracker" value="y" />
				<p><strong><?php echo _('WARNING!!! Cloning this tracker will duplicate all the fields and all the elements from those fields into this tracker. There is nothing to prevent you from cloning multiple times or making a huge mess. You have been warned!') ?></strong></p>
				<p><?php echo html_build_select_box_from_arrays($ids,$titles,'clone_id','',false); ?></p>
				<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
				</form></p>
				<?php
				$ath->footer(array());
			}
		}

?>
