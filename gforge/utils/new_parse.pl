#!/usr/bin/perl
#
# $Id$
#
# new_parse.pl - new script to parse out the database dumps and create/update/delete user
#		 accounts on the client machines
use Sys::Hostname;

#$hostname = hostname();

require("/usr/lib/sourceforge/lib/include.pl");  # Include all the predefined functions and variables

$hostname = "cvs";

my $user_file = $file_dir . "dumps/user_dump";
my $group_file = $file_dir . "dumps/group_dump";
my ($uid, $status, $username, $shell, $passwd, $realname);
my ($gname, $gstatus, $gid, $userlist);

# Open up all the files that we need.
@userdump_array = open_array_file($user_file);
@groupdump_array = open_array_file($group_file);

open (FD,'>'.$file_dir."chroot/etc/passwd"); close(FD);
open (FD,'>'.$file_dir."chroot/etc/shadow"); close(FD);
open (FD,'>'.$file_dir."chroot/etc/group"); close(FD);

@passwd_array = open_array_file($file_dir."chroot/etc/passwd");
push @passwd_array, "root:x:0:0:Root:/:/bin/bash\n";
push @passwd_array, "dummy:x:$dummy_uid:$dummy_uid:Dummy User:/:/bin/false\n";
push @passwd_array, "nobody:x:65534:65534:nobody:/:/bin/false\n";

@shadow_array = open_array_file($file_dir."chroot/etc/shadow");
push @shadow_array, "root:*:11142:0:99999:7:::\n";
push @shadow_array, "dummy:*:11142:0:99999:7:::\n";
push @shadow_array, "nobody:*:11142:0:99999:7:::\n";

@group_array = open_array_file($file_dir."chroot/etc/group");
push @group_array, "root:x:0\n";
push @group_array, "dummy:x:$dummy_uid:\n";
push @group_array, "nogroup:x:65534:\n";

#
# Loop through @userdump_array and deal w/ users.
#
#CB# Let's make this silent
#print ("\n\n	Processing Users\n\n");
while ($ln = pop(@userdump_array)) {
	chop($ln);
	($uid, $status, $username, $shell, $passwd, $realname) = split(":", $ln);

#CB# Shell is now taken in the database
#	if (substr($hostname,0,3) eq "cvs") {
#		$shell = "/bin/cvssh";
#	}
	
	$uid += $uid_add;

	$username =~ tr/A-Z/a-z/;
	
#	$user_exists = getpwnam($username);
#	$user_exists = stat($homedir_prefix . $username);
	$user_exists = (-d $homedir_prefix . $username);
#	$user_exists = 0;
	
	if ($status eq 'A' && $user_exists) {
		update_user($uid, $username, $realname, $shell, $passwd);
	
	} elsif ($status eq 'A' && !$user_exists) {
		add_user($uid, $username, $realname, $shell, $passwd);
	
	} elsif ($status eq 'D' && $user_exists) {
		delete_user($username);
	
	} elsif ($status eq 'D' && !$user_exists) {
		print("Error trying to delete user: $username\n");
		
	} elsif ($status eq 'S' && $user_exists) {
		suspend_user($username);
		
	} elsif ($status eq 'S' && !$user_exists) {
		print("Error trying to suspend user: $username\n");
		
	} else {
		print("Unknown Status Flag: $username\n");
	}
}

#
# Loop through @groupdump_array and deal w/ users.
#
#CB# Let's make this silent
#print ("\n\n	Processing Groups\n\n");
while ($ln = pop(@groupdump_array)) {
	chop($ln);
	($gname, $gstatus, $gid, $userlist) = split(":", $ln);
	
	$cvs_id = $gid + 50000;
	$gid += $gid_add;
	$userlist =~ tr/A-Z/a-z/;

#	$group_exists = getgrnam($gname);
#	$group_exists = stat($grpdir_prefix . $gname);
	$group_exists = (-d $grpdir_prefix . $gname);
#	$group_exists = 0;

	if ($gstatus eq 'A' && $group_exists) {
		update_group($gid, $gname, $userlist);
	
	} elsif ($gstatus eq 'A' && !$group_exists) {
		add_group($gid, $gname, $userlist);
	
	} elsif ($gstatus eq 'D' && $group_exists) {
		delete_group($gname);

	} 
	#CB# these lines cause a bug.
	#elsif ($gstatus eq 'D' && !$group_exists) {
	#	print("Error trying to delete group: $gname\n");
	#}

	if ((substr($hostname,0,3) eq "cvs") && $gstatus eq 'A' && !(-e "$cvs_root$gname/CVSROOT")) {
		#CB# Added this for the first time
		if (!(-d "$cvs_root")) {
			print("Creating $cvs_root\n");
			system("mkdir -p $cvs_root");
		}
		print("Creating a CVS Repository for: $gname\n");
		# Let's create a CVS repository for this group
		$cvs_dir = "$cvs_root$gname";

		# Firce create the repository
		#CB# Let's make this more paranoia, not yet for cvsweb
		mkdir $cvs_dir, 0775;
		#mkdir $cvs_dir, 0770;
		system("/usr/bin/cvs -d$cvs_dir init");
	
		# turn off pserver writers, on anonymous readers
		system("echo \"\" > $cvs_dir/CVSROOT/writers");
		system("echo \"anonymous\" > $cvs_dir/CVSROOT/readers");
		system("echo \"anonymous:\\\$1\\\$0H\\\$2/LSjjwDfsSA0gaDYY5Df/:anoncvs_$gname\" > $cvs_dir/CVSROOT/passwd");

		# setup loginfo to make group ownership every commit
		#CB# This cause a bug on the client
		##system("echo \"ALL chgrp -R $gname /cvsroot/$gname\" > $cvs_dir/CVSROOT/loginfo");
		system("echo \"\" > $cvs_dir/CVSROOT/val-tags");
		chmod 0644, "$cvs_dir/CVSROOT/val-tags";

		# set group ownership, anonymous group user
		system("chown -R nobody:$gid $cvs_dir");
		#CB# Added s bit to have all owned by group
		#system("chmod g+rw $cvs_dir");
		system("chmod g+rws $cvs_dir");

		# And finally add a user for this repository
		#CB# Do it all the time
		#CB# push @passwd_array, "anoncvs_$gname:x:$cvs_id:$gid:Anonymous CVS User for $gname:/cvsroot/$gname:/bin/false\n";
	}
	#CB# Do it all the time
	push @passwd_array, "anoncvs_$gname:x:$cvs_id:$gid:Anonymous CVS User for $gname:/cvsroot/$gname:/bin/false\n";
}

#
# Now write out the new files
#
write_array_file($file_dir."chroot/etc/passwd", @passwd_array);
write_array_file($file_dir."chroot/etc/shadow", @shadow_array);
write_array_file($file_dir."chroot/etc/group", @group_array);



###############################################
# Begin functions
###############################################

#############################
# User Add Function
#############################
sub add_user {  
	my ($uid, $username, $realname, $shell, $passwd) = @_;
	my $skel_array = ();
	
	$home_dir = $homedir_prefix.$username;

	print("Making a User Account for : $username\n");
		
	push @passwd_array, "$username:x:$uid:$uid:$realname:/home/users/$username:$shell\n";
	push @shadow_array, "$username:$passwd:$date:0:99999:7:::\n";
	push @group_array, "$username:x:$uid:\n";
	
	# Now lets create the homedir and copy the contents of /etc/skel into it.
	mkdir $home_dir, 0751;
	
        chown $uid, $uid, $home_dir;
}

#############################
# User Add Function
#############################
sub update_user {
	my ($uid, $username, $realname, $shell, $passwd) = @_;
	my ($p_uid, $p_junk, $p_uid, $p_gid, $p_realname, $p_homedir, $p_shell);
	my ($s_username, $s_passwd, $s_date, $s_min, $s_max, $s_inact, $s_expire, $s_flag, $s_resv, $counter);
	
	#CB# Let's make this silent
	#print("Updating Account for: $username\n");
	
#	foreach (@passwd_array) {
#		($p_uid, $p_junk, $p_uid, $p_gid, $p_realname, $p_homedir, $p_shell) = split(":", $_);
#		
#		if ($uid == $p_uid) {
#			if ($realname ne $p_realname) {
#				$passwd_array[$counter] = "$username:x:$uid:$uid:$realname:$p_homedir:$shell\n";
#			} elsif ($shell ne $t_shell) {
#				$passwd_array[$counter] = "$username:x:$uid:$uid:$p_realname:$p_homedir:$p_shell";
#			}
#		}
#		$counter++;
#	}
#	
#	$counter = 0;
#	
#	foreach (@shadow_array) {
#		($s_username, $s_passwd, $s_date, $s_min, $s_max, $s_inact, $s_expire, $s_flag, $s_resv) = split(":", $_);
#		if ($username eq $s_username) {
#			if ($passwd ne $s_passwd) {
#				$shadow_array[$counter] = "$username:$passwd:$s_date:$s_min:$s_max:$s_inact:$s_expire:$s_flag:$s_resv";
#			}
#		}
#		$counter++;
#	}
	push @passwd_array, "$username:x:$uid:$uid:$realname:/home/users/$username:$shell\n";
	push @shadow_array, "$username:$passwd:$date:0:99999:7:::\n";
	push @group_array, "$username:x:$uid:\n";
}

#############################
# User Deletion Function
#############################
sub delete_user {
	my ($username, $junk, $uid, $gid, $realname, $homedir, $shell, $counter);
	my $this_user = shift(@_);
	
#	foreach (@passwd_array) {
#		($username, $junk, $uid, $gid, $realname, $homedir, $shell) = split(":", $_);
#		if ($this_user eq $username) {
#			$passwd_array[$counter] = '';
#		}
#		$counter++;
#	}
	
	print("Deleting User : $this_user\n");
# Find a better solution
# I don't like this with vars
#	system("cd $homedir_prefix ; /bin/tar -czf $tar_dir/$username.tar.gz $username");
#	system("rm -fr $homedir_prefix/$username");
	system("/bin/mv /var/lib/sourceforge/cvsroot/home/users/$username /var/lib/sourceforge/cvsroot/home/users/deleted_$username");
	system("/bin/tar -czf /var/lib/sourceforge/tmp/$username.tar.gz /var/lib/sourceforge/chroot/home/users/deleted_$username && /bin/rm -rf /var/lib/sourceforge/home/users/deleted_$username");
}

#############################
# User Suspension Function
#############################
sub suspend_user {
	my $this_user = shift(@_);
	my ($s_username, $s_passwd, $s_date, $s_min, $s_max, $s_inact, $s_expire, $s_flag, $s_resv, $counter);
	
	my $new_pass = "!!" . $s_passwd;
	
#	foreach (@shadow_array) {
#		($s_username, $s_passwd, $s_date, $s_min, $s_max, $s_inact, $s_expire, $s_flag, $s_resv) = split(":", $_);
#		if ($username eq $s_username) {
#		       $shadow_array[$counter] = "$s_username:$new_pass:$s_date:$s_min:$s_max:$s_inact:$s_expire:$s_flag:$s_resv";
#		}
#		$counter++;
#	}
	push @passwd_array, "$username:x:$uid:$uid:$realname:/home/users/$username:$shell\n";
	push @shadow_array, "$username:!!$passwd:$date:0:99999:7:::\n";
}


#############################
# Group Add Function
#############################
sub add_group {  
	my ($gid, $gname, $userlist) = @_;
	my ($log_dir, $cgi_dir, $ht_dir, $cvs_dir, $cvs_id);
	
	$group_dir = $grpdir_prefix.$gname;
	$log_dir = $group_dir."/log";
	$cgi_dir = $group_dir."/cgi-bin";
	$ht_dir = $group_dir."/htdocs";

	print("Making a Group for : $gname\n");
		
	push @group_array, "$gname:x:$gid:$userlist\n";
	
	#if (substr($hostname,0,3) ne "cvs") {
		# Now lets create the group's homedir.
		mkdir $group_dir, 0775;
		mkdir $log_dir, 0775;
		mkdir $cgi_dir, 0775;
		mkdir $ht_dir, 0775;
		chown $dummy_uid, $gid, ($group_dir, $log_dir, $cgi_dir, $ht_dir);
	#}
}

#############################
# Group Update Function
#############################
sub update_group {
	my ($gid, $gname, $userlist) = @_;
	my ($p_gname, $p_junk, $p_gid, $p_userlist, $counter);
	
	#CB# Let's make this silent
	#print("Updating Group: $gname\n");
	
#	foreach (@group_array) {
#		($p_gname, $p_junk, $p_gid, $p_userlist) = split(":", $_);
#		
#		if ($gid == $p_gid) {
#			if ($userlist ne $p_userlist) {
#				$group_array[$counter] = "$gname:x:$gid:$userlist\n";
#			}
#		}
#		$counter++;
#	}
	push @group_array, "$gname:x:$gid:$userlist\n";
}

#############################
# Group Delete Function
#############################
sub delete_group {
	my ($gname, $x, $gid, $userlist, $counter);
	my $this_group = shift(@_);
	$counter = 0;
	
#	foreach (@group_array) {
#		($gname, $x, $gid, $userlist) = split(":", $_);
#		if ($this_user eq $gname) {
#			$group_array[$counter] = '';
#		}
#		$counter++;
#	}

	if (substr($hostname,0,3) ne "cvs") {
		print("Deleting Group: $this_group\n");
# Find a better solution
#		system("cd $grpdir_prefix ; /bin/tar -czf $tar_dir/$this_group.tar.gz $this_group");
#		system("rm -fr $grpdir_prefix/$this_group");
		system("/bin/mv /var/lib/sourceforge/cvsroot/home/groups/$this_group /var/lib/sourceforge/cvsroot/home/groups/deleted_group_$this_group");
		system("/bin/tar -czf /var/lib/sourceforge/tmp/$this_group.tar.gz /var/lib/sourceforge/chroot/home/groups/deleted_group_$this_group && /bin/rm -rf /var/lib/sourceforge/home/groups/deleted_group_$this_group");
	}
}
