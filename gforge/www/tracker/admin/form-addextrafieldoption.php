<?php

//
//  FORM TO ADD ELEMENTS TO EXTRA FIELD
//
	$boxid = getIntFromRequest('boxid');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		exit_error('Error','Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		exit_error('Error',$ac->getErrorMessage());
	} else {
		$efearr=$ath->getExtraFieldElements($boxid);
		$ath->adminHeader(array ('title'=>_('Manage Custom Fields')));

		echo "<h3>".$Language->getText('tracker_admin_build_boxes','opt_title',$ath->getName())."</h3>";
		echo "<br />";
		$rows=count($efearr);
		if ($rows > 0) {
			
			$title_arr=array();
			$title_arr[]=_('Elements Defined');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td><a href="'.getStringFromServer('PHP_SELF').'?update_opt=1&amp;id='.
					$efearr[$i]['element_id'].'&amp;boxid='.			
					$boxid.'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
					$efearr[$i]['element_name'].' ['._('Edit').']</a></td>';
			}		   
			echo $GLOBALS['HTML']->listTableBottom();

		} else { 
			echo "\n<h3>"._('You have not defined any elements')."</h3>";
		}
		?>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&boxid='.$boxid.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_opt" value="y" />
		<strong><?php echo _('Add New Element') ?>:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /> <br \>
		<!--
		Show a pop-up box to choose the possible statuses that this element will map to
		-->
		<?php if ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) { ?>
		<strong><?php echo _('Status'); ?></strong><br />
		<?php echo $ath->statusBox('status_id',1,false,false); ?>
		<?php } ?>
		<p>
		<span class="warning"><?php echo _('Once you add a new element, it cannot be deleted') ?></span></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo_('Submit') ?>" /></p>
		</form>
		</p>
		<?php
		$ath->footer(array());
	}
?>
