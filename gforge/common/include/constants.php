<?php

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
define('GROUP_IS_NEWS', 3);
define('GROUP_IS_STATS', 2);
define('GROUP_IS_PEER_RATINGS', 4);

/* Admin */
define('ADMIN_CRONMAN_ROWS', 30);

?>