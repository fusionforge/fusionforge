<?php
/**
 * News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
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

function news_header($params) {
	global $HTML,$group_id,$news_name,$news_id;

	if (!forge_get_config('use_news')) {
		exit_disabled();
	}

	$params['toptab']='news';
	$params['group']=$group_id;

	if ($group_id && ($group_id != forge_get_config('news_group'))) {
		$menu_texts=array();
		$menu_links=array();

		$menu_texts[]=_('View News');
		$menu_links[]='/news/?group_id='.$group_id;
		$menu_texts[]=_('Submit');
		$menu_links[]='/news/submit.php?group_id='.$group_id;
		if (session_loggedin()) {
			$project = group_get_object($params['group']);
			if ($project && is_object($project) && !$project->isError()) {
				if (forge_check_perm ('project_admin', $group_id)) {
					$menu_texts[]=_('Administration');
					$menu_links[]='/news/admin/?group_id='.$group_id;
				}
			}
		}
		$params['submenu'] = $HTML->subMenu($menu_texts,$menu_links);
	}
	/*
		Show horizontal links
	*/
	if ($group_id && ($group_id != forge_get_config('news_group'))) {
		site_project_header($params);
	} else {
		site_header($params);
	}
}

function news_footer($params) {
	GLOBAL $HTML;
	$HTML->footer($params);
}

/**
 * Display latest news for frontpage or news page.
 *
 * @param int  $group_id group_id of the news (forge_get_config('news_group') used if none given)
 * @param int  $limit number of news to display (default: 10)
 * @param bool $show_summaries (default: true)
 * @param bool $allow_submit (default: true)
 * @param bool $flat (default: false)
 * @param int  $tail_headlines number of additional news to display in short (-1 for all the others, default: 0)
 */
function news_show_latest($group_id='',$limit=10,$show_summaries=true,$allow_submit=true,$flat=false,$tail_headlines=0,$show_forum=true) {

	if (!$group_id) {
		$group_id=forge_get_config('news_group');
	}
	/*
		Show a simple list of the latest news items with a link to the forum
	*/
	if ($tail_headlines == -1) {
		$l = 0 ;
	} else {
		$l = $limit + $tail_headlines ;
	}
	$result = db_query_params ('
SELECT groups.group_name, groups.unix_group_name, groups.group_id,
       groups.type_id, users.user_name, users.realname,
       news_bytes.forum_id, news_bytes.summary, news_bytes.post_date,
       news_bytes.details
FROM users,news_bytes,groups
WHERE (news_bytes.group_id=$1 AND news_bytes.is_approved <> 4 OR 1!=$2)
  AND (news_bytes.is_approved=1 OR 1 != $3)
  AND users.user_id=news_bytes.submitted_by
  AND news_bytes.group_id=groups.group_id
  AND groups.status=$4
ORDER BY post_date DESC',
				   array ($group_id,
					  $group_id != forge_get_config('news_group') ? 1 : 0,
					  $group_id != forge_get_config('news_group') ? 0 : 1,
					  'A'),
				   $l);
	$rows=db_numrows($result);

	$return = '';

	if (!$result || $rows < 1) {
		$return .= _('No News Items Found');
		$return .= db_error();
//		$return .= "</div>";
	} else {
		for ($i=0; $i<$rows; $i++) {
			$t_thread_title = db_result($result,$i,'summary');
			$t_thread_url = "/forum/forum.php?forum_id=" . db_result($result,$i,'forum_id');
			$t_thread_author = db_result($result,$i,'realname');

			$return .= '<div class="one-news bordure-dessous">';
			$return .= "\n";
			if ($show_summaries && $limit) {
				//get the first paragraph of the story
				if (strstr(db_result($result,$i,'details'),'<br/>')) {
					// the news is html, fckeditor made for example
					$arr=explode("<br/>",db_result($result,$i,'details'));
				} else {
					$arr=explode("\n",db_result($result,$i,'details'));
				}
				$summ_txt=util_make_links( $arr[0] );
				$proj_name=util_make_link_g (strtolower(db_result($result,$i,'unix_group_name')),db_result($result,$i,'group_id'),db_result($result,$i,'group_name'));
			} else {
				$proj_name='';
				$summ_txt='';
			}

			if (!$limit) {
				if ($show_forum) {
					$return .= '<h3>'.util_make_link ($t_thread_url, $t_thread_title).'</h3>';
				} else {
					$return .= '<h3>'. $t_thread_title . '</h3>';
				}
				$return .= ' &nbsp; <em>'. date(_('Y-m-d H:i'),db_result($result,$i,'post_date')).'</em><br />';
			} else {
				if ($show_forum) {
					$return .= '<h3>'.util_make_link ($t_thread_url, $t_thread_title).'</h3>';
				} else {
					$return .= '<h3>'. $t_thread_title . '</h3>';
				}
				$return .= "<div>";
				$return .= '<em>';
				$return .= $t_thread_author;
				$return .= '</em>';
				$return .= ' - ';
				$return .= date(_('Y-m-d H:i'),db_result($result,$i,'post_date'));
				$return .= ' - ';
				$return .= $proj_name ;
				$return .= "</div>\n";

				if ($summ_txt != "") {
					$return .= '<p>'.$summ_txt.'</p>';
				}

				$res2 = db_query_params ('SELECT total FROM forum_group_list_vw WHERE group_forum_id=$1',
							 array (db_result($result,$i,'forum_id')));
				$num_comments = db_result($res2,0,'total');

				if (!$num_comments) {
					$num_comments = '0';
				}

				if ($num_comments <= 1) {
					$comments_txt = _('Comment');
				} else {
					$comments_txt = _('Comments');
				}

				if ($show_forum) {
					$link_text =  _('Read&nbsp;More/Comment') ;
					$extra_params = array( 'class'      => 'dot-link',
					             		   'title'      => $link_text . ' ' . $t_thread_title);
					$return .= "\n";
					$return .= '<div>' . $num_comments .' '. $comments_txt .' ';
					$return .= util_make_link ($t_thread_url, $link_text, $extra_params);
					$return .= '</div>';
				} else {
					$return .= '';
				}
			}

			if ($limit) {
				$limit--;
			}
			$return .= "\n";
			$return .= '</div><!-- class="one-news" -->';
			$return .= "\n\n";
		}

		if ($group_id != forge_get_config('news_group')) {
			$archive_url = '/news/?group_id='.$group_id;
		} else {
			$archive_url = '/news/';
		}
		if ($tail_headlines != -1) {
			if ($show_forum) {
				$return .= '<div>' . util_make_link($archive_url, _('News archive'), array('class' => 'dot-link')) . '</div>';
			} else {
				$return .= '<div>...</div>';
			}
		}
	}
	if ($allow_submit && $group_id != forge_get_config('news_group')) {
		if(!$result || $rows < 1) {
			$return .= '';
		}
		//you can only submit news from a project now
		//you used to be able to submit general news
		$return .= '<div>' . util_make_link ('/news/submit.php?group_id='.$group_id, _('Submit News')).'</div>';
	}
	return $return;
}

function news_foundry_latest($group_id=0,$limit=5,$show_summaries=true) {
	/*
		Show a the latest news for a portal
	*/

	$result=db_query_params("SELECT groups.group_name,groups.unix_group_name,groups.group_id,
		users.user_name,users.realname,news_bytes.forum_id,
		news_bytes.summary,news_bytes.post_date,news_bytes.details
		FROM users,news_bytes,groups,foundry_news
		WHERE foundry_news.foundry_id=$1
		AND users.user_id=news_bytes.submitted_by
		AND foundry_news.news_id=news_bytes.id
		AND news_bytes.group_id=groups.group_id
		AND foundry_news.is_approved=1
		ORDER BY news_bytes.post_date DESC", array($group_id),$limit);

	$rows=db_numrows($result);

	if (!$result || $rows < 1) {
		$return .= '<h3>' . _('No News Items Found') . '</h3>';
		$return .= db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			if ($show_summaries) {
				//get the first paragraph of the story
				$arr=explode("\n",db_result($result,$i,'details'));
				if ((isset($arr[1]))&&(isset($arr[2]))&&(strlen($arr[0]) < 200) && (strlen($arr[1].$arr[2]) < 300) && (strlen($arr[2]) > 5)) {
					$summ_txt=util_make_links( $arr[0].'<br />'.$arr[1].'<br />'.$arr[2] );
				} else {
					$summ_txt=util_make_links( $arr[0] );
				}

				//show the project name
				$proj_name=' &nbsp; - &nbsp; '.util_make_link_g (strtolower(db_result($result,$i,'unix_group_name')),db_result($result,$i,'group_id'),db_result($result,$i,'group_name'));
			} else {
				$proj_name='';
				$summ_txt='';
			}
			$return .= util_make_link ('/forum/forum.php?forum_id='. db_result($result,$i,'forum_id'),'<strong>'. db_result($result,$i,'summary') . '</strong>')
				.'<br /><em>'. db_result($result,$i,'realname') .' - '.
					date(_('Y-m-d H:i'),db_result($result,$i,'post_date')) . $proj_name . '</em>
				'. $summ_txt .'';
		}
	}
	return $return;
}

function get_news_name($id) {
	/*
		Takes an ID and returns the corresponding forum name
	*/
	$result=db_query_params('SELECT summary FROM news_bytes WHERE id=$1', array($id));
	if (!$result || db_numrows($result) < 1) {
		return _('Not Found');
	} else {
		return db_result($result, 0, 'summary');
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
