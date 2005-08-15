<?php
/**
 * GForge News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
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
	News System
	By Tim Perdue, Sourceforge, 12/99
*/

function news_header($params) {
	global $HTML,$group_id,$news_name,$news_id,$sys_news_group,$Language,$sys_use_news;

	if (!$sys_use_news) {
		exit_disabled();
	}

	$params['toptab']='news';
	$params['group']=$group_id;
	/*
		Show horizontal links
	*/
	if ($group_id && ($group_id != $sys_news_group)) {
		site_project_header($params);
	} else {
		$HTML->header($params);
	}
	if ($group_id && ($group_id != $sys_news_group)) {
		$menu_texts=array();
		$menu_links=array();

		$menu_texts[]=$Language->getText('menu','submit');
		$menu_links[]='/news/submit.php?group_id='.$group_id;
		if (session_loggedin()) {
			$project =& group_get_object($params['group']);
			if ($project && is_object($project) && !$project->isError()) {
				$perm =& $project->getPermission(session_get_user());
				if ($perm && is_object($perm) && !$perm->isError() && $perm->isAdmin()) {
					$menu_texts[]=$Language->getText('menu','admin');
					$menu_links[]='/news/admin/?group_id='.$group_id;
				}
			}
		}
		echo $HTML->subMenu($menu_texts,$menu_links);
	}
}

function news_footer($params) {
	GLOBAL $HTML;
	$HTML->footer($params);
}

function news_show_latest($group_id='',$limit=10,$show_summaries=true,$allow_submit=true,$flat=false,$tail_headlines=0,$show_forum=true) {
	global $sys_datefmt,$sys_news_group,$Language;
	if (!$group_id) {
		$group_id=$sys_news_group;
	}
	/*
		Show a simple list of the latest news items with a link to the forum
	*/

	if ($group_id != $sys_news_group) {
		$wclause="news_bytes.group_id='$group_id' AND news_bytes.is_approved <> '4'";
	} else {
		$wclause='news_bytes.is_approved=1';
	}

	$sql="SELECT groups.group_name,groups.unix_group_name,
		groups.type_id,users.user_name,users.realname,
		news_bytes.forum_id,news_bytes.summary,news_bytes.post_date,news_bytes.details 
		FROM users,news_bytes,groups 
		WHERE $wclause 
		AND users.user_id=news_bytes.submitted_by 
		AND news_bytes.group_id=groups.group_id 
		AND groups.status='A'
		ORDER BY post_date DESC";

	$result=db_query($sql,$limit+$tail_headlines);
	$rows=db_numrows($result);
	
	$return = '';

	if (!$result || $rows < 1) {
		$return .= $Language->getText('news_utils', 'nonews');
		$return .= db_error();
	} else {
		if (!$limit) $return .= '<ul>';
		for ($i=0; $i<$rows; $i++) {
			if ($show_summaries && $limit) {
				//get the first paragraph of the story
				$arr=explode("\n",db_result($result,$i,'details'));
				//if the first paragraph is short, and so are following paragraphs, add the next paragraph on
				if ((strlen($arr[0]) < 200) && (strlen($arr[1].$arr[2]) < 300) && (strlen($arr[2]) > 5)) {
					$summ_txt='<br />'. util_make_links( $arr[0].'<br />'.$arr[1].'<br />'.$arr[2] );
				} else {
					$summ_txt='<br />'. util_make_links( $arr[0] );
				}
				$proj_name=' &nbsp; - &nbsp; <a href="/projects/'. strtolower(db_result($result,$i,'unix_group_name')) .'/">'. db_result($result,$i,'group_name') .'</a>';
			} else {
				$proj_name='';
				$summ_txt='';
			}

			if (!$limit) {
				if ($show_forum) {
					$return .= '<li><a href="/forum/forum.php?forum_id='. db_result($result,$i,'forum_id') .'"><strong>'. db_result($result,$i,'summary') . '</strong></a>';
				} else {
					$return .= '<li><strong>'. db_result($result,$i,'summary') . '</strong>';
				}
				$return .= ' &nbsp; <em>'. date($sys_datefmt,db_result($result,$i,'post_date')).'</em><br /></li>';
			} else {
				if ($show_forum) {
					$return .= '
					<a href="/forum/forum.php?forum_id='. db_result($result,$i,'forum_id') .'"><strong>'. db_result($result,$i,'summary') . '</strong></a>';
				} else {
					$return .= '
					<strong>'. db_result($result,$i,'summary') . '</strong>';
				}
				if (!$flat) {
					$return .= '
					<br />&nbsp;';
				}
				$return .= '&nbsp;&nbsp;&nbsp;<em>'. db_result($result,$i,'realname') .' - '.
					date($sys_datefmt,db_result($result,$i,'post_date')). '</em>' .
					$proj_name . $summ_txt;

				$sql="SELECT total FROM forum_group_list_vw WHERE group_forum_id='" . db_result($result,$i,'forum_id') . "'";
				$res2 = db_query($sql);
				$num_comments = db_result($res2,0,'total');

				if (!$num_comments) {
					$num_comments = '0';
				}

				if ($num_comments <= 1) {
					$comments_txt = $Language->getText('news_utils', 'comment_1');
				} else {
					$comments_txt = $Language->getText('news_utils', 'comment_2');
				}

				if ($show_forum){
					$return .= '<div align="center">(' . $num_comments .' '. $comments_txt . ') <a href="/forum/forum.php?forum_id='. db_result($result,$i,'forum_id') .'">[' . $Language->getText('news_utils', 'readmore') . ']</a></div><hr width="100%" size="1" />';
				} else {
					$return .= '<hr width="100%" size="1" />';
				}
			}

			if ($limit==1 && $tail_headlines) {
				$return .= "<ul>\n";
			}
			if ($limit) {
				$limit--;
			}
			if (!$limit && $i==$rows-1) {
				$return .= '</ul><hr width="100%" size="1" />'."\n";
			}
		}
		if (!$limit) $return .= '</ul>';
		if ($group_id != $sys_news_group) {
			$archive_url='/news/?group_id='.$group_id;
		} else {
			$archive_url='/news/';
		}

		if ($show_forum) {
			$return .= '<div align="center">'
				.'<a href="'.$archive_url.'">[' . $Language->getText('news_utils', 'archive') . ']</a></div>';
		} else {
			$return .= '<div align="center">...</div>';
		}
	}

	if ($allow_submit && $group_id != $sys_news_group) {
		if(!$result || $rows < 1) {
			$return .= '<hr width="100%" size="1" />';
		}
		//you can only submit news from a project now
		//you used to be able to submit general news
		$return .= '<div align="center"><a href="/news/submit.php?group_id='.$group_id.'"><span style="font-size:smaller">[' . $Language->getText('news_utils', 'submit') . ']</span></a></div>';
	}

	return $return;
}

function news_foundry_latest($group_id=0,$limit=5,$show_summaries=true) {
	global $sys_datefmt,$Language;
	/*
		Show a the latest news for a portal
	*/

	$sql="SELECT groups.group_name,groups.unix_group_name,
		users.user_name,users.realname,news_bytes.forum_id,
		news_bytes.summary,news_bytes.post_date,news_bytes.details 
		FROM users,news_bytes,groups,foundry_news 
		WHERE foundry_news.foundry_id='$group_id' 
		AND users.user_id=news_bytes.submitted_by 
		AND foundry_news.news_id=news_bytes.id 
		AND news_bytes.group_id=groups.group_id 
		AND foundry_news.is_approved=1 
		ORDER BY news_bytes.post_date DESC";

	$result=db_query($sql,$limit);
	$rows=db_numrows($result);

	if (!$result || $rows < 1) {
		$return .= '<h3>' . $Language->getText('news_utils', 'nonews') . '</h3>';
		$return .= db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			if ($show_summaries) {
				//get the first paragraph of the story
				$arr=explode("\n",db_result($result,$i,'details'));
				if ((strlen($arr[0]) < 200) && (strlen($arr[1].$arr[2]) < 300) && (strlen($arr[2]) > 5)) {
					$summ_txt=util_make_links( $arr[0].'<br />'.$arr[1].'<br />'.$arr[2] );
				} else {
					$summ_txt=util_make_links( $arr[0] );
				}

				//show the project name
				$proj_name=' &nbsp; - &nbsp; <a href="/projects/'. strtolower(db_result($result,$i,'unix_group_name')) .'/">'. db_result($result,$i,'group_name') .'</a>';
			} else {
				$proj_name='';
				$summ_txt='';
			}
			$return .= '
				<a href="/forum/forum.php?forum_id='. db_result($result,$i,'forum_id') .'"><strong>'. db_result($result,$i,'summary') . '</strong></a>
				<br /><em>'. db_result($result,$i,'realname') .' - '.
					date($sys_datefmt,db_result($result,$i,'post_date')) . $proj_name . '</em>
				'. $summ_txt .'<hr width="100%" size="1" />';
		}
	}
	return $return;
}

function get_news_name($id) {
	/*
		Takes an ID and returns the corresponding forum name
	*/
	$sql="SELECT summary FROM news_bytes WHERE id='$id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		return "Not Found";
	} else {
		return db_result($result, 0, 'summary');
	}
}

?>
