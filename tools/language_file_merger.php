#!/usr/bin/php -q
<?
#$argc=$ARGC;
#$argv=$ARGV;

if ($argc!=3 || ("-h"==$argv[1] || "--help"==$argv[1])) {
	showHelp();
	exit;
}


$langdir = "/www/include/languages/";
$basefile = $argv[1] . $langdir . "Base.tab";
if (!eregi("\.tab$", $argv[2])) {
	$targfile = $argv[1] . $langdir . $argv[2] . ".tab";
} else {
	$targfile = $argv[1] . $langdir . $argv[2];
}

if (!file_exists($basefile) || !file_exists($targfile)) {
	echo "FATAL: Base.tab or $argv[2] may not exist.\n";
	exit;
}

$basectnt = getLangAsArray($basefile);
$targctnt = getLangAsArray($targfile);

reset($basectnt);

while (list($k1, $v1) = each($basectnt)) {
	while (list($k2, $v2) = each($v1)) {
		if (!isset($targctnt[$k1][$k2])) {
			$targctnt[$k1][$k2] = $v2	;
		}
	}
}

ksort($targctnt);
reset($targctnt);

while (list($k1, $v1) = each($targctnt)) {
	while (list($k2, $v2) = each($v1)) {
		echo "$k1\t$k2\t$v2";
	}
}

function showHelp() {
	global $argc, $argv;
	$self = basename($argv[0]);
?>
>> GForge language file merge utility, by Hunte Swee<hunte@users.sourceforge.net> <<

Usage:
<?=$self?> <GForge root directory> <Target language>
Example:
<?=$self?> /usr/share/gforge SimplifiedChinese

<?
}

function getLangAsArray($langfile) {
	$ctnt = file($langfile);
	reset($ctnt);
	while (list(, $line)=each($ctnt)) {
		if (eregi("^#", trim($line))) {
			continue;
		}
		list($pn, $key, $val) = explode("\t", $line, 3);
		$result[$pn][$key] = $val;
	}
	return $result;
}
?>
