<?php
/**
 * Cache functions library.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: cache.php,v 1.49 2001/06/08 14:56:43 dbrogdon Exp $
 */

/**
 * cache_display() - Cache the output of a function
 * 
 * Caches the output of a function for the duration of $time.
 *
 * @param		string	The cache name
 * @param		string	The funcion who's output is to be cached
 * @param		int		The lenght of time the output should be cached
 */
function cache_display($name,$function,$time) {
	global $Language;
	$filename = $GLOBALS['sf_cache_dir']."/sfcache_". $Language->getLanguageId() ."_". $GLOBALS['sys_theme'] ."_$name.sf";

	while ((!file_exists($filename))
	       || (filesize($filename)<=1)
	       || ((time() - filectime($filename)) > $time)) {
		// file is non-existant or expired, must redo, or wait for someone else to

		if (!file_exists($filename)) {
			@touch($filename);
		}

		// open file. If this does not work, wait one second and try cycle again
		if ($rfh=@fopen($filename,'r')) {
			// obtain a blocking write lock, else wait 1 second and try again
			if(flock($rfh,2)) { 
				// open file for writing. if this does not work, something is broken.
				if (!$wfh = @fopen($filename,'w')) {
					return "Unable to open cache file for writing after obtaining lock.";
				}
				// have successful locks and opens now
				$return=cache_get_new_data($function);
				fwrite($wfh,$return); //write the file
				fclose($wfh); //close the file
				flock($rfh,3); //release lock
				fclose($rfh); //close the lock
				return $return;
			} else { // unable to obtain flock
				sleep(1);
				clearstatcache();
			}
		} else { // unable to open for reading
			sleep(1);
			clearstatcache();
		}
	} 
		
	// file is now good, use it for return value
	if (!$rfh = fopen($filename,'r')) { //bad filename
		return cache_get_new_data($function);
	}
	while(!flock($rfh,1+4) && ($counter < 30)) { // obtained non blocking shared lock 
		usleep(500000); // wait 0.5 seconds for the lock to become available
		$counter++;
	}
	$result=stripslashes(fread($rfh,200000));
	flock($rfh,3); // cancel read lock
	fclose($rfh);
	return $result;
}

/**
 * cache_get_new_data() - Get new output for a function
 *
 * @param		string	The name of the function who's output is to be updated
 */
function cache_get_new_data($function) {
	global $Language;
	// Here should be localhost! It is chacked in write_cache.php .
	//$furl=fopen("http://localhost/write_cache.php?sys_themeid=".$GLOBALS['sys_themeid']."&lang=".$Language->getLanguageId()."&function=".urlencode($function),'r');
	//return stripslashes(fread($furl,200000));
	eval("\$res= $function;");
	return $res;
}
?>
