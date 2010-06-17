#!/usr/bin/perl
#
# dump_database.pl - script to dump data from the database to flat files so the ofher perl
#		     scripts can process it without needing to access the database.
use DBI;

require("/usr/share/gforge/lib/include.pl");  # Include all the predefined functions

my $user_array = ();
my $group_array = ();

&db_connect;

# Dump the users Table information
#my $query = "select unix_uid, unix_status, user_name, shell, unix_pw, realname from users where unix_status != \"N\"";
my $query = "select unix_uid, unix_status, user_name, shell, unix_pw, realname from users where unix_status != 'N'";
my $c = $dbh->prepare($query);
$c->execute();
	
while(my ($id, $status, $username, $shell, $passwd, $realname) = $c->fetchrow()) {
	$home_dir = $homedir_prefix.$username;

	$userlist = "$id:$status:$username:$shell:$passwd:$realname\n";

	push @user_array, $userlist;
}


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
write_array_file($file_dir."/dumps/user_dump", @user_array);
write_array_file($file_dir."/dumps/group_dump", @group_array);
