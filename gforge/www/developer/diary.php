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
	<H2>Diary And Notes For: '. $user_obj->getRealName() .'</H2>
	<P>

	<P>';

	echo $HTML->box1_top('Existing Diary And Note Entries');

	if ($diary_id) {
		$sql="SELECT * FROM user_diary WHERE user_id='$diary_user' AND id='$diary_id' AND is_public=1";
		$res=db_query($sql);
		if (!$res || db_numrows($res) < 1) {
			echo '<TR><TD COLSPAN=2>Entry Not Found For This User.</TD></TR>';
		} else {
			echo '<TR><TD COLSPAN=2><B>Date:</B> '. date($sys_datefmt, db_result($res,$i,'date_posted')) .'<BR>
			<B>Subject:</B> '. db_result($res,$i,'summary') .'<P>
			<B>Body:</B><BR>
			'. nl2br(db_result($res,$i,'details')) .'
			</TD></TR>';
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
			<TR><TD COLSPAN=2><B>This User Has No Diary Entries</B></TD></TR>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo '
			<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD><A HREF="'. $PHP_SELF .'?diary_id='.
				db_result($result,$i,'id').'&diary_user='. $diary_user .'">'.db_result($result,$i,'summary').'</A></TD>'.
				'<TD>'. date($sys_datefmt, db_result($result,$i,'date_posted')).'</TD></TR>';
		}
		echo '
		<TR><TD COLSPAN="2" BGCOLOR="'.$HTML->COLOR_CONTENT_BACK.'">&nbsp;</TD></TR>';
	}

	echo $HTML->box1_bottom();

	echo $HTML->footer(array());

} else {

	exit_error('ERROR','No User Selected');

}

?>
