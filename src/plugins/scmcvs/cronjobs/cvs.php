#! /usr/bin/php
<?php
/**
 * GForge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require dirname(__FILE__).'/../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';
require $gfcommon.'include/utils.php';
require_once $gfcommon.'mail/MailingList.class.php';
require_once $gfcommon.'mail/MailingListFactory.class.php';

require_once $gfcommon.'include/SCMPlugin.class.php' ;

$err='';

setup_plugin_manager () ;

//
//	Some OSs do not allow root to do a commit
//	so having this script do a proper checkout/commit is not possible
//
$scmcvs_proper_commit_loginfo=false;

/**
 * Retrieve a file into a temporary directory from a CVS server
 *
 * @param String $repos Repository Name
 * @param String $file File Name
 *
 * return String the FileName in the working repository
 */
function checkout_cvs_file($repos,$file) {
//echo "$repos,$file\n";
	global $scmcvs_proper_commit_loginfo;
	if (!$scmcvs_proper_commit_loginfo) {
		return $repos.'/'.$file;
	}
	$actual_dir = getcwd();
	$tempdirname = tempnam("/tmp","cvstracker");
	if (!$tempdirname)
		return false;
	if (!unlink($tempdirname))
		return false;

	// Create the temporary directory and returns its name.
	if (!mkdir($tempdirname))
		return false;

	if (!chdir($tempdirname))
		return false;
	system("cvs -d ".$repos." co ".$file);

	chdir($actual_dir);
	return $tempdirname."/".$file;
}

/**
 * commit_cvs_file commit a file to the repository
 *
 * @param String $repos Repository
 * @param String $file to commit
 * @param String $message to commit
 */
function commit_cvs_file($repos,$file,$message="Automatic updated by cvstracker") {
	global $scmcvs_proper_commit_loginfo;
	if (!$scmcvs_proper_commit_loginfo) {
		return true;
	}
	$actual_dir = getcwd();
	chdir(dirname($file));
	system("cvs -d ".$repos." ci -m \"".$message."\" ".basename($file));
	// unlink (basename($file));
	chdir($actual_dir);
}

/**
 * release_cvs_file - Remove the file that was checked out from cvs
 * @see checkout_cvs_file
 */
function release_cvs_file($file) {
	global $scmcvs_proper_commit_loginfo;
	if (!$scmcvs_proper_commit_loginfo) {
		return true;
	}
	// $file is something like /tmp/(tmp_dir)/path/to/file
	// we must delete /tmp/tmp_dir
	if (!preg_match("/^(\\/tmp\\/[^\\/]*)\\/.* /", $file, $result)) {		// Make sure the dir is under /tmp
		echo "Trying to release a directory not in /tmp. Skipping...";
		return;
	}
	$dir = $result[1];

	// this shouldn't happen... but add it as a security checke
	if (util_is_root_dir($dir)) {
		echo "Trying to delete root dir. Skipping...";
		return;
	}
	$dir = escapeshellarg($dir);
	system("rm -rf ".$dir);
}

function cvs_write_file($filePath, $content, $append=1) {
	if ($append) {
		$file = fopen($filePath, 'a');
	} else {
		$file = fopen($filePath, 'w');
	}
	flock($file, LOCK_EX);
	if(!empty($content)) {
		fwrite($file, $content);
	}
	flock($file, LOCK_UN);
	fclose($file);
}

/**
 *add_sync_mail write to /CVSROOT/loginfo unix_name-commits@lists.gforge.company.com
 *
 *@param $unix_group_name Name Group
 *@return void
 *@date 2004-10-25
 */
function add_sync_mail($unix_group_name) {

	global  $cvsdir_prefix ;
	$cvs_binary_version = get_cvs_binary_version () ;
	$loginfo_file=$cvsdir_prefix.'/'.$unix_group_name.'/CVSROOT/loginfo';

	if (!$loginfo_file) {
		echo "Couldn't get loginfo for $unix_group_name";
		return;
	}

	$content = file_get_contents ($loginfo_file);
	if ( strstr($content, "syncmail") == FALSE) {
//		echo $unix_group_name.":Syncmail not found in loginfo.Adding\n";
		if ( $cvs_binary_version == "1.11" ) {
			$pathsyncmail = "DEFAULT ".
				forge_get_config('plugins_path')."/cvssyncmail/bin/syncmail -u %{sVv} ".
				$unix_group_name."-commits@".forge_get_config('lists_host');
		} else { //it's 1.12
			$pathsyncmail = "DEFAULT ".
				forge_get_config('plugins_path')."/cvssyncmail/bin/syncmail -u %p %{sVv} ".
				$unix_group_name."-commits@".forge_get_config('lists_host');
		}
		$content = "\n#BEGIN Added by cvs.php script\n".
			$pathsyncmail. "\n#END Added by cvs.php script\n";
		$loginfo_file = checkout_cvs_file($cvsdir_prefix.'/'.$unix_group_name,'CVSROOT/loginfo');
		if(is_file($loginfo_file)){
			//echo $unix_group_name.":About to write the lines\n";
			cvs_write_file($loginfo_file, $content, 1);
		}
		commit_cvs_file($cvsdir_prefix."/".$unix_group_name,$loginfo_file);
		release_cvs_file($loginfo_file);
	} else {
//		echo "Syncmail Found!\n";
	}
}

/**
 * Function to add cvstracker lines to a loginfo file
 * @param   string  the unix_group_name
 *
 */
function add_cvstracker($unix_group_name) {
	global $cvsdir_prefix;
	$cvs_binary_version = get_cvs_binary_version () ;
	$loginfo_file=$cvsdir_prefix.'/'.$unix_group_name.'/CVSROOT/loginfo';

	if (!$loginfo_file) {
		echo "Couldn't get loginfo for $unix_group_name";
		return;
	}

	$content = file_get_contents ($loginfo_file);
	if ( strstr($content, "cvstracker") == FALSE) {
		$content = "\n# BEGIN added by gforge-plugin-cvstracker";
		if ( $cvs_binary_version == "1.11" ) {
			$content .= "\nALL ( php -q -d include_path=".ini_get('include_path').
				" ".forge_get_config('plugins_path')."/cvstracker/bin/post.php
 ".$unix_group_name." %{sVv} )";
		} else { //it's version 1.12
			$content .= "\nALL ( php -q -d include_path=".ini_get('include_path').
				" ".forge_get_config('plugins_path')."/cvstracker/bin/post.php
 %r %p %{sVv} )";
		}
		$content .= "\n# END added by gforge-plugin-cvstracker";

		$loginfo_file = checkout_cvs_file($cvsdir_prefix.'/'.$unix_group_name,'CVSROOT/loginfo');
		if(is_file($loginfo_file)){
			//echo $unix_group_name.":About to write the lines\n";
			cvs_write_file($loginfo_file, $content, 1);
		}
		commit_cvs_file($cvsdir_prefix."/".$unix_group_name,$loginfo_file);
		release_cvs_file($loginfo_file);
	} else {
//		echo "cvstracker Found!\n";
	}

	// now make sure that if cvs version is 1.12, "UseNewInfoFmtStrings=yes" line
	// MUST be present in CVSROOT/config, or else cvstracker won't work
	if ($cvs_binary_version == "1.12") {
		$config_file = $loginfo_file=$cvsdir_prefix.'/'.$unix_group_name.'/CVSROOT/config';
		if (!is_file($config_file)) {
			echo "Couldn't get CVSROOT/config for $unix_group_name";
			return;
		}

		$content = file_get_contents($config_file);
		if (!preg_match("/UseNewInfoFmtStrings=yes/i", $content)) {
			// file must be modified
			$config_file = checkout_cvs_file($cvsdir_prefix.'/'.$unix_group_name,'CVSROOT/config');
			if (is_file($config_file)) {
				$lines = file($config_file);
				$newlines = array();
				foreach ($lines as $line) {
					if (!preg_match("/UseNewInfoFmtStrings/i", $line)) {		// maybe it was set to "no"?
						$newlines[] = trim($line);
					}
				}
				$newlines[] = "UseNewInfoFmtStrings=yes";	// add the required line at the end
			}
			$content = implode("\n", $newlines);
			cvs_write_file($config_file, $content, 0);
			commit_cvs_file($cvsdir_prefix."/".$unix_group_name, $config_file);
			release_cvs_file($config_file);
		}
	}
}

function add_acl_check($unix_group_name) {
	global $cvsdir_prefix;
	$cvs_binary_version = get_cvs_binary_version () ;

	$commitinfofile = $cvsdir_prefix."/".$unix_group_name.'/CVSROOT/commitinfo';

	$content = file_get_contents ($commitinfofile);
	if ( strstr($content, "aclcheck") == FALSE) {

		$commitinfofile = checkout_cvs_file($cvsdir_prefix.'/'.$unix_group_name,'CVSROOT/commitinfo');
		if ( $cvs_binary_version == "1.11" ) {
			$aclcheck = "\n#BEGIN adding cvs acl check".
				"\nALL php -q -d include_path=".ini_get('include_path').
					" ".forge_get_config('plugins_path')."/scmcvs/bin/aclcheck.php ".$cvsdir_prefix."/".$unix_group_name.
				"\n#END adding cvs acl check\n";
		} else { //it's version 1.12
			$aclcheck = "\n#BEGIN adding cvs acl check".
				"\nALL php -q -d include_path=".ini_get('include_path').
					" ".forge_get_config('plugins_path')."/scmcvs/bin/aclcheck.php %r %p ".
				"\n#END adding cvs acl check\n";
		}



		cvs_write_file($commitinfofile, $aclcheck, 1);
		commit_cvs_file($cvsdir_prefix."/".$unix_group_name,$commitinfofile);
		release_cvs_file($commitinfofile);
	} else {
//		echo "cvstracker Found!\n";
	}
}

function update_cvs_repositories() {
	global $cvsdir_prefix;
	global $err;

	$res = db_query_params ('select groups.group_id,groups.unix_group_name,groups.enable_anonscm,groups.enable_pserver
 FROM groups, plugins, group_plugin
 WHERE groups.status != $1
 AND groups.group_id=group_plugin.group_id
 AND group_plugin.plugin_id=plugins.plugin_id
 AND plugins.plugin_name=$2',
			array('P',
				'scmcvs'));

	//
        // Move CVS trees for deleted groups
        //
        $res8 = db_query_params ('SELECT unix_group_name FROM deleted_groups WHERE isdeleted = 0;',
			array());
        $err .= db_error();
        $rows    = db_numrows($res8);
        for($k = 0; $k < $rows; $k++) {
                $deleted_group_name = db_result($res8,$k,'unix_group_name');

                if(!is_dir($cvsdir_prefix."/.deleted"))
                        system("mkdir ".$cvsdir_prefix."/.deleted");

                system("mv -f $cvsdir_prefix/$deleted_group_name/ $cvsdir_prefix/.deleted/");
                system("chown -R root:root $cvsdir_prefix/.deleted/$deleted_group_name");
                system("chmod -R o-rwx $cvsdir_prefix/.deleted/$deleted_group_name");


                $res9 = db_query_params ('UPDATE deleted_groups set isdeleted = 1 WHERE unix_group_name = $1',
			array ($deleted_group_name));
                $err .= db_error();
        }

}



/*


	Loop through and create/update each repository for every project
	that uses SCMCVS plugin


*/
$err = "";

if(is_dir($cvsdir_prefix)) {
	update_cvs_repositories();
} else {
	if(is_file($cvsdir_prefix)) {
		$err .= "$cvsdir_prefix exists but is a file\n";
		exit;
	} else {
		if (mkdir($cvsdir_prefix)) {
			//need to update group permissions using chmod
			update_cvs_repositories();
		} else {
			$err .= "unable to make $cvsdir_prefix directory\n";
			exit;
		}
	}
}


cron_entry(13,$err);

?>
