#! /usr/bin/php -f
<?php

require ('squal_pre.php');

$res=db_query("SELECT user_name,email FROM users WHERE status = 'A' AND email != ''");
echo db_error();

$rows=db_numrows($res);

$fp = fopen("/etc/mail/aliases.gforge","w");
if (!($fp)) {
        print ("ERROR: unable to open target file\n");
        exit;
}

$allusers = "gforge-users: ";
$first = 1;

for ($i=0; $i<$rows; $i++) {
	$user = db_result($res,$i,0);
        $email = db_result($res,$i,1);
	fputs($fp, $user . ": " . $email . "\n");
	if ($first == 1) {
		$first = 0;
	}
	else {
		$allusers = $allusers . ", ";
	}
	$allusers = $allusers . $user;
}
fputs($fp,"\n");
fputs($fp,$allusers . "\n");
fclose($fp);

db_free_result($res);
$ok = `newaliases`;
echo $ok;
?>
