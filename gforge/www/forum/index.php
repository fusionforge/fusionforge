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
        exit_error($Language->getText('general','error'),$ff->getErrorMessage());
    }

	forum_header(array('title'=>$Language->getText('forum','forums_for', array($g->getPublicName())) ,'pagename'=>'forum','sectionvals'=>array($g->getPublicName())));

	$farr =& $ff->getForums();

	if ($ff->isError() || count($farr) < 1) {
		echo '<h1>'.$Language->getText('forum','error_no_forums_found', array($g->getPublicName())) .'</h1>';
		echo $ff->getErrorMessage();
		forum_footer(array());
		exit;
	}

//	echo $Language->getText('forum', 'choose');

	$tablearr=array($Language->getText('forum_forum','forum'),$Language->getText('forum_forum','threads'),$Language->getText('forum_forum','posts'), $Language->getText('forum_forum','lastpost'));
	echo $HTML->listTableTop($tablearr);

	/*
		Put the result set (list of forums for this group) into a column with folders
	*/

	for ($j = 0; $j < count($farr); $j++) {
		if ($farr[$j]->isError()) {
			echo $farr->getErrorMessage();
		} else {
			echo '<tr '. $HTML->boxGetAltRowStyle($j) . '><td><a href="forum.php?forum_id='. $farr[$j]->getID() .'">'.
				html_image("ic/forum20w.png","20","20",array("border"=>"0")) .
				'&nbsp;' .
				$farr[$j]->getName() .'</a><br />'.$farr[$j]->getDescription().'</td>
				<td align="center">'.$farr[$j]->getThreadCount().'</td>
				<td align="center">'. $farr[$j]->getMessageCount() .'</td>
				<td>'.  date($sys_datefmt,$farr[$j]->getMostRecentDate()) .'</td></tr>';
		}
	}
	echo $HTML->listTableBottom();

	forum_footer(array());

} else {

	exit_no_group();

}

?>
