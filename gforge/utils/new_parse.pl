#!/usr/bin/perl
#
# $Id$
#
# new_parse.pl - new script to parse out the database dumps and create/update/delete user
#		 accounts on the client machines
use Sys::Hostname;

#$hostname = hostname();

require("/usr/lib/gforge/lib/include.pl");  # Include all the predefined functions and variables

$hostname = "cvs";

my $user_file = $file_dir . "dumps/user_dump";
my $group_file = $file_dir . "dumps/group_dump";
my ($uid, $status, $username, $shell, $passwd, $realname);
my ($gname, $gstatus, $gid, $userlist);

# Open up all the files that we need.
@userdump_array = open_array_file($user_file);
@groupdump_array = open_array_file($group_file);

#LDAP_NOW#open (FD,'>'.$file_dir."chroot/etc/passwd"); close(FD);
#LDAP_NOW#open (FD,'>'.$file_dir."chroot/etc/shadow"); close(FD);
#LDAP_NOW#open (FD,'>'.$file_dir."chroot/etc/group"); close(FD);

#LDAP_NOW#@passwd_array = open_array_file($file_dir."chroot/etc/passwd");
#LDAP_NOW#push @passwd_array, "root:x:0:0:Root:/:/bin/bash\n";
#LDAP_NOW#push @passwd_array, "dummy:x:$dummy_uid:$dummy_uid:Dummy User:/:/bin/false\n";
#LDAP_NOW#push @passwd_array, "nobody:x:65534:65534:nobody:/:/bin/false\n";

#LDAP_NOW#@shadow_array = open_array_file($file_dir."chroot/etc/shadow");
#LDAP_NOW#push @shadow_array, "root:*:11142:0:99999:7:::\n";
#LDAP_NOW#push @shadow_array, "dummy:*:11142:0:99999:7:::\n";
#LDAP_NOW#push @shadow_array, "nobody:*:11142:0:99999:7:::\n";

#LDAP_NOW#@group_array = open_array_file($file_dir."chroot/etc/group");
#LDAP_NOW#push @group_array, "root:x:0\n";
#LDAP_NOW#push @group_array, "dummy:x:$dummy_uid:\n";
#LDAP_NOW#push @group_array, "nogroup:x:65534:\n";

#
# Loop through @userdump_array and deal w/ users.
#
#CB# Let's make this silent
#print ("\n\n	Processing Users\n\n");
while ($ln = pop(@userdump_array)) {
	chop($ln);
	($uid, $status, $username, $shell, $passwd, $realname) = split(":", $ln);
	$uid += $uid_add;
	$username =~ tr/A-Z/a-z/;
	$user_exists = (-d $homedir_prefix . $username || -f "/var/lib/gforge/tmp/$username.tar.gz");
	
	if ($status eq 'A' && $user_exists) {
		update_user($uid, $username, $realname, $shell, $passwd);
	
	} elsif ($status eq 'A' && !$user_exists) {
		add_user($uid, $username, $realname, $shell, $passwd);
	
	} elsif ($status eq 'D' && $user_exists) {
		delete_user($username);
	
	} elsif ($status eq 'D' && !$user_exists) {
		#print("Error trying to delete user: $username\n");
		
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
	
	$cvs_id = $gid + $anoncvs_uid_add;
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
		chmod 0664, "$cvs_dir/CVSROOT/val-tags";

		# set group ownership, anonymous group user
		system("chown -R nobody:$gid $cvs_dir");
		#CB# Added s bit to have all owned by group
		#system("chmod g+rw $cvs_dir");
		system("chmod g+rws $cvs_dir");

	}
	#CB# Do it all the time
	#LDAP_NOW#push @passwd_array, "anoncvs_$gname:x:$cvs_id:$gid:Anonymous CVS User for $gname:/cvsroot/$gname:/bin/false\n";
}

#
# Now write out the new files
#
#LDAP_NOW#write_array_file($file_dir."chroot/etc/passwd", @passwd_array);
#LDAP_NOW#write_array_file($file_dir."chroot/etc/shadow", @shadow_array);
#LDAP_NOW#write_array_file($file_dir."chroot/etc/group", @group_array);



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
		
	#LDAP_NOW#push @passwd_array, "$username:x:$uid:$uid:$realname:/home/users/$username:$shell\n";
	#LDAP_NOW#push @shadow_array, "$username:$passwd:$date:0:99999:7:::\n";
	#LDAP_NOW#push @group_array, "$username:x:$uid:\n";
	
	# Now lets create the homedir and copy the contents of /etc/skel into it.
	mkdir $home_dir, 0755;
        chown $uid, $uid, $home_dir;
	
	mkdir $home_dir.'/incoming', 0755;
	chown $uid, $uid, $home_dir.'/incoming' ;
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
	
        $home_dir = $homedir_prefix.$username;
	unless (-d $home_dir.'/incoming') {
	    mkdir $home_dir.'/incoming', 0755;
	}
        system("chown $username:$username $home_dir/incoming");
	system("chmod 0755 $home_dir/incoming");

	#LDAP_NOW#push @passwd_array, "$username:x:$uid:$uid:$realname:/home/users/$username:$shell\n";
	#LDAP_NOW#push @shadow_array, "$username:$passwd:$date:0:99999:7:::\n";
	#LDAP_NOW#push @group_array, "$username:x:$uid:\n";
}

#############################
# User Deletion Function
#############################
sub delete_user {
	#my ($username, $junk, $uid, $gid, $realname, $homedir, $shell, $counter);
	my $username = shift(@_);
	
	my $alreadydone=(-f "/var/lib/gforge/tmp/$username.tar.gz");
	if (!$alreadydone){
	print("Deleting User : $username\n");
		print("/bin/mv /var/lib/gforge/chroot/home/users/$username /var/lib/gforge/chroot/home/users/deleted_$username\n");
		system("/bin/mv /var/lib/gforge/chroot/home/users/$username /var/lib/gforge/chroot/home/users/deleted_$username");
		print("/bin/tar -czf /var/lib/gforge/tmp/$username.tar.gz /var/lib/gforge/chroot/home/users/deleted_$username && /bin/rm -rf /var/lib/gforge/chroot/home/users/deleted_$username\n");
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
	
	#LDAP_NOW#push @passwd_array, "$username:x:$uid:$uid:$realname:/home/users/$username:$shell\n";
	#LDAP_NOW#push @shadow_array, "$username:!!$passwd:$date:0:99999:7:::\n";
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
		
	#LDAP_NOW#push @group_array, "$gname:x:$gid:$userlist\n";
	
	mkdir $group_dir, 0775;
	mkdir $log_dir, 0775;
	mkdir $cgi_dir, 0775;
	mkdir $ht_dir, 0775;
	chown $dummy_uid, $gid, ($group_dir, $log_dir, $cgi_dir, $ht_dir);
}

#############################
# Group Update Function
#############################
sub update_group {
	my ($gid, $gname, $userlist) = @_;
	my ($p_gname, $p_junk, $p_gid, $p_userlist, $counter);
	
	#CB# Let's make this silent
	#print("Updating Group: $gname\n");
	
	#LDAP_NOW#push @group_array, "$gname:x:$gid:$userlist\n";
}

#############################
# Group Delete Function
#############################
sub delete_group {
	my ($gname, $x, $gid, $userlist, $counter);
	my $this_group = shift(@_);
	$counter = 0;
	
	if (substr($hostname,0,3) ne "cvs") {
		print("Deleting Group: $this_group\n");
		system("/bin/mv /var/lib/gforge/chroot/home/groups/$this_group /var/lib/gforge/chroot/home/groups/deleted_group_$this_group");
		system("/bin/tar -czf /var/lib/gforge/tmp/$this_group.tar.gz /var/lib/gforge/chroot/home/groups/deleted_group_$this_group && /bin/rm -rf /var/lib/gforge/chroot/home/groups/deleted_group_$this_group");
	}
}
