<?php
/**
 * Forums Facility
 *
 * Copyright 1999-2001, Tim Perdue - Sourceforge
 * Copyright 2002, Tim Perdue - GForge, LLC
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'forum/ForumHTML.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/Forum.class.php';

$group_id = getIntFromRequest('group_id');
if ($group_id) {
	$g = group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$ff=new ForumFactory($g);
	if (!$ff || !is_object($ff) || $ff->isError()) {
		exit_error($ff->getErrorMessage(),'forums');
	}

	$farr = $ff->getForums();

	if ( $farr !== false && count($farr) == 1 ) {
        session_redirect('/forum/forum.php?forum_id='.$farr[0]->getID());
	}

	forum_header(array('title'=>sprintf(_('Forums for %1$s'), $g->getPublicName()) ));

	if ($ff->isError()) {
        echo '<div class="error">'. $ff->getErrorMessage().'</div>';
		forum_footer(array());
		exit;
    } else if ( count($farr) < 1) {
		echo '<div class="warning_msg">'.sprintf(_('No Forums Found for %1$s'), $g->getPublicName()) .'</div>';
		forum_footer(array());
		exit;
	}

//	echo _('<p>Choose a forum and you can browse, search, and post messages.<p>');

	echo $HTML->printsubMenu(array(_("My Monitored Forums")),array("/forum/myforums.php?group_id=$group_id"));

	plugin_hook ("blocks", "forum index");

	$tablearr=array(_('Forum'),_('Description'),_('Threads'),_('Posts'), _('Last Post'),_('Moderation Level'));
	echo $HTML->listTableTop($tablearr);

	/*
		Put the result set (list of forums for this group) into a column with folders
	*/

	for ($j = 0; $j < count($farr); $j++) {
		if (!is_object($farr[$j])) {
			//just skip it - this object should never have been placed here
		} elseif ($farr[$j]->isError()) {
			echo $farr[$j]->getErrorMessage();
		} else {
			switch ($farr[$j]->getModerationLevel()) {
				case 0 : $modlvl = _('No Moderation');break;
				case 1 : $modlvl = _('Anonymous & Non Project Users');break;
				case 2 : $modlvl = _('All Except Admins');break;
			}
			echo '<tr '. $HTML->boxGetAltRowStyle($j) . '><td>'.
				'<a href="'.util_make_uri('/forum/forum.php?forum_id='.$farr[$j]->getID().'&amp;group_id='.$group_id).'">'.
				html_image('ic/forum20w.png') .
				'&nbsp;' .
				$farr[$j]->getName() .'</a></td>
				<td>'.$farr[$j]->getDescription().'</td>
				<td style="text-align:center">'.$farr[$j]->getThreadCount().'</td>
				<td style="text-align:center">'. $farr[$j]->getMessageCount() .'</td>
				<td>'.  date(_('Y-m-d H:i'),$farr[$j]->getMostRecentDate()) .'</td>
				<td style="text-align:center">'. $modlvl  .'</td></tr>';
		}
	}
	echo $HTML->listTableBottom();

	forum_footer(array());

} else {

	exit_no_group();

}

?>
