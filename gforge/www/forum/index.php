<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
    Message Forums
    By Tim Perdue, Sourceforge, 11/99

    Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

    Complete OO rewrite by Tim Perdue 12/2002
*/

require_once('pre.php');
require_once('www/forum/include/ForumHTML.class');
require_once('common/forum/ForumFactory.class');
require_once('common/forum/Forum.class');

if ($group_id) {
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$ff=new ForumFactory($g);
    if (!$ff || !is_object($ff) || $ff->isError()) {
        exit_error('Error',$ff->getErrorMessage());
    }

	forum_header(array('title'=>'Forums for '. $g->getPublicName() ,'pagename'=>'forum','sectionvals'=>array($g->getPublicName())));

	$farr =& $ff->getForums();
	
	if ($ff->isError() || count($farr) < 1) {
		echo '<H1>No forums found for '. $g->getPublicName() .'</H1>';
		echo $ff->getErrorMessage();
		forum_footer(array());
		exit;
	}

//	echo $Language->getText('forum', 'choose');

	$tablearr=array('Forum','Threads','Posts','Last Post');
	echo $HTML->listTableTop($tablearr);

	/*
		Put the result set (list of forums for this group) into a column with folders
	*/

	for ($j = 0; $j < count($farr); $j++) { 
		if ($farr[$j]->isError()) {
			echo $farr->getErrorMessage();
		} else {
			echo '<TR '. $HTML->boxGetAltRowStyle($j) . '><TD><A HREF="forum.php?forum_id='. $farr[$j]->getID() .'">'.
				html_image("ic/forum20w.png","20","20",array("BORDER"=>"0")) . 
				'&nbsp;' .
				$farr[$j]->getName() .'</A><BR>'.$farr[$j]->getDescription().'</TD>
				<TD ALIGN=CENTER>'.$farr[$j]->getThreadCount().'</TD>
				<TD ALIGN=CENTER>'. $farr[$j]->getMessageCount() .'</TD>
				<TD>'.  date($sys_datefmt,$farr[$j]->getMostRecentDate()) .'</TD></TR>';
		}
	}
	echo $HTML->listTableBottom();

	forum_footer(array());

} else {

	exit_no_group();

}

?>
