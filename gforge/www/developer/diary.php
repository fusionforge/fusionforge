<?php
/**
  *
  * SourceForge Developer's Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('vote_function.php');

if ($diary_user) {
	$user_obj=user_get_object($diary_user);
	if (!$user_obj || $user_obj->isError()) {
		exit_error('ERROR','User could not be found: '.$user_obj->getErrorMessage());
	}

	echo $HTML->header(array('title'=>'My Diary And Notes'));

	echo '
	<h2>Diary And Notes For: '. $user_obj->getRealName() .'</h2>
	<p>&nbsp;</p>

	<p>&nbsp;</p>';

	echo $HTML->boxTop('Existing Diary And Note Entries');

	if ($diary_id) {
		$sql="SELECT * FROM user_diary WHERE user_id='$diary_user' AND id='$diary_id' AND is_public=1";
		$res=db_query($sql);
		if (!$res || db_numrows($res) < 1) {
			echo '<tr><td colspan="2">Entry Not Found For This User.</td></tr>';
		} else {
			echo '<tr><td colspan="2"><strong>Date:</strong> '. date($sys_datefmt, db_result($res,$i,'date_posted')) .'<br />
			<strong>Subject:</strong> '. db_result($res,$i,'summary') .'<p>
			<strong>Body:</strong><br />
			'. nl2br(db_result($res,$i,'details')) .'
			</p></td></tr>';
		}
	}


	/*

		List all diary entries

	*/
	$sql="SELECT * FROM user_diary WHERE user_id='$diary_user' AND is_public=1 ORDER BY id DESC";

	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<tr><td colspan="2"><strong>This User Has No Diary Entries</strong></td></tr>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><a href="'. $PHP_SELF .'?diary_id='.
				db_result($result,$i,'id').'&amp;diary_user='. $diary_user .'">'.db_result($result,$i,'summary').'</a></td>'.
				'<td>'. date($sys_datefmt, db_result($result,$i,'date_posted')).'</td></tr>';
		}
		echo '
		<tr><td colspan="2" bgcolor="'.$HTML->COLOR_CONTENT_BACK.'">&nbsp;</td></tr>';
	}

	echo $HTML->boxBottom();

	echo $HTML->footer(array());

} else {

	exit_error('ERROR','No User Selected');

}

?>
