#!/usr/bin/php
<?php

require_once('squal_pre.php');

$res=db_query("SELECT user_name,user_id,authorized_keys FROM users WHERE authorized_keys != ''");

for ($i=0; $i<db_numrows($res); $i++) {
	$ssh_key=db_result($res,$i,'authorized_keys');
	$username=db_result($res,$i,'user_name');
	$uid=db_result($res,$i,'user_id');
/*
	$ssh_key =~ s/\#\#\#/\n/g;
	$uid += 1000;

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
*/
}

?>
