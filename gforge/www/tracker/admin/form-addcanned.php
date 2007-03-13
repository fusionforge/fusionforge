<?php
//
//  FORM TO ADD CANNED RESPONSES
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_add_canned','title',$ath->getName()).'Add/Change Canned Responses to: '.$ath->getName()));

		echo "<h1>".$Language->getText('tracker_admin_add_canned','title', $ath->getName())."</h1>";

		/*
			List of existing canned responses
		*/
		$result=$ath->getCannedResponses();
		$rows=db_numrows($result);
		echo "<p>&nbsp;</p>";

		if ($result && $rows > 0) {
			//code to show existing responses and link to update page
			echo '
			<h2>'._('Existing Responses').':</h2>
			<p>&nbsp;</p>';
			$title_arr=array();
			$title_arr[]=_('ID');
			$title_arr[]=_('Title');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td>'.db_result($result, $i, 'id').'</td>'.
					'<td><a href="'.getStringFromServer('PHP_SELF').'?update_canned=1&amp;id='.
						db_result($result, $i, 'id').'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						db_result($result, $i, 'title').'</a></td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo "\n<h1>"._('No responses set up in this group')."</h1>";
		}
		?>
		<p><?php echo _('Creating useful generic messages can save you a lot of time when handling common artifact requests.') ?></p>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_canned" value="y" />
		<strong><?php echo _('Title') ?>:</strong><br />
		<input type="text" name="title" value="" size="50" maxlength="50" />
		<p>
		<strong><?php echo _('Message Body') ?>:</strong><br />
		<textarea name="body" rows="30" cols="65" wrap="hard"></textarea></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

?>
