<?php
/**
 * File checking functions
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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

	if (preg_match('/^\./',$filename)) {
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
		exit_error(sprintf(_('Failed tar/gz integrity check. Output follows: <p>$s</p>'),$exitout),'');
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
		exit_error(sprintf(_('Failed gzip integrity check. Output follows: <p>$s</p>'),$exitout),'');
	}
}

/**
 * filechecks_getfiletype() - Resolve the filetype of a file.
 *
 * @param		string	The name of the file to resolve.
 */
function filechecks_getfiletype($filename) {

	$filename = chop($filename);

	if (preg_match("/\.diff$/",$filename)) {
		$filetype = "diff";
	}
	elseif (preg_match("/\.diff.gz$/",$filename)) {
		$filetype = "diff/gz";
		filechecks_gz($filename);
	}
	elseif (preg_match("/\.asc$/",$filename)) {
		$filetype = "asc";
	}
	elseif (preg_match("/\.bin$/",$filename)) {
		$filetype = "bin";
	}
	elseif (preg_match("/\.exe$/",$filename)) {
		$filetype = "exe";
	}
	elseif (preg_match("/\.jar$/",$filename)) {
		$filetype = "jar";
	}
	elseif (preg_match("/\.lsm$/",$filename)) {
		$filetype = "lsm";
	}
	elseif (preg_match("/\.pdb$/",$filename)) {
		$filetype = "pilot";
	}
	elseif (preg_match("/\.pl$/",$filename)) {
		$filetype = "perl";
	}
	elseif (preg_match("/\.py$/",$filename)) {
		$filetype = "python";
	}
	elseif (preg_match("/\.prc$/",$filename)) {
		$filetype = "pilot";
	}
	elseif (preg_match("/\.sig$/",$filename)) {
		$filetype = "sig";
	}
	elseif (preg_match("/\.tar.bz2$/",$filename)) {
		$filetype = "tar/bz2";
	}
	elseif (preg_match("/\.tar.gz$/",$filename)) {
		$filetype = "tar/gz";
		filechecks_targz($filename);
	}
	elseif (preg_match("/\.tgz$/",$filename)) {
		$filetype = "tgz";
	}
	elseif (preg_match("/\.zip$/",$filename)) {
		$filetype = "zip";
	}
	elseif (preg_match("/\.shar.gz$/",$filename)) {
		$filetype = "shar/gz";
	}
	elseif (preg_match("/\.bz2$/",$filename)) {
		$filetype = "bz2";
	}
	elseif (preg_match("/\.gz$/",$filename)) {
		$filetype = "gz";
		filechecks_gz($filename);
	}
	elseif (preg_match("/\.i386.rpm$/",$filename)) {
		$filetype = "i386 rpm";
	}
	elseif (preg_match("/\.alpha.rpm$/",$filename)) {
		$filetype = "alpha rpm";
	}
	elseif (preg_match("/\.src.rpm$/",$filename)) {
		$filetype = "src rpm";
	}
	elseif (preg_match("/\.rpm$/",$filename)) {
		$filetype = "rpm";
	}
	elseif (preg_match("/\.deb$/",$filename)) {
		$filetype = "deb";
	}
	elseif (preg_match("/\.([a-zA-Z]+)$/",$filename,$regs)) {
		$filetype = $regs[1];
	}

	if (!$filetype) {
		exit_error (_('This file does not have a system-recognized filename type.'),'');
	}

	if (!$filename) {
		exit_error (_('File does not exist. You must supply a filename.'),'');
	}

	if (!file_exists("$GLOBALS[FTPINCOMING_DIR]/$filename")) {
		exit_error (sprintf(_('File does not exist. File %s is not in incoming FTP directory.'),$filename),'');
	}
	return $filetype;
}

?>
