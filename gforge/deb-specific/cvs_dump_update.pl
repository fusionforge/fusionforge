#!/usr/bin/perl
#
# $Id$
#
# cvs_dump_update.pl - script to dump data from the database 
#		       and update cvs consequently
#		       inspired from sourceforge scripts
use DBI;
use Sys::Hostname;

require("/usr/lib/gforge/lib/include.pl");  # Include all the predefined functions

my $group_array = ();

&db_connect;

# Get hostname
#$hostname = hostname();
$hostname = "cvs";

# Dump the Groups Table information
$query = "SELECT group_id,unix_group_name,status,use_cvs,enable_pserver,enable_anoncvs FROM groups";
# AND cvs_box=$hostname to be added for multi-cvs server support
$c = $dbh->prepare($query);
$c->execute();

my $cvs_file = $file_dir . "dumps/cvs_dump";

while(my ($group_id, $group_name, $status, $use_cvs, $enable_pserver, $enable_anoncvs) = $c->fetchrow()) {

	my $new_query = "SELECT users.user_name AS user_name FROM users,user_group WHERE users.user_id=user_group.user_id AND group_id=$group_id";
	my $d = $dbh->prepare($new_query);
	$d->execute();

	my $user_list = "";
	
	while($user_name = $d->fetchrow()) {
	   $user_list .= "$user_name,";
	}

	$grouplist = "$group_name:$status:$group_id:$use_cvs:$enable_pserver:$enable_anoncvs:$user_list\n";
	$grouplist =~ s/,$//;

	push @group_array, $grouplist;
}

# Now write out the files (not necessary, but can give info in case of problems)
write_array_file($cvs_file, @group_array);
$group_array = ();

#
# Script parse out the database dumps and create/update/delete cvs
#		 accounts on the client machines
#
# Open up all the files that we need.
#
@group_array = open_array_file($cvs_file);

#
# Loop through @groupdump_array and deal w/ users.
#
print ("\n\n	Processing Groups\n\n");
while ($ln = pop(@group_array)) {
	chop($ln);
	($group_name, $status, $group_id, $use_cvs, $enable_pserver, $enable_anoncvs, $userlist) = split(":", $ln);
	
	$cvs_uid = $group_id + $anoncvs_uid_add;
	$cvs_gid = $group_id + $gid_add;
	$userlist =~ tr/A-Z/a-z/;

	$group_exists = (-d $grpdir_prefix . $group_name);

	# CVS repository creation
	if ($group_exists && $use_cvs && $status eq 'A' && !(-e "$cvs_root$group_name/CVSROOT")) {
		# This for the first time
		if (!(-d "$cvs_root")) {
			print("Creating $cvs_root\n");
			system("mkdir -p $cvs_root");
		}
		print("Creating a CVS Repository for: $group_name\n");
		# Let's create a CVS repository for this group
		$cvs_dir = "$cvs_root$group_name";

		# Firce create the repository
		# Let's make this more paranoia, cvsweb has to be modified to get access
		mkdir $cvs_dir, 0770;
		system("/usr/bin/cvs -d$cvs_dir init");
	
		system("echo \"\" > $cvs_dir/CVSROOT/val-tags");
		chmod 0664, "$cvs_dir/CVSROOT/val-tags";

		# set group ownership, anonymous group user
		system("chown -R nobody:$cvs_gid $cvs_dir");
		# s bit to have all owned by group
		system("chmod g+rws $cvs_dir");
	}

	# Right management
	if ($group_exists && $use_cvs && $status eq 'A'){
		if ($enable_pserver){
			# turn on pserver writers
			echo TODO
		} else {
			# turn off pserver writers
			system("echo \"\" > $cvs_dir/CVSROOT/writers");
		}

		if ($enable_anoncvs){
			# turn on anonymous readers
			system("echo \"anonymous\" > $cvs_dir/CVSROOT/readers");
			system("echo \"anonymous:\\\$1\\\$0H\\\$2/LSjjwDfsSA0gaDYY5Df/:anoncvs_${group_name}\" > $cvs_dir/CVSROOT/passwd");
		} else {
			# turn off anonymous readers
			system("echo \"\" > $cvs_dir/CVSROOT/readers");
			system("echo \"\" > $cvs_dir/CVSROOT/passwd");
		}
	}
}
