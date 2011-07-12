<?php

/**
 * mailmanPlugin class
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Portions Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Portions Copyright 2010 (c) Mélanie Le Bail
 */

require_once 'plugins_utils.php';
require_once('common/system_event/SystemEvent.class.php');
require_once('common/event/Event.class.php');
require_once 'MailmanList.class.php';
require_once 'MailmanListFactory.class.php';
require_once 'MailmanListDao.class.php';
require_once 'common/dao/CodendiDataAccess.class.php';

require_once'mailman/include/events/SystemEvent_MAILMAN_LIST_CREATE.class.php';
require_once'mailman/include/events/SystemEvent_MAILMAN_LIST_DELETE.class.php';


class mailmanPlugin extends Plugin {
	public function __construct($id=0) {
		$this->Plugin($id);
		//$this->setScope(Plugin::SCOPE_PROJECT);
		$this->name = "mailman" ;
		$this->text = "Mailman" ; // To show in the tabs, use...
		$this->_addHook("user_personal_links");//to make a link to the user�s personal part of the plugin
		$this->_addHook("usermenu") ;
		$this->_addHook("groupmenu");	// To put into the project tabs
		$this->_addHook("groupisactivecheckbox") ; // The "use ..." checkbox in editgroupinfo
		$this->_addHook("groupisactivecheckboxpost") ; //
		$this->_addHook("userisactivecheckbox") ; // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost") ; //
		$this->_addHook("project_admin_plugins"); // to show up in the admin page fro group
		$this->_addHook("monitored_element"); // to show monitored items in my page
		$this->_addHook("group_delete"); // to delete mailing list associated when deleting a group
		$this->_addHook("group_approve"); // to create mailing list 'Commit' when creating a group
		$this->_addHook('site_admin_option_hook');
		$this->_addHook(Event::GET_SYSTEM_EVENT_CLASS,'getSystemEventClass', false);//to make SystemEvent manager knows about mailman plugin

	}

	function process() {
		echo '<h1>Mailman</h1>';
		echo $this->getPluginInfo()->getpropVal('answer');
	}

	function getPluginInfo() {
		if (!is_a($this->pluginInfo, 'MailmanPluginInfo')) {
			require_once('MailmanPluginInfo.class.php');
			$this->pluginInfo = new MailmanPluginInfo($this);
		}
		return $this->pluginInfo;
	}

	function CallHook ($hookname, &$params) {
		global $use_mailmanplugin,$G_SESSION,$HTML,$gfcommon,$gfwww,$gfplugins;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("mailman")) {
				$param = '?type=user&amp;id=' . $G_SESSION->getId() . "&amp;pluginname=" . $this->name; // we indicate the part we're calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
					array ('/plugins/mailman/index.php' . $param ));
			}
		} elseif ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$project = &group_get_object($group_id);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if (!$project->isProject()) {
				return;
			}
			if ( $project->usesPlugin ( $this->name ) ) {
				$params['TITLES'][]=$this->text;
				$params['DIRS'][]='/plugins/mailman/index.php?group_id=' . $group_id . "&amp;pluginname=" . $this->name; // we indicate the part we�re calling is the project one
                $params['ADMIN'][]='';
			}
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		} elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_mailmanplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ( $group->usesPlugin ( $this->name ) ) {
				echo "CHECKED";
			}
			echo "><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>Use ".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";

		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			$use_mailmanplugin = getStringFromRequest('use_mailmanplugin');
			if ( $use_mailmanplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "user_personal_links") {
			// this displays the link in the user�s profile page to it�s personal mailman (if you want other sto access it, youll have to change the permissions in the index.php
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>' ;
				echo util_make_link ("/plugins/mailman/index.php?id=$userid&amp;type=user&amp;pluginname=".$this->name,
									 _('View Personal mailman')
				);
				echo '</p>';
			}
		} elseif ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it�s  mailman administration
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);
			if ( $group->usesPlugin ( $this->name ) ) {
				echo '<p>'.util_make_link ("/plugins/projects_hierarchy/index.php?id=".$group->getID().'&amp;type=admin&amp;pluginname='.$this->name,
									 _('View the mailman Administration')).'</p>';
			}
		}
		elseif ($hookname == "monitored_element") {
			$current_user=UserManager::instance()->getCurrentUser();
			$last_group=0;
			$order_name_arr=array();
			$order_name_arr[]=_('Remove');
			$order_name_arr[]=_('Monitored Lists');
			echo $HTML->listTableTop($order_name_arr);
			$dao = new MailmanListDao(CodendiDataAccess::instance());
			$result = $dao->listsMonitoredByUser($current_user->getEmail());
			for ($i=0; $i<$result->rowCount(); $i++) {
				$listResult = $result->getRow();
				$list = new MailmanList($listResult['group_id'],$listResult['group_list_id']);
				if ($list->isError()) {
						$this->setError($list->getErrorMessage());
				} else {
						$lists[] =& $list;
				}
			}
			if (count($lists) < 1) {
				echo '<tr><td colspan="2"><strong>'._('You are not monitoring any lists.').'</strong></td></tr>';
			} else {
				echo '<tr><td colspan="2"><strong>'.util_make_link ('/plugins/mailman',_('My Monitored Lists')).'</strong></td></tr>';
				foreach ($lists as $l) {
					$group = $l->getGroup();
					if ($group->getID() != $last_group) {
						echo ' <tr '. $HTML->boxGetAltRowStyle(1) .'><td colspan="2">'.util_make_link ('/plugins/mailman/index.php?group_id='.$group->getID(),$group->getPublicName()).'</td></tr>';
					}
					echo '
						<tr '.$HTML->boxGetAltRowStyle(0).'><td class="align-center"><a href="/plugins/mailman/index.php?group_id='.$group->getID().
						'&amp;action=unsubscribe&amp;id='.$l->getID().'">' .
						'<img src="'.$HTML->imgroot.'/ic/trash.png" height="16" width="16" '.'border="0" alt="" /></a>' .
						'</td><td width="99%"><a href="/plugins/mailman/index.php?group_id='.$group->getID().'&amp;action=options&amp;id='.$l->getID().'">'.
						$l->getName().'"</a></td></tr>';
					$last_group= $group->getID();
				}
			}
			echo $HTML->listTableBottom();

		}
		elseif ($hookname == "group_delete") {
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);
			$mlf = new MailmanListFactory($group);
			$ml_arr =& $mlf->getMailmanLists();
			for ($i=0; $i<count($ml_arr); $i++) {
				if (!is_object($ml_arr[$i])) {
					printf (_("Not Object: MailmanList: %d"),$i);
					continue;
				}
				if (!$ml_arr[$i]->deleteList(1,1)) {
					$this->setError(_('Could not properly delete the mailing list'));
				}
				//echo 'MailmanListFactory'.db_error();
			}
		}
		elseif ($hookname == "group_approve") {
			$idadmin_group =$params[0];
			$group_id=$params[1];
			$group = &group_get_object($group_id);
			$mlist = new MailmanList($group);
			if (!$mlist->create('commits','Commits',1,$idadmin_group)) {
				$this->setError(sprintf(_('ML: %s'),$mlist->getErrorMessage()));
				db_rollback();
				return false;
			}
		}
		// TODO : WTF ? : I think this should probably be gotten rid of -- OlivierBerger
		elseif ($hookname=='site_admin_option_hook') {
			echo '<li><a href="'.$this->getPluginPath().'/">Template [' . _('Mailman plugin') . ']</a></li>';
		}



	}
	function getSystemEventClass($params) {

		switch($params['type']) {
			case 'MAILMAN_LIST_CREATE' :

				require_once(dirname(__FILE__).'/events/SystemEvent_MAILMAN_LIST_CREATE.class.php');

				$params['class'] = 'SystemEvent_MAILMAN_LIST_CREATE';
				break;
			case 'MAILMAN_LIST_DELETE' :
				require_once(dirname(__FILE__).'/events/SystemEvent_MAILMAN_LIST_DELETE.class.php');
				$params['class'] = 'SystemEvent_MAILMAN_LIST_DELETE';
				break;
			default:
				break;
		}

	}





}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
