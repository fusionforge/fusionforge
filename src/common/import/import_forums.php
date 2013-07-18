<?php

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfwww.'forum/include/AttachManager.class.php';

class Forums {

	function __construct($forums, $group_id, $msgonly = false) {
		$this->forums = $forums;
		$this->msgonly = $msgonly;
		$this->group_id = $group_id;
		$group =& group_get_object($group_id);
		if (!$group || !is_object($group)) {
			print "error retrieving group from id";
		} else if ($group->isError()) {
			print "error";
		}
		$this->group = $group;
	}

	function add_monitoring_users($users, $forumObject){
		foreach($users as $user){
			$uid = &user_get_object_by_name($user)->getID();
			$added = $forumObject->setMonitor($uid);
		}
	}

	//recursive
	//content is a list of messages

	function forum_fill_content($content, $forumObject, $parentMessageId = '', $thread_id = ''){
		foreach ($content as $message){
			$messageObject = new ForumMessage($forumObject);
			$msg_time = strtotime($message['date']);
			$attachment = false;
			if (array_key_exists('name', $message['attachment'])){
				$attachment = true;
			}
			$messageObject->create($message['subject'], $message['content'], $thread_id, $parentMessageId, $attachment, $msg_time);
			if ($attachment == true){
				$am = new AttachManager();
				$am->SetForumMsg($messageObject);
				$am->Setmsgid($messageObject->getID());
				$userid = $messageObject->getPosterID();
				$path = '/tmp/' . $message['attachment']['url'];
				$filename = addslashes($message['attachment']['name']);
				$filedata = file_get_contents($path);
				$fs = filesize($path);
				$finfo = new finfo(FILEINFO_MIME, "/usr/share/misc/magic"); // Retourne le type mime
				if (!$finfo) {
					echo "error opening fileinfo";
					exit();
				}
				$mimetype = $finfo->file($path);
				$filehash = md5($filedata);
				$am->AddToDBOnly($userid, $msg_time, $filename, base64_encode($filedata), $fs, 1, $filehash, $mimetype);
			}

			if (count($message['children']) != 0){
				$this->forum_fill_content($message['children'], $forumObject, $messageObject->getID(), $messageObject->getThreadID());
			}
		}
		return $forumObject;
	}

	function create_forum($forum){
		$forumObject = new Forum($this->group);
		if ($this->msgonly){
			$forumObject->create($forum['summary'], $forum['news_content'],1,'',0,0);
			$this->forum_fill_content($forum['forum'], $forumObject);
			return $forumObject->getID();
		}else{
			$bostr = array('Yes'=>0,'No'=>1);
			$modlev = array('No Moderation'=>0, 'Moderation Level 1'=>1, 'Moderation Level 2'=>2);
			$forumObject->create($forum['name'], $forum['description'], $bostr[$forum['admin']['is_public']], $forum['admin']['email_posts_to'], 1, $bostr[$forum['admin']['allow_anonymous_posts']], $modlev[$forum['admin']['moderation_level']]);
			$fFact = new ForumMessageFactory($forumObject);
			$thread = $fFact->getFlat();
			$initMsg=$thread[0];
			$initMsg->delete();
			//Monitoring users can be skipped seeing it's the only part of a project where monitoring users are imported, as of Aug.2010
			$this->add_monitoring_users($forum['monitoring_users'], $forumObject);
			$this->forum_fill_content($forum['content'], $forumObject);
		}
	}

	/**
	 * deleteForums - Delete all existing default forums from a projet
	 */
	function deleteForums(){
		$res = db_query_params ('SELECT group_forum_id FROM forum_group_list
				WHERE group_id=$1',
		array ($this->group_id));
		while($row=db_fetch_array($res)){
			$f = new Forum($this->group, $row['group_forum_id']);
			$f->delete(true,true);
		}
	}

	function forums_fill(){
		$this->deleteForums();
		foreach($this->forums as $forum){
			$this->create_forum($forum);
		}
	}
}
