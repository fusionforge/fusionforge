<?php

//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999, 2000, 2001 (c) VA Linux Systems, Inc.
// http://sourceforge.net
//
// $Id$
//

// colorized link to a file with CVS history
// @param threshold age when quicklink is no longer highlighted (default is one week)
// @param path path to file within CVS module
//
//
//
//
//
function util_cvs_query($path, $threshold = 604800, $module = "alexandria", $cvsroot = "/home/cvsroot", $viewcvs = "http://webdev.sourceforge.net/cgi-bin/viewcvs.cgi/") {

	// cvs, the client, is too restrictive to use via PHP
	// instead, read and munge the RCS files directly
	//

	// clean up slashes on inputs: path and cvsroot should only have
	// leading slashes, module should have none
	//
	// TODO

	// fail if path is unset or a directory
	//
	if (($path == '') || (substr($path, -1) == '/')) {

		return 0;

	} // if

	// extract head line and first date line
	//
	$cvspath       = escapeshellcmd($cvsroot . "/" . $module . "/" . $path . ",v");
	$cvstemp       = explode("/", $cvspath); 
	$cvsfile       = $cvstemp[sizeof($cvstemp) - 1];

  $datecmd       = "grep -a ^date $cvspath | head -n1";
	$datestring    = exec($datecmd);

  $versioncmd    = "grep -a ^head $cvspath | head -n1";
	$versionstring = exec($versioncmd);

	// test for error
	//
	if (substr($datestring, 0, 4) != "date") {

		return 0;

	} else {

		// pull out date and format
		//
		$result["date_full"] = substr($datestring, 5, 19);
		$result["date_YYYY"] = substr($datestring, 5, 4);
		$result["date_MM"]   = substr($datestring, 10, 2);
		$result["date_DD"]   = substr($datestring, 13, 2);
		$result["date_HH"]   = substr($datestring, 16, 2);
		$result["date_II"]   = substr($datestring, 19, 2);
		$result["date_SS"]   = substr($datestring, 22, 2);

    $result["date_UNIX"] = mktime($result["date_HH"], 
																	$result["date_II"],
																	$result["date_SS"],
																	$result["date_MM"],  
																	$result["date_DD"],  
																	$result["date_YYYY"]);
    $result["date_RFC"]  = date("Y/m/d H:i:s", $result["date_UNIX"]) . " GMT";

		// pull author name
		//
		eregi(".*author.(.*);.state.*", $datestring, $eregi_result);
		$result["author"]    = $eregi_result[1];

		// pull head version number
		//
		eregi("head.(.*);", $versionstring, $eregi_result);
		$result["version"]   = $eregi_result[1];

		// generate ViewCVS string
		//
		$result["viewcvs"]   = $viewcvs . $module . "/" . $path;

		// build quick-status HTML string
		//
		$result["status"]    = "<font size=\"-2\"><a href=\"" . $result["viewcvs"] . "\">" . $cvsfile . "</a>&nbsp;" . $result["version"] . "&nbsp;" . $result["date_RFC"] . "&nbsp;" . $result["author"] . "</font>";
		if (time() < ($result["date_UNIX"] + $threshold)) {
			$result["status"] = "<b><font color=\"#000000\">" . $result["status"] . "</b></font>";
		} // if

	} // if ... else

	return $result;

} // util_cvs_query

// utility function to call util_cvs_query
//
function util_cvs_status($path) {

	$result = util_cvs_query($path);

	if (is_array($result)) {
		return $result["status"] . "&nbsp;";
	}
	else {
		return "<font size=\"-2\">revision history n/a</font>&nbsp;";
	} // if
} // function util_cvs_status

?>
