#!/usr/bin/perl -w
#
# Debian-specific script to upgrade the database between releases
# Roland Mas <lolando@debian.org>

use strict ;
use diagnostics ;

use DBI ;
use MIME::Base64 ;
use HTML::Entities ;

use vars qw/$dbh @reqlist $query/ ;
use vars qw/$sys_default_domain $sys_scm_host $sys_download_host
    $sys_shell_host $sys_users_host $sys_docs_host $sys_lists_host
    $sys_dns1_host $sys_dns2_host $FTPINCOMING_DIR $FTPFILES_DIR
    $sys_urlroot $sf_cache_dir $sys_name $sys_themeroot
    $sys_news_group $sys_dbhost $sys_dbname $sys_dbuser $sys_dbpasswd
    $sys_ldap_base_dn $sys_ldap_host $admin_login $admin_password
    $domain_name $newsadmin_groupid $statsadmin_groupid
    $skill_list/ ;

sub debug ( $ ) ;
sub convert_column_to_charset ( $$$$$ ) ;

require ("/usr/share/gforge/lib/include.pl") ; # Include a few predefined functions 
require ("/usr/share/gforge/lib/sqlparser.pm") ; # Our magic SQL parser

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
    convert_column_to_charset ('canned_responses', 'response_text', $from, $to, -1) ;

    convert_column_to_charset ('db_images', 'description', $from, $to, -1) ;
    convert_column_to_charset ('db_images', 'bin_data', $from, $to, -1) ;
    convert_column_to_charset ('db_images', 'filename', $from, $to, -1) ;
    convert_column_to_charset ('db_images', 'filetype', $from, $to, -1) ;

    convert_column_to_charset ('doc_data', 'title', $from, $to, 255) ;
    convert_column_to_charset ('doc_data', 'data', $from, $to, -1) ;
    convert_column_to_charset ('doc_data', 'description', $from, $to, -1) ;
    convert_column_to_charset ('doc_data', 'filename', $from, $to, -1) ;
    convert_column_to_charset ('doc_data', 'filetype', $from, $to, -1) ;

    convert_column_to_charset ('doc_groups', 'groupname', $from, $to, 255) ;

    convert_column_to_charset ('doc_states', 'name', $from, $to, 255) ;

    convert_column_to_charset ('forum', 'subject', $from, $to, -1) ;
    convert_column_to_charset ('forum', 'body', $from, $to, -1) ;

    convert_column_to_charset ('forum_group_list', 'forum_name', $from, $to, -1) ;
    convert_column_to_charset ('forum_group_list', 'description', $from, $to, -1) ;
    convert_column_to_charset ('forum_group_list', 'send_all_posts_to', $from, $to, -1) ;

    convert_column_to_charset ('frs_file', 'filename', $from, $to, -1) ;

    convert_column_to_charset ('frs_filetype', 'name', $from, $to, -1) ;

    convert_column_to_charset ('frs_package', 'name', $from, $to, -1) ;

    convert_column_to_charset ('frs_processor', 'name', $from, $to, -1) ;

    convert_column_to_charset ('frs_release', 'name', $from, $to, -1) ;
    convert_column_to_charset ('frs_release', 'notes', $from, $to, -1) ;
    convert_column_to_charset ('frs_release', 'changes', $from, $to, -1) ;

    convert_column_to_charset ('frs_status', 'name', $from, $to, -1) ;

    convert_column_to_charset ('group_history', 'field_name', $from, $to, -1) ;
    convert_column_to_charset ('group_history', 'old_value', $from, $to, -1) ;

    convert_column_to_charset ('groups', 'group_name', $from, $to, 40) ;
    convert_column_to_charset ('groups', 'homepage', $from, $to, 128) ;
    convert_column_to_charset ('groups', 'unix_group_name', $from, $to, 30) ;
    convert_column_to_charset ('groups', 'unix_box', $from, $to, 20) ;
    convert_column_to_charset ('groups', 'http_domain', $from, $to, 80) ;
    convert_column_to_charset ('groups', 'short_description', $from, $to, 255) ;
    convert_column_to_charset ('groups', 'cvs_box', $from, $to, 20) ;
    convert_column_to_charset ('groups', 'license', $from, $to, 16) ;
    convert_column_to_charset ('groups', 'register_purpose', $from, $to, -1) ;
    convert_column_to_charset ('groups', 'license_other', $from, $to, -1) ;
    convert_column_to_charset ('groups', 'rand_hash', $from, $to, -1) ;
    convert_column_to_charset ('groups', 'new_doc_address', $from, $to, -1) ;

    convert_column_to_charset ('mail_group_list', 'list_name', $from, $to, -1) ;
    convert_column_to_charset ('mail_group_list', 'password', $from, $to, 16) ;
    convert_column_to_charset ('mail_group_list', 'description', $from, $to, -1) ;

    convert_column_to_charset ('news_bytes', 'summary', $from, $to, -1) ;
    convert_column_to_charset ('news_bytes', 'details', $from, $to, -1) ;

    convert_column_to_charset ('people_job', 'title', $from, $to, -1) ;
    convert_column_to_charset ('people_job', 'description', $from, $to, -1) ;

    convert_column_to_charset ('people_job_category', 'name', $from, $to, -1) ;

    convert_column_to_charset ('people_job_status', 'name', $from, $to, -1) ;

    convert_column_to_charset ('people_skill', 'name', $from, $to, -1) ;

    convert_column_to_charset ('people_skill_level', 'name', $from, $to, -1) ;

    convert_column_to_charset ('people_skill_year', 'name', $from, $to, -1) ;

    convert_column_to_charset ('project_group_list', 'project_name', $from, $to, -1) ;
    convert_column_to_charset ('project_group_list', 'description', $from, $to, -1) ;
    convert_column_to_charset ('project_group_list', 'send_all_posts_to', $from, $to, -1) ;

    convert_column_to_charset ('project_history', 'field_name', $from, $to, -1) ;
    convert_column_to_charset ('project_history', 'old_value', $from, $to, -1) ;

    convert_column_to_charset ('project_status', 'status_name', $from, $to, -1) ;

    convert_column_to_charset ('project_task', 'summary', $from, $to, -1) ;
    convert_column_to_charset ('project_task', 'details', $from, $to, -1) ;

    convert_column_to_charset ('snippet', 'name', $from, $to, -1) ;
    convert_column_to_charset ('snippet', 'description', $from, $to, -1) ;
    convert_column_to_charset ('snippet', 'license', $from, $to, -1) ;

    convert_column_to_charset ('snippet_package', 'name', $from, $to, -1) ;
    convert_column_to_charset ('snippet_package', 'description', $from, $to, -1) ;

    convert_column_to_charset ('snippet_package_version', 'changes', $from, $to, -1) ;
    convert_column_to_charset ('snippet_package_version', 'version', $from, $to, -1) ;

    convert_column_to_charset ('snippet_version', 'changes', $from, $to, -1) ;
    convert_column_to_charset ('snippet_version', 'version', $from, $to, -1) ;
    convert_column_to_charset ('snippet_version', 'code', $from, $to, -1) ;

    convert_column_to_charset ('survey_question_types', 'type', $from, $to, -1) ;

    convert_column_to_charset ('survey_questions', 'question', $from, $to, -1) ;

    convert_column_to_charset ('survey_responses', 'response', $from, $to, -1) ;

    convert_column_to_charset ('surveys', 'survey_title', $from, $to, -1) ;
    convert_column_to_charset ('surveys', 'survey_questions', $from, $to, -1) ;

    convert_column_to_charset ('trove_cat', 'shortname', $from, $to, 80) ;
    convert_column_to_charset ('trove_cat', 'fullname', $from, $to, 80) ;
    convert_column_to_charset ('trove_cat', 'description', $from, $to, 255) ;
    convert_column_to_charset ('trove_cat', 'fullpath', $from, $to, -1) ;
    convert_column_to_charset ('trove_cat', 'fullpath_ids', $from, $to, -1) ;

    convert_column_to_charset ('user_bookmarks', 'bookmark_url', $from, $to, -1) ;
    convert_column_to_charset ('user_bookmarks', 'bookmark_title', $from, $to, -1) ;

    convert_column_to_charset ('user_diary', 'summary', $from, $to, -1) ;
    convert_column_to_charset ('user_diary', 'details', $from, $to, -1) ;

    convert_column_to_charset ('user_preferences', 'preference_name', $from, $to, 20) ;
    convert_column_to_charset ('user_preferences', 'preference_value', $from, $to, -1) ;

    convert_column_to_charset ('users', 'user_name', $from, $to, -1) ;
    convert_column_to_charset ('users', 'email', $from, $to, -1) ;
    convert_column_to_charset ('users', 'user_pw', $from, $to, 32) ;
    convert_column_to_charset ('users', 'realname', $from, $to, 32) ;
    convert_column_to_charset ('users', 'shell', $from, $to, 20) ;
    convert_column_to_charset ('users', 'unix_pw', $from, $to, 40) ;
    convert_column_to_charset ('users', 'unix_box', $from, $to, 10) ;
    convert_column_to_charset ('users', 'confirm_hash', $from, $to, 32) ;
    convert_column_to_charset ('users', 'authorized_keys', $from, $to, -1) ;
    convert_column_to_charset ('users', 'email_new', $from, $to, -1) ;
    convert_column_to_charset ('users', 'people_resume', $from, $to, -1) ;
    convert_column_to_charset ('users', 'timezone', $from, $to, 64) ;
    convert_column_to_charset ('users', 'jabber_address', $from, $to, -1) ;

    convert_column_to_charset ('prdb_dbs', 'dbname', $from, $to, -1) ;
    convert_column_to_charset ('prdb_dbs', 'dbusername', $from, $to, -1) ;
    convert_column_to_charset ('prdb_dbs', 'dbuserpass', $from, $to, -1) ;

    convert_column_to_charset ('prdb_states', 'statename', $from, $to, -1) ;

    convert_column_to_charset ('prdb_types', 'dbservername', $from, $to, -1) ;
    convert_column_to_charset ('prdb_types', 'dbsoftware', $from, $to, -1) ;

    convert_column_to_charset ('prweb_vhost', 'vhost_name', $from, $to, -1) ;
    convert_column_to_charset ('prweb_vhost', 'docdir', $from, $to, -1) ;
    convert_column_to_charset ('prweb_vhost', 'cgidir', $from, $to, -1) ;

    convert_column_to_charset ('artifact_group_list', 'name', $from, $to, -1) ;
    convert_column_to_charset ('artifact_group_list', 'description', $from, $to, -1) ;
    convert_column_to_charset ('artifact_group_list', 'email_address', $from, $to, -1) ;
    convert_column_to_charset ('artifact_group_list', 'submit_instructions', $from, $to, -1) ;
    convert_column_to_charset ('artifact_group_list', 'browse_instructions', $from, $to, -1) ;

    convert_column_to_charset ('artifact_resolution', 'resolution_name', $from, $to, -1) ;

    convert_column_to_charset ('artifact_category', 'category_name', $from, $to, -1) ;

    convert_column_to_charset ('artifact_group', 'group_name', $from, $to, -1) ;

    convert_column_to_charset ('artifact_status', 'status_name', $from, $to, -1) ;

    convert_column_to_charset ('artifact', 'summary', $from, $to, -1) ;
    convert_column_to_charset ('artifact', 'details', $from, $to, -1) ;

    convert_column_to_charset ('artifact_history', 'field_name', $from, $to, -1) ;
    convert_column_to_charset ('artifact_history', 'old_value', $from, $to, -1) ;

    convert_column_to_charset ('artifact_file', 'description', $from, $to, -1) ;
    convert_column_to_charset ('artifact_file', 'bin_data', $from, $to, -1) ;
    convert_column_to_charset ('artifact_file', 'filename', $from, $to, -1) ;
    convert_column_to_charset ('artifact_file', 'filetype', $from, $to, -1) ;

    convert_column_to_charset ('artifact_message', 'from_email', $from, $to, -1) ;
    convert_column_to_charset ('artifact_message', 'body', $from, $to, -1) ;

    convert_column_to_charset ('artifact_monitor', 'email', $from, $to, -1) ;

    convert_column_to_charset ('artifact_canned_responses', 'title', $from, $to, -1) ;
    convert_column_to_charset ('artifact_canned_responses', 'body', $from, $to, -1) ;

    convert_column_to_charset ('massmail_queue', 'type', $from, $to, 8) ;
    convert_column_to_charset ('massmail_queue', 'subject', $from, $to, -1) ;
    convert_column_to_charset ('massmail_queue', 'message', $from, $to, -1) ;

    convert_column_to_charset ('activity_log', 'browser', $from, $to, 8) ;
    convert_column_to_charset ('activity_log', 'platform', $from, $to, 8) ;
    convert_column_to_charset ('activity_log', 'page', $from, $to, -1) ;

    convert_column_to_charset ('trove_agg', 'group_name', $from, $to, 40) ;
    convert_column_to_charset ('trove_agg', 'unix_group_name', $from, $to, 30) ;
    convert_column_to_charset ('trove_agg', 'short_description', $from, $to, 255) ;

    convert_column_to_charset ('frs_dlstats_file', 'ip_address', $from, $to, -1) ;

    convert_column_to_charset ('group_cvs_history', 'user_name', $from, $to, 80) ;

    convert_column_to_charset ('themes', 'dirname', $from, $to, 80) ;
    convert_column_to_charset ('themes', 'fullname', $from, $to, 80) ;

    convert_column_to_charset ('supported_languages', 'name', $from, $to, -1) ;
    convert_column_to_charset ('supported_languages', 'filename', $from, $to, -1) ;
    convert_column_to_charset ('supported_languages', 'classname', $from, $to, -1) ;

    convert_column_to_charset ('skills_data_types', 'type_name', $from, $to, 25) ;

    convert_column_to_charset ('skills_data', 'title', $from, $to, 100) ;
    convert_column_to_charset ('skills_data', 'keywords', $from, $to, 255) ;

    convert_column_to_charset ('project_category', 'category_name', $from, $to, -1) ;

    convert_column_to_charset ('project_messages', 'body', $from, $to, -1) ;

    convert_column_to_charset ('plugins', 'plugin_name', $from, $to, 32) ;
    convert_column_to_charset ('plugins', 'plugin_desc', $from, $to, -1) ;

    $dbh->commit ();

    debug "It seems your database conversion went well and smoothly.  That's cool." ;
    debug "Please enjoy using Gforge." ;

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
