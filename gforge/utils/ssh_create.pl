#!/usr/bin/perl
#
# $Id$
#
# ssh_create.pl - Dumps SSH authorized_keys into users homedirs on the cvs server.
#

require("/usr/lib/sourceforge/lib/include.pl");  # Include all the predefined functions and variables

my @ssh_key_file = open_array_file($file_dir."dumps/ssh_dump");
my ($username, $ssh_keys, $ssh_dir);

#print("\n\n	Processing Users\n\n");
while ($ln = pop(@ssh_key_file)) {
	chop($ln);

	($username, $uid, $ssh_key) = split(":", $ln);

	$ssh_key =~ s/\#\#\#/\n/g;
	$username =~ tr/[A-Z]/[a-z]/;
	$uid += $uid_add;

	push @user_authorized_keys, $ssh_key . "\n";

	$ssh_dir = "$homedir_prefix$username/.ssh";

	if (! -d $ssh_dir) {
		mkdir $ssh_dir, 0755;
	}

	#print("Writing authorized_keys for $username: ");

	write_array_file("$ssh_dir/authorized_keys", @user_authorized_keys);
	system("chown $uid:$uid $homedir_prefix$username");
	system("chown $uid:$uid $ssh_dir");
	system("chmod 0644 $ssh_dir/authorized_keys");
	system("chown $uid:$uid $ssh_dir/authorized_keys");

	#print ("Done\n");

	undef @user_authorized_keys;
}
