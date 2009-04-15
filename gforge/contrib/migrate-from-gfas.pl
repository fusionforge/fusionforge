#! /usr/bin/perl -w

# GForge AS to FusionForge database migration script
# Copyright 2009, Roland Mas

# GForge AS database is gfas
# FusionForge will be fusionforge

# /etc/gforge/gforge.conf: db_name → fusionforge
# gforge-config

# su - postgres -c 'dropdb fusionforge'
# /usr/lib/gforge/bin/install-db.sh configure

use DBI ;
use Data::Dumper ;
# use strict ;

use vars qw/$dbhAS $dbhFF $map @arrayAS $sthAS $sthFF/ ;

$dbhAS = DBI->connect("DBI:Pg:dbname=gfas;host=localhost","gforge","$ENV{DB_PW}") ;
$dbhFF = DBI->connect("DBI:Pg:dbname=fusionforge;host=localhost","gforge","$ENV{DB_PW}") ;

$dbhFF->begin_work ;

sub migrate_with_mapping ( $$$;$ ) {
    my $tsrc = shift ;
    my $tdest = shift ;
    my $mapping = shift ;
    my $where = shift || "" ;

    my @scols = keys %$mapping ;
    
    my $sql1 = "SELECT " . join (", ", @scols) . " FROM $tsrc $where" ;
    my $sth1 = $dbhAS->prepare ($sql1) ;
    # print Dumper $sql1 ;

    my $sql2 = "INSERT INTO $tdest (" . join (", ", map { $mapping->{$_} } @scols)
	. ") VALUES (" . join (", ", map { "?" } @scols) . ")" ;
    my $sth2 = $dbhFF->prepare ($sql2) ;
    # print Dumper $sql2 ;

    $sth1->execute ;
    while (my @arr = $sth1->fetchrow_array) {
	unless ($sth2->execute (@arr)) {
	    print "$sql2\n" ;
	    print Dumper \@arr ;
	    return 0;
	}
    }
    $sth1->finish ; $sth2->finish ;

    return 1 ;
}

### Migrate users
$map = {
    'user_id' => 'user_id',
    'unix_name' => 'user_name',
    'email' => 'email',
    'status' => 'status',
    'password_md5' => 'user_pw',
    'password_crypt' => 'unix_pw',
    'extract (epoch from create_date)::integer' => 'add_date',
    'confirm_hash' => 'confirm_hash',
    'email_new' => 'email_new',
    'timezone' => 'timezone',
    "address" => 'address',
    "address2" => 'address2',
    'phone' => 'phone',
    'fax' => 'fax',
    "title" => 'title',
    "firstname" => 'firstname',
    "lastname" => 'lastname',
    "firstname || ' ' || lastname" => 'realname',
    'ccode' => 'ccode',
    'language_id' => 'language',
} ;
print STDERR "Migrating users\n" ;
migrate_with_mapping ('public.user', 'users', $map, "where unix_name not in ('admin', 'None')") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

print STDERR "Updating users\n" ;
$dbhFF->do ("update users set status='A' where status='1'") ;
$dbhFF->do ("update users set status='N' where status='2'") ;
$dbhFF->do ("update users set status='P' where status='0'") ;
# Order matters!
$dbhFF->do ("update users set language = 23 where language = 4") ;
$dbhFF->do ("update users set language =  4 where language = 2") ;
$dbhFF->do ("update users set language =  2 where language = 9") ;
$dbhFF->do ("update users set language = 22 where language = 5") ;
$dbhFF->do ("update users set language = 11 where language = 6") ;
$dbhFF->do ("update users set language =  6 where language = 8") ;
$dbhFF->do ("update users set language =  8 where language = 7") ;
$dbhFF->do ("update users set language =  7 where language = 3") ;

foreach my $i (qw/address address2 title firstname lastname realname/) {
    $dbhFF->do ("update users set $i=convert_from (convert (convert_to ($i, 'UTF8'), 'UTF8', 'ISO-8859-9'), 'UTF8') where $i LIKE '%Ã%'") or die $i ;
}    

$sthAS = $dbhAS->prepare ("select user_unix.user_id, unix_shell.path from user_unix, unix_shell where user_unix.unix_shell_id = unix_shell.unix_shell_id") ;
$sthFF = $dbhFF->prepare ("update users set unix_status='A', shell=? where user_id=? and status='A'") ;
$sthAS->execute ;
while (@arrayAS = $sthAS->fetchrow_array) {
    my $uid = $arrayAS[0] ;
    my $shell = $arrayAS[1] ;
    if ($shell eq '/bin/cvssh.pl') { $shell = '/bin/cvssh' ; }
    $sthFF->execute ($shell, $uid) ;
}
$sthAS->finish ;
$sthFF->finish ;

### User preferences
$map = {
    'user_id' => 'user_id',
    'preference_name' => 'preference_name',
    'preference_value' => 'preference_value',
    'extract (epoch from set_date)::integer' => 'set_date',
} ;
print STDERR "Migrating user preferences\n" ;
migrate_with_mapping ('user_preference', 'user_preferences', $map) ;

### Migrate groups
# First need to get rid of the template project
$dbhFF->do ("delete from groups where group_id = 5") ;
$dbhFF->do ("delete from forum_group_list where group_id = 5") ;

$map = {
    'project_id' => 'group_id',
    "project_name" => 'group_name',
    'unix_name' => 'unix_group_name',
    'homepage_url' => 'homepage',
    'is_public' => 'is_public',
    'status' => 'status',
    "substr (description, 0, 255)" => 'short_description',
    "register_purpose" => 'register_purpose',
    "register_license_other" => 'license_other',
    'extract (epoch from create_date)::integer' => 'register_time',
} ;
print STDERR "Migrating groups\n" ;
migrate_with_mapping ('project', 'groups', $map, "where project_id > 4") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

print STDERR "Updating groups\n" ;
$dbhFF->do ("update groups set status='A' where status='1'") ;
$dbhFF->do ("update groups set status='H' where status='3'") ;

$map = {
    'forum' => 'forum',
    'tracker' => 'tracker',
    'docman' => 'docman',
    'news' => 'news',
    'frs' => 'frs',
    'mailman' => 'mail',
    'scmcvs' => 'scm',
    'scmsvn' => 'scm',
} ;

$sthAS = $dbhAS->prepare ("select project_plugin.project_id, count (plugin.plugin_id) from plugin, project_plugin where project_plugin.plugin_id = plugin.plugin_id and plugin.plugin_name=? group by project_plugin.project_id") ;
foreach my $i (keys %$map) {
    $dbhFF->do ("update groups set use_$map->{$i} = 0") ;

    $sthAS->execute ($i) ;
    $sthFF = $dbhFF->prepare ("update groups set use_$map->{$i} = ? where group_id = ?") ;
    while (@arrayAS = $sthAS->fetchrow_array) {
	my $project_id = $arrayAS[0] ;
	my $count = $arrayAS[1] ;
	$sthFF->execute ($count, $project_id) ;
    }
    $sthFF->finish ;
}
$sthAS->finish ;

foreach my $i (qw/short_description group_name register_purpose license_other/) {
    $dbhFF->do ("update groups set $i=convert_from (convert (convert_to ($i, 'UTF8'), 'UTF8', 'ISO-8859-9'), 'UTF8') where $i LIKE '%Ã%'") ;
}    

### Group memberships for users
$map = {
    'role_id' => 'role_id',
    'project_id' => 'group_id',
    'role_name' => 'role_name',
} ;
print STDERR "Migrating roles\n" ;
migrate_with_mapping ('role', 'role', $map, "where project_id > 4") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'user_project.user_id' => 'user_id',
    'user_project.project_id' => 'group_id', 
    'user_project_role.role_id' => 'role_id',
} ;
print STDERR "Migrating group memberships\n" ;
migrate_with_mapping ('user_project, user_project_role', 'user_group', $map, "where user_project.project_id > 4 and user_project.user_project_id = user_project_role.user_project_id") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

print STDERR "Updating siteadmin permissions\n" ;
my $siteadmin_roleid = 1 ;
$sthFF = $dbhFF->prepare ("select role_id from role where group_id=1 and role_name='Admin'") ;
$sthFF->execute ;
if (@arrayFF = $sthFF->fetchrow_array) {
    $siteadmin_roleid = $arrayFF[0] ;
}
$sthFF->finish ;

$sthFF = $dbhFF->prepare ("insert into user_group (user_id, group_id, role_id) values (?, 1, ?)") ;
$sthAS = $dbhAS->prepare ("select user_id from site_admin where user_id != 101") ;
$sthAS->execute ;
while (@arrayAS = $sthAS->fetchrow_array) {
    my $uid = $arrayAS[0] ;
    $sthFF->execute ($uid, $siteadmin_roleid) ;
}
$sthAS->finish ;
$sthFF->finish ;

### Role settings
$map = {
    'role_id' => 'role_id',
    'section' => 'section_name',
    'ref_id' => 'ref_id',
    'value' => 'value',
} ;
print STDERR "Migrating role settings\n" ;
migrate_with_mapping ('role_setting', 'role_setting', $map, "where role_id in (select role_id from role where project_id > 4)") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;
$dbhFF->do ("update role_setting set value='A' where value='1' and section_name='projectadmin'") ;
$dbhFF->do ("update user_group set admin_flags=(select value from role_setting where role_setting.role_id = user_group.role_id and  section_name='projectadmin')") ;

### Group join requests
$map = {
    'project_id' => 'group_id',
    'user_id' => 'user_id',
    "comments" => 'comments',
    'extract (epoch from request_date)::integer' => 'request_date',
} ;
print STDERR "Migrating group join requests\n" ;
migrate_with_mapping ('project_join_request', 'group_join_request', $map) 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

### Not migrating trove map categories (default values are identical), only mappings
$map = {
    'trove_link.ref_id' => 'group_id', 
    'trove_link.trove_category_id' => 'trove_cat_id',
    'trove_category.root_trove_category_id' => 'trove_cat_root',
} ;
print STDERR "Migrating trove categorisation\n" ;
migrate_with_mapping ('trove_link, trove_category', 'trove_group_link', $map, "where trove_link.trove_category_id = trove_category.trove_category_id and trove_link.section = 'project'") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

### Forums
$map = {
    'forum_id' => 'group_forum_id',
    'forum_name' => 'forum_name',
    'is_public' => 'is_public',
    'description' => 'description',
    'send_all_posts_to' => 'send_all_posts_to',
    'moderation_level' => 'moderation_level',
    'ref_id' => 'group_id',
} ;
print STDERR "Migrating forums\n" ;
migrate_with_mapping ('forum', 'forum_group_list', $map, "where section = 'project'") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'forum_message.forum_message_id' => 'msg_id',
    'forum_message.forum_thread_id' => 'thread_id',
    'forum_message.created_by' => 'posted_by',
    'forum_message.subject' => 'subject',
    'forum_message.body' => 'body',
    'extract (epoch from forum_message.post_date)::integer' => 'post_date',
    'forum_message.parent_forum_message_id' => 'is_followup_to',
    'forum_thread.forum_id' => 'group_forum_id',
    'extract (epoch from forum_thread.most_recent_date)::integer' => 'most_recent_date',
} ;
print STDERR "Migrating forum messages\n" ;
migrate_with_mapping ('forum_message, forum_thread', 'forum', $map, "where forum_message.is_approved = 't' and forum_message.forum_thread_id = forum_thread.forum_thread_id") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;
migrate_with_mapping ('forum_message, forum_thread', 'forum_pending_messages', $map, "where forum_message.is_approved = 'f' and forum_message.forum_thread_id = forum_thread.forum_thread_id") 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

### File release system
$map = {
    'frs_package_id' => 'package_id',
    'project_id' => 'group_id',
    'package_name' => 'name',
    'status_id' => 'status_id',
    'is_public' => 'is_public',
} ;
migrate_with_mapping ('frs_package', 'frs_package', $map) 
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'frs_release_id' => 'release_id',
    'frs_package_id' => 'package_id',
    'release_name' => 'name',
    'release_notes' => 'notes',
    'changes' => 'changes',
    'status_id' => 'status_id',
    'preformatted' => 'preformatted',
    'extract (epoch from release_date)::integer' => 'release_date',
    'released_by' => 'released_by',
} ;
migrate_with_mapping ('frs_release', 'frs_release', $map, "where status_id != 0")
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$sthAS = $dbhAS->prepare ("select filesystem.file_name, filesystem.ref_id, filesystem.file_size, filesystem.file_type, filesystem.posted_by, filesystem.download_count, extract (epoch from frs_release.release_date)::integer, frs_release.release_name, frs_package.package_name, project.unix_name from filesystem, frs_release, frs_package, project where filesystem.section = 'frsrelease' and filesystem.ref_id = frs_release.frs_release_id and frs_release.frs_package_id = frs_package.frs_package_id and frs_package.project_id = project.project_id and frs_release.status_id != 0") ;
$sthFF = $dbhFF->prepare ("insert into frs_file (filename, release_id, type_id, file_size, release_time, post_date, processor_id) values (?, ?, ?, ?, ?, ?, ?)") ;
$sthAS->execute ;
while (@arrayAS = $sthAS->fetchrow_array) {
    my $filename = $arrayAS[0] ;
    my $releaseid = $arrayAS[1] ;
    my $filesize = $arrayAS[2] ;
    my $filetype = $arrayAS[3] ;
    my $postedby = $arrayAS[4] ;
    my $downloadcount = $arrayAS[5] ;
    my $releasedate = $arrayAS[6] ;
    my $releasename = $arrayAS[7] ;
    my $packagename = $arrayAS[8] ;
    my $projectname = $arrayAS[9] ;

    my $mimemap = {
	'application/binary' => 9999,
	'application/gzip' => 3110,
	'application/java-archive' => 5900,
	'application/octet-stream' => 9999,
	'application/ogg' => 9999,
	'application/pdf' => 8300,
	'application/x-compressed-tar' => 5900,
	'application/x-gtar' => 5900,
	'application/x-gzip' => 3110,
	'application/x-java-archive' => 5900,
	'application/x-msdos-program' => 9999,
	'application/x-zip-compressed' => 3000,
	'application/zip' => 3000,
	'text/html' => 8200,
    } ;
    my $typeid = $mimemap->{$filetype} ;

    $packagename =~ s/[^a-zA-Z0-9_.-]//g ;
    $releasename =~ s/[^a-zA-Z0-9_.-]//g ;

    my $destdir = "/var/lib/gforge/download/$projectname/$packagename/$releasename" ;
    my $destfile = "$destdir/$filename" ;

    system "mkdir -p $destdir" ;
    system "touch $destfile" ; # Need to actually put the contents there...

    $sthFF->execute ($filename, $releaseid, $typeid, $filesize, $releasedate, $releasedate, 8000) ;
}
$sthAS->finish ;
$sthFF->finish ;

print STDERR "Migration script completed OK\n" ;
$dbhFF->commit ; print STDERR "Committed\n" ;
