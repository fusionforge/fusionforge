#!/usr/bin/perl
#
# $Id$
#
# cvs_dump_update.pl - script to dump data from the database 
#		       and update cvs consequently
#		       inspired from sourceforge scripts
# Christian Bayle <bayle@debian.org>
#
use DBI;
use Sys::Hostname;

require("/usr/lib/gforge/lib/include.pl");  # Include all the predefined functions

my $group_array = ();
my $verbose = 0;
my $cvs_file = $file_dir . "dumps/cvs_dump";

#
# Script parse out the database dumps and create/update/delete cvs
#		 accounts on the client machines
#
# Open up all the files that we need.
#
if($verbose) {print ("\nReading list");}
@group_array = open_array_file($cvs_file);

#
# Loop through @group_array and deal w/ cvs.
#
if($verbose) {print ("\n\nProcessing CVS\n\n");}
while ($ln = pop(@group_array)) {
	chop($ln);
	($group_name, $status, $group_id, $use_cvs, $enable_pserver, $enable_anoncvs, $userlist) = split(":", $ln);
	
	$cvs_uid = $group_id + $anoncvs_uid_add;
	$cvs_gid = $group_id + $gid_add;
	$cvs_dir = "$cvs_root$group_name";

	$userlist =~ tr/A-Z/a-z/;

	#$group_exists = (-d $grpdir_prefix . $group_name);
	$group_exists = ($status eq 'A');
	$cvs_exists = (-d "$cvs_root$group_name/CVSROOT");

	if (!$group_exists && $use_cvs && $status eq 'A' ) {
		print ("ERROR: $group_name home dir $grpdir_prefix$group_name doesn't exists\n");
		print ("	but use_cvs=$use_cvs\tstatus=$status\n");
	}
	if ($cvs_exists && !$group_exists && $status eq 'A') {
		print ("ERROR: CVS $cvs_root$group_name/CVSROOT exists\n");
		print ("	but no $group_name home dir at $grpdir_prefix$group_name\n");
		print ("	use_cvs=$use_cvs\tstatus=$status\n");
	}
	# CVS repository creation
	if ($group_exists && !$cvs_exists && $use_cvs && $status eq 'A' && !(-e "$cvs_root$group_name/CVSROOT")) {
		# This for the first time
		if (!(-d "$cvs_root")) {
			if($verbose){print("Creating $cvs_root\n");}
			system("mkdir -p $cvs_root");
		}
		if($verbose){print("Creating a CVS Repository for: $group_name\n");}
		# Let's create a CVS repository for this group

		# Firce create the repository
		# Unix right will lock access to all users not in the group including cvsweb
		# when anoncvs is not enabled
		if ($enable_anoncvs){
			mkdir $cvs_dir, 0775;
		} else {
			mkdir $cvs_dir, 0770;
		}
		system("/usr/bin/cvs -d$cvs_dir init");
	
		system("echo \"\" > $cvs_dir/CVSROOT/val-tags");
		chmod 0664, "$cvs_dir/CVSROOT/val-tags";

		# set group ownership, anonymous group user
		system("chown -R nobody:$cvs_gid $cvs_dir");
		# s bit to have all owned by group
		system("chmod -R g+rws $cvs_dir");
	}

	# Right management
	if ($group_exists && $use_cvs && $status eq 'A'){
		if ($enable_pserver){
			# turn on pserver writers
			my $userlistcr=join("\n",split(",", $userlist));
			open (WRITERS,">$cvs_dir/CVSROOT/writers");
			print WRITERS "$userlistcr\n";
			close WRITERS;
			if($verbose) { print("Enable pserver for $group_name:\t$userlist in $cvs_dir/CVSROOT/writers \n"); }
			open (CONFIG,">$cvs_dir/CVSROOT/config");
			print CONFIG "SystemAuth=yes\n";
			close CONFIG;
		} else {
			# turn off pserver writers
			open (WRITERS,">$cvs_dir/CVSROOT/writers");
			print WRITERS "\n";
			close WRITERS;
			#system("echo \"\" > $cvs_dir/CVSROOT/writers");
			if($verbose) { print("Disable pserver for $group_name\n"); }
			open (CONFIG,">$cvs_dir/CVSROOT/config");
			print CONFIG "SystemAuth=no\n";
			close CONFIG;
		}

		if ($enable_anoncvs){
			# turn on anonymous readers
			system("echo \"anonymous\" > $cvs_dir/CVSROOT/readers");
			system("echo \"anonymous:\\\$1\\\$0H\\\$2/LSjjwDfsSA0gaDYY5Df/:anoncvs_${group_name}\" > $cvs_dir/CVSROOT/passwd");
			# This will give access to all users and cvsweb
			chmod 02775, "$cvs_dir";

			my $gid = $group_id + $gid_add ;
			my $uid = $group_id + $anoncvs_uid_add ;
			my $username = "anoncvs_" . $group_name ;

			add_or_update_anoncvs_user ($uid, $username, $gid) ;
			
		} else {
			# turn off anonymous readers
			system("echo \"\" > $cvs_dir/CVSROOT/readers");
			system("echo \"\" > $cvs_dir/CVSROOT/passwd");
			# This will lock all access from users not in the group and cvsweb
			chmod 02770, "$cvs_dir";
		}
	}
}

#############################
# User Add Function
#############################
sub add_or_update_anoncvs_user {  
	my ($uid, $username, $gid) = @_;
	
	$home_dir = $homedir_prefix.$username;
	
	if ( -d $home_dir ) {
	    chmod 0755, $home_dir;
	} else {
	    mkdir $home_dir, 0755;
	}
	
	chown $uid, $gid, $home_dir;
}
