#!/usr/bin/php -f
<?php
$fp = fopen("php://stdin","r");
$fp2 = fopen("/etc/mail/aliases.mailman","a+");
if (!($fp)) {
	print ("ERROR: unable to open standard input\n");
	exit;
}
if (!($fp2)) {
	print ("ERROR: unable to open target file\n");
	exit;
}
while (!feof($fp)) {
	$alias = fgets($fp, 4096);
	fputs($fp2, $alias);
}
fclose($fp);
fclose($fp2);
?>
