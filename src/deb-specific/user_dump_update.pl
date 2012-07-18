#!/usr/bin/perl
#
# dump_database.pl - script to dump data from the database to flat files so the ofher perl
#		     scripts can process it without needing to access the database.
use DBI;

require("/usr/share/gforge/lib/include.pl");  # Include all the predefined functions

# Run as gforge
my($name,$passwd,$uid,$gid,$quota,$comment,$gcos,$dir,$shell) = getpwnam("gforge");
$> = $uid;

my $verbose = 0;
my $user_array = ();
my $group_array = ();

&db_connect;

# Dump the users Table information
my $query = "select unix_uid, unix_gid, unix_status, user_name, shell, unix_pw, realname from users where unix_status != 'N'";
my $c = $dbh->prepare($query);
$c->execute();
	
while(my ($uid, $gid, $status, $username, $shell, $passwd, $realname) = $c->fetchrow()) {
	$home_dir = $homedir_prefix.$username;

	$userlist = "$uid:$gid:$status:$username:$shell:$passwd:$realname\n";

	push @user_array, $userlist;
}

# Run as root
$> =  0;

# Now write out the files
write_array_file($file_dir."/dumps/user_dump", @user_array);
system("chmod o-r,g-r $file_dir/dumps/user_dump");

my $user_file = $file_dir . "/dumps/user_dump";
my ($uid, $gid, $status, $username, $shell, $passwd, $realname);

# Open up all the files that we need.
@userdump_array = open_array_file($user_file);

#
# Loop through @userdump_array and deal w/ users.
#
if($verbose){print ("\n\n	Processing Users\n\n")};
while ($ln = pop(@userdump_array)) {
	chop($ln);
	($uid, $gid, $status, $username, $shell, $passwd, $realname) = split(":", $ln);
	$username =~ tr/A-Z/a-z/;
	$user_exists = (-d $homedir_prefix .'/'. $username || -f "/var/lib/gforge/tmp/$username.tar.gz");
	
	if ($status eq 'A' && $user_exists) {
		update_user($uid, $gid, $username, $realname, $shell, $passwd);
	
	} elsif ($status eq 'A' && !$user_exists) {
		add_user($uid, $gid, $username, $realname, $shell, $passwd);
	
	} elsif ($status eq 'D' && $user_exists) {
		delete_user($username);
	
	} elsif ($status eq 'D' && !$user_exists) {
		if($verbose){print("Error trying to delete user: $username\n")};
		
	} elsif ($status eq 'S' && $user_exists) {
		suspend_user($username);
		
	} elsif ($status eq 'S' && !$user_exists) {
		if($verbose){print("Error trying to suspend user: $username\n")};
		
	} else {
		if($verbose){print("Unknown Status Flag: $username\n")};
	}
}

###############################################
# Begin functions
###############################################

#############################
# User Add Function
#############################
sub add_user {  
	my ($uid, $gid, $username, $realname, $shell, $passwd) = @_;
	my $skel_array = ();
	
	$home_dir = $homedir_prefix."/".$username;

	if($verbose){print("Making a User Account for : $username\n")};
		
	# Now lets create the homedir and copy the contents of /var/lib/gforge/chroot/etc/skel into it.
	if (-d "/var/lib/gforge/chroot/etc/skel") {
	    system "cp -r /var/lib/gforge/chroot/etc/skel $home_dir";
	    chmod 0755, $home_dir;
	} else {
	    mkdir $home_dir, 0755;
	}
	mkdir $home_dir.'/incoming', 0755;

	my @a;
	push @a, $home_dir;
	push @a, glob "$home_dir/*";
	chown $uid, $gid, @a;
}

#############################
# User Update Function
#############################
sub update_user {
	my ($uid, $gid, $username, $realname, $shell, $passwd) = @_;
	my ($realuid, $realgid); 
	
	if($verbose){print("Updating Account for: $username\n")};
	
        $home_dir = $homedir_prefix.'/'.$username;
	unless (-d $home_dir.'/incoming') {
	    mkdir $home_dir.'/incoming', 0755;
	}

	my $realuid=get_file_owner_uid($home_dir);
	if ($uid eq $realuid){
        	system("chown $uid $home_dir/incoming");
		system("chmod 0755 $home_dir/incoming");
	} else {
		if($verbose){print("Changing owner of $home_dir $realuid -> $uid\n")};
        	system("chown -R $uid $home_dir");
		system("chmod 0755 $home_dir/incoming");
	}
	my $realgid=get_file_owner_gid($home_dir);
	if ($gid eq $realgid){
        	system("chgrp $gid $home_dir/incoming");
	} else {
		if($verbose){print("Changing group of $home_dir $realgid -> $gid\n")};
        	system("chgrp -R $gid $home_dir");
	}
}

#############################
# User Deletion Function
#############################
sub delete_user {
	my $username = shift(@_);
	
	my $alreadydone=(-f "/var/lib/gforge/tmp/$username.tar.gz");
	if (!$alreadydone){
	if($verbose){print("Deleting User : $username\n")};
		if($verbose){print("/bin/mv /var/lib/gforge/chroot/home/users/$username /var/lib/gforge/chroot/home/users/deleted_$username\n")};
		system("/bin/mv /var/lib/gforge/chroot/home/users/$username /var/lib/gforge/chroot/home/users/deleted_$username");
		if($verbose){print("/bin/tar -czf /var/lib/gforge/tmp/$username.tar.gz /var/lib/gforge/chroot/home/users/deleted_$username && /bin/rm -rf /var/lib/gforge/chroot/home/users/deleted_$username\n")};
		system("/bin/tar -czf /var/lib/gforge/tmp/$username.tar.gz /var/lib/gforge/chroot/home/users/deleted_$username && /bin/rm -rf /var/lib/gforge/chroot/home/users/deleted_$username");
	}
}

#############################
# User Suspension Function
#############################
sub suspend_user {
	my $this_user = shift(@_);
	my ($s_username, $s_passwd, $s_date, $s_min, $s_max, $s_inact, $s_expire, $s_flag, $s_resv, $counter);
	
	my $new_pass = "!!" . $s_passwd;
}

#############################
# Get File Owner UID
#############################
sub get_file_owner_uid {
	my $filename = shift(@_);
	my ($dev,$ino,$mode,$nlink,$uid,$gid,$rdev,$size,$atime,$mtime,$ctime,$blksize,$blocks) = stat($filename);
	return $uid;
}
#############################
# Get File Owner GID
#############################
sub get_file_owner_gid {
	my $filename = shift(@_);
	my ($dev,$ino,$mode,$nlink,$uid,$gid,$rdev,$size,$atime,$mtime,$ctime,$blksize,$blocks) = stat($filename);
	return $gid;
}
