#!/usr/bin/perl
#
# $Id$
#
# dump_database.pl - script to dump data from the database to flat files so the ofher perl
#		     scripts can process it without needing to access the database.
use DBI;

require("/usr/lib/gforge/lib/include.pl");  # Include all the predefined functions

my $verbose = 0;
my $user_array = ();
my $group_array = ();

&db_connect;

# Dump the Groups Table information
$query = "select group_id,unix_group_name,status from groups";
$c = $dbh->prepare($query);
$c->execute();

while(my ($group_id, $group_name, $status) = $c->fetchrow()) {

	my $new_query = "select users.user_name AS user_name FROM users,user_group WHERE users.user_id=user_group.user_id AND group_id=$group_id";
	my $d = $dbh->prepare($new_query);
	$d->execute();

	my $user_list = "";
	
	while($user_name = $d->fetchrow()) {
	   $user_list .= "$user_name,";
	}

	$grouplist = "$group_name:$status:$group_id:$user_list\n";
	$grouplist =~ s/,$//;

	push @group_array, $grouplist;
}

# Now write out the files
write_array_file($file_dir."dumps/group_dump", @group_array);

my $group_file = $file_dir . "dumps/group_dump";
my ($gname, $gstatus, $gid, $userlist);

# Open up all the files that we need.
@groupdump_array = open_array_file($group_file);

#
# Loop through @groupdump_array and deal w/ users.
#
if($verbose) {print ("\n\n	Processing Groups\n\n")};
while ($ln = pop(@groupdump_array)) {
	chop($ln);
	($gname, $gstatus, $gid, $userlist) = split(":", $ln);
	
	$gid += $gid_add;
	$userlist =~ tr/A-Z/a-z/;

	$group_exists = (-d $grpdir_prefix . $gname);

	if ($gstatus eq 'A' && $group_exists) {
		update_group($gid, $gname, $userlist);
	
	} elsif ($gstatus eq 'A' && !$group_exists) {
		add_group($gid, $gname, $userlist);
	
	} elsif ($gstatus eq 'D' && $group_exists) {
		delete_group($gname);

	} 
}

###############################################
# Begin functions
###############################################

#############################
# Group Add Function
#############################
sub add_group {  
	my ($gid, $gname, $userlist) = @_;
	my ($log_dir, $cgi_dir, $ht_dir);
	
	$group_dir = $grpdir_prefix.$gname;
	$log_dir = $group_dir."/log";
	$cgi_dir = $group_dir."/cgi-bin";
	$ht_dir = $group_dir."/htdocs";

	if ($verbose) {print("Making a Group for : $gname\n")};
		
	mkdir $group_dir, 2775;
	mkdir $log_dir, 2775;
	mkdir $cgi_dir, 2775;
	mkdir $ht_dir, 2775;
	# perl is sometime fucked to create with right permission
	system("chmod 2775 $group_dir");
	system("chmod 2775 $log_dir");
	system("chmod 2775 $cgi_dir");
	system("chmod 2775 $ht_dir");
	chown $dummy_uid, $gid, ($group_dir, $log_dir, $cgi_dir, $ht_dir);
}

#############################
# Group Update Function
#############################
sub update_group {
	my ($gid, $gname, $userlist) = @_;
	my ($p_gname, $p_junk, $p_gid, $p_userlist, $counter);
	
	if ($verbose) {print("Updating Group: $gname\n")};
}

#############################
# Group Delete Function
#############################
sub delete_group {
	my ($gname, $x, $gid, $userlist, $counter);
	my $this_group = shift(@_);
	$counter = 0;
	
	if (substr($hostname,0,3) ne "cvs") {
		if ($verbose) {print("Deleting Group: $this_group\n")};
		system("/bin/mv /var/lib/gforge/chroot/home/groups/$this_group /var/lib/gforge/chroot/home/groups/deleted_group_$this_group");
		system("/bin/tar -czf /var/lib/gforge/tmp/$this_group.tar.gz /var/lib/gforge/chroot/home/groups/deleted_group_$this_group && /bin/rm -rf /var/lib/gforge/chroot/home/groups/deleted_group_$this_group");
	}
}
