<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/*
	Message Forums
	By Tim Perdue, Sourceforge, 11/99

	Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

	Complete OO rewrite by Tim Perdue 12/2002
*/

require_once('common/include/Error.class.php');
require_once('common/forum/ForumMessage.class.php');
// This string is used when sending the notification mail for identifying the
// user response
define('FORUM_MAIL_MARKER', '#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+');	

class Forum extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $data_array.
	 */
	var $data_array;

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group; //group object

	/**
	 * An array of 'types' for this forum - nested, flat, ultimate, etc.
	 *
	 * @var	 array	view_types.
	 */
	var $view_types;

	var $current_user_perm;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this forum is associated.
	 *  @param  int	 The group_forum_id.
	 *  @param  array	The associative array of data.
	 *	@return	boolean	success.
	 */
	function Forum(&$Group, $group_forum_id=false, $arr=false) {
		global $Language;
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError(sprintf(_('%1$s:: No Valid Group Object'), "Forum"));
			return false;
		}
		if ($Group->isError()) {
			$this->setError('Forum:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($group_forum_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($group_forum_id)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError(_('Group_id in db result does not match Group Object'));
					$this->data_array = null;
					return false;
				}
			}
			//
			//	Make sure they can even access this object
			//
			if (!$this->userCanView()) {
				$this->setPermissionDeniedError();
				$this->data_array = null;
				return false;
			}
		}
		$this->view_types[]='ultimate';
		$this->view_types[]='flat';
		$this->view_types[]='nested';
		$this->view_types[]='threaded';
		return true;
	}

	/**
	 *	create - use this function to create a new entry in the database.
	 *
	 *	@param	string	The name of the forum.
	 *	@param	string	The description of the forum.
	 *	@param	int	Pass (1) if it should be public (0) for private.
	 *	@param	string	The email address to send all new posts to.
	 *	@param	int	Pass (1) if a welcome message should be created (0) for no welcome message.
	 *	@param	int	Pass (1) if we should allow non-logged-in users to post (0) for mandatory login.
	 *	@param	int Pass (0) if the messages that are posted in the forum should go to moderation before available. 0-> no moderation 1-> moderation for anonymous and non-project members 2-> moderation for everyone
	 *	@return	boolean	success.
	 */
	function create($forum_name,$description,$is_public=1,$send_all_posts_to='',$create_default_message=1,$allow_anonymous=1,$moderation_level=0) {
		global $Language;
		if (strlen($forum_name) < 3) {
			$this->setError(_('Forum Name Must Be At Least 3 Characters'));
			return false;
		}
		if (strlen($description) < 10) {
			$this->setError(_('Forum Description Must Be At Least 10 Characters'));
			return false;
		}
		if (eregi('[^_\.0-9a-z-]',$forum_name)) {
			$this->setError(_('Illegal Characters in Forum Name'));
			return false;
		}
		if ($send_all_posts_to) {
			$invalid_mails = validate_emails($send_all_posts_to);
			if (count($invalid_mails) > 0) {
				$this->setInvalidEmailError();
				return false;
			}
		}

		$project_name = $this->Group->getUnixName();
		$result_list_samename = db_query('SELECT 1 FROM mail_group_list WHERE list_name = \''.$project_name.'-'.$forum_name.'\' AND group_id='.$this->Group->getID().''); 

		if (db_numrows($result_list_samename) > 0){
			$this->setError(_('Mailing List Exists with same name'));	
			return false;
		}


		// This is a hack to allow non-site-wide-admins to post
		// news.  The news/submit.php checks for proper permissions.
		// This needs to be revisited.
		global $sys_news_group;
		if ($this->Group->getID() == $sys_news_group) {
			// Future check will be added.

		} else {
			// Current permissions check.

			$perm =& $this->Group->getPermission( session_get_user() );

			if (!$perm || !is_object($perm) || !$perm->isForumAdmin()) {
				$this->setPermissionDeniedError();
				return false;
			}
		}

		$sql="INSERT INTO forum_group_list (group_id,forum_name,is_public,description,send_all_posts_to,allow_anonymous,moderation_level)
			VALUES ('".$this->Group->getId()."',
			'". strtolower($forum_name) ."',
			'$is_public',
			'". htmlspecialchars($description) ."',
			'$send_all_posts_to',
			'$allow_anonymous','$moderation_level')";

		db_begin();
		$result=db_query($sql);
		if (!$result) {
			db_rollback();
			$this->setError(_('Error Adding Forum').db_error());
			return false;
		}
		$this->group_forum_id=db_insertid($result,'forum_group_list','group_forum_id');
		$this->fetchData($this->group_forum_id);
		if ($create_default_message) {
			$fm=new ForumMessage($this);
			if (!$fm->create("Welcome to ".$forum_name,"Welcome to ".$forum_name)) {
				$this->setError($fm->getErrorMessage());
				return false;
			}
		}
		db_commit();
		return true;
	}

	/**
	 *  fetchData - re-fetch the data for this forum from the database.
	 *
	 *  @param  int	 The forum_id.
	 *	@return	boolean	success.
	 */
	function fetchData($group_forum_id) {
		global $Language, $sys_database_type;

		if ($sys_database_type == "mysql") {
			$sql="
				SELECT fgl.*,
					(SELECT count(*) AS `count`
						FROM (
							SELECT DISTINCT group_forum_id, thread_id FROM forum
						) AS tmp
						WHERE tmp.group_forum_id = fgl.group_forum_id
					) AS threads 
				FROM forum_group_list_vw AS fgl
				WHERE group_forum_id='$group_forum_id'";
		} else {
			$sql="SELECT * FROM forum_group_list_vw
				WHERE group_forum_id='$group_forum_id'";
		}
		$res=db_query($sql);
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Invalid forum group identifier'));
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getGroup - get the Group object this ArtifactType is associated with.
	 *
	 *	@return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getID - The id of this forum.
	 *
	 *	@return	int	The group_forum_id #.
	 */
	function getID() {
		return $this->data_array['group_forum_id'];
	}

	/**
	 *	getNextThreadID - The next thread_id for a new top in this forum.
	 *
	 *	@return	int	The next thread_id #.
	 */
	function getNextThreadID() {
		global $sys_database_type;

		if ($sys_database_type == "mysql") {
			$sql="call newval('forum_thread_seq', @res)";
			$result=db_mquery($sql);
			if (!$result) {
				echo db_error();
				return false;
			}
			$sql="select @res";
		} else {
			$sql="SELECT nextval('forum_thread_seq')";
		}
		$result=db_query($sql);
		if (!$result || db_numrows($result) < 1) {
			echo db_error();
			return false;
		}
		return db_result($result,0,0);
	}

	/**
	 * getUnixName - returns the name used by email gateway
	 *
	 * @return string unix name
	 */
	function getUnixName() {
		return $this->Group->getUnixName().'-'.$this->getName();
	}

	/**
	 *	getSavedDate - The unix time when the person last hit "save my place".
	 *
	 *	@return	int	The unix time.
	 */
	function getSavedDate() {
		if (@$this->save_date) {
			return $this->save_date;
		} else {
			if (session_loggedin()) {
				$sql="SELECT save_date FROM forum_saved_place
					WHERE user_id='".user_getid()."' AND forum_id='". $this->getID() ."';";
				$result = db_query($sql);
				if ($result && db_numrows($result) > 0) {
					$this->save_date=db_result($result,0,'save_date');
					return $this->save_date;
				} else {
					//highlight new messages from the past week only
					$this->save_date=(time()-604800);
					return $this->save_date;
				}
			} else {
				//highlight new messages from the past week only
				$this->save_date=(time()-604800);
				return $this->save_date;
			}
		}
	}

	/**
	 *	allowAnonymous - does this forum allow non-logged in users to post.
	 *
	 *	@return boolean	allow_anonymous.
	 */
	function allowAnonymous() {
		return $this->data_array['allow_anonymous'];
	}

	/**
	 *	isPublic - Is this forum open to the general public.
	 *
	 *	@return boolean	is_public.
	 */
	function isPublic() {
		return $this->data_array['is_public'];
	}

	/**
	 *	getName - get the name of this forum.
	 *
	 *	@return string	The name of this forum.
	 */
	function getName() {
		return $this->data_array['forum_name'];
	}

	/**
	 *	getSendAllPostsTo - an optional email address to send all forum posts to.
	 *
	 *	@return string	The email address.
	 */
	function getSendAllPostsTo() {
		return $this->data_array['send_all_posts_to'];
	}

	/**
	 *	getDescription - the description of this forum.
	 *
	 *	@return string	The description.
	 */
	function getDescription() {
		return $this->data_array['description'];
	}
	
	/**
	 *	getModerationLevel - the moderation level of the forum
	 *
	 *	@return int	The moderation level.
	 */
	function getModerationLevel() {
		return $this->data_array['moderation_level'];
	}

	/**
	 *	getMessageCount - the total number of messages in this forum.
	 *
	 *	@return int	The count.
	 */
	function getMessageCount() {
		return $this->data_array['total'];
	}

	/**
	 *	getThreadCount - the total number of threads in this forum.
	 *
	 *	@return int	The count.
	 */
	function getThreadCount() {
		return $this->data_array['threads'];
	}

	/**
	 *	getMostRecentDate - the most recent date of a post to this board.
	 *
	 *	@return int	The most recent date.
	 */
	function getMostRecentDate() {
		return $this->data_array['recent'];
	}

	/**
	 *	getMonitoringIDs - return an array of user_id's for those monitoring this forum.
	 *
	 *	@return	array	The array of user_id's.
	 */
	function getMonitoringIDs() {
		$sql="SELECT user_id FROM forum_monitored_forums WHERE forum_id='".$this->getID()."'";
		$result=db_query($sql);
		return util_result_column_to_array($result);
	}
	
	/**
	 *	getForumAdminIDs - return an array of user_id's for those users which are forum admins.
	 *
	 *	@return	array 	The array of user_id's.
	 */
	function getForumAdminIDs() {
		$sql = "SELECT user_group.user_id
                        FROM user_group, role_setting
                        WHERE role_setting.section_name='forum'
                          AND role_setting.ref_id='".$this->getID()."'
                          AND role_setting.value > 1
                          AND user_group.role_id = role_setting.role_id";
		$result = db_query($sql);
		return util_result_column_to_array($result);
	}
	
	/**
	 * getReturnEmailAddress() - return the return email address for notification emails
	 *
	 * @return string return email address
	 */
	function getReturnEmailAddress() {
		global $sys_default_domain, $sys_use_gateways;
		$address = '';
		if($sys_use_gateways) {
			$address .= $this->getUnixName();
		} else {
			$address .= 'noreply';
		}
		$address .= '@';
		if($sys_use_gateways && isset($GLOBALS['sys_forum_return_domain'])) {
			$address .= $GLOBALS['sys_forum_return_domain'];
		} else {
			$address .= $sys_default_domain;
		}
		return $address;
	}

	/**
	 *	setMonitor - Add the current user to the list of people monitoring the forum.
	 *
	 *	@return	boolean	success.
	 */
	function setMonitor() {
		global $Language;
		if (!session_loggedin()) {
			$this->setError(_('You can only monitor if you are logged in'));
			return false;
		}
		$sql="SELECT * FROM forum_monitored_forums
			WHERE user_id='".user_getid()."' AND forum_id='".$this->getID()."';";
		$result = db_query($sql);

		if (!$result || db_numrows($result) < 1) {
			/*
				User is not already monitoring thread, so
				insert a row so monitoring can begin
			*/
			$sql="INSERT INTO forum_monitored_forums (forum_id,user_id)
				VALUES ('".$this->getID()."','".user_getid()."')";

			$result = db_query($sql);

			if (!$result) {
				$this->setError(_('Unable To Add Monitor').' : '.db_error());
				return false;
			}

		}
		return true;
	}

	/**
	 *	stopMonitor - Remove the current user from the list of people monitoring the forum.
	 *
	 *	@return	boolean	success.
	 */
	function stopMonitor() {
		global $Language;
		if (!session_loggedin()) {
			$this->setError(_('You can only monitor if you are logged in'));
			return false;
		}
		$sql="DELETE FROM forum_monitored_forums
			WHERE user_id='".user_getid()."' AND forum_id='".$this->getID()."';";
		return db_query($sql);
	}

	/**
	 *	isMonitoring - See if the current user is in the list of people monitoring the forum.
	 *
	 *	@return	boolean	is_monitoring.
	 */
	function isMonitoring() {
		if (!session_loggedin()) {
			return false;
		}
		$sql="SELECT count(*) AS count FROM forum_monitored_forums WHERE user_id='".user_getid()."' AND forum_id='".$this->getID()."';";
		$result = db_query($sql);
		$row_count = db_fetch_array($result);
		return $result && $row_count['count'] > 0;
	}

	/**
	 *	savePlace - set a unix time into the database for this user, so future messages can be highlighted.
	 *
	 *	@return	boolean	success.
	 */
	function savePlace() {
		global $Language;
		if (!session_loggedin()) {
			$this->setError(_('You Can Only Save Your Place If You Are Logged In'));
			return false;
		}
		$sql="SELECT * FROM forum_saved_place
			WHERE user_id='".user_getid()."' AND forum_id='".$this->getID()."'";

		$result = db_query($sql);

		if (!$result || db_numrows($result) < 1) {
			/*
				User is not already monitoring thread, so
				insert a row so monitoring can begin
			*/
			$sql="INSERT INTO forum_saved_place (forum_id,user_id,save_date)
				VALUES ('".$this->getID()."','".user_getid()."','".time()."')";

			$result = db_query($sql);

			if (!$result) {
				$this->setError(_('Forum::savePlace()').': '.db_error());
				return false;
			}

		} else {
			$sql="UPDATE forum_saved_place
				SET save_date='".time()."'
				WHERE user_id='".user_getid()."' AND forum_id='".$this->getID()."'";
			$result = db_query($sql);

			if (!$result) {
				$this->setError('Forum::savePlace() '.db_error());
				return false;
			}
		}
		return true;
	}

	/**
	 *	update - use this function to update an entry in the database.
	 *
	 *	@param	string	The name of the forum.
	 *	@param	string	The description of the forum.
	 *	@param	int		if it should be public (0) for private.
	 *	@param	int	 	if we should allow non-logged-in users to post (0) for mandatory login.
	 *	@param	string	The email address to send all new posts to.
	 *	@param	int		if the messages that are posted in the forum should go to moderation before available. 0-> no moderation 1-> moderation for anonymous and non-project members 2-> moderation for everyone
	 *	@return	boolean	success.
	 */
	function update($forum_name,$description,$allow_anonymous,$is_public,$send_all_posts_to='',$moderation_level=0) {
		global $Language;
		if (strlen($forum_name) < 3) {
			$this->setError(_('Forum Name Must Be At Least 3 Characters'));
			return false;
		}
		if (strlen($description) < 10) {
			$this->setError(_('Forum Description Must Be At Least 10 Characters'));
			return false;
		}
		if (eregi('[^_\.0-9a-z-]',$forum_name)) {
			$this->setError(_('Illegal Characters in Forum Name'));
			return false;
		}
		if ($send_all_posts_to) {
			$invalid_mails = validate_emails($send_all_posts_to);
			if (count($invalid_mails) > 0) {
				$this->setInvalidEmailError();
				return false;
			}
		}

		if (!$this->userIsAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		}

		$res=db_query("UPDATE forum_group_list SET
			forum_name='". strtolower($forum_name) ."',
			description='". htmlspecialchars($description) ."',
			send_all_posts_to='".$send_all_posts_to ."',
			allow_anonymous='" .$allow_anonymous . "',
			moderation_level='" .$moderation_level . "',
			is_public='" .$is_public . "'
			WHERE group_id='".$this->Group->getID()."'
			AND group_forum_id='".$this->getID()."'");

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Error On Update:').': '.db_error());
			return false;
		}
		return true;
	}

	/**
	 *  delete - delete this forum and all its related data.
	 *
	 *  @param  bool	I'm Sure.
	 *  @param  bool	I'm REALLY sure.
	 *  @return   bool true/false;
	 */
	function delete($sure, $really_sure) {
		if (!$sure || !$really_sure) {
			$this->setMissingParamsError();
			return false;
		}
		if (!$this->userIsAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		}
		db_begin();
		db_query("DELETE FROM forum_agg_msg_count
			WHERE group_forum_id='".$this->getID()."'");
//echo '1'.db_error();
		db_query("DELETE FROM forum_monitored_forums
			WHERE forum_id='".$this->getID()."'");
//echo '2'.db_error();
		db_query("DELETE FROM forum_saved_place
			WHERE forum_id='".$this->getID()."'");
//echo '3'.db_error();
		$res = db_query("SELECT msg_id from forum where group_forum_id='".$this->getID()."'");//get the messages for this forum, to delete its attachments
		$delete_ids = array();
		for ($i=0;$i<db_numrows($res);$i++) {
			$aux = db_fetch_array($res);
			$delete_ids[] = $aux[0];
		}
		foreach ($delete_ids as $id) {
			db_query("DELETE FROM forum_attachment where msg_id='$id'");
		}
		
		db_query("DELETE FROM forum
			WHERE group_forum_id='".$this->getID()."'");
//echo '4'.db_error();
		db_query("DELETE FROM forum_group_list
			WHERE group_forum_id='".$this->getID()."'");
//echo '5'.db_error();
		db_commit();
		return true;
	}

	/*

		USER PERMISSION FUNCTIONS

	*/

	/**
	 *  userCanView - determine if the user can view this forum.
	 *
	 *  @return boolean   user_can_view.
	 */
	function userCanView() {
		if ($this->isPublic()) {
			return true;
		} else {
			if (!session_loggedin()) {
				return false;
			} else {
				//
				//  You must have a role in the project if this forum is not public
				//
				if ($this->getCurrentUserPerm() >= 0) {
					return true;
				} else {
					return false;
				}
			}
		}
	}

	/**
	 *  userIsModLvl1 - see if the user goes into the moderated level 1 category
	 *
	 *  @return boolean user_is_mod_lvl1
	 */
	function userIsModLvl1() {
		if (!session_loggedin()) {
			if ( ($this->isPublic() && $this->allowAnonymous()) ) {
				return true;//public forum, anonymous allowed, user not logged in
			}
		} else {
			$perm =& $this->Group->getPermission( session_get_user() );
			if (  (!$perm->isMember() )) {
				//the user isn't a member of the project
				return true;
			}
		}
		return false;
	}
	
	/**
	 *  userIsModLvl2 - see if the user goes into the moderated level 2 category
	 *
	 *  @return boolean user_is_mod_lvl1
	 */
	function userIsModLvl2() {
		if ( $this->userIsAdmin() ) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 *  userCanPost - see if the logged-in user's perms are >= 1 or Group ForumAdmin.
	 *
	 *  @return boolean user_can_post.
	 */
	function userCanPost() {
		if (($this->isPublic() && $this->allowAnonymous()) || $this->userIsAdmin()) {
			return true;
		} elseif ($this->isPublic() && session_loggedin()) {
			return true;
		} else {
			if (!session_loggedin()) {
				return false;
			} else {
				if ($this->getCurrentUserPerm() >= 1) {
					return true;
				} else {
					return false;
				}
			}
		}
	}

	/**
	 *  userIsAdmin - see if the logged-in user's perms are >= 2 or Group ForumAdmin.
	 *
	 *  @return boolean user_is_admin.
	 */
	function userIsAdmin() {
		if (!session_loggedin()) {
				return false;
		} else {
			$perm =& $this->Group->getPermission( session_get_user() );

			if (($this->getCurrentUserPerm() >= 2) || ($perm->isForumAdmin())) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 *  getCurrentUserPerm - get the logged-in user's perms from his role.
	 *
	 *  @return int perm level for the logged-in user.
	 */
	function getCurrentUserPerm() {
		if (!session_loggedin()) {
			return -1;
		} else {
			if (!isset($this->current_user_perm)) {
				$sql="SELECT role_setting.value
				FROM role_setting, user_group
				WHERE role_setting.ref_id='". $this->getID() ."'
				AND user_group.role_id = role_setting.role_id
                                AND user_group.user_id='".user_getid()."'
                                AND role_setting.section_name='forum'";
				$this->current_user_perm=db_result(db_query($sql),0,0);

				// Return no access if no access rights defined.
				if (!$this->current_user_perm)
					$this->current_user_perm=-1;
			}
			return $this->current_user_perm;
		}
	}


}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
