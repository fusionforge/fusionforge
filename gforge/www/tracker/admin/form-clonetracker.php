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

				$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin','clone_tracker')));

				echo "<h1>".$Language->getText('tracker_admin','clone_tracker')."</h1>";

				?>
				<p><?php echo $Language->getText('tracker_admin','choose_tracker') ?></p>
				<p>
				<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
				<input type="hidden" name="clone_tracker" value="y" />
				<p><strong><?php echo $Language->getText('tracker_admin','clone_warning') ?></strong></p>
				<p><?php echo html_build_select_box_from_arrays($ids,$titles,'clone_id','',false); ?></p>
				<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
				</form></p>
				<?php
				$ath->footer(array());
			}
		}

?>
