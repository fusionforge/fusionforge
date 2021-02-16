<?php
/**
 * News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2017, Franck Villaume - TrivialDev
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

/**
 * news_header() - Display header for news pages
 *
 * @param array $params
 */

function news_header($params) {
	global $HTML, $group_id;

	if (!forge_get_config('use_news')) {
		exit_disabled();
	}

	$params['toptab'] = 'news';
	$params['group'] = $group_id;

	if ($group_id && ($group_id != GROUP_IS_NEWS)) {
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
	if ($group_id && ($group_id != GROUP_IS_NEWS)) {
		site_project_header($params);
	} else {
		site_header($params);
	}
}

function news_footer($params = array()) {
	global $HTML;
	$HTML->footer($params);
}

/**
 * Display latest news for frontpage or news page.
 *
 * @param int  $group_id group_id of the news (GROUP_IS_NEWS used if none given)
 * @param int  $limit number of news to display (default: 10)
 * @param bool $show_summaries (default: true)
 * @param bool $allow_submit (default: true)
 * @param bool $flat (default: false)
 * @param int  $tail_headlines number of additional news to display in short (-1 for all the others, default: 0)
 * @param bool $show_forum
 * @return string
 */
function news_show_latest($group_id = 0, $limit = 10, $show_summaries = true, $allow_submit = true, $flat = false, $tail_headlines = 0, $show_forum = true) {
	global $HTML;
	if (!$group_id) {
		$group_id = GROUP_IS_NEWS;
	}
	/*
		Show a simple list of the latest news items with a link to the forum
	*/
	if ($tail_headlines == -1) {
		$l = 0;
	} else {
		$l = $limit + $tail_headlines;
	}
	$result = db_query_params ('
				SELECT groups.group_name, groups.unix_group_name, groups.group_id,
				users.user_name, users.realname, users.user_id,
				news_bytes.forum_id, news_bytes.summary, news_bytes.post_date,
				news_bytes.details,forum_group_list.forum_name
                FROM users
                  JOIN news_bytes ON (users.user_id=news_bytes.submitted_by)
                  JOIN groups ON (news_bytes.group_id=groups.group_id)
                  LEFT OUTER JOIN forum_group_list ON news_bytes.forum_id = forum_group_list.group_forum_id
				WHERE (news_bytes.group_id=$1 AND news_bytes.is_approved <> 4 OR 1!=$2)
				AND (news_bytes.is_approved=1 OR 1 != $3)
				AND groups.status=$4
				ORDER BY post_date DESC',
				array ($group_id,
					$group_id != GROUP_IS_NEWS ? 1 : 0,
					$group_id != GROUP_IS_NEWS ? 0 : 1,
					'A'),
					$l);
	$rows=db_numrows($result);

	$return = '';

	if (!$result || $rows < 1) {
		$return .= $HTML->warning_msg(_('No news found.'));
		$return .= db_error();
//		$return .= "</div>";
	} else {
		for ($i=0; $i<$rows; $i++) {
			$t_thread_title = db_result($result,$i,'summary');
			$t_thread_url = "/forum/forum.php?forum_id=" . db_result($result,$i,'forum_id');
			$t_thread_author = util_display_user(db_result($result,$i,'user_name'), db_result($result,$i,'user_id'), db_result($result,$i,'realname'));

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

            $forum_exists = False;
            if (db_result($result,$i,'forum_name')) {
                    $forum_exists = True;
            }

			if (!$limit) {
				if ($show_forum && $forum_exists) {
					$return .= '<h3>'.util_make_link ($t_thread_url, $t_thread_title).'</h3>';
				} else {
					$return .= '<h3>'. $t_thread_title . '</h3>';
				}
				$return .= ' &nbsp; <em>'. date(_('Y-m-d H:i'),db_result($result,$i,'post_date')).'</em><br />';
			} else {
				if ($show_forum && $forum_exists) {
					$return .= '<h3>'.util_make_link ($t_thread_url, $t_thread_title).'</h3>';
				} else {
					$return .= '<h3>'. $t_thread_title . '</h3>';
				}
				$return .= "<div>";
				$return .= $t_thread_author;
				$return .= ' - ';
				$return .= relative_date(db_result($result,$i,'post_date'));
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
					$link_text = _('Read More/Comment') ;
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

		if ($group_id != GROUP_IS_NEWS) {
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
	if ($allow_submit && $group_id != GROUP_IS_NEWS) {
		if(!$result || $rows < 1) {
			$return .= '';
		}
		//you can only submit news from a project now
		//you used to be able to submit general news
		$return .= '<div>' . util_make_link ('/news/submit.php?group_id='.$group_id, _('Submit News')).'</div>';
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
