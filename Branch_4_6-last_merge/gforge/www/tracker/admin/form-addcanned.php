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
			<h2>'.$Language->getText('tracker_admin_add_canned','existing_responses').':</h2>
			<p>&nbsp;</p>';
			$title_arr=array();
			$title_arr[]=$Language->getText('tracker_admin','tracker_id');
			$title_arr[]=$Language->getText('tracker_admin','tracker_title');

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
			echo "\n<h1>".$Language->getText('tracker_admin_add_canned','no_responses')."</h1>";
		}
		?>
		<p><?php echo $Language->getText('tracker_admin_add_canned','canned_response_info') ?></p>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_canned" value="y" />
		<strong><?php echo $Language->getText('tracker_admin_add_canned','canned_response_title') ?>:</strong><br />
		<input type="text" name="title" value="" size="50" maxlength="50" />
		<p>
		<strong><?php echo $Language->getText('tracker_admin_add_canned','canned_response_body') ?>:</strong><br />
		<textarea name="body" rows="30" cols="65" wrap="hard"></textarea></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

?>
