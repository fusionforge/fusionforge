<?php
/**
 * FusionForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003 (c) Guillaume Smet
 * Portions Copyright 2010 (c) Mélanie Le Bail
 * Copyright 2016, Franck Villaume - TrivialDev
 * http://fusionforge.org
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
require_once 'mailman/include/MailmanList.class.php';
require_once 'mailman/include/MailmanListFactory.class.php';

function sendCreationMail($userEmail,&$list) {
	$message = sprintf(_('A mailing list will be created on %s in few minutes and you are the list administrator.'), forge_get_config ('forge_name'))

			. sprintf(_('This list is: %1$s@%2$s'),  $list->getName(), forge_get_config('lists_host')) . "\n\n"

			. _('Your mailing list info is at:') . "\n"
			. $this->getExternalInfoUrl() . "\n\n"

			. _('List administration can be found at:') . "\n"
			. util_make_url('/mailman/admin/'.$list->getName()) . "\n\n"

			. _('Your list password is: ') . $list->getPassword(). "\n"
			. _('You are encouraged to change this password as soon as possible.') . "\n\n"

			. sprintf(_('Thank you for registering your project with %s.'), forge_get_config ('forge_name'))
			. "\n\n"

			. sprintf(_('-- the %s staff'), forge_get_config ('forge_name'))
			. "\n";

	$mailSubject = sprintf(_('%s New Mailing List'), forge_get_config ('forge_name'));

	$hdrs = "From: ".$GLOBALS['sys_email_admin'].$GLOBALS['sys_lf'];
	$hdrs .='Content-type: text/plain; charset=utf-8'.$GLOBALS['sys_lf'];

	mail ($userEmail,$mailSubject,$message,$hdrs);
}

function table_begin()
{

  echo "<table class='border' width='100%' border='0'>
            <tr class='boxtable'>
                <th class='forumml' width='15%'>"._('Mailing List')."</th>
                <th class='forumml' width='30%'>"._('Description')."</th>
                <th class='forumml' width='15%'>"._('Archives')."</th>";
  if (isLogged()) {
	  echo "<th class='forumml' width='10%'>"._('Subscription')."</th>";
	  echo "<th class='forumml' width='10%'>"._('Preferences')."</th>";
	  echo "<th class='forumml' width='10%'>"._('Administrate')."</th>";
  }

}
function table_begin_admin()
{

  echo "<table class='border' width='100%' border='0'>
            <tr class='boxtable'>
                <th class='forumml' width='15%'>"._('Mailing List')."</th>
                <th class='forumml' width='20%'>"._('Description')."</th>
                <th class='forumml' width='15%'>"._('Update')."</th>";
	  echo "<th class='forumml' width='10%'>"._('Delete')."</th>";
	  echo "<th class='forumml' width='10%'>"._('Administrate')."</th>";

}

function table_end()
{
	//	echo '</TD></TR></TABLE>';
	echo '</table>';
}
function display_list($currentList) {
	global $class, $HTML;
	$plugin_manager =& PluginManager::instance();
	$p =& $plugin_manager->getPluginByName('mailman');
	$request =& HTTPRequest::instance();

	if($currentList->isPublic()!='9'){
		if ($currentList->isError()) {
			echo $currentList->getErrorMessage();
		} else {
			if ($class=="boxitem bgcolor-white") {
				$class="boxitemalt bgcolor-grey";
			}
			else {
				$class = "boxitem bgcolor-white";
			}
			echo "<tr class='".$class."'>";
			echo '<td>';
			if ($currentList->isMonitoring()) {
				echo '<img src ="'.$p->getThemePath().'/images/ic/tick.png"'.' title="You are monitoring this list">';
			}
			echo $currentList->getName().'</td> ';
			echo '<td>';
			if($currentList->getStatus() == '1') {
				if($currentList->activationRequested()){
					echo	_('Not activated yet');
				}
				else{
					echo _('Error during creation');
				}
				echo '</td><td></td>';
				if (isLogged()) {
					echo '<td></td><td></td><td></td>';
				}
			} else {
				echo htmlspecialchars($currentList->getDescription()).'</td>';
				$archives =' <a href="index.php?group_id='.$request->get('group_id').'&amp;action=pipermail&amp;id='.$currentList->getID().'">'._('Archives').'</a>';
				plugin_hook('browse_archives', array('html' => &$archives, 'group_list_id' => $currentList->getID()));
				echo '<td>'.$archives.'</td>';
				if(isLogged())
				{
					echo '<td>';
					if ($currentList->isMonitoring()) {
						echo ' <a href="index.php?group_id='.$request->get('group_id').'&action=unsubscribe&id='.$currentList->getID().'"><img src="'.$p->getThemePath().'/images/ic/delete.png" title='._('Unsubscribe').'>';
						echo ' </a></td>';
						echo 	' <td> <a href="index.php?group_id='.$request->get('group_id').'&action=options&id='.$currentList->getID().'">'._('Preferences').'</a></td>';
					} else {
						echo 	'  <a href="index.php?group_id='.$request->get('group_id').'&action=subscribe&id='.$currentList->getID().'">'.$HTML->getNewPic(_('Subscribe'));
						;
						echo  '</a></td> <td></td>';
					}
					if ($currentList->getListAdminID() == user_getid()){
						echo ' <td> <a href="index.php?group_id='. $request->get('group_id').'&amp;action=admin&amp;id='. $currentList->getID() .'">'._('Administrate').'</a> ';
					}
					else { echo '<td>';}
				}
			}
			echo '</td></tr>';

		}
	}
}

function display_list_admin($currentList) {
	global $class;
	$request =& HTTPRequest::instance();
	if($currentList->isPublic()!='9'){
		if ($currentList->isError() ) {
			echo $currentList->getErrorMessage();
		} else
		{
			if ($class=="boxitem bgcolor-white") {
				$class="boxitemalt bgcolor-grey";
			}
			else {
				$class = "boxitem bgcolor-white";
			}

			echo "<tr class='".$class."'>";
			echo '<td>'.$currentList->getName().'</td> ';
		}
		if($currentList->getStatus() == '1') {
			if($currentList->activationRequested()){
				echo	'<td>'._('Not activated yet').'</td>';
			}
			else{
				echo '<td>'._('Error during creation').'  <a href="/plugins/mailman/admin/index.php?group_id='.$request->get('group_id').'&amp;action=recreate&amp;group_list_id='.$currentList->getID().'">'._('Re-create').'</a></td>';
			}
			echo '<td></td><td></td><td></td>';
		} else {

			echo '<td>'.htmlspecialchars($currentList->getDescription()).'</td>';
			echo '<td> <a href="index.php?group_id='.$request->get('group_id').'&amp;change_status=1&amp;group_list_id='.$currentList->getID().'">'._('Update').'</a></td>';
			echo '<td> <a href="deletelist.php?group_id='.$currentList->Group->getID().'&id='.$currentList->getID().'">'. _('Delete').'</td>';

			if ($currentList->getListAdminID() == user_getid()){
				echo ' <td> <a href="../index.php?group_id='. $request->get('group_id').'&amp;action=admin&amp;id='. $currentList->getID() .'">'._('Administrate from Mailman').'</a></td> ';
			}
		}

		echo ' </tr>';
	}
}

function mailman_header($params) {
	global $group_id;
	$current_user = session_get_user();
	$request =& HTTPRequest::instance();

	//required for site_project_header
	$params['group'] = $request->get('group_id');
	$params['toptab'] = 'mailman';

	site_project_header($params);
	echo '<p><b>';
	// admin link is only displayed if the user is a project administrator
	if ($current_user->isMember($request->get('group_id'),'A')) {
		if (isset($params['admin'])){
			echo '<a href="index.php?group_id='.$request->get('group_id').'">'._('Administration').'</a>';
		}
		else{
			echo '<a href="admin/index.php?group_id='.$request->get('group_id').'">'._('Administration').'</a>';
		}

	}
	if ($params['help']) {
		helpButton($params['help']);
	}
	echo "</b></p>\n";
}

function mail_footer($params = array()) {
	site_project_footer($params);
}
