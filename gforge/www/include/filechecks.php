<?php
/**
 * File checking functions
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/**
 * filechecks_islegalname() - Make sure a filename is legal
 *
 * @param		string	The name of the file to check
 */
function filechecks_islegalname($filename) {
	if (strstr($filename,' ') || strstr($filename,'\\')
		|| strstr($filename,"'") || strstr($filename,'"')
		|| strstr($filename,';') || strstr($filename,'`')
		|| strstr($filename,'|') || strstr($filename,'$')
		|| strstr($filename,',') || strstr($filename,':')
		|| strstr($filename,'@') || strstr($filename,'*')
		|| strstr($filename,'%') || strstr($filename,'^')
		|| strstr($filename,'&')
	        || strstr($filename,'(') || strstr($filename,')')
		|| strstr($filename,'>') || strstr($filename,'<')) {
		return 0;
	}

	if (ereg('^\.',$filename)) {
		return 0;
	}

	return 1;
}

/**
 * filechecks_targz() - Verify the integrity of a .tar.gz file.
 *
 * @param		string	The name of the targz file to check
 */
function filechecks_targz($filename) {
	exec("tar -ztvf $GLOBALS[FTPINCOMING_DIR]/" . EscapeShellCmd($filename),$output,$ret);
	if ($ret) {
		for ($i=0;$i<count($output);$i++) {
			$exitout .= "<br />" . $output[$i] . "\n";
		}
		exit_error("Failed tar/gz integrity check.","Output follows: <p>$exitout</p>");
	}
}

/**
 * filechecks_gz(0 - Verify the integrity of a .gz file.
 *
 * @param		string	The name of the gz file to check.
 */
function filechecks_gz($filename) {
	exec("gunzip -t $GLOBALS[FTPINCOMING_DIR]/" . EscapeShellCmd($filename),$output,$ret);
	if ($ret) {
		for ($i=0;$i<count($output);$i++) {
			$exitout .= "<br />" . $output[$i] . "\n";
		}
		exit_error("Failed gzip integrity check.","Output follows: <p>$exitout</p>");
	}
}

/**
 * filechecks_getfiletype() - Resolve the filetype of a file.
 *
 * @param		string	The name of the file to resolve.
 */
function filechecks_getfiletype($filename) {

	$filename = chop($filename);

	if (ereg(".diff$",$filename)) {
		$filetype = "diff";
	}
	elseif (ereg(".diff.gz$",$filename)) {
		$filetype = "diff/gz";
		filechecks_gz($filename);
	}
	elseif (ereg(".asc$",$filename)) {
		$filetype = "asc";
	}
	elseif (ereg(".bin$",$filename)) {
		$filetype = "bin";
	}
	elseif (ereg(".exe$",$filename)) {
		$filetype = "exe";
	}
	elseif (ereg(".jar$",$filename)) {
		$filetype = "jar";
	}
	elseif (ereg(".lsm$",$filename)) {
		$filetype = "lsm";
	}
	elseif (ereg(".pdb$",$filename)) {
		$filetype = "pilot";
	}
	elseif (ereg(".pl$",$filename)) {
		$filetype = "perl";
	}
	elseif (ereg(".py$",$filename)) {
		$filetype = "python";
	}
	elseif (ereg(".prc$",$filename)) {
		$filetype = "pilot";
	}
	elseif (ereg(".sig$",$filename)) {
		$filetype = "sig";
	}
	elseif (ereg(".tar.bz2$",$filename)) {
		$filetype = "tar/bz2";
	}
	elseif (ereg(".tar.gz$",$filename)) {
		$filetype = "tar/gz";
		filechecks_targz($filename);
	}
	elseif (ereg(".tgz$",$filename)) {
		$filetype = "tgz";
	}
	elseif (ereg(".zip$",$filename)) {
		$filetype = "zip";
	}
	elseif (ereg(".shar.gz$",$filename)) {
		$filetype = "shar/gz";
	}
	elseif (ereg(".bz2$",$filename)) {
		$filetype = "bz2";
	}
	elseif (ereg(".gz$",$filename)) {
		$filetype = "gz";
		filechecks_gz($filename);
	}
	elseif (ereg(".i386.rpm$",$filename)) {
		$filetype = "i386 rpm";
	}
	elseif (ereg(".alpha.rpm$",$filename)) {
		$filetype = "alpha rpm";
	}
	elseif (ereg(".src.rpm$",$filename)) {
		$filetype = "src rpm";
	}
	elseif (ereg(".rpm$",$filename)) {
		$filetype = "rpm";
	}
	elseif (ereg(".deb$",$filename)) {
		$filetype = "deb";
	}
	elseif (ereg("\.([a-zA-Z]+)$",$filename,$regs)) {
		$filetype = $regs[1];		
	} 

	if (!$filetype) {
		exit_error ("Unknown file type","This file does not have a system-recognized filename type.");
	}

	if (!$filename) {
		exit_error ("File does not exist","You must supply a filename.");
	}

	if (!file_exists("$GLOBALS[FTPINCOMING_DIR]/$filename")) {
		exit_error ("File does not exist","File $filename is not in incoming FTP directory.");
	}
	return $filetype;
}

?>
