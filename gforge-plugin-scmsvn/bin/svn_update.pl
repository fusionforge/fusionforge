#!/usr/bin/perl
#
# $Id$
#
# svn_update.pl - script to update svn
#		       inspired from sourceforge scripts
# Roland Mas <lolando@debian.org>
# Christian Bayle <bayle@debian.org>

use DBI;
use Sys::Hostname;

require("/usr/lib/gforge/lib/include.pl"); # Include all the predefined functions

my $group_array = ();
my $verbose = 0;
my $svn_file = $file_dir . "/dumps/scmsvn.dump";
my $anoncvs_uid_add = 50000;
my $gid_add = 10000;


# Script parse out the database dumps and create/update/delete svn
#                accounts on the client machines
#
# Open up all the files that we need.
#
if($verbose) {print ("\nReading list");}
@group_array = open_array_file($svn_file);

#
# Loop through @group_array and deal w/ svn.
#
if($verbose) {print ("\n\nProcessing SVN\n\n");}
while ($ln = pop(@group_array)) {
	chop($ln);
		($group_name, $status, $group_id, $use_scm, $enable_pserver, $enable_anonscm, $userlist) = split(":", $ln);

	$svn_uid = $dummy_uid;
	$svn_gid = $group_id + $anoncvs_uid_add;
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
		system("chown -R $svn_uid:$svn_gid $svn_dir");
		# s bit to have all owned by group
		system("chmod -R g+rws $svn_dir");
	}
    
	# Right management
	if ($group_exists && $status eq 'A'){
		chmod 02775, "$svn_dir";
	}
}
