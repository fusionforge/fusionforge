#! /usr/bin/perl -w

# GForge AS to FusionForge database migration script
# Copyright 2009, Roland Mas
#
# This file is part of FusionForge.
#
# FusionForge is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# FusionForge is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

######
# Usage:
# 0. Backups!
# 1. Rename the GForge AS database to "gfas"
# 2. Create the initial FusionForge database as "gforge"
# 3. Store a copy of the /var/lib/gforge/filesystem as /tmp/filesystem
# 4. export DB_PW=foobar (the password of the databases)
# 5. run migrate-from-gforge-as.pl
# In case something breaks, to get back to step 2:
# su - postgres -c 'dropdb gforge'
# /usr/share/gforge/bin/install-db.sh configure
# 6. If no error appears, uncomment the last line of this script and re-run it
######
# This script isn't complete, but it migrates the most important data.
# Feel free to adapt to your needs.
######

use DBI ;
use Data::Dumper ;
use MIME::Base64 ;
# use strict ;

require "/usr/share/gforge/lib/sqlhelper.pm" ;

use vars qw/$dbhAS $dbhFF $map @arrayAS $sthAS $sthFF/ ;

$dbhAS = DBI->connect("DBI:Pg:dbname=gfas;host=localhost","gforge","$ENV{DB_PW}") ;
$dbhFF = DBI->connect("DBI:Pg:dbname=gforge;host=localhost","gforge","$ENV{DB_PW}") ;

$dbhFF->begin_work ;

sub migrate_with_mapping ( $$$;$ ) {
    my $tsrc = shift ;
    my $tdest = shift ;
    my $mapping = shift ;
    my $where = shift || "" ;

    my @scols = keys %$mapping ;
    
    my $sql1 = "SELECT " . join (", ", @scols) . " FROM $tsrc $where" ;
    my $sth1 = $dbhAS->prepare ($sql1) ;
    # print STDERR Dumper $sql1 ;

    my $sql2 = "INSERT INTO $tdest (" . join (", ", map { $mapping->{$_} } @scols)
	. ") VALUES (" . join (", ", map { "?" } @scols) . ")" ;
    my $sth2 = $dbhFF->prepare ($sql2) ;
    # print STDERR Dumper $sql2 ;

    $sth1->execute ;
    while (my @arr = $sth1->fetchrow_array) {
	unless ($sth2->execute (@arr)) {
	    print STDERR "$sql2\n" ;
	    print STDERR Dumper \@arr ;
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
print STDERR "Migrating files\n" ;
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

$sthAS = $dbhAS->prepare ("
select filesystem.file_name_safe, filesystem.ref_id,
filesystem.file_size, filesystem.file_type, filesystem.posted_by,
filesystem.download_count, extract (epoch from
frs_release.release_date)::integer, frs_release.release_name,
frs_package.package_name, project.unix_name, filesystem.filesystem_id

from filesystem, frs_release, frs_package, project

where filesystem.section = 'frsrelease'
and filesystem.ref_id = frs_release.frs_release_id
and frs_release.frs_package_id = frs_package.frs_package_id
and frs_package.project_id = project.project_id
and frs_release.status_id != 0") ;

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
    my $fsid = $arrayAS[10] ;

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

    my $srcdir = "/tmp/filesystem/frsrelease/" . join ('/', split ('', sprintf ("%03d", substr ($fsid, 0, 3)))) . "/$fsid" ;
    my $srcfile = "$srcdir/$filename" ;

    # print STDERR "Copying $srcfile to $destfile\n" ;

    system "mkdir -p $destdir" ;
    system "touch $destfile" ; # Need to actually put the contents there...
    # chown + chmod

    $sthFF->execute ($filename, $releaseid, $typeid, $filesize, $releasedate, $releasedate, 8000) ;
}
$sthAS->finish ;
$sthFF->finish ;

### Docman
$map = {
    'docman_folder_id' => 'doc_group',
    'project_id' => 'group_id',
    'folder_name' => 'groupname',
    'parent_folder_id' => 'parent_doc_group',
} ;
print STDERR "Migrating docman\n" ;
migrate_with_mapping ('docman_folder', 'doc_groups', $map)
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;



$sthAS = $dbhAS->prepare ("
select filesystem.file_name_safe, filesystem.filesystem_id,
extract (epoch from docman_file_version.create_date)::integer,
docman_file_version.created_by, docman_file.docman_folder_id,
filesystem.file_type, filesystem.file_size, docman_folder.project_id,
docman_folder.is_public

from docman_file, docman_file_version, docman_folder, filesystem

where filesystem.section='docmanfileversion'
and filesystem.ref_id = docman_file_version.docman_file_version_id
and docman_file_version.docman_file_id = docman_file.docman_file_id
and docman_file.docman_folder_id = docman_folder.docman_folder_id
and filesystem.file_type != 'URL'
") ;
$sthFF = $dbhFF->prepare ("insert into doc_data (doc_group, description, title, data, updatedate, createdate, created_by, filename, filetype, group_id, filesize, stateid) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)") ;
$sthAS->execute ;
while (@arrayAS = $sthAS->fetchrow_array) {
    my $filename = $arrayAS[0] ;
    my $fsid = $arrayAS[1] ;
    my $createdate = $arrayAS[2] ;
    my $createdby = $arrayAS[3] ;
    my $docgroup = $arrayAS[4] ;
    my $filetype = $arrayAS[5] ;
    my $size = $arrayAS[6] ;
    my $groupid = $arrayAS[7] ;
    my $ispublic = $arrayAS[8] ;

    my $srcdir = "/tmp/filesystem/docmanfileversion/" . join ('/', split ('', sprintf ("%03d", substr ($fsid, 0, 3)))) . "/$fsid" ;
    my $srcfile = "$srcdir/$filename" ;

    # print STDERR "Copying $srcfile to database\n" ;
    my $data = '' ;
#     open F, $srcfile;
#     while (my $l = <F>) {
# 	$data .= $l ;
#     }
#     close F ;
#     $data =~ s/\\//g ;
    $data = encode_base64 ($data) ;

    my $stateid = $ispublic ? 1 : 5 ;

    $sthFF->execute ($docgroup, $filename, $filename, $data, $createdate, $createdate, $createdby, $filename, $filetype, $groupid, $size, $stateid) ;
}
$sthAS->finish ;
$sthFF->finish ;

### Mailing lists
$map = {
    'project_id' => 'group_id',
    'list_name' => 'list_name',
    'is_public' => 'is_public',
    'list_password' => 'password',
    'created_by' => 'list_admin',
    'status' => 'status',
    'list_description' => 'description',
} ;
print STDERR "Migrating mailing lists\n" ;
migrate_with_mapping ('mailman', 'mail_group_list', $map)
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

### Trackers
$map = {
    'tracker_id' => 'group_artifact_id',
    'project_id' => 'group_id',
    'tracker_name' => 'name',
    'description' => 'description',
    'is_public' => 'is_public',
    'not restrict_browse' => 'allow_anon',
    'email_all_updates' => 'email_all_updates',
    'email_address' => 'email_address',
    'due_period' => 'due_period',
    'submit_instructions' => 'submit_instructions',
    'browse_instructions' => 'browse_instructions',
    '0' => 'datatype',
} ;
print STDERR "Migrating trackers\n" ;
migrate_with_mapping ('tracker', 'artifact_group_list', $map, 'where datatype=1')
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'ti.tracker_item_id' => 'artifact_id',
    'ti.tracker_id' => 'group_artifact_id',
    'case when ti.status_id = 0 then 2 else ti.status_id end' => 'status_id',
    'ti.priority' => 'priority',
    'ti.submitted_by' => 'submitted_by',
    'extract (epoch from ti.open_date)::integer' => 'open_date',
    'ti.summary' => 'summary',
    'ti.details' => 'details',
    'extract (epoch from ti.last_modified_date)::integer' => 'last_modified_date',
    'tia.assignee' => 'assigned_to',
    'case when ti.status_id = 0 then extract (epoch from ti.close_date)::integer else 0 end' => 'close_date'
} ;
migrate_with_mapping ('tracker_item ti, tracker_item_assignee tia, tracker t', 'artifact', $map, 'where t.datatype=1 and ti.tracker_id=t.tracker_id and tia.tracker_item_id=ti.tracker_item_id and tia.assignee = (select max(assignee) from tracker_item_assignee where tracker_item_id=ti.tracker_item_id)') # An artifact can't be assigned to several users in FusionForge, so we arbitrarily pick the one most recently created
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'tim.tracker_item_message_id' => 'id',
    'tim.tracker_item_id' => 'artifact_id',
    'tim.submitted_by' => 'submitted_by',
    'extract (epoch from tim.adddate)::integer' => 'adddate',
    'tim.body' => 'body',
    '\'\'' => 'from_email',
} ;
migrate_with_mapping ('tracker_item_message tim, tracker_item ti, tracker t', 'artifact_message', $map, 'where tim.tracker_item_id = ti.tracker_item_id and ti.tracker_id = t.tracker_id and t.datatype = 1')
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'tef.tracker_extra_field_id' => 'extra_field_id',
    'tef.tracker_id' => 'group_artifact_id',
    'tef.field_name' => 'field_name',
    'tef.field_type' => 'field_type',
    'tef.attribute1' => 'attribute1',
    'tef.attribute2' => 'attribute2',
    'tef.is_required' => 'is_required',
    'tef.alias' => 'alias',
} ;
migrate_with_mapping ('tracker_extra_field tef, tracker t', 'artifact_extra_field_list', $map, 'where tef.tracker_id = t.tracker_id and t.datatype = 1 and tef.field_type != 8') # FusionForge doesn't have a concept of "Found/Fixed in revision X"
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'tefe.element_id' => 'element_id',
    'tefe.tracker_extra_field_id' => 'extra_field_id',
    'tefe.element_name' => 'element_name',
    'tefe.status_id' => 'status_id',
} ;
migrate_with_mapping ('tracker_extra_field_element tefe, tracker_extra_field tef, tracker t', 'artifact_extra_field_elements', $map, 'where tefe.tracker_extra_field_id = tef.tracker_extra_field_id and tef.tracker_id = t.tracker_id and t.datatype = 1 and tef.field_type != 8') # FusionForge doesn't have a concept of "Found/Fixed in revision X"
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'tefd.tracker_extra_field_data_id' => 'data_id',
    'tefd.tracker_item_id' => 'artifact_id',
    'tefd.field_data' => 'field_data',
    'tefd.tracker_extra_field_id' => 'extra_field_id',
} ;
migrate_with_mapping ('tracker_extra_field_data tefd, tracker_extra_field tef, tracker_item ti, tracker t', 'artifact_extra_field_data', $map, 'where tefd.tracker_item_id = ti.tracker_item_id and ti.tracker_id = t.tracker_id and t.datatype = 1 and tefd.tracker_extra_field_id = tef.tracker_extra_field_id and tef.field_type != 8') # FusionForge doesn't have a concept of "Found/Fixed in revision X"
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'tcr.tracker_canned_response_id' => 'id',
    'tcr.title' => 'title',
    'tcr.body' => 'body',
    'tcr.tracker_id' => 'group_artifact_id',
} ;
migrate_with_mapping ('tracker_canned_response tcr, tracker t', 'artifact_canned_responses', $map, 'where tcr.tracker_id = t.tracker_id and t.datatype = 1')
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'tq.tracker_query_id' => 'artifact_query_id',
    'tq.user_id' => 'user_id',
    'tq.query_name' => 'query_name',
    'tq.tracker_id' => 'group_artifact_id',
} ;
migrate_with_mapping ('tracker_query tq, tracker t', 'artifact_query', $map, 'where tq.tracker_id = t.tracker_id and t.datatype = 1')
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'tqf.tracker_query_id' => 'artifact_query_id',
    'tqf.query_field_type' => 'query_field_type',
    'tqf.query_field_id' => 'query_field_id',
    'tqf.query_field_values' => 'query_field_values',
} ;
migrate_with_mapping ('tracker_query_field tqf, tracker_query tq, tracker t', 'artifact_query_fields', $map, 'where tqf.tracker_query_id = tq.tracker_query_id and tq.tracker_id = t.tracker_id and t.datatype = 1')
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

### Task managers
$map = {
    'tracker_id' => 'group_project_id',
    'project_id' => 'group_id',
    'tracker_name' => 'project_name',
    'description' => 'description',
    'is_public' => 'is_public',
    'email_address' => 'send_all_posts_to',
} ;
print STDERR "Migrating task managers\n" ;
migrate_with_mapping ('tracker', 'project_group_list', $map, 'where datatype=2')
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'ti.tracker_item_id' => 'project_task_id',
    'ti.tracker_id' => 'group_project_id',
    'case when ti.status_id = 0 then 2 else ti.status_id end' => 'status_id',
    'ti.priority' => 'priority',
    'ti.submitted_by' => 'created_by',
    'extract (epoch from ti.open_date)::integer' => 'start_date',
    'extract (epoch from ti.close_date)::integer' => 'end_date',
    'ti.summary' => 'summary',
    'ti.details' => 'details',
    'extract (epoch from ti.last_modified_date)::integer' => 'last_modified_date',
    'ti.parent_id' => 'parent_id',
} ;
migrate_with_mapping ('tracker_item ti, tracker t', 'project_task', $map, 'where ti.tracker_id = t.tracker_id and t.datatype = 2')
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$sthAS = $dbhAS->prepare ("select tefd.field_data, ti.tracker_item_id from tracker_extra_field_data tefd, tracker_extra_field tef, tracker_item ti, tracker t where tefd.tracker_item_id = ti.tracker_item_id and ti.tracker_id = t.tracker_id and t.datatype = 2 and tefd.tracker_extra_field_id = tef.tracker_extra_field_id and field_name = 'Estimated Effort (Hours)'") ;
$sthFF = $dbhFF->prepare ("update project_task set hours=? where project_task_id=?") ;
$sthAS->execute ;
while (@arrayAS = $sthAS->fetchrow_array) {
    my $hours = $arrayAS[0] ;
    my $ptid = $arrayAS[1] ;
    next unless $hours =~ /^[0-9]+$/ ;
    $sthFF->execute ($hours, $ptid) ;
}
$sthAS->finish ;
$sthFF->finish ;
		      
$sthAS = $dbhAS->prepare ("select tefd.field_data, ti.tracker_item_id from tracker_extra_field_data tefd, tracker_extra_field tef, tracker_item ti, tracker t where tefd.tracker_item_id = ti.tracker_item_id and ti.tracker_id = t.tracker_id and t.datatype = 2 and tefd.tracker_extra_field_id = tef.tracker_extra_field_id and field_name = 'Percent Complete (0-100)'") ;
$sthFF = $dbhFF->prepare ("update project_task set percent_complete=? where project_task_id=?") ;
$sthAS->execute ;
while (@arrayAS = $sthAS->fetchrow_array) {
    my $percent = $arrayAS[0] ;
    my $ptid = $arrayAS[1] ;
    next unless $percent =~ /^[0-9]+$/ ;
    $sthFF->execute ($percent, $ptid) ;
}
$sthAS->finish ;
$sthFF->finish ;
		      
$map = {
    'tia.tracker_item_id' => 'project_task_id',
    'tia.assignee' => 'assigned_to_id',
} ;
migrate_with_mapping ('tracker_item_assignee tia, tracker_item ti, tracker t', 'project_assigned_to', $map, 'where tia.tracker_item_id = ti.tracker_item_id and ti.tracker_id = t.tracker_id and t.datatype = 2')
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;

$map = {
    'tim.tracker_item_message_id' => 'project_message_id',
    'tim.tracker_item_id' => 'project_task_id',
    'tim.submitted_by' => 'posted_by',
    'extract (epoch from tim.adddate)::integer' => 'postdate',
    'tim.body' => 'body',
} ;
migrate_with_mapping ('tracker_item_message tim, tracker_item ti, tracker t', 'project_messages', $map, 'where tim.tracker_item_id = ti.tracker_item_id and ti.tracker_id = t.tracker_id and t.datatype = 2')
    or do {
	$dbhFF->rollback ;
	die "Rolling back" ;
} ;


sub push_sequence_for_table {
    my $table = shift ;
    my $field = shift ;
    my $seqname = shift ;

    my $sql = "SELECT max ($field) FROM $table" ;
    my $sth = $dbhFF->prepare ($sql) ;
    $sth->execute ;
    my @arr = $sth->fetchrow_array ;
    my $cur = $arr[0] ;
    if ($cur) {
	print STDERR "Pushing $seqname to $cur\n" ;
	&bump_sequence_to ($dbhFF, $seqname, $cur) ;
    } else {
	print STDERR "Not pushing $seqname\n" ;
    }
    $sth->finish ;
}

print STDERR "Pushing sequences to appropriate values\n" ;
&push_sequence_for_table ('groups', 'group_id', 'groups_pk_seq') ;
&push_sequence_for_table ('users', 'user_id', 'users_pk_seq') ;
&push_sequence_for_table ('role', 'role_id', 'role_role_id_seq') ;
&push_sequence_for_table ('user_group', 'user_group_id', 'user_group_pk_seq') ;
&push_sequence_for_table ('trove_group_link', 'trove_group_id', 'trove_group_link_pk_seq') ;
&push_sequence_for_table ('forum_group_list', 'group_forum_id', 'forum_group_list_pk_seq') ;
&push_sequence_for_table ('forum', 'msg_id', 'forum_pk_seq') ;
&push_sequence_for_table ('forum_pending_messages', 'msg_id', 'forum_pending_messages_msg_id_seq') ;
&push_sequence_for_table ('frs_package', 'package_id', 'frs_package_pk_seq') ;
&push_sequence_for_table ('frs_release', 'release_id', 'frs_release_pk_seq') ;
&push_sequence_for_table ('frs_file', 'file_id', 'frs_file_pk_seq') ;
&push_sequence_for_table ('doc_groups', 'doc_group', 'doc_groups_pk_seq') ;
&push_sequence_for_table ('doc_data', 'docid', 'doc_data_pk_seq') ;
&push_sequence_for_table ('mail_group_list', 'group_list_id', 'mail_group_list_pk_seq') ;
&push_sequence_for_table ('artifact_group_list', 'group_artifact_id', 'artifact_grou_group_artifac_seq') ;
&push_sequence_for_table ('artifact', 'artifact_id', 'artifact_artifact_id_seq') ;
&push_sequence_for_table ('artifact_message', 'id', 'artifact_message_id_seq') ;
&push_sequence_for_table ('artifact_extra_field_list', 'extra_field_id', 'artifact_extra_field_list_extra_field_id_seq') ;
&push_sequence_for_table ('artifact_extra_field_elements', 'element_id', 'artifact_extra_field_elements_element_id_seq') ;
&push_sequence_for_table ('artifact_extra_field_data', 'data_id', 'artifact_extra_field_data_data_id_seq') ;
&push_sequence_for_table ('artifact_canned_responses', 'id', 'artifact_canned_response_id_seq') ;
&push_sequence_for_table ('artifact_query', 'artifact_query_id', 'artifact_query_artifact_query_id_seq') ;
&push_sequence_for_table ('project_group_list', 'group_project_id', 'project_group_list_pk_seq') ;
&push_sequence_for_table ('project_task', 'project_task_id', 'project_task_pk_seq') ;
&push_sequence_for_table ('project_messages', 'project_message_id', 'project_messages_project_message_id_seq') ;

print STDERR "Migration script completed OK\n" ;
# $dbhFF->commit ; print STDERR "Committed\n" ;
