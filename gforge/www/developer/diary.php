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

	echo $HTML->header(array('title'=>$Language->getText('my_diary','title')));

	echo '
	<h2>'.$Language->getText('developer','diary_and_notes').': '. $user_obj->getRealName() .'</h2>
	<p>&nbsp;</p>

	<p>&nbsp;</p>';

	echo $HTML->boxTop($Language->getText('my_diary','existing_entries'));

	if ($diary_id) {
		$sql="SELECT * FROM user_diary WHERE user_id='$diary_user' AND id='$diary_id' AND is_public=1";
		$res=db_query($sql);
		if (!$res || db_numrows($res) < 1) {
			echo '<tr><td colspan="2">'.$Language->getText('developer','entry_not_found').'.</td></tr>';
		} else {
			echo '<tr><td colspan="2"><strong>'.$Language->getText('developer','date').':</strong> '. date($sys_datefmt, db_result($res,$i,'date_posted')) .'<br />
			<strong>'.$Language->getText('developer','subject').':</strong> '. db_result($res,$i,'summary') .'<p>
			<strong>'.$Language->getText('developer','body').':</strong><br />
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
			<tr><td colspan="2"><strong>'.$Language->getText('developer','no_entries').'</strong></td></tr>';
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

	exit_error($Language->getText('general','error'),$Language->getText('developer','no_user_selected'));

}

?>
