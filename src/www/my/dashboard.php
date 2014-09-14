<?php
/**
 * FusionForge User's Personal Page
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge Team
 * Copyright 2009, Jean-Pierre Fortune/Spirtech
 * Copyright 2009-2010, Roland Mas
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright (C) 2012-2013 Marc-Etienne Vargenau - Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
 * Copyright 2014, Stéphane-Eymeric Bredthauer
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/vote_function.php';
require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'tracker/ArtifactsForUser.class.php';
require_once $gfcommon.'pm/ProjectTasksForUser.class.php';

global $HTML; // Layout object

if (!session_loggedin()) {
	exit_not_logged_in();
} else {
	$u = session_get_user();
	site_user_header(array('title'=>sprintf(_('Personal Page for %s'), $u->getRealName())));
	echo html_ao('table', array('class' => 'fullwidth'));
	echo html_ao('tr');
	echo html_ao('td', array('class' => 'top'));
	echo $HTML->boxTop(_('All trackers for my projects'), false, false);
	// Include both groups and foundries; developers should be similarly
	// aware of membership in either.
	$projects = session_get_user()->getGroups();
	if (count ($projects) < 1) {
		echo $HTML->information(_("You're not a member of any active projects"));
	} else {
		$display_col=array('summary'=>1,
				'changed'=>1,
				'status'=>0,
				'priority'=>1,
				'assigned_to'=>1,
				'submitted_by'=>1,
				'related_tasks'=>1);

		$title_arr=array();

		$title_arr[]=_('Id');
		if ($display_col['summary']) {
			$title_arr[]=_('Summary');
		}
		if ($display_col['changed']) {
			$title_arr[]=_('Changed');
		}
		if ($display_col['status']) {
			$title_arr[]=_('Status');
		}
		if ($display_col['priority']) {
			$title_arr[]=_('Priority');
		}
		if ($display_col['assigned_to']) {
			$title_arr[]=_('Assigned to');
		}
		if ($display_col['submitted_by']) {
			$title_arr[]=_('Submitted by');
		}
		if ($display_col['related_tasks']) {
			$title_arr[]=_('Tasks');
		}

		echo $HTML->listTableTop($title_arr);

		foreach ($projects as $p) {
			$admin_flags = forge_check_perm('project_admin', $p->getID()) ;

			//  get the Project object
			//
			if (!$p->usesTracker()) {
				$cell_text = html_e('strong', array(), ' • '. util_make_link('/tracker/?group_id='.$p->getID(), $p->getPublicName()));
				$cell_attrs = array('colspan' => (array_sum($display_col)+1), 'align' => 'left', 'style' => 'background-color: #CADACA; padding-top: 4px; border-top: 1px dotted darkgreen; border-bottom: 1px solid darkgreen; font-size: larger; color: darkgreen;');
				$cell_data = array(array_merge((array)$cell_text, $cell_attrs));
				echo $HTML->multiTableRow(array(), $cell_data);
				$cell_text =  html_e('strong', array(), _('This project is not using the tracker feature.'));
				$cell_attrs = array('colspan' => (array_sum($display_col)+1), 'align' => 'left');
				$cell_data = array(array_merge((array)$cell_text, $cell_attrs));
				echo $HTML->multiTableRow(array(), $cell_data);

			} else {
				$atf = new ArtifactTypeFactory($p);
				$at_arr = $atf->getArtifactTypes();

				$art_found = 0;

				if(count($at_arr) > 0) {
					$cell_text = html_e('strong', array(), ' • '.util_make_link('/tracker/?group_id='.$p->getID(), $p->getPublicName()));
					$cell_attrs = array('colspan' => (array_sum($display_col)+1), 'align' => 'left', 'style' => 'background-color: #CADACA; padding-top: 4px; border-top: 1px dotted darkgreen; border-bottom: 1px solid darkgreen; font-size: larger; color: darkgreen;');
					$cell_data = array(array_merge((array)$cell_text, $cell_attrs));
					echo $HTML->multiTableRow(array(), $cell_data);
					foreach($at_arr as $at) {
						$art_found = 1;
						//
						//      Create the ArtifactType object
						//
						$ath = new ArtifactTypeHtml($p,$at->getID());
						// create artifact object, setup object
						$af = new ArtifactFactory($ath);
						$af->setup(0,"priority","DESC",0,"",0,1,null);
						// get artifacts from object
						$art_arr = $af->getArtifacts();
						if (count($art_arr) > 0) {
							$cell_text = html_e('strong', array(), ' · '. util_make_link ('/tracker/?group_id='.$at->Group->getID().'&atid='.$at->getID(), $at->getName()));
							$cell_attrs = array('colspan' => (array_sum($display_col)+1), 'align' => 'left', 'style' => 'color: darkred; border-bottom: 1px solid #A0A0C0; border-top: 1px dotted #A0A0C0; background-color: #CACADA;');
							$cell_data = array(array_merge((array)$cell_text, $cell_attrs));
							echo $HTML->multiTableRow(array(), $cell_data);
							$toggle=0;

							foreach($art_arr as $art) {
								$cell_data = array();
								$row_attrs = array('class' => $HTML->boxGetAltRowStyle($toggle++, true));
								$cell_attrs = array('class' => 'align-center');
								$cell_text = $art->getID();
								$cell_data [] = array_merge((array)$cell_text, $cell_attrs);
								if ($display_col['summary']) {
									$cell_attrs = array('class' => 'align-left');
									$cell_text = util_make_link('/tracker/?func=detail&aid='.$art->getID().'&group_id='.$p->getID().'&atid='.$ath->getID(), $art->getSummary());
									$cell_data [] = array_merge((array)$cell_text, $cell_attrs);
								}
								if ($display_col['changed']) {
									$cell_attrs = array('width' => '150');
									$cell_text = date(_('Y-m-d'),$art->getLastModifiedDate());
									$cell_data [] = array_merge((array)$cell_text, $cell_attrs);
								}
								if ($display_col['status']) {
									$cell_attrs = array();
									$cell_text = $art->getStatusName();
									$cell_data [] = array_merge((array)$cell_text, $cell_attrs);
								}
								if ($display_col['priority']) {
									$cell_attrs = array('class' => 'priority'.$art->getPriority(), 'align' => 'center');
									$cell_text = $art->getPriority();
									$cell_data [] = array_merge((array)$cell_text, $cell_attrs);
								}
								if ($display_col['assigned_to']) {
									$cell_attrs = array();
									$cell_text = $art->getAssignedRealName();
									$cell_data [] = array_merge((array)$cell_text, $cell_attrs);
								}
								if ($display_col['submitted_by']) {
									$cell_attrs = array();
									$cell_text = $art->getSubmittedRealName();
									$cell_data [] = array_merge((array)$cell_text, $cell_attrs);
								}
								if ($display_col['related_tasks']) {
									$result_tasks = $art->getRelatedTasks();
									if($result_tasks) {
										$cell_text ='';
										$taskcount = db_numrows($art->relatedtasks);
										if ($taskcount > 0) {
											for ($itask = 0; $itask < $taskcount; $itask++) {
												if($itask>0)
													$cell_text .= html_e('br');

												$taskinfo = db_fetch_array($art->relatedtasks, $itask);
												$taskid = $taskinfo['project_task_id'];
												$projectid = $taskinfo['group_project_id'];
												$groupid   = $taskinfo['group_id'];
												$g = group_get_object($groupid);
												$pg = new ProjectGroup($g, $projectid);
												$cell_text .= $pg->getName().html_e('br');
												$summary   = util_unconvert_htmlspecialchars($taskinfo['summary']);
												$cell_text .= util_make_link('/pm/task.php?func=detailtask&project_task_id='.$taskid.'&group_id='.$groupid.'&group_project_id='.$projectid, $summary);
											}
										}
										$cell_data [] = array_merge((array)$cell_text, $cell_attrs);
									}
								}
								echo $HTML->multiTableRow($row_attrs, $cell_data);
							}
						}
					}
					if (!$art_found) {
						$cell_text =  html_e('strong', array(), ' --');
						$cell_attrs = array('colspan' => (array_sum($display_col)+1), 'align' => 'left');
						$cell_data = array(array_merge((array)$cell_text, $cell_attrs));
						echo $HTML->multiTableRow(array(), $cell_data);
					}
				}
			}
		}
		echo $HTML->listTableBottom();
	}
	echo $HTML->boxBottom();
	echo html_ac(html_ap()-2);

// priority colors
/*
	echo html_ao('tr');
	echo html_ao('td', array('colspan' => '2'));
	show_priority_colors_key();
	echo html_ac(html_ap()-2);
*/
	echo html_ac(html_ap()-1);
	site_user_footer();
}
