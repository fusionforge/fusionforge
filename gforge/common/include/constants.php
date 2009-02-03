<?php
/**
 * FusionForge constants
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

/* Search */

define('SEARCH__TYPE_IS_ARTIFACT', 'artifact');
define('SEARCH__TYPE_IS_SOFTWARE', 'soft');
define('SEARCH__TYPE_IS_FORUM', 'forum');
define('SEARCH__TYPE_IS_PEOPLE', 'people');
define('SEARCH__TYPE_IS_SKILL', 'skill');
define('SEARCH__TYPE_IS_DOCS', 'docs');
define('SEARCH__TYPE_IS_TRACKERS', 'trackers');
define('SEARCH__TYPE_IS_TASKS', 'tasks');
define('SEARCH__TYPE_IS_FORUMS', 'forums');
define('SEARCH__TYPE_IS_NEWS', 'news');
define('SEARCH__TYPE_IS_FRS', 'frs');
define('SEARCH__TYPE_IS_FULL_PROJECT', 'full');
define('SEARCH__TYPE_IS_ADVANCED', 'advanced');

define('SEARCH__DEFAULT_ROWS_PER_PAGE', 25);
define('SEARCH__ALL_SECTIONS', 'all');

define('SEARCH__PARAMETER_GROUP_ID', 'group_id');
define('SEARCH__PARAMETER_ARTIFACT_ID', 'atid');
define('SEARCH__PARAMETER_FORUM_ID', 'forum_id');
define('SEARCH__PARAMETER_GROUP_PROJECT_ID', 'group_project_id');

define('SEARCH__OUTPUT_RSS', 'rss');
define('SEARCH__OUTPUT_HTML', 'html');

define('SEARCH__MODE_OR', 'or');
define('SEARCH__MODE_AND', 'and');

/* Mailing lists */

define('MAIL__MAILING_LIST_IS_PRIVATE', '0');
define('MAIL__MAILING_LIST_IS_PUBLIC', '1');
define('MAIL__MAILING_LIST_IS_DELETED', '9');

define('MAIL__MAILING_LIST_IS_REQUESTED', '1');
define('MAIL__MAILING_LIST_IS_CREATED', '2');

define('MAIL__MAILING_LIST_NAME_MIN_LENGTH', 4);

/* Groups */
define('GROUP_IS_MASTER', 1);
define('GROUP_IS_STATS', $sys_stats_group);
define('GROUP_IS_NEWS', $sys_news_group);
define('GROUP_IS_PEER_RATINGS', $sys_peer_rating_group);
define('GROUP_IS_TEMPLATE', $sys_template_group);

/* Admin */
define('ADMIN_CRONMAN_ROWS', 30);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
