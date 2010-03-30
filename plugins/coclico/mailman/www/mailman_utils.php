<?php
/**
 * FusionForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003 (c) Guillaume Smet
 * Portions Copyright 2010 (c) Mélanie Le Bail
 *
 * @version   $Id$
 *
 */


require_once 'mailman/include/MailmanList.class.php';
require_once 'mailman/include/MailmanListFactory.class.php';

$current_user=UserManager::instance()->getCurrentUser();


function sendCreationMail($userEmail,&$list) {
 $message = sprintf(_('A mailing list will be created on %1$s in few minutes 
and you are the list administrator.

This list is: %3$s@%2$s .

Your mailing list info is at:
%4$s .

List administration can be found at:
%5$s .

Your list password is: %6$s .
You are encouraged to change this password as soon as possible.

Thank you for registering your project with %1$s.

-- the %1$s staff
'), $GLOBALS['sys_name'], $GLOBALS['sys_lists_host'], $list->getName(), $list->getExternalInfoUrl(), 'http://'.$GLOBALS['sys_lists_host'].'/mailman/admin/'.$this->getName(), $list->getPassword());
       $mailSubject = sprintf(_('%1$s New Mailing List'), $GLOBALS['sys_name']);




	$hdrs = "From: ".$GLOBALS['sys_email_admin'].$GLOBALS['sys_lf'];
	$hdrs .='Content-type: text/plain; charset=utf-8'.$GLOBALS['sys_lf'];

	mail ($userEmail,$mailSubject,$message,$hdrs);


}
function table_begin()
{

	echo "<table WIDTH=\"100%\" border=0>\n"."<TR><TD VALIGN=\"TOP\">\n"; 
}
function table_end()
{
	echo '</TD></TR></TABLE>';

}
function personalized_message()
{

	echo _('<p>Mailing lists provided via a GForge version of <a href="http://www.list.org/">GNU Mailman</a>. Thanks to the Mailman and <a href="http://www.python.org/">Python</a> crews for excellent software.</p>');
}
function display_list($currentList)
{
	$request =& HTTPRequest::instance();
	$current_user=UserManager::instance()->getCurrentUser();
	
	if($currentList->isPublic()!='9'){
		if ($currentList->isError()) {
			echo $currentList->getErrorMessage();
		} else {
			getIcon();
			echo '&nbsp;<b>'.$currentList->getName().'</b> [';
			if($currentList->getStatus() == '3') {
				echo	_('Not activated yet');
			} else {
				echo ' <A HREF="index.php?group_id='.$request->get('group_id').'&action=pipermail&id='.$currentList->getID().'">'._('Archives').'</A>';
				if(isLogged())
				{ 
					if ($currentList->isMonitoring()) {
						echo 	' | <a href="index.php?group_id='.$request->get('group_id').'&action=unsubscribe&id='.$currentList->getID().'">'._('Unsubscribe').' </a>';
						echo 	' | <a href="index.php?group_id='.$request->get('group_id').'&action=options&id='.$currentList->getID().'">'._('Preferences').'</a>';
					} else {
						echo 	' | <a href="index.php?group_id='.$request->get('group_id').'&action=subscribe&id='.$currentList->getID().'">'._('Subscribe').'</a>';
					}
					if ($currentList->getListAdminID() == $current_user->getID()){
						echo ' | <A HREF="index.php?group_id='. $request->get('group_id').'&action=admin&id='. $currentList->getID() .'">'._('Administrate').'</A> ';
					}
				}
			}
			echo ' ] <br>&nbsp;';
			echo htmlspecialchars($currentList->getDescription()).'<p>';

		}
	}

}
function display_list_admin($currentList)
{
	$request =& HTTPRequest::instance();
	$current_user=UserManager::instance()->getCurrentUser();
	if($currentList->isPublic()!='9'){
		if ($currentList->isError() ) {
			echo $currentList->getErrorMessage();
		} else
		{
			getIcon();
			echo '&nbsp;<b>'.$currentList->getName().'</b> [';
		}
		if($currentList->getStatus() == '3') {
			echo	_('Not activated yet');
		} else {

			echo ' <A HREF="index.php?group_id='.$request->get('group_id').'&change_status=1&group_list_id='.$currentList->getID().'">'._('Update').'</A>';
			echo '	| <a href="deletelist.php?group_id='.$currentList->Group->getID().'&id='.$currentList->getID().'">'. _('Delete').'</a>';

			if ($currentList->getListAdminID() == $current_user->getID()){
				echo ' | <A HREF="../index.php?group_id='. $request->get('group_id').'&action=admin&id='. $currentList->getID() .'">'._('Administrate from Mailman').'</A> ';
			}
		}

		echo ' ] <br>&nbsp;';
		echo htmlspecialchars($currentList->getDescription()).'<p>';
	}
}


function mailman_header($params) {
	global $group_id;
	$current_user=UserManager::instance()->getCurrentUser();
	$request =& HTTPRequest::instance();

	//required for site_project_header
	$params['group'] = $request->get('group_id');
	$params['toptab'] = 'mailman';

	site_project_header($params);
	echo '<P><B>';
	// admin link is only displayed if the user is a project administrator
	if ($current_user->isMember($request->get('group_id'),'A')) {
		if (isset($params['admin'])){
			echo '<A HREF="index.php?group_id='.$request->get('group_id').'">'._('Administration').'</A>';
		}
		else{
			echo '<A HREF="admin/index.php?group_id='.$request->get('group_id').'">'._('Administration').'</A>';
		}

	}
	if ($params['help']) {
		helpButton($params['help']);
	}

}

function mail_footer($params) {
	site_project_footer($params);
}


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
