#!/usr/bin/php
<?php

require_once('squal_pre.php');

$res=db_query("SELECT user_name,user_id,authorized_keys 
	FROM users 
	WHERE authorized_keys != ''
	AND status='A'");

for ($i=0; $i<db_numrows($res); $i++) {
	$ssh_key=db_result($res,$i,'authorized_keys');
	$username=db_result($res,$i,'user_name');
	$uid=db_result($res,$i,'user_id');

	$ssh_key str_replace('###',"\n",$ssh_key);
	$uid += 1000;

	$ssh_dir = "$homedir_prefix$username/.ssh";

	if (!is_dir($ssh_dir)) {
		mkdir ($ssh_dir, 0755);
	}

	$h8 = fopen("$ssh_dir/authorized_keys","w");
	fwrite($h8,$ssh_key);
	fclose($h8);

	system("chown $uid:$uid $homedir_prefix$username");
	system("chown $uid:$uid $ssh_dir");
	system("chmod 0644 $ssh_dir/authorized_keys");
	system("chown $uid:$uid $ssh_dir/authorized_keys");

}

?>
