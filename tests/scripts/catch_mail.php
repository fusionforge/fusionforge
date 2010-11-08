#!/usr/bin/php
<?php

$LOG_FILE = '/tmp/catch_mail.log';

// Clear log file ?
if ($argv[1] == '-c') {
	if (file_exists($LOG_FILE)) {
		unlink($LOG_FILE);
	}
	exit(0);
}

// Catch the mail
$stdin = fopen('php://stdin', 'r');
$in = '';
$inBody = 0;
$cr = 0;
while (!feof($stdin)) {
	$line = rtrim(fgets($stdin), "\n\r");
	
	if ($inBody) {
		$in .= $line . "\n";
	}
	elseif (! $line) {
		$inBody = 1;
		$in .= "\n";
	}
	elseif (preg_match('/^(\w+):.*/', $line, $matches)) {
		switch (strtolower($matches[1])) {
			case 'from':
			case 'to':
			case 'bcc':
			case 'subject':
				$in .= $line . "\n";
				break;
		}
	}
}

$fp = fopen($LOG_FILE, 'w');
fwrite($fp, $in);
fclose($fp);

?>