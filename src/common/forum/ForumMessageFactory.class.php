<?php
/**
 * FusionForge forums
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';

class ForumMessageFactory extends Error {

	/**
	 * The Forum object.
	 *
	 * @var	 object  $Forum.
	 */
	var $Forum;

	/**
	 * The forum_messages array.
	 *
	 * @var  array  forum_messages.
	 */
	var $forum_messages;
	var $style;
	var $offset;
	var $max_rows;
	var $fetched_rows;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Forum object to which this ForumMessageFactory is associated.
	 *	@return	boolean	success.
	 */
	function ForumMessageFactory(&$Forum) {
		$this->Error();
		if (!$Forum || !is_object($Forum)) {
			$this->setError("ForumMessage:: Invalid group_form_id");
			return false;
		}
		if ($Forum->isError()) {
			$this->setError('ForumMessage:: '.$Forum->getErrorMessage());
			return false;
		}
		$this->Forum =& $Forum;

		return true;
	}

	/**
	 *	setup - call this function before getThreaded/nested/etc to set up the user preferences.
	 *
	 *	@param	int	The number of rows to skip.
	 *	@param	string	The style of forum, whether it's nested, ultimate, etc.
	 *	@param	int	The maximum number of rows to return.
	 *	@param	int	Whether to set these prefs into the database - use "custom".
	 */
	function setup($offset,$style,$max_rows,$set) {
//echo "<br />offset: $offset| style: $style|max_rows: $max_rows|set: $set+";
		if ((!$offset) || ($offset < 0)) {
			$this->offset=0;
		} else {
			$this->offset=$offset;
		}

		if (!$style || ($style != 'ultimate' && $style != 'flat' && $style != 'nested' && $style != 'threaded')) {
			$style='ultimate';
		}
		if (!$max_rows || $max_rows < 5) {
			$max_rows=25;
		}
		if (session_loggedin()) {
			$u =& session_get_user();
			$_pref=$style.'|'.$max_rows;
			if ($set=='custom') {
				if ($u->getPreference('forum_style')) {
					if ($_pref == $u->getPreference('forum_style')) {
						//pref already stored
					} else {
						//set the pref
						$u->setPreference ('forum_style',$_pref);
					}
				} else {
					//set the pref
					$u->setPreference ('forum_style',$_pref);
				}
			} else {
				if ($u->getPreference('forum_style')) {
					$_pref_arr=explode ('|',$u->getPreference('forum_style'));
					$style=$_pref_arr[0];
					$max_rows=$_pref_arr[1];
				} else {
					//no saved pref and we're not setting
					//one because this is all default settings
				}
			}

		}
		if (!$style || ($style != 'ultimate' && $style != 'flat' && $style != 'nested' && $style != 'threaded')) {
			$style='ultimate';
		}
		$this->style=$style;
		if (!$max_rows || $max_rows < 5) {
			$max_rows=25;
		}
		$this->max_rows=$max_rows;
	}

	/**
	 *	getStyle - the style of forum this is - nested/ultimate/etc.
	 *
	 *	@return	string	The style.
	 */
	function getStyle() {
		return $this->style;
	}

	/**
	 *	nestArray - take an array of Forum Messages and building a multi-dimensional associative array for followups.
	 *
	 *	@return	array	The nested multi-dimensional associative array.
	 */
	function &nestArray($row) {
		$cnt=count($row);
		for ($i=0; $i<$cnt; $i++) {
			if ($row[$i]) {
				$msg_arr["".$row[$i]->getParentID().""][] =& $row[$i];
			}
	  	}
		return $msg_arr;
	}

	/**
	 *	getNested - Return an array of ForumMessage objects arranged for nested forum views.
	 *
	 *	@return	array	The array of ForumMessages.
	 */
	function &getNested($thread_id=false) {
		if ($this->forum_messages) {
			return $this->forum_messages;
		}
		if (isset ($thread_id) && is_numeric($thread_id)) {
			$result = db_query_params ('SELECT * FROM forum_user_vw
		WHERE group_forum_id=$1
                  AND thread_id=$2
		ORDER BY most_recent_date DESC',
						   array ($this->Forum->getID(),
							  $thread_id),
						   $this->max_rows+25,
						   $this->offset);
		} else {
			$result = db_query_params ('SELECT * FROM forum_user_vw
		WHERE group_forum_id=$1
		ORDER BY most_recent_date DESC',
						   array ($this->Forum->getID()),
						   $this->max_rows+25,
						   $this->offset);
		}

		$rows = db_numrows($result);
		$this->fetched_rows=$rows;
		$this->forum_messages = array();
		if (!$result) {
			$this->setError('No Messages Found '.db_error());
			$this->forum_messages = false;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->forum_messages[] = new ForumMessage($this->Forum, $arr['msg_id'], $arr);
			}
		}
		return $this->forum_messages;
	}

	/**
	 *	getThreaded - Return an array of ForumMessage objects arranged for threaded forum views.
	 *
	 *	@return	array	The array of ForumMessages.
	 */
	function &getThreaded($thread_id=false) {
		if ($this->forum_messages) {
			return $this->forum_messages;
		}
		if (isset ($thread_id) && is_numeric($thread_id)) {
			$result = db_query_params ('SELECT * FROM forum_user_vw
		WHERE group_forum_id=$1
                  AND thread_id=$2
		ORDER BY most_recent_date DESC',
						   array ($this->Forum->getID(),
							  $thread_id),
						   $this->max_rows+25,
						   $this->offset);
		} else {
			$result = db_query_params ('SELECT * FROM forum_user_vw
		WHERE group_forum_id=$1
		ORDER BY most_recent_date DESC',
						   array ($this->Forum->getID()),
						   $this->max_rows+25,
						   $this->offset);
		}
		$rows = db_numrows($result);
		$this->fetched_rows=$rows;
		if (!$result) {
			$this->setError('Error when fetching messages '.db_error());
			return false;
		} else if ($rows < 1) {
			$this->forum_messages = array();
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->forum_messages[] = new ForumMessage($this->Forum, $arr['msg_id'], $arr);
			}
		}
		return $this->forum_messages;
	}

	/**
	 *	getFlat - Return an array of ForumMessage objects arranged for flat forum views.
	 *
	 *	@return	array	The array of ForumMessages.
	 */
	function &getFlat($thread_id=false) {
		if ($this->forum_messages) {
			return $this->forum_messages;
		}
		if (isset ($thread_id) && is_numeric($thread_id)) {
			$result = db_query_params ('SELECT * FROM forum_user_vw
		WHERE group_forum_id=$1
                  AND thread_id=$2
		ORDER BY msg_id DESC',
						   array ($this->Forum->getID(),
							  $thread_id),
						   $this->max_rows+25,
						   $this->offset);
		} else {
			$result = db_query_params ('SELECT * FROM forum_user_vw
		WHERE group_forum_id=$1
		ORDER BY msg_id DESC',
						   array ($this->Forum->getID()),
						   $this->max_rows+25,
						   $this->offset);
		}

		$rows = db_numrows($result);
		$this->fetched_rows=$rows;
		if (!$result || $rows < 1) {
			$this->setError('No Messages Found '.db_error());
			return false;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->forum_messages[] = new ForumMessage($this->Forum, $arr['msg_id'], $arr);
			}
		}
		return $this->forum_messages;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
