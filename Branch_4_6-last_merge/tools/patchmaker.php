<?php

/*

	Patch maker for Gforge.
	by Daniel Pérez 2005

*/

/**** START PROGRAM ****/

$STDIN = fopen('php://stdin','r');
define ("RED", "\033[01;31m" );
define ("NORMAL", "\033[00m" );
define ("GREEN", "\033[01;32m" );
define ("BLUE", "\033[00;36m" );
define ("BLINK", "\033[05m" );
define ("YELL", "\033[01;33m" );
define('FILE_APPEND', 1);

/* CONFIG VARS */

$cvspath = "/cvsroot/gforge";
$cvsmodule = "gforge"; //the name of the module from cvs repository
$authentication = "pserver"; //ext for it to ask you for your password
$cvsuser = "anonymous";
$cvsserver = "cvs.gforge.org";
$cvsroot = ":" . $authentication . ":" . $cvsuser . "@" . $cvsserver . ":" . $cvspath;
//this is the branch config for GFORGE (not the plugins)
$old_branch = "2005-11-08";
$old_branch_is_date = true; // if the old_branch is a date instead of a branch or tag, the old_branch should be something like 2005-07-10
$new_branch = "HEAD";
$new_branch_is_date = false;

$only_parse_diffs = false; // leave false to generate the diff first
$only_test_patch = false; // only tests applyting the patch. the diffs must be generated and parsed already
$debug_patch_output = true;

$plugins = array();

$plugins[0]['name'] = "scmcvs";
$plugins[0]['old_branch'] = "upstream_version_4_5_2";
$plugins[0]['new_branch'] = "HEAD";
$plugins[0]['cvsmodule'] = "gforge-plugin-scmcvs";
$plugins[0]['new_branch_is_date'] = false;
$plugins[0]['old_branch_is_date'] = false;

$plugins[1]['name'] = "scmsvn";
$plugins[1]['old_branch'] = "2005-08-08";
$plugins[1]['new_branch'] = "HEAD";
$plugins[1]['cvsmodule'] = "gforge-plugin-scmsvn";
$plugins[1]['new_branch_is_date'] = false;
$plugins[1]['old_branch_is_date'] = true;

$plugins[2]['name'] = "cvstracker";
$plugins[2]['old_branch'] = "upstream_version_4_5_2";
$plugins[2]['new_branch'] = "v4_5_3";
$plugins[2]['cvsmodule'] = "gforge-plugin-cvstracker";
$plugins[2]['new_branch_is_date'] = false;
$plugins[2]['old_branch_is_date'] = false;

// The next one is an example of a plugin that requires different cvspath and must be checked out and worked with ext auth
/*
$plugins[3]['name'] = "svntracker";
$plugins[3]['old_branch'] = "Branch_4_5";
$plugins[3]['new_branch'] = "HEAD";
$plugins[3]['cvsmodule'] = "gforge-plugin-svntracker";
$plugins[3]['new_branch_is_date'] = false;
$plugins[3]['old_branch_is_date'] = false;
$plugins[3]['cvspath'] =  "/cvsroot/scmplugins"; // only set this var if it´s different than the main one
$plugins[3]['authentication'] =  "ext"; // only set this var if it´s different than the main one
$plugins[3]['cvsuser'] =  "danper"; // only set this var if it´s different than the main one
*/

/* END OF CONFIG VARS */

	// php.net function workaround because this is only on Php5
	function file_put_contents($filename, $data, $flags = 0, $f = FALSE) {
		if(($f===FALSE) && (($flags%2)==1)) $f=fopen($filename, 'a'); else if($f===FALSE) $f=fopen($filename, 'w');
		if(round($flags/2)==1) while(!flock($f, LOCK_EX)) { /* lock */ }
		if(is_array($data)) $data=implode('', $data);
		fwrite($f, $data);
		if(round($flags/2)==1) flock($f, LOCK_UN);
		fclose($f);
	}


	/**
	* searchNextIndex -> Searchs for the next "Index:" line
	*
	*	@param	&array The file lines array
	*	@param  int position in the array where this index is found
	*	@param  int position where to begin searching from
	*	@param  boolean	Whether it´s a plugin diff or the main gforge plugin
	*	@param  string	The module name
	*	@param	boolean	true on success, false on EOF
	*
	*/
	function searchNextIndex(&$lines,&$position,$beginfrom,$isplugin,$module) {
		$i = $beginfrom;
		while ( ($i <count($lines)) ) {
			if (preg_match('/^Index:/',$lines[$i])) {
				if ($isplugin) {
					//if it´s a plugin, add the plugin relative path to the file path
					$lines[$i] = "Index: " . "plugins/" . $module . "/" . substr($lines[$i],7);
				}
				$position = $i;
				return true;
			}
			$i++;
		}
		return false;
	}

	/**
	* searchFirstModification -> Searchs for the next "Index:" line
	*
	*	@param	&array The file lines array
	*	@param  int position in the array where the first "@@" line is found
	*	@param  int position where to begin searching from
	*	@param  boolean	Whether it´s a plugin diff or the main gforge plugin
	*	@param  string	The module name
	*	@param  boolean whether EOF was found instead of "@@" or "Index:" lines
	*	@param	boolean	true on success
	*
	*/	
	
	function searchFirstModification(&$lines,&$firstmodposition,$beginfrom,$isplugin,$module,&$foundEOF) {
		$i = $beginfrom;
		$foundEOF = false;
		while ( ( $i < count($lines) ) ) {
			
			if ($isplugin) {
				//if it´s a plugin, add the plugin relative path to the file path
				if (preg_match('/^--- /',$lines[$i])) {
					$lines[$i] = "--- " . "plugins/" . $module . "/" . substr($lines[$i],4);
				}
				if (preg_match('/^\+\+\+ /',$lines[$i])) {
					$lines[$i] = "+++ " . "plugins/" . $module . "/" . substr($lines[$i],4);
				}				
			}
			
			if (preg_match('/^@@/',$lines[$i])) {
				$firstmodposition = $i;
				return true;
			}
			$i++;
		}
		$foundEOF = true;
		return true;
	}

	/**
	* searchSecondModification -> Searchs for the next "Index:" line
	*
	*	@param	&array The file lines array
	*	@param  int position in the array where the second "@@" line is found (if it is)
	*	@param  int position in the array where the first "@@" line was found
	*	@param  boolean whether a new index was found instead of a second "@@" line
	*	@param 	if $foundnewindex is true, this indicates the position where this new index has been found
	*	@param  boolean whether EOF was found instead of "@@" or "Index:" lines
	*	@param  boolean	whether a "version" line was found in the first "@@" section
	*	@param  int position where to begin searching from
	*	@param  boolean	Whether it´s a plugin diff or the main gforge plugin
	*	@param  string	The module name
	*	@param	boolean	true on success
	*
	*/		
	function searchSecondModification(&$lines,&$secondmodposition,$firstmodposition,&$foundnewindex,&$new_indexposition,&$foundEOF,&$foundversion,$isplugin,$module) {
		$i = $firstmodposition + 1;
		$foundversion = false;
		$foundEOF = false;
		$foundnewindex = false;
		while ( ($i <count($lines)) ) {
			if (preg_match('/^@@/',$lines[$i])) {
				$secondmodposition = $i;
				return true;
			}
			$j = $i+1; //this doesn´t work... ->  $lines[($i+1)]
			if ( (preg_match('/@version[\s|\t]+\$Id$lines[$i])) && (preg_match('/@version[\s|\t]+\$Id$lines[$j])) ) { // only if there´s a - version and a + version (changed version)
				$foundversion = true;
				echo "." ;
			}
			if (preg_match('/^Index:/',$lines[$i])) {
				$new_indexposition = $i;
				$foundnewindex = true;
				if ($isplugin) {
					//if it´s a plugin, add the plugin relative path to the file path
					$lines[$i] = "Index: " . "plugins/" . $module . "/" . substr($lines[$i],7);
				}				
				return true;
			}
			$i++;
		}
		$foundEOF = true;
		return true;
	}
	
	/**
	* parseDiffs -> read the files in the current dir and parse the .diff files
	*
	*/	
	
	function parseDiffs() {
		$current=getcwd();
		//open current directory for reading
		$handle=opendir(".");
		while ($filename = readdir($handle)) {
			//Don't add special directories '..' or '.' to the list
			if (($filename!='..') && ($filename!='.') && (strstr($filename,".diff") && (filesize($filename)>0))) { // only get the diff files
				if (!strstr($filename,"gforge")) {
					$isplugin = true;
				} else {
					$isplugin = false;
				}
				echo "Parsing file $filename ...\n";
				parseDiff($filename,$isplugin);
			}
		}
		closedir($handle);		
	}

	/**
	* testPatch -> simulates a patching process
	*
	*	@param array the plugins info
	*	@param boolean	Output the patching process?
	*
	*	@return boolean	true on success
	*/
	
	function testPatch($plugins,$debug) {
		global $old_branch_is_date,$old_branch,$cvsmodule;
		
		echo YELL . "Performing test of the created patch. Proceeding to update CVS to old versions...\n" . NORMAL;
		//now we change the plugins to update to the old branch instead.
		for ($i=0;$i<count($plugins);$i++) {
			$plugins[$i]['new_branch'] = $plugins[$i]['old_branch'];
			$plugins[$i]['new_branch_is_date'] = $plugins[$i]['old_branch_is_date'];
		}
		updateAll($old_branch,$old_branch_is_date,$plugins,1);
		exec("patch --dry-run -p0 < ../GFORGEPATCH",$output,$return_value);
		if ($debug) {
			foreach ($output as $out) {
				echo BLUE . $out  . "\n" . NORMAL;
			}
		}
		if ($return_value!=0) {
			return false;
		} else {
			return true;
		}
		
	}
	
	/**
	* joinDiffs -> just grabs the parsed diffs and joins them into 1 big file
	*
	*/	
	
	function joinDiffs() {
		$current=getcwd();
		//open current directory for reading
		$handle=opendir(".");
		echo "Creating joined file GFORGEPATCH ...\n";
		while ($filename = readdir($handle)) {
			//Don't add special directories '..' or '.' to the list
			if (($filename!='..') && ($filename!='.') && (strstr($filename,".diff") && (strstr($filename,"parsed-")) && (filesize($filename)>0))) { // only get the diff files
				file_put_contents("GFORGEPATCH",file_get_contents($filename),FILE_APPEND);
			}
		}
		closedir($handle);
		if (is_file("GFORGEPATCH")) {
			echo GREEN . "File GFORGEPATCH created...\n" . NORMAL;
		} else {
			echo RED . "File GFORGEPATCH couldn´t be created successfully\n" . NORMAL;
			exit();
		}
	}
	
	/**
	* parseDiff -> update the plugins from cvs
	*
	*	@param	string	The file name
	*	@param  boolean	Whether it´s a plugin diff or the main gforge plugin
	*	@param	boolean	true on success, false on failure
	*
	*/
	function parseDiff($filename,$isplugin) {
		if (! ($vals = file_get_contents($filename)) ){
			echo RED . "Failed to open file $filename \n" . NORMAL;
			return false;
		}
		$lines = explode("\n",$vals); //now we have the lines in an array
		for ($i=0;$i<count($lines);$i++) {
			$linestowrite[$i] = 1; // write ALL lines first...
		}
		
		$arr = explode(".diff",$filename);
		$module = $arr[0]; // get the module name (in case it´s a plugin)

		$exit = false;
		$beginfrom = 0;
		// we are going to suppose the files are WELL FORMED (not index without @@ lines, has at least 1 Index, etc)
		searchNextIndex($lines,$indexposition,$beginfrom,$isplugin,$module);
		if ($indexposition>0) {
			//remove the " ? filename" lines
			for ($i=0;$i<$indexposition;$i++) {
				$linestowrite[$i] = 0;
			}
		}
		do  {
			searchFirstModification($lines,$firstmodposition,$indexposition,$isplugin,$module,$foundEOF); //search for first @@ line
			//weird condition -> i found one diff that had an index and no @@... the file hadn´t been modified. the program crashed. this fixes it
			if ($foundEOF) {
				//ignore this last index and finish
				for ($i=$indexposition;$i<count($lines);$i++) {
					//mark this section as allowed
					$linestowrite[$i] = 0;
				}
				break;
			}
			//echo $firstmodposition . " ";
			searchSecondModification($lines,$secondmodposition,$firstmodposition,$foundnewindex,$new_indexposition,$foundEOF,$foundversion,$isplugin,$module); //search for next @@ line (maybe we just find the next index or EOF)
			//echo $new_indexposition . " ";
			$foundversion?$value=0:$value=1; // if we found the "version" CVS stuff change we set it at not to be written
			if ($foundnewindex) {
				for ($i=$indexposition;$i<$firstmodposition;$i++) {
					//mark this section as allowed
					$linestowrite[$i] = $value;
				}
				$indexposition = $new_indexposition;
				for ($i=$firstmodposition;$i<$indexposition;$i++) {
					//mark this section as allowed
					$linestowrite[$i] = $value;
				}
			} else { //didn´t find new index
				if ($foundEOF) { //we have reached EOF, write all from secondmodposition to the end
					for ($i=$firstmodposition;$i<count($lines);$i++) {
						//mark this section as allowed
						$linestowrite[$i] = $value;
					}
					$exit = true;
				} else { //found next set of @@
					for ($i=$indexposition;$i<$firstmodposition;$i++) {
						//mark this section as allowed
						$linestowrite[$i] = 1;
					}
					for ($i=$firstmodposition;$i<$secondmodposition;$i++) {
						//mark this section as forbidden or allowed
						$linestowrite[$i] = $value;
					}
					$found = searchNextIndex($lines,$indexposition,$secondmodposition,$isplugin,$module); //search for the next index
					if (!$found) {
						//we have reached EOF, write all from secondmodposition to the end
						for ($i=$secondmodposition;$i<count($lines);$i++) {
							//mark this section as allowed
							$linestowrite[$i] = 1;
						}
						$exit = true;
					} else {
						//we have reached the next "Index:" line, write all from secondmodposition to this point
						for ($i=$secondmodposition;$i<$indexposition;$i++) {
							//mark this section as allowed
							$linestowrite[$i] = 1;
						}
					}
				}
			}
			
		} while (!$exit);
		
		// write to file
		$fd = fopen("parsed-" . $filename,"w+");
		if (! $fd ){
			echo RED . "Failed to open file parsed-$filename \n" . NORMAL;
			return false;
		}
		
		for ($i=0;$i<count($lines);$i++) {
			if ($linestowrite[$i] == 1) {
				fwrite($fd,$lines[$i] . "\n");
			}
		}
		fclose($fd);
		echo "\n";
		echo GREEN. "File parsed-" . $filename . " wrote successfully\n" . NORMAL;
	}

	/**
	* updateAll -> updates gforge and the plugins from cvs
	*
	*	@param  string	Branch to update to
	*	@param  boolean	Whether the branch is a date
	*	@param  array of plugins
	*	@param  boolean	is this a test?
	*	@return	boolean	true on success, exits on failure
	*
	*/
	function updateAll($branch,$branch_is_date,$plugins,$test) {
		global $cvsmodule,$cvsroot;
		
		echo "Updating to " . $branch . " branch...\n";
		
		chdir ($cvsmodule);
		
		if ($branch_is_date) {
			exec("cvs -Q -d " . $cvsroot . " update -dP -D $branch   2>>/tmp/patchmaker-errorlog",$out,$return_value); // returns 0 on success... that´s why the return_value var	
		} else {
			exec("cvs -Q -d " . $cvsroot . " update -dP -r $branch   2>>/tmp/patchmaker-errorlog",$out,$return_value); // returns 0 on success... that´s why the return_value var
		}
		
		if ($return_value != 0) {
			die(RED . "Could not update from cvs, exiting...\n" . NORMAL);
		}
		
		echo GREEN . "Module " . $cvsmodule . " updated successfully.\n" . NORMAL;
		
		if (!empty($plugins)) {
			if (!is_dir('plugins')) {
				if (!mkdir('plugins')) {
					die(RED . "Could not create dir plugins, exiting...\n" . NORMAL);
				}
				echo "Dir plugins Created.\n";
			} else {
				echo "Dir plugins already exists.\n";
			}
			
			if (!updatePlugins($plugins,!$test)) {
				exit();
			}
		}	
		return true;	
	}
	
	/**
	* updatePlugins -> update the plugins from cvs
	*
	*	@param	array	Plugins that will be updated
	*	@param  boolean	Whether to create the diffs or just update. True by default
	*	@return	boolean	true on success, false on failure
	*
	*/
	function updatePlugins($plugins,$creatediffs=true) {
		global $authentication,$cvsuser,$cvsserver,$cvspath;
		
		chdir("plugins");
		foreach ($plugins as $plugin) {
			//if the plugin has different cvspath use it, else use the original one
			($plugin['cvspath'])?$path=$plugin['cvspath']:$path=$cvspath; 
			($plugin['authentication'])?$auth=$plugin['authentication']:$auth=$authentication;
			($plugin['cvsuser'])?$user=$plugin['cvsuser']:$user=$cvsuser;
			$cvsroot = ":" . $auth . ":" . $user . "@" . $cvsserver . ":" . $path;		
			
			if (!(is_dir($plugin['name']))) {
				//only checkout if there´s nothing in here
				echo "Checking out " . $plugin['cvsmodule'] . "...\n";
				exec("cvs -Q -d " . $cvsroot . " checkout " . $plugin['cvsmodule'] . "    2>>/tmp/patchmaker-errorlog",$out,$return_value);
				if ($return_value != 0) {
					die(RED . "Could not checkout module " . $plugin['cvsmodule'] . " from cvs, exiting...\n" . NORMAL);
				}
				echo GREEN . "Module " . $plugin['cvsmodule'] . " checked out successfully.\n" . NORMAL;
				//rename the dir
				if (exec("mv " . $plugin['cvsmodule'] . " " . $plugin['name'])) { //mv returns 0 on succes...
					die(RED . "Could not rename the dir " . $plugin['cvsmodule'] . ", exiting...\n");
				}
				echo "Dir " . $plugin['cvsmodule'] . " renamed to " . $plugin['name'] . "\n";
			} else {
				echo BLUE . "Module " . $plugin['cvsmodule'] . " has already been checked out before.\n" . NORMAL;
			}
			echo "Updating " . $plugin['name'] . " to " . $plugin['new_branch'] . " branch...\n";
			chdir ($plugin['name']);
			if ($plugin['new_branch_is_date']) {
				exec("cvs -Q -d " . $cvsroot . " update -dP -D " . $plugin['new_branch'] . " 2>>/tmp/patchmaker-errorlog",$out,$return_value); // returns 0 on success... that´s why the return_value var	
			} else {
				exec("cvs -Q -d " . $cvsroot . " update -dP -r " . $plugin['new_branch'] . " 2>>/tmp/patchmaker-errorlog",$out,$return_value); // returns 0 on success... that´s why the return_value var
			}
			if ($return_value != 0) {
				die("Could not update" . $plugin['name'] . "from cvs, exiting...\n");
			}
			echo GREEN . "Module " . $plugin['name'] . " updated successfully.\n" . NORMAL;
			if ($creatediffs) {
				echo "Creating the diff for module " . $plugin['name'] . "...\n";
				if ($plugin['new_branch_is_date']) {
					if ($plugin['old_branch_is_date']) {
						exec("cvs -Q -d " . $cvsroot . " diff -BbuN -D " . $plugin['old_branch'] . " -D " . $plugin['new_branch'] . " > ../../../" . $plugin['name'] . ".diff    2>>/tmp/patchmaker-errorlog");
					} else {
						exec("cvs -Q -d " . $cvsroot . " diff -BbuNr " . $plugin['old_branch'] . " -D " . $plugin['new_branch'] . " > ../../../" . $plugin['name'] . ".diff    2>>/tmp/patchmaker-errorlog");
					}
				} else {
					if ($plugin['old_branch_is_date']) {
						exec("cvs -Q -d " . $cvsroot . " diff -BbuN -D " . $plugin['old_branch'] . " -r " . $plugin['new_branch'] . " > ../../../" . $plugin['name'] . ".diff    2>>/tmp/patchmaker-errorlog");
					} else {
						exec("cvs -Q -d " . $cvsroot . " diff -BbuNr " . $plugin['old_branch'] . " -r " . $plugin['new_branch'] . " > ../../../" . $plugin['name'] . ".diff    2>>/tmp/patchmaker-errorlog");
					}
				}
				if ((!(file_exists("../../../".$plugin['name'].".diff"))) || ((filesize("../../../".$plugin['name'].".diff")) < 1)  ){
					if ((filesize("../../../".$plugin['name'].".diff")) < 1) {
						echo BLUE . "The diff for " . $plugin['name'] . " is empty...  ---> " . $plugin['name'] . ".diff" . "\n" . NORMAL;
						unlink("../../../" . $plugin['name'] . ".diff");
					} else {
						die("Could not create the diff, exiting...\n");
					}
				} else {
					echo GREEN . "Diff created successfully ---> " . $plugin['name'] . ".diff" . ".\n" . NORMAL;
				}
			}			
			chdir (".."); // return to plugins dir
		}
		chdir (".."); //return to main module dir
		return true;
	}

/* MAIN */	

if ($only_parse_diffs) {
	chdir($new_branch);
	parseDiffs();
	joinDiffs();
	exit();
}

if ($only_test_patch) {
	chdir($new_branch);
	if (testPatch($plugins,$debug_patch_output)) {
		echo GREEN . "GFORGEPATCH file applied successfully, you can distribute the file\n" . NORMAL ;
	} else {
		echo RED . "GFORGEPATCH file couldn´t be applied correctly, please check the errors and correct manually\n" . NORMAL ;
	}
	exit();
}

// get the cvs version of new branch

echo "Creating Dir " . $new_branch . " ...\n";

$makedir = true;
if (is_dir(getcwd() . "/" . $new_branch)) {
	echo BLUE . "Dir " . $new_branch . " already exists.\n" . NORMAL;
	echo BLUE . "The files are going to be changed. Continue? (Y/N) :" . NORMAL;
	$sure = fread($STDIN,1);
	if ( ($sure!='y') && ($sure!='Y') ) {
		die(RED . "Exiting...\n");
	}
	fclose($STDIN);
	$makedir = false;
}

if ($makedir) {
	if (!mkdir(getcwd() . "/" . $new_branch)) {
		die(RED . "Could not create dir $new_branch, exiting...\n");
	}
	echo "Dir " . $new_branch . " Created.\n";
}

chdir($new_branch);

if (!(is_dir($cvsmodule))) {
	//only checkout if there´s nothing in here
	echo "Checking out " . $cvsmodule . "...\n";
	exec("cvs -Q -d " . $cvsroot . " checkout $cvsmodule    2>>/tmp/patchmaker-errorlog",$out,$return_value);
	if ($return_value != 0) {
		die("Could not checkout from cvs, exiting...\n");
	}
	echo GREEN . "Module " . $cvsmodule . " checked out successfully.\n" . NORMAL;
} else {
	echo BLUE . "Module " . $cvsmodule . " has already been checked out before.\n" . NORMAL;
}

updateAll($new_branch,$new_branch_is_date,$plugins,0);

echo "Creating the diff for module " . $cvsmodule . "...\n";

if ($new_branch_is_date) {
	if ($old_branch_is_date) {
		exec("cvs -Q -d " . $cvsroot . " diff -BbuN -D $old_branch -D $new_branch > ../$cvsmodule.diff");
	} else {
		exec("cvs -Q -d " . $cvsroot . " diff -BbuNr $old_branch -D $new_branch > ../$cvsmodule.diff");
	}
} else {
	if ($old_branch_is_date) {
		exec("cvs -Q -d " . $cvsroot . " diff -BbuN -D $old_branch -r $new_branch > ../$cvsmodule.diff");
	} else {
		exec("cvs -Q -d " . $cvsroot . " diff -BbuNr $old_branch -r $new_branch > ../$cvsmodule.diff");
	}
}

chdir ("..");
if ((!(file_exists("$cvsmodule.diff"))) || ((filesize("$cvsmodule.diff")) < 1)  ){
	if ((filesize("$cvsmodule.diff")) < 1) {
		echo BLUE . "The diff for " . $cvsmodule . " is empty...in " . getcwd() . $cvsmodule . ".diff" . ".\n" . NORMAL;
	} else {
		die(RED . "Could not create the diff, exiting...\n" . NORMAL);
	}
} else {
	echo GREEN . "Diff created successfully ---> " . $cvsmodule . ".diff" . ".\n" . NORMAL;
}

parseDiffs();
joinDiffs();
if (testPatch($plugins,$debug_patch_output)) {
	echo GREEN . "GFORGEPATCH file applied successfully, you can distribute the file\n" . NORMAL ;
} else {
	echo RED . "GFORGEPATCH file couldn´t be applied correctly, please check the errors and correct manually\n" . NORMAL ;
}

/* END MAIN */

/**** END PROGRAM ****/

?>