#!/usr/bin/perl -w
#
# $Id$
#
# Debian-specific script to upgrade the database between releases
# Roland Mas <lolando@debian.org>

use strict ;
use diagnostics ;

use DBI ;
use MIME::Base64 ;
use HTML::Entities ;

use vars qw/$dbh @reqlist $query/ ;
use vars qw/$sys_default_domain $sys_cvs_host $sys_download_host
    $sys_shell_host $sys_users_host $sys_docs_host $sys_lists_host
    $sys_dns1_host $sys_dns2_host $FTPINCOMING_DIR $FTPFILES_DIR
    $sys_urlroot $sf_cache_dir $sys_name $sys_themeroot
    $sys_news_group $sys_dbhost $sys_dbname $sys_dbuser $sys_dbpasswd
    $sys_ldap_base_dn $sys_ldap_host $admin_login $admin_password
    $server_admin $domain_name $newsadmin_groupid $statsadmin_groupid
    $skill_list/ ;

sub debug ( $ ) ;
sub convert_column_to_charset ( $$$$$ ) ;

require ("/usr/lib/gforge/lib/include.pl") ; # Include a few predefined functions 
require ("/usr/lib/gforge/lib/sqlparser.pm") ; # Our magic SQL parser

debug "You'll see some debugging info during this installation." ;
debug "Do not worry unless told otherwise." ;

&db_connect ;

# debug "Connected to the database OK." ;

$dbh->{AutoCommit} = 0;
$dbh->{RaiseError} = 1;
eval {
    my $from = "latin-1" ;
    my $to = "utf-8" ;

    convert_column_to_charset ('canned_responses', 'response_title', $from, $to, 25) ;
    convert_column_to_charset ('canned_responses', 'response_text', $from, $to, 0) ;

    convert_column_to_charset ('db_images', 'response_text', $from, $to, 0) ;
    convert_column_to_charset ('db_images', 'description', $from, $to, 0) ;
    convert_column_to_charset ('db_images', 'bin_data', $from, $to, 0) ;
    convert_column_to_charset ('db_images', 'filename', $from, $to, 0) ;
    convert_column_to_charset ('db_images', 'filetype', $from, $to, 0) ;

    convert_column_to_charset ('doc_data', 'filetype', $from, $to, 0) ;
    convert_column_to_charset ('doc_data', 'title', $from, $to, 255) ;
    convert_column_to_charset ('doc_data', 'data', $from, $to, 0) ;
    convert_column_to_charset ('doc_data', 'description', $from, $to, 0) ;
    convert_column_to_charset ('doc_data', 'filename', $from, $to, 0) ;
    convert_column_to_charset ('doc_data', 'filetype', $from, $to, 0) ;

    convert_column_to_charset ('doc_groups', 'filetype', $from, $to, 0) ;
    convert_column_to_charset ('doc_groups', 'groupname', $from, $to, 255) ;

    convert_column_to_charset ('doc_states', 'groupname', $from, $to, 255) ;
    convert_column_to_charset ('doc_states', 'name', $from, $to, 255) ;

    convert_column_to_charset ('filemodule_monitor', 'name', $from, $to, 255) ;

    convert_column_to_charset ('forum', 'name', $from, $to, 255) ;
    convert_column_to_charset ('forum', 'subject', $from, $to, 0) ;
    convert_column_to_charset ('forum', 'body', $from, $to, 0) ;

    convert_column_to_charset ('forum_agg_msg_count', 'body', $from, $to, 0) ;

    convert_column_to_charset ('forum_group_list', 'body', $from, $to, 0) ;
    convert_column_to_charset ('forum_group_list', 'forum_name', $from, $to, 0) ;
    convert_column_to_charset ('forum_group_list', 'description', $from, $to, 0) ;
    convert_column_to_charset ('forum_group_list', 'send_all_posts_to', $from, $to, 0) ;

    convert_column_to_charset ('forum_monitored_forums', 'send_all_posts_to', $from, $to, 0) ;

    convert_column_to_charset ('forum_saved_place', 'send_all_posts_to', $from, $to, 0) ;

    convert_column_to_charset ('frs_file', 'send_all_posts_to', $from, $to, 0) ;
    convert_column_to_charset ('frs_file', 'filename', $from, $to, 0) ;

    convert_column_to_charset ('frs_filetype', 'filename', $from, $to, 0) ;
    convert_column_to_charset ('frs_filetype', 'name', $from, $to, 0) ;

    convert_column_to_charset ('frs_package', 'name', $from, $to, 0) ;

    convert_column_to_charset ('frs_processor', 'name', $from, $to, 0) ;

    convert_column_to_charset ('frs_release', 'name', $from, $to, 0) ;
    convert_column_to_charset ('frs_release', 'notes', $from, $to, 0) ;
    convert_column_to_charset ('frs_release', 'changes', $from, $to, 0) ;

    convert_column_to_charset ('frs_status', 'changes', $from, $to, 0) ;
    convert_column_to_charset ('frs_status', 'name', $from, $to, 0) ;

    convert_column_to_charset ('group_history', 'name', $from, $to, 0) ;
    convert_column_to_charset ('group_history', 'field_name', $from, $to, 0) ;
    convert_column_to_charset ('group_history', 'old_value', $from, $to, 0) ;

    convert_column_to_charset ('groups', 'old_value', $from, $to, 0) ;
    convert_column_to_charset ('groups', 'group_name', $from, $to, 40) ;
    convert_column_to_charset ('groups', 'homepage', $from, $to, 128) ;
    convert_column_to_charset ('groups', 'unix_group_name', $from, $to, 30) ;
    convert_column_to_charset ('groups', 'unix_box', $from, $to, 20) ;
    convert_column_to_charset ('groups', 'http_domain', $from, $to, 80) ;
    convert_column_to_charset ('groups', 'short_description', $from, $to, 255) ;
    convert_column_to_charset ('groups', 'cvs_box', $from, $to, 20) ;
    convert_column_to_charset ('groups', 'license', $from, $to, 16) ;
    convert_column_to_charset ('groups', 'register_purpose', $from, $to, 0) ;
    convert_column_to_charset ('groups', 'license_other', $from, $to, 0) ;
    convert_column_to_charset ('groups', 'rand_hash', $from, $to, 0) ;
    convert_column_to_charset ('groups', 'dead4', $from, $to, 0) ;
    convert_column_to_charset ('groups', 'dead5', $from, $to, 0) ;
    convert_column_to_charset ('groups', 'dead6', $from, $to, 0) ;
    convert_column_to_charset ('groups', 'new_doc_address', $from, $to, 0) ;

    convert_column_to_charset ('mail_group_list', 'new_doc_address', $from, $to, 0) ;
    convert_column_to_charset ('mail_group_list', 'list_name', $from, $to, 0) ;
    convert_column_to_charset ('mail_group_list', 'password', $from, $to, 16) ;
    convert_column_to_charset ('mail_group_list', 'description', $from, $to, 0) ;

    convert_column_to_charset ('news_bytes', 'description', $from, $to, 0) ;
    convert_column_to_charset ('news_bytes', 'summary', $from, $to, 0) ;
    convert_column_to_charset ('news_bytes', 'details', $from, $to, 0) ;

    convert_column_to_charset ('people_job', 'details', $from, $to, 0) ;
    convert_column_to_charset ('people_job', 'title', $from, $to, 0) ;
    convert_column_to_charset ('people_job', 'description', $from, $to, 0) ;

    convert_column_to_charset ('people_job_category', 'description', $from, $to, 0) ;
    convert_column_to_charset ('people_job_category', 'name', $from, $to, 0) ;

    convert_column_to_charset ('people_job_inventory', 'name', $from, $to, 0) ;

    convert_column_to_charset ('people_job_status', 'name', $from, $to, 0) ;

    convert_column_to_charset ('people_skill', 'name', $from, $to, 0) ;

    convert_column_to_charset ('people_skill_inventory', 'name', $from, $to, 0) ;

    convert_column_to_charset ('people_skill_level', 'name', $from, $to, 0) ;

    convert_column_to_charset ('people_skill_year', 'name', $from, $to, 0) ;

    convert_column_to_charset ('project_assigned_to', 'name', $from, $to, 0) ;

    convert_column_to_charset ('project_dependencies', 'name', $from, $to, 0) ;

    convert_column_to_charset ('project_group_list', 'name', $from, $to, 0) ;
    convert_column_to_charset ('project_group_list', 'project_name', $from, $to, 0) ;
    convert_column_to_charset ('project_group_list', 'description', $from, $to, 0) ;
    convert_column_to_charset ('project_group_list', 'send_all_posts_to', $from, $to, 0) ;

    convert_column_to_charset ('project_history', 'send_all_posts_to', $from, $to, 0) ;
    convert_column_to_charset ('project_history', 'field_name', $from, $to, 0) ;
    convert_column_to_charset ('project_history', 'old_value', $from, $to, 0) ;

    convert_column_to_charset ('project_metric', 'old_value', $from, $to, 0) ;

    convert_column_to_charset ('project_metric_tmp1', 'old_value', $from, $to, 0) ;

    convert_column_to_charset ('project_status', 'old_value', $from, $to, 0) ;
    convert_column_to_charset ('project_status', 'status_name', $from, $to, 0) ;

    convert_column_to_charset ('project_task', 'status_name', $from, $to, 0) ;
    convert_column_to_charset ('project_task', 'summary', $from, $to, 0) ;
    convert_column_to_charset ('project_task', 'details', $from, $to, 0) ;

    convert_column_to_charset ('project_weekly_metric', 'details', $from, $to, 0) ;

    convert_column_to_charset ('session', 'details', $from, $to, 0) ;

    convert_column_to_charset ('snippet', 'details', $from, $to, 0) ;
    convert_column_to_charset ('snippet', 'name', $from, $to, 0) ;
    convert_column_to_charset ('snippet', 'description', $from, $to, 0) ;
    convert_column_to_charset ('snippet', 'license', $from, $to, 0) ;

    convert_column_to_charset ('snippet_package', 'license', $from, $to, 0) ;
    convert_column_to_charset ('snippet_package', 'name', $from, $to, 0) ;
    convert_column_to_charset ('snippet_package', 'description', $from, $to, 0) ;

    convert_column_to_charset ('snippet_package_item', 'description', $from, $to, 0) ;

    convert_column_to_charset ('snippet_package_version', 'description', $from, $to, 0) ;
    convert_column_to_charset ('snippet_package_version', 'changes', $from, $to, 0) ;
    convert_column_to_charset ('snippet_package_version', 'version', $from, $to, 0) ;

    convert_column_to_charset ('snippet_version', 'version', $from, $to, 0) ;
    convert_column_to_charset ('snippet_version', 'changes', $from, $to, 0) ;
    convert_column_to_charset ('snippet_version', 'version', $from, $to, 0) ;
    convert_column_to_charset ('snippet_version', 'code', $from, $to, 0) ;

    convert_column_to_charset ('stats_agg_logo_by_day', 'code', $from, $to, 0) ;

    convert_column_to_charset ('stats_agg_pages_by_day', 'code', $from, $to, 0) ;

    convert_column_to_charset ('survey_question_types', 'code', $from, $to, 0) ;
    convert_column_to_charset ('survey_question_types', 'type', $from, $to, 0) ;

    convert_column_to_charset ('survey_questions', 'type', $from, $to, 0) ;
    convert_column_to_charset ('survey_questions', 'question', $from, $to, 0) ;

    convert_column_to_charset ('survey_rating_aggregate', 'question', $from, $to, 0) ;

    convert_column_to_charset ('survey_rating_response', 'question', $from, $to, 0) ;

    convert_column_to_charset ('survey_responses', 'question', $from, $to, 0) ;
    convert_column_to_charset ('survey_responses', 'response', $from, $to, 0) ;

    convert_column_to_charset ('surveys', 'response', $from, $to, 0) ;
    convert_column_to_charset ('surveys', 'survey_title', $from, $to, 0) ;
    convert_column_to_charset ('surveys', 'survey_questions', $from, $to, 0) ;

    convert_column_to_charset ('trove_cat', 'survey_questions', $from, $to, 0) ;
    convert_column_to_charset ('trove_cat', 'shortname', $from, $to, 80) ;
    convert_column_to_charset ('trove_cat', 'fullname', $from, $to, 80) ;
    convert_column_to_charset ('trove_cat', 'description', $from, $to, 255) ;
    convert_column_to_charset ('trove_cat', 'fullpath', $from, $to, 0) ;
    convert_column_to_charset ('trove_cat', 'fullpath_ids', $from, $to, 0) ;

    convert_column_to_charset ('trove_group_link', 'fullpath_ids', $from, $to, 0) ;

    convert_column_to_charset ('user_bookmarks', 'fullpath_ids', $from, $to, 0) ;
    convert_column_to_charset ('user_bookmarks', 'bookmark_url', $from, $to, 0) ;
    convert_column_to_charset ('user_bookmarks', 'bookmark_title', $from, $to, 0) ;

    convert_column_to_charset ('user_diary', 'bookmark_title', $from, $to, 0) ;
    convert_column_to_charset ('user_diary', 'summary', $from, $to, 0) ;
    convert_column_to_charset ('user_diary', 'details', $from, $to, 0) ;

    convert_column_to_charset ('user_diary_monitor', 'details', $from, $to, 0) ;

    convert_column_to_charset ('user_group', 'details', $from, $to, 0) ;

    convert_column_to_charset ('user_metric', 'details', $from, $to, 0) ;

    convert_column_to_charset ('user_metric0', 'details', $from, $to, 0) ;

    convert_column_to_charset ('user_preferences', 'details', $from, $to, 0) ;
    convert_column_to_charset ('user_preferences', 'preference_name', $from, $to, 20) ;
    convert_column_to_charset ('user_preferences', 'dead1', $from, $to, 20) ;
    convert_column_to_charset ('user_preferences', 'preference_value', $from, $to, 0) ;

    convert_column_to_charset ('user_ratings', 'preference_value', $from, $to, 0) ;

    convert_column_to_charset ('users', 'preference_value', $from, $to, 0) ;
    convert_column_to_charset ('users', 'user_name', $from, $to, 0) ;
    convert_column_to_charset ('users', 'email', $from, $to, 0) ;
    convert_column_to_charset ('users', 'user_pw', $from, $to, 32) ;
    convert_column_to_charset ('users', 'realname', $from, $to, 32) ;
    convert_column_to_charset ('users', 'shell', $from, $to, 20) ;
    convert_column_to_charset ('users', 'unix_pw', $from, $to, 40) ;
    convert_column_to_charset ('users', 'unix_box', $from, $to, 10) ;
    convert_column_to_charset ('users', 'confirm_hash', $from, $to, 32) ;
    convert_column_to_charset ('users', 'authorized_keys', $from, $to, 0) ;
    convert_column_to_charset ('users', 'email_new', $from, $to, 0) ;
    convert_column_to_charset ('users', 'people_resume', $from, $to, 0) ;
    convert_column_to_charset ('users', 'timezone', $from, $to, 64) ;
    convert_column_to_charset ('users', 'jabber_address', $from, $to, 0) ;

    convert_column_to_charset ('project_sums_agg', 'jabber_address', $from, $to, 0) ;

    convert_column_to_charset ('prdb_dbs', 'jabber_address', $from, $to, 0) ;
    convert_column_to_charset ('prdb_dbs', 'dbname', $from, $to, 0) ;
    convert_column_to_charset ('prdb_dbs', 'dbusername', $from, $to, 0) ;
    convert_column_to_charset ('prdb_dbs', 'dbuserpass', $from, $to, 0) ;

    convert_column_to_charset ('prdb_states', 'dbuserpass', $from, $to, 0) ;
    convert_column_to_charset ('prdb_states', 'statename', $from, $to, 0) ;

    convert_column_to_charset ('prdb_types', 'statename', $from, $to, 0) ;
    convert_column_to_charset ('prdb_types', 'dbservername', $from, $to, 0) ;
    convert_column_to_charset ('prdb_types', 'dbsoftware', $from, $to, 0) ;

    convert_column_to_charset ('prweb_vhost', 'dbsoftware', $from, $to, 0) ;
    convert_column_to_charset ('prweb_vhost', 'vhost_name', $from, $to, 0) ;
    convert_column_to_charset ('prweb_vhost', 'docdir', $from, $to, 0) ;
    convert_column_to_charset ('prweb_vhost', 'cgidir', $from, $to, 0) ;

    convert_column_to_charset ('artifact_group_list', 'cgidir', $from, $to, 0) ;
    convert_column_to_charset ('artifact_group_list', 'name', $from, $to, 0) ;
    convert_column_to_charset ('artifact_group_list', 'description', $from, $to, 0) ;
    convert_column_to_charset ('artifact_group_list', 'email_address', $from, $to, 0) ;
    convert_column_to_charset ('artifact_group_list', 'submit_instructions', $from, $to, 0) ;
    convert_column_to_charset ('artifact_group_list', 'browse_instructions', $from, $to, 0) ;

    convert_column_to_charset ('artifact_resolution', 'browse_instructions', $from, $to, 0) ;
    convert_column_to_charset ('artifact_resolution', 'resolution_name', $from, $to, 0) ;

    convert_column_to_charset ('artifact_perm', 'resolution_name', $from, $to, 0) ;

    convert_column_to_charset ('artifact_category', 'resolution_name', $from, $to, 0) ;
    convert_column_to_charset ('artifact_category', 'category_name', $from, $to, 0) ;

    convert_column_to_charset ('artifact_group', 'category_name', $from, $to, 0) ;
    convert_column_to_charset ('artifact_group', 'group_name', $from, $to, 0) ;

    convert_column_to_charset ('artifact_status', 'group_name', $from, $to, 0) ;
    convert_column_to_charset ('artifact_status', 'status_name', $from, $to, 0) ;

    convert_column_to_charset ('artifact', 'status_name', $from, $to, 0) ;
    convert_column_to_charset ('artifact', 'summary', $from, $to, 0) ;
    convert_column_to_charset ('artifact', 'details', $from, $to, 0) ;

    convert_column_to_charset ('artifact_history', 'details', $from, $to, 0) ;
    convert_column_to_charset ('artifact_history', 'field_name', $from, $to, 0) ;
    convert_column_to_charset ('artifact_history', 'old_value', $from, $to, 0) ;

    convert_column_to_charset ('artifact_file', 'old_value', $from, $to, 0) ;
    convert_column_to_charset ('artifact_file', 'description', $from, $to, 0) ;
    convert_column_to_charset ('artifact_file', 'bin_data', $from, $to, 0) ;
    convert_column_to_charset ('artifact_file', 'filename', $from, $to, 0) ;
    convert_column_to_charset ('artifact_file', 'filetype', $from, $to, 0) ;

    convert_column_to_charset ('artifact_message', 'filetype', $from, $to, 0) ;
    convert_column_to_charset ('artifact_message', 'from_email', $from, $to, 0) ;
    convert_column_to_charset ('artifact_message', 'body', $from, $to, 0) ;

    convert_column_to_charset ('artifact_monitor', 'body', $from, $to, 0) ;
    convert_column_to_charset ('artifact_monitor', 'email', $from, $to, 0) ;

    convert_column_to_charset ('artifact_canned_responses', 'email', $from, $to, 0) ;
    convert_column_to_charset ('artifact_canned_responses', 'title', $from, $to, 0) ;
    convert_column_to_charset ('artifact_canned_responses', 'body', $from, $to, 0) ;

    convert_column_to_charset ('artifact_counts_agg', 'body', $from, $to, 0) ;

    convert_column_to_charset ('stats_site_pages_by_day', 'body', $from, $to, 0) ;

    convert_column_to_charset ('massmail_queue', 'body', $from, $to, 0) ;
    convert_column_to_charset ('massmail_queue', 'type', $from, $to, 8) ;
    convert_column_to_charset ('massmail_queue', 'subject', $from, $to, 0) ;
    convert_column_to_charset ('massmail_queue', 'message', $from, $to, 0) ;

    convert_column_to_charset ('stats_agg_site_by_group', 'message', $from, $to, 0) ;

    convert_column_to_charset ('stats_project_metric', 'message', $from, $to, 0) ;

    convert_column_to_charset ('stats_agg_logo_by_group', 'message', $from, $to, 0) ;

    convert_column_to_charset ('stats_subd_pages', 'message', $from, $to, 0) ;

    convert_column_to_charset ('stats_cvs_user', 'message', $from, $to, 0) ;

    convert_column_to_charset ('stats_cvs_group', 'message', $from, $to, 0) ;

    convert_column_to_charset ('stats_project_developers', 'message', $from, $to, 0) ;

    convert_column_to_charset ('stats_project', 'message', $from, $to, 0) ;

    convert_column_to_charset ('stats_site', 'message', $from, $to, 0) ;

    convert_column_to_charset ('activity_log_old_old', 'message', $from, $to, 0) ;
    convert_column_to_charset ('activity_log_old_old', 'browser', $from, $to, 8) ;
    convert_column_to_charset ('activity_log_old_old', 'platform', $from, $to, 8) ;
    convert_column_to_charset ('activity_log_old_old', 'page', $from, $to, 0) ;

    convert_column_to_charset ('activity_log_old', 'page', $from, $to, 0) ;
    convert_column_to_charset ('activity_log_old', 'browser', $from, $to, 8) ;
    convert_column_to_charset ('activity_log_old', 'platform', $from, $to, 8) ;
    convert_column_to_charset ('activity_log_old', 'page', $from, $to, 0) ;

    convert_column_to_charset ('activity_log', 'page', $from, $to, 0) ;
    convert_column_to_charset ('activity_log', 'browser', $from, $to, 8) ;
    convert_column_to_charset ('activity_log', 'platform', $from, $to, 8) ;
    convert_column_to_charset ('activity_log', 'page', $from, $to, 0) ;

    convert_column_to_charset ('user_metric_history', 'page', $from, $to, 0) ;

    convert_column_to_charset ('frs_dlstats_filetotal_agg', 'page', $from, $to, 0) ;

    convert_column_to_charset ('stats_project_months', 'page', $from, $to, 0) ;

    convert_column_to_charset ('stats_site_pages_by_month', 'page', $from, $to, 0) ;

    convert_column_to_charset ('stats_site_months', 'page', $from, $to, 0) ;

    convert_column_to_charset ('trove_agg', 'page', $from, $to, 0) ;
    convert_column_to_charset ('trove_agg', 'group_name', $from, $to, 40) ;
    convert_column_to_charset ('trove_agg', 'unix_group_name', $from, $to, 30) ;
    convert_column_to_charset ('trove_agg', 'short_description', $from, $to, 255) ;

    convert_column_to_charset ('trove_treesums', 'short_description', $from, $to, 255) ;

    convert_column_to_charset ('frs_dlstats_file', 'ip_address', $from, $to, 0) ;

    convert_column_to_charset ('group_cvs_history', 'ip_address', $from, $to, 0) ;
    convert_column_to_charset ('group_cvs_history', 'user_name', $from, $to, 80) ;

    convert_column_to_charset ('themes', 'user_name', $from, $to, 80) ;
    convert_column_to_charset ('themes', 'dirname', $from, $to, 80) ;
    convert_column_to_charset ('themes', 'fullname', $from, $to, 80) ;

    convert_column_to_charset ('theme_prefs', 'fullname', $from, $to, 80) ;

    convert_column_to_charset ('supported_languages', 'fullname', $from, $to, 80) ;
    convert_column_to_charset ('supported_languages', 'name', $from, $to, 0) ;
    convert_column_to_charset ('supported_languages', 'filename', $from, $to, 0) ;
    convert_column_to_charset ('supported_languages', 'classname', $from, $to, 0) ;

    convert_column_to_charset ('skills_data_types', 'classname', $from, $to, 0) ;
    convert_column_to_charset ('skills_data_types', 'type_name', $from, $to, 25) ;

    convert_column_to_charset ('skills_data', 'type_name', $from, $to, 25) ;
    convert_column_to_charset ('skills_data', 'title', $from, $to, 100) ;
    convert_column_to_charset ('skills_data', 'keywords', $from, $to, 255) ;

    convert_column_to_charset ('project_category', 'keywords', $from, $to, 255) ;
    convert_column_to_charset ('project_category', 'category_name', $from, $to, 0) ;

    convert_column_to_charset ('project_task_artifact', 'category_name', $from, $to, 0) ;

    convert_column_to_charset ('project_group_forum', 'category_name', $from, $to, 0) ;

    convert_column_to_charset ('project_group_doccat', 'category_name', $from, $to, 0) ;

    convert_column_to_charset ('project_messages', 'category_name', $from, $to, 0) ;
    convert_column_to_charset ('project_messages', 'body', $from, $to, 0) ;

    convert_column_to_charset ('plugins', 'body', $from, $to, 0) ;
    convert_column_to_charset ('plugins', 'plugin_name', $from, $to, 32) ;
    convert_column_to_charset ('plugins', 'plugin_desc', $from, $to, 0) ;

    convert_column_to_charset ('group_plugin', 'plugin_desc', $from, $to, 0) ;

    convert_column_to_charset ('user_plugin', 'plugin_desc', $from, $to, 0) ;

# CREATE TABLE "canned_responses" (
# 	"response_title" character varying(25),
# 	"response_text" text,

# CREATE TABLE "db_images" (
# 	"description" text DEFAULT '' NOT NULL,
# 	"bin_data" text DEFAULT '' NOT NULL,
# 	"filename" text DEFAULT '' NOT NULL,
# 	"filetype" text DEFAULT '' NOT NULL,

# CREATE TABLE "doc_data" (
# 	"title" character varying(255) DEFAULT '' NOT NULL,
# 	"data" text DEFAULT '' NOT NULL,
# 	"description" text,
# 	"filename" text,
# 	"filetype" text,

# CREATE TABLE "doc_groups" (
# 	"groupname" character varying(255) DEFAULT '' NOT NULL,

# CREATE TABLE "doc_states" (
# 	"name" character varying(255) DEFAULT '' NOT NULL,

# CREATE TABLE "forum" (
# 	"subject" text DEFAULT '' NOT NULL,
# 	"body" text DEFAULT '' NOT NULL,

# CREATE TABLE "forum_group_list" (
# 	"forum_name" text DEFAULT '' NOT NULL,
# 	"description" text,
# 	"send_all_posts_to" text,

# CREATE TABLE "frs_file" (
# 	"filename" text,

# CREATE TABLE "frs_filetype" (
# 	"name" text,

# CREATE TABLE "frs_package" (
# 	"name" text,

# CREATE TABLE "frs_processor" (
# 	"name" text,

# CREATE TABLE "frs_release" (
# 	"name" text,
# 	"notes" text,
# 	"changes" text,

# CREATE TABLE "frs_status" (
# 	"name" text,

# CREATE TABLE "group_history" (
# 	"field_name" text DEFAULT '' NOT NULL,
# 	"old_value" text DEFAULT '' NOT NULL,

# CREATE TABLE "groups" (
# 	"group_name" character varying(40),
# 	"homepage" character varying(128),
# 	"unix_group_name" character varying(30) DEFAULT '' NOT NULL,
# 	"unix_box" character varying(20) DEFAULT 'shell1' NOT NULL,
# 	"http_domain" character varying(80),
# 	"short_description" character varying(255),
# 	"cvs_box" character varying(20) DEFAULT 'cvs1' NOT NULL,
# 	"license" character varying(16),
# 	"register_purpose" text,
# 	"license_other" text,
# 	"rand_hash" text,
# 	"new_doc_address" text DEFAULT '' NOT NULL,

# CREATE TABLE "mail_group_list" (
# 	"list_name" text,
# 	"password" character varying(16),
# 	"description" text,

# CREATE TABLE "news_bytes" (
# 	"summary" text,
# 	"details" text,

# CREATE TABLE "people_job" (
# 	"title" text,
# 	"description" text,

# CREATE TABLE "people_job_category" (
# 	"name" text,

# CREATE TABLE "people_job_status" (
# 	"name" text,

# CREATE TABLE "people_skill" (
# 	"name" text,

# CREATE TABLE "people_skill_level" (
# 	"name" text,

# CREATE TABLE "people_skill_year" (
# 	"name" text,

# CREATE TABLE "project_group_list" (
# 	"project_name" text DEFAULT '' NOT NULL,
# 	"description" text,
# 	"send_all_posts_to" text,

# CREATE TABLE "project_history" (
# 	"field_name" text DEFAULT '' NOT NULL,
# 	"old_value" text DEFAULT '' NOT NULL,

# CREATE TABLE "project_status" (
# 	"status_name" text DEFAULT '' NOT NULL,

# CREATE TABLE "project_task" (
# 	"summary" text DEFAULT '' NOT NULL,
# 	"details" text DEFAULT '' NOT NULL,

# CREATE TABLE "session" (
# 	"session_hash" character(32) DEFAULT '' NOT NULL,
# 	"ip_addr" character(15) DEFAULT '' NOT NULL,

# CREATE TABLE "snippet" (
# 	"name" text,
# 	"description" text,
# 	"license" text DEFAULT '' NOT NULL,

# CREATE TABLE "snippet_package" (
# 	"name" text,
# 	"description" text,

# CREATE TABLE "snippet_package_version" (
# 	"changes" text,
# 	"version" text,

# CREATE TABLE "snippet_version" (
# 	"changes" text,
# 	"version" text,
# 	"code" text,

# CREATE TABLE "survey_question_types" (
# 	"type" text DEFAULT '' NOT NULL,

# CREATE TABLE "survey_questions" (
# 	"question" text DEFAULT '' NOT NULL,

# CREATE TABLE "survey_responses" (
# 	"response" text DEFAULT '' NOT NULL,

# CREATE TABLE "surveys" (
# 	"survey_title" text DEFAULT '' NOT NULL,
# 	"survey_questions" text DEFAULT '' NOT NULL,

# CREATE TABLE "trove_cat" (
# 	"shortname" character varying(80),
# 	"fullname" character varying(80),
# 	"description" character varying(255),
# 	"fullpath" text DEFAULT '' NOT NULL,
# 	"fullpath_ids" text,

# CREATE TABLE "user_bookmarks" (
# 	"bookmark_url" text,
# 	"bookmark_title" text,

# CREATE TABLE "user_diary" (
# 	"summary" text,
# 	"details" text,

# CREATE TABLE "user_group" (
# 	"admin_flags" character(16) DEFAULT '' NOT NULL,

# CREATE TABLE "user_preferences" (
# 	"preference_name" character varying(20),
# 	"preference_value" text

# CREATE TABLE "users" (
# 	"user_name" text DEFAULT '' NOT NULL,
# 	"email" text DEFAULT '' NOT NULL,
# 	"user_pw" character varying(32) DEFAULT '' NOT NULL,
# 	"realname" character varying(32) DEFAULT '' NOT NULL,
# 	"shell" character varying(20) DEFAULT '/bin/bash' NOT NULL,
# 	"unix_pw" character varying(40) DEFAULT '' NOT NULL,
# 	"authorized_keys" text,
# 	"email_new" text,
# 	"people_resume" text DEFAULT '' NOT NULL,
# 	"jabber_address" text,

# CREATE TABLE "prdb_dbs" (
# 	"dbname" text NOT NULL,
# 	"dbusername" text NOT NULL,
# 	"dbuserpass" text NOT NULL,

# CREATE TABLE "prdb_states" (
# 	"statename" text

# CREATE TABLE "prdb_types" (
# 	"dbservername" text NOT NULL,
# 	"dbsoftware" text NOT NULL,

# CREATE TABLE "prweb_vhost" (
# 	"vhost_name" text,
# 	"docdir" text,
# 	"cgidir" text,

# CREATE TABLE "artifact_group_list" (
# 	"name" text,
# 	"description" text,
# 	"email_address" text NOT NULL,
# 	"submit_instructions" text,
# 	"browse_instructions" text,

# CREATE TABLE "artifact_resolution" (
# 	"resolution_name" text,


# CREATE TABLE "artifact_category" (
# 	"category_name" text NOT NULL,

# CREATE TABLE "artifact_group" (
# 	"group_name" text NOT NULL,

# CREATE TABLE "artifact_status" (
# 	"status_name" text NOT NULL,

# CREATE TABLE "artifact" (
# 	"summary" text NOT NULL,
# 	"details" text NOT NULL,

# CREATE TABLE "artifact_history" (
# 	"field_name" text DEFAULT '' NOT NULL,
# 	"old_value" text DEFAULT '' NOT NULL,

# CREATE TABLE "artifact_file" (
# 	"description" text NOT NULL,
# 	"bin_data" text NOT NULL,
# 	"filename" text NOT NULL,
# 	"filetype" text NOT NULL,

# CREATE TABLE "artifact_message" (
# 	"from_email" text NOT NULL,
# 	"body" text NOT NULL,

# CREATE TABLE "artifact_monitor" (
# 	"email" text,

# CREATE TABLE "artifact_canned_responses" (
# 	"title" text NOT NULL,
# 	"body" text NOT NULL,

# CREATE TABLE "massmail_queue" (
# 	"subject" text NOT NULL,
# 	"message" text NOT NULL,

# CREATE TABLE "activity_log_old_old" (
# 	"browser" character varying(8) DEFAULT 'OTHER' NOT NULL,
# 	"platform" character varying(8) DEFAULT 'OTHER' NOT NULL,
# 	"page" text,

# CREATE TABLE "activity_log_old" (
# 	"browser" character varying(8) DEFAULT 'OTHER' NOT NULL,
# 	"platform" character varying(8) DEFAULT 'OTHER' NOT NULL,
# 	"page" text,

# CREATE TABLE "activity_log" (
# 	"browser" character varying(8) DEFAULT 'OTHER' NOT NULL,
# 	"platform" character varying(8) DEFAULT 'OTHER' NOT NULL,
# 	"page" text,

# CREATE TABLE "trove_agg" (
# 	"group_name" character varying(40),
# 	"unix_group_name" character varying(30),
# 	"short_description" character varying(255),

# CREATE TABLE "frs_dlstats_file" (
# 	"ip_address" text,

# CREATE TABLE "group_cvs_history" (
# 	"user_name" character varying(80) DEFAULT '' NOT NULL,

# CREATE TABLE "themes" (
# 	"dirname" character varying(80),
# 	"fullname" character varying(80)

# CREATE TABLE "theme_prefs" (
# 	"body_font" character(80) DEFAULT '',
# 	"body_size" character(5) DEFAULT '',
# 	"titlebar_font" character(80) DEFAULT '',
# 	"titlebar_size" character(5) DEFAULT '',
# 	"color_titlebar_back" character(7) DEFAULT '',
# 	"color_ltback1" character(7) DEFAULT '',

# CREATE TABLE "supported_languages" (
# 	"name" text,
# 	"filename" text,
# 	"classname" text,

# CREATE TABLE "skills_data_types" (
# 	"type_name" character varying(25) DEFAULT '' NOT NULL,

# CREATE TABLE "skills_data" (
# 	"title" character varying(100) DEFAULT '' NOT NULL,
# 	"keywords" character varying(255) DEFAULT '' NOT NULL,

# CREATE TABLE "project_category" (
# 	"category_name" text


# CREATE TABLE "project_messages" (
# 	"body" text,

# CREATE TABLE "plugins" (
# 	"plugin_name" character varying(32) NOT NULL,
# 	"plugin_desc" text,

    debug "It seems your database conversion went well and smoothly.  That's cool." ;
    debug "Please enjoy using Debian Sourceforge." ;

    # There should be a commit at the end of every block above.
    # If there is not, then it might be symptomatic of a problem.
    # For safety, we roll back.
    $dbh->rollback ();
};

if ($@) {
    warn "Transaction aborted because $@" ;
    debug "Transaction aborted because $@" ;
    debug "Last SQL query was:\n$query\n(end of query)" ;
    $dbh->rollback ;
    my $version = &get_db_version ; 
    if ($version) {
	debug "Your database schema is at version $version" ;
    } else {
	debug "Couldn't get your database schema version." ;
    }
    debug "Please report this bug on the Debian bug-tracking system." ;
    debug "Please include the previous messages as well to help debugging." ;
    debug "You should not worry too much about this," ;
    debug "your DB is still in a consistent state and should be usable." ;
    exit 1 ;
}

$dbh->rollback ;
$dbh->disconnect ;

sub convert_column_to_charset ( $$$$$ ) {
    my $table = shift or die "Not enough arguments" ;
    my $column = shift or die "Not enough arguments" ;
    my $from = shift or die "Not enough arguments" ;
    my $to = shift or die "Not enough arguments" ;
    my $size = shift or die "Not enough arguments" ;

    if ($size > 0) {
	$query = "UPDATE $table SET $column = substr (convert ($column, '$from', '$to'), 0, $size)" ;
    } else {
	$query = "UPDATE $table SET $column = convert ($column, '$from', '$to')" ;
    }
    # debug $query ;
    my $sth = $dbh->prepare ($query) ;
    $sth->execute () ;
    $sth->finish () ;
}

sub debug ( $ ) {
    my $v = shift ;
    chomp $v ;
    print STDERR "$v\n" ;
}
