<?php
/**
 * Copyright (c) STMicroelectronics, 2004-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'common/dao/include/DataAccessObject.class.php';

define('FORUMML_MESSAGE_ID', 1);
define('FORUMML_DATE', 2);
define('FORUMML_FROM', 3);
define('FORUMML_SUBJECT', 4);
define('FORUMML_CONTENT_TYPE', 12);
define('FORUMML_CC', 34);
class ForumML_MessageDao extends DataAccessObject {

	function __construct($da) {
		parent::__construct($da);
	}

	function searchHeaderValue($messageId, $headerId) {
		$sql = 'SELECT mh.value'.
			' FROM plugin_forumml_message m'.
			'  JOIN plugin_forumml_messageheader mh'.
			'   ON (mh.id_message = m.id_message)'.
			'  JOIN plugin_forumml_header h'.
			'   ON (h.id_header = mh.id_header)'.
			' WHERE m.id_message = $1'.
			'  AND h.id_header = $2';
		return $this->retrieve($sql,array($messageId,$headerId));
	}

	function getMessageHeaders($id_message) {
		$sql = 'SELECT value'.
			' FROM plugin_forumml_messageheader'.
			' WHERE id_message = $1'.
			' AND id_header < 5'.
			' ORDER BY id_header';
		return  $this->retrieve($sql,array($id_message));
	}

	function getSpecificMessage($id_message,$list_id) {
		$sql ='SELECT value, body'.
			' FROM plugin_forumml_message m, plugin_forumml_messageheader mh'.
			' WHERE m.id_message =$1 '.
			' AND mh.id_message = m.id_message'.
			' AND m.id_list = $2'.
			' AND mh.id_header = $3';
		return $this->retrieve($sql,array($id_message,$list_id,FORUMML_SUBJECT));
	}

	function getHeaderValue($id, $ids) {
		if (!isset($ids)) {
			$ids = 'SELECT id_header FROM plugin_forumml_messageheader';
		}
		$sql = 'SELECT value , id_header FROM plugin_forumml_messageheader'.
			' WHERE id_message =$1 and id_header IN ($2,$3) ORDER BY id_header DESC';
		return $this->retrieve($sql,array($id,$ids[0],$ids[1]));
	}

	function getAllThreadsFromList($list_id,$offset,$chunks) {
		$sql = 'SELECT m.id_message, m.last_thread_update as lastup, mh_d.value as date, mh_f.value as sender, mh_s.value as subject'.
			' FROM plugin_forumml_message m'.
			' LEFT JOIN plugin_forumml_messageheader mh_d ON (mh_d.id_message = m.id_message AND mh_d.id_header = $1)'.
			' LEFT JOIN plugin_forumml_messageheader mh_f ON (mh_f.id_message = m.id_message AND mh_f.id_header = $2) '.
			' LEFT JOIN plugin_forumml_messageheader mh_s ON (mh_s.id_message = m.id_message AND mh_s.id_header = $3) '.
			' WHERE m.id_parent = 0'.
			' AND id_list = $4 '.
			' ORDER BY m.last_thread_update DESC'.
			' OFFSET $5'.
			' LIMIT $6';
		return $this->retrieve($sql,array(FORUMML_DATE,FORUMML_FROM,FORUMML_SUBJECT,$list_id,$offset,$chunks));
	}

	function countAllThreadsFromList($list_id) {
		$sql = 'SELECT COUNT(*) as nb'.
			' FROM plugin_forumml_message m'.
			' LEFT JOIN plugin_forumml_messageheader mh_d ON (mh_d.id_message = m.id_message AND mh_d.id_header = $1)'.
			' LEFT JOIN plugin_forumml_messageheader mh_f ON (mh_f.id_message = m.id_message AND mh_f.id_header = $2) '.
			' LEFT JOIN plugin_forumml_messageheader mh_s ON (mh_s.id_message = m.id_message AND mh_s.id_header = $3) '.
			' WHERE m.id_parent = 0'.
			' AND id_list = $4 GROUP BY m.last_thread_update'.
			' ORDER BY m.last_thread_update DESC';
		return $this->retrieve($sql,array(FORUMML_DATE,FORUMML_FROM,FORUMML_SUBJECT,$list_id));
	}

	function countChildrenFromParents ($parents) {
		$sql = 'SELECT id_message'.
			' FROM plugin_forumml_message m'.
			' WHERE m.id_parent IN ($1)';
		return $this->retrieve($sql,array($parents));
	}

	function getChildrenFromDepthLevel($parents) {
		$sql = 'SELECT m.*, mh_d.value as date, mh_f.value as sender, mh_s.value as subject, mh_ct.value as content_type, mh_cc.value as cc, a.id_attachment, a.file_name, a.file_type, a.file_size, a.file_path, a.content_id'.
			' FROM plugin_forumml_message m'.
			' LEFT JOIN plugin_forumml_messageheader mh_d ON (mh_d.id_message = m.id_message AND mh_d.id_header = $1)'.
			' LEFT JOIN plugin_forumml_messageheader mh_f ON (mh_f.id_message = m.id_message AND mh_f.id_header = $2) '.
			' LEFT JOIN plugin_forumml_messageheader mh_s ON (mh_s.id_message = m.id_message AND mh_s.id_header = $3) '.
			' LEFT JOIN plugin_forumml_messageheader mh_ct ON (mh_ct.id_message = m.id_message AND mh_ct.id_header = $4) '.
			' LEFT JOIN plugin_forumml_messageheader mh_cc ON (mh_cc.id_message = m.id_message AND mh_cc.id_header = $5) '.
			" LEFT JOIN plugin_forumml_attachment a ON (a.id_message = m.id_message AND a.content_id = '')".
			' WHERE m.id_parent IN ($6)';

		return $this->retrieve($sql,array(FORUMML_DATE,FORUMML_FROM,FORUMML_SUBJECT,FORUMML_CONTENT_TYPE,FORUMML_CC,$parents));
	}

	function getFlattenedThread($topic) {
		$sql = 'SELECT m.*, mh_d.value as date, mh_f.value as sender, mh_s.value as subject, mh_ct.value as content_type, mh_cc.value as cc, a.id_attachment, a.file_name, a.file_type, a.file_size, a.file_path, a.content_id'.
			' FROM plugin_forumml_message m'.
			' LEFT JOIN plugin_forumml_messageheader mh_d ON (mh_d.id_message = m.id_message AND mh_d.id_header = $1)'.
			' LEFT JOIN plugin_forumml_messageheader mh_f ON (mh_f.id_message = m.id_message AND mh_f.id_header = $2)'.
			' LEFT JOIN plugin_forumml_messageheader mh_s ON (mh_s.id_message = m.id_message AND mh_s.id_header = $3)'.
			' LEFT JOIN plugin_forumml_messageheader mh_ct ON (mh_ct.id_message = m.id_message AND mh_ct.id_header = $4)'.
			' LEFT JOIN plugin_forumml_messageheader mh_cc ON (mh_cc.id_message = m.id_message AND mh_cc.id_header = $5)'.
			" LEFT JOIN plugin_forumml_attachment a ON (a.id_message = m.id_message AND a.content_id = '')".
			' WHERE m.id_message =$6 ';
		return $this->retrieve($sql,array(FORUMML_DATE,FORUMML_FROM,FORUMML_SUBJECT,FORUMML_CONTENT_TYPE,FORUMML_CC,$topic));
	}

	function updateCacheHTML($cache,$id) {
		return $this->update('UPDATE plugin_forumml_message SET cached_html= $1 WHERE id_message= $2',array($cache,$id));
	}

	function getAttachment($id_message,$match) {
		$sql = 'SELECT id_attachment FROM plugin_forumml_attachment WHERE id_message=$1 and content_id=<$2>';
		return $this->retrieve($sql,array($id_message , $match));
	}
	function searchArchives($list_id,$pattern) {
		$sql = 'SELECT mh.id_message, mh.value'.
			' FROM plugin_forumml_message m, plugin_forumml_messageheader mh'.
			' WHERE mh.id_header = $1'.
			' AND m.id_list = $2'.
			' AND m.id_parent = 0'.
			' AND m.id_message = mh.id_message'.
			' AND mh.value LIKE $3';
		return $this->retrieve($sql,array(FORUMML_SUBJECT,$list_id,$pattern));

	}

	function hasArchives($list_id) {
		$qry = 'SELECT NULL FROM plugin_forumml_message WHERE id_list = $1 LIMIT 1';
		return $this->retrieve($qry,array($list_id));
	}

}

?>
