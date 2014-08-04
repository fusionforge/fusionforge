#!/usr/bin/perl
#
# dump_database.pl - script to dump data from the database to flat files so the ofher perl
#		     scripts can process it without needing to access the database.
use DBI;

my $source_path = `forge_get_config source_path`;
chomp $source_path;

require ("$source_path/lib/include.pl") ; # Include all the predefined functions 

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
	$user_exists = (-d $homedir_prefix .'/'. $username || -f (forge_get_config("data_path")."/tmp/$username.tar.gz"));
	
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

## Become this effective user (EUID/EGID) and perform this action.
## 
## This protect against symlink attacks; they are inevitable when
## working in a directory owned by a local user.  We could naively
## check for the presence of symlinks, but then we'd still be
## vulnerable to a symlink race attack.
## 
## We'll use set_e_uid/set_e_gid for efficiency and simplicity
## (e.g. we can get the return value directly), which is enough for
## opening files and similar basic operations.  When calling external
## programs, you should use fork&exec&setuid/setgid.
## 
# arg1: username
# arg2: a Perl sub{}
sub SudoEffectiveUser {
    my $user = $_[0];
    my $sub_unprivileged = $_[1];

    my ($uid,$gid) = GetUserUidGid($user);
    if ($uid eq "" or $gid eq "") {
	print "Unknown user: $user\n";
	return;
    }

    my $old_GID = $GID; # save additional groups
    $! = '';
    $EGID = "$gid $gid"; # set egid and additional groups
    if ($! ne '') {
	warn "Cannot setegid($gid $gid): $!";
	return;
    }
    $EUID = $uid;
    if ($! ne '') {
	warn "Cannot seteuid($uid): $!";
	return;
    }

    # Perform the action under this effective user:
    my $ret = &$sub_unprivileged();

    # Back to root
    undef($EUID);     # restore euid==uid
    $EGID = $old_GID; # restore egid==gid + additional groups

    return $ret;
}

## Get system uid/gid
sub GetUserUidGid {
    my $user = $_[0];
    my ($name,$passwd,$uid,$gid,
	$quota,$comment,$gcos,$dir,$shell,$expire) = getpwnam($user);
    return ($uid,$gid);
}

#############################
# Helper Function
#############################
sub run_verbose {
	my $thecmd = shift(@_);

	if ($verbose) {
		print "$thecmd\n";
	}
	return system($thecmd);
}

#############################
# User Add Function
#############################
sub add_user {  
	my ($uid, $gid, $username, $realname, $shell, $passwd) = @_;
	my $skel_array = ();
	
	$home_dir = $homedir_prefix."/".$username;

	if($verbose){print("Making a User Account for : $username\n")};
		
	# Now lets create the homedir and copy the contents of /etc/skel into it.
	mkdir $home_dir, 0755;
        chown $uid, $gid, $home_dir;
	
	SudoEffectiveUser($username, sub {
	    if (-d $skel_dir) {
		system "find $skel_dir -mindepth 1 -maxdepth 1 -print0 | xargs -0 cp -t $home_dir";
		chmod 0755, $home_dir;
	    }
	    mkdir $home_dir.'/incoming', 0755;
			  });
}

#############################
# User Update Function
#############################
sub update_user {
	my ($uid, $gid, $username, $realname, $shell, $passwd) = @_;
	my ($realuid, $realgid); 
	
	if($verbose){print("Updating Account for: $username\n")};
	
	SudoEffectiveUser($username, sub {
	    $home_dir = $homedir_prefix.'/'.$username;
	    if (-d $home_dir.'/incoming') {
		chmod 0755, $home_dir.'/incoming';
	    } else {
		mkdir $home_dir.'/incoming', 0755;
	    }
			  });
}

#############################
# User Deletion Function
#############################
sub delete_user {
	my $username = shift(@_);
	my $data_path = forge_get_config("data_path");
	my $alreadydone=(-f "$data_path/tmp/$username.tar.gz");
	if (!$alreadydone) {
		my $oldmask = umask(077);
		if ($verbose) {
			print("Deleting User : $username\n");
		}
		my $chroot = forge_get_config("chroot");
		run_verbose("/bin/mv $chroot/home/users/$username $chroot/home/users/deleted_$username");
		run_verbose("cd / && /bin/tar -cf - $chroot/home/users/deleted_$username | /bin/gzip -n9 > $data_path/tmp/$username.tar.gz && /bin/rm -rf $chroot/home/users/deleted_$username");
		umask($oldmask);
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
