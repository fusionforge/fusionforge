<?php
/**
  *
  * SourceForge Forums Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('../forum/forum_utils.php');

if ($group_id) {

	forum_header(array('title'=>'Forums for '.group_getname($group_id),'pagename'=>'forum','sectionvals'=>array(group_getname($group_id))));

	if (user_isloggedin() && user_ismember($group_id)) {
		$public_flag='<3';
	} else {
		$public_flag='=1';
	}

	$sql="SELECT g.group_forum_id,g.forum_name, g.description, famc.count as total
		FROM forum_group_list g
		LEFT JOIN forum_agg_msg_count famc USING (group_forum_id)
		WHERE g.group_id='$group_id' AND g.is_public $public_flag;";

	$result = db_query ($sql);

	$rows = db_numrows($result); 

	if (!$result || $rows < 1) {
		echo '<H1>No forums found for '. group_getname($group_id) .'</H1>';
		echo db_error();
		forum_footer(array());
		exit;
	}

	echo $Language->getText('forum', 'choose');

	/*
		Put the result set (list of forums for this group) into a column with folders
	*/

	for ($j = 0; $j < $rows; $j++) { 
		echo '<A HREF="forum.php?forum_id='. db_result($result, $j, 'group_forum_id') .'">'.
			html_image("images/ic/cfolder15.png","15","13",array("BORDER"=>"0")) . 
			'&nbsp;' .
			db_result($result, $j, 'forum_name').'</A> ';
		//message count
		echo '('. ((db_result($result, $j, 'total'))?db_result($result, $j, 'total'):'0') .' msgs)';
		echo "<BR>\n";
		echo db_result($result,$j,'description').'<P>';
	}
	forum_footer(array());

} else {

	exit_no_group();

}

?>
