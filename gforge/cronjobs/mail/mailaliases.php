#! /usr/bin/php4 -f
<?php

require ('squal_pre.php');
require ('common/include/cron_utils.php');

if (!file_exists('/etc/aliases.org')) {
	$err .= "CANNOT PROCEED - you must first backup your /etc/aliases file";
	exit;
}

//
//	Write out all the aliases
//
$fp = fopen("/etc/aliases","w");
if (!($fp)) {
	$err .= ("ERROR: unable to open target file\n");
	exit;
}

//
//	Read in the "default" aliases
//
$h = fopen("/etc/aliases.org","r");
$aliascontents = fread($h,filesize("/etc/aliases.org"));
$aliaslines = explode("\n",$aliascontents);
for($k = 0; $k < count($aliaslines); $k++) {
	$aliasline = explode(":",$aliaslines[$k]);
	$def_aliases[strtolower($aliasline[0])]=1;
	fwrite($fp,$aliaslines[$k]."\n");
}
$err .= "\n$k Alias Lines";
fclose($h);

//
//	Read in the mailman aliases
//
$h2 = fopen("/tmp/mailman-aliases","r");
$mailmancontents = fread($h2,filesize("/tmp/mailman-aliases"));
$mailmanlines = explode("\n",$mailmancontents);
for($k = 0; $k < count($mailmanlines); $k++) {
	$mailmanline = explode(":",$mailmanlines[$k]);
	if ($def_aliases[strtolower($mailmanline[0])]) {
		//alias is already taken - perhaps by default
	} else {
		$def_aliases[strtolower($mailmanline[0])]=1;
		fwrite($fp,$mailmanlines[$k]."\n");
	}
}
$err .= "\n$k Mailman Lines";
fclose($h2);


//
//	Write out the user aliases
//
$res=db_query("SELECT user_name,email FROM users WHERE status = 'A' AND email != ''");
$err .= db_error();

$rows=db_numrows($res);


for ($i=0; $i<$rows; $i++) {
	$user = db_result($res,$i,0);
    $email = db_result($res,$i,1);
	if ($def_aliases[$user]) {
		//alias is already taken - perhaps by default or by a mailing list
	} else {
		fwrite($fp, $user . ": " . $email . "\n");
	}
}

fclose($fp);

db_free_result($res);
$ok = `newaliases`;
$err .= $ok;

cron_entry(17,$err);

?>
