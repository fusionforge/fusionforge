#!/usr/bin/perl
#
# $Id$
#
# svn_dump_update.pl - script to dump data from the database 
#		       and update svn consequently
#		       inspired from sourceforge scripts
# Roland Mas <lolando@debian.org>

use DBI;
use Sys::Hostname;

require("/usr/lib/gforge/lib/include.pl"); # Include all the predefined functions
require($db_include); # Include global configuration variables
require("/etc/gforge/plugins/scmsvn/config.pl"); # Include plugin config vars

my $svn_root = "/var/lib/gforge/chroot/svnroot" ;
my $verbose = 0;
my $anoncvs_uid_add = 50000;
my $gid_add = 10000;

if($verbose) {print ("\nConnecting to database");}
&db_connect;

$query = "SELECT plugin_id FROM plugins WHERE plugin_name = 'scmsvn'" ;
$c = $dbh->prepare ($query) ;
$c->execute () ;
my ($plugin_id) = $c->fetchrow () ;
$c->finish () ;

if($verbose) {print ("\nGetting group list");}
#$query = "SELECT groups.group_id, unix_group_name, status
#          FROM groups, group_plugin, plugin_scmsvn_group_usage
#          WHERE groups.group_id = group_plugin.group_id
#          AND group_plugin.plugin_id = $plugin_id
#          AND plugin_scmsvn_group_usage.group_id = groups.group_id
#          AND plugin_scmsvn_group_usage.svn_host = '$this_server'" ;

#$query = "SELECT groups.group_id, unix_group_name, status
#          FROM groups, group_plugin
#          WHERE groups.group_id = group_plugin.group_id
#          AND group_plugin.plugin_id = $plugin_id
#          AND groups.scm_host = '$this_server'" ;

$query = "SELECT groups.group_id, unix_group_name, status
          FROM groups, group_plugin
          WHERE groups.group_id = group_plugin.group_id
          AND group_plugin.plugin_id = $plugin_id";

$c = $dbh->prepare($query);
$c->execute();

while(my ($group_id, $group_name, $status) = $c->fetchrow()) {

    $svn_uid = $group_id + $anoncvs_uid_add;
    $svn_gid = $group_id + $gid_add;
    $svn_dir = "$svn_root/$group_name";

    $userlist =~ tr/A-Z/a-z/;

    $group_exists = (-d $grpdir_prefix . $group_name);
    $svn_exists = (-d "$svn_root/$group_name");

    # SVN repository creation
    if ($group_exists && !$svn_exists && $status eq 'A' && !(-e "$svn_root/$group_name/format")) {
	# This for the first time
	if (!(-d "$svn_root")) {
	    if($verbose){print("Creating $svn_root\n");}
	    system("mkdir -p $svn_root");
	}
	if($verbose){print("Creating a Subversion Repository for: $group_name\n");}
	# Let's create a Subversion repository for this group
	
	# Firce create the repository
	# Unix right will lock access to all users not in the group including cvsweb
	# when anoncvs is not enabled
	mkdir $svn_dir, 0775;
	system("/usr/bin/svnadmin create $svn_dir");
	
	# set group ownership, anonymous group user
	system("chown -R nobody:$svn_gid $svn_dir");
	# s bit to have all owned by group
	system("chmod -R g+rws $svn_dir");
    }
    
    # Right management
    if ($group_exists && $status eq 'A'){
	chmod 02775, "$svn_dir";
    }
}
