#!/usr/bin/php -q
<?php
#$argc=$ARGC;
#$argv=$ARGV;

define('TO_TRANSLATE', '#TO_TRANSLATE#');
define('TO_REMOVE', '#TO_REMOVE#');

if ($argc!=3 || ('-h'==$argv[1] || '--help'==$argv[1])) {
	showHelp();
	exit;
}

$languageDir = '/www/include/languages/';

$baseFile = $argv[1].$languageDir.'Base.tab';
if(!eregi('\.tab$', $argv[2])) {
	$targetFile = $argv[1].$languageDir.$argv[2].'.tab';
} else {
	$targetFile = $argv[1].$languageDir.$argv[2];
}

if (!file_exists($baseFile) || !file_exists($targetFile)) {
	echo "FATAL: Base.tab or $argv[2].tab may not exist.\n";
	exit;
}

$baseContent = getLanguageAsArray($baseFile);
$targetContent = getLanguageAsArray($targetFile);

reset($baseContent);
$resultContent = array();

$stderr = fopen('php://stderr', 'w');
foreach($baseContent AS $pageName=>$page) {
	foreach($page AS $key => $value) {
		if(!isset($targetContent[$pageName][$key])) {
			fwrite($stderr, 'TO_TRANSLATE - added : '.$pageName.' '.$key."\n");
			$resultContent[$pageName][$key] = array('value' => $value['value'], 'prefix' => TO_TRANSLATE);
		} else {
			if($targetContent[$pageName][$key]['prefix'] == TO_TRANSLATE) {
				fwrite($stderr, 'TO_TRANSLATE - updated : '.$pageName.' '.$key."\n");
				$resultContent[$pageName][$key] = array('value' => $value['value'], 'prefix' => TO_TRANSLATE);
			} else {
				$resultContent[$pageName][$key] = $targetContent[$pageName][$key];
			}
			unset($targetContent[$pageName][$key]);
		}
	}
}

reset($targetContent);
foreach($targetContent AS $pageName => $page) {
	foreach($page AS $key => $value) {
		fwrite($stderr, 'TO_REMOVE - added : '.$pageName.' '.$key."\n");
		$resultContent[$pageName][$key] = array('value' => $value['value'], 'prefix' => TO_REMOVE);
	}
}
fclose($stderr);

unset($targetContent);
unset($baseContent);

ksort($resultContent);
reset($resultContent);

foreach($resultContent AS $pageName => $page) {
	foreach($page AS $key => $value) {
		echo $value['prefix'].$pageName."\t".$key."\t".$value['value']."\n";
	}
}

function showHelp() {
	global $argc, $argv;
	$self = basename($argv[0]);
?>
>> GForge language file merge utility, by Hunte Swee<hunte@users.sourceforge.net> and Guillaume Smet<guillaume-gforge@smet.org><<

Usage:
<?=$self?> <GForge root directory> <Target language> 1>merge.tab 2>merge.log
Example:
<?=$self?> /usr/share/gforge SimplifiedChinese 1>merge.tab 2>merge.log

<?php
}

function getLanguageAsArray($languageFile) {
	$content = file($languageFile);
	reset($content);
	$result = array();
	while(list(, $line)=each($content)) {
		$line = trim($line);
		if(!empty($line)) {
			if(eregi('^'.TO_TRANSLATE, $line)) {
				$line = substr($line, strlen(TO_TRANSLATE));
				$prefix = TO_TRANSLATE;
			} elseif(eregi('^'.TO_REMOVE, $line)) {
				$line = substr($line, strlen(TO_REMOVE));
				$prefix = TO_REMOVE;
			} elseif(eregi('^#', $line)) {
				continue;
			} else {
				$prefix = '';
			}
			list($pn, $key, $val) = explode("\t", $line, 3);
			$result[$pn][$key] = array('value' => $val, 'prefix' => $prefix);
		}
	}
	return $result;
}
?>
