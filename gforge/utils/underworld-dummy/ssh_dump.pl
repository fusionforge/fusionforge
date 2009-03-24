#!/usr/bin/perl
#
# ssh_dump.pl - Script to suck data outta the database to be processed by ssh_create.pl
#
use DBI;

require("/usr/share/gforge/lib/include.pl");  # Include all the predefined functions

my $ssh_array = ();

&db_connect;

# Dump the Table information
$query = "SELECT user_name,unix_uid,authorized_keys FROM users WHERE authorized_keys != '' AND status !='D'";
$c = $dbh->prepare($query);
$c->execute();
while(my ($username, $unix_uid, $ssh_key) = $c->fetchrow()) {
	$new_list = "$username:$unix_uid:$ssh_key\n";
	push @ssh_array, $new_list;
}

# Now write out the files
write_array_file($file_dir."/dumps/ssh_dump", @ssh_array);
