#! /usr/bin/php4 -f
<?php

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$use_cvs_acl = false;
$maincvsroot = "/cvsroot/";

//the directory exists
if(is_dir($maincvsroot)) {
	addProjectRepositories();
} else {
	if(is_file($maincvsroot)) {
		$err .= "$maincvsroot exists but is a file\n";
		exit;
	} else {
		if (mkdir($maincvsroot)) {
			//need to update group permissions using chmod
			addProjectRepositories();
		} else {
			$err .= "unable to make $maincvsroot directory\n";
			exit;
		}	
	}
}

function writeFile($filePath, $content) {
	$file = fopen($filePath, 'a');
	flock($file, LOCK_EX);
	ftruncate($file, 0);
	rewind($file);
	if(!empty($content)) {
		fwrite($file, $content);
	}
	flock($file, LOCK_UN);
	fclose($file);
}

/**
*addsyncmail
*Copyright GForge 2004
*addsyncmail write to /CVSROOT/loginfo unix_name-commits@lists.gforge.company.com
*
*@autor Luis A. Hurtado A. luis@gforgegroup.com
*@param $unix_group_name Name Group
*@return void
*@date 2004-10-25
*/
function addsyncmail($unix_group_name) {
	global $sys_lists_host;
	global $maincvsroot;
	$pathsyncmail = $unix_group_name." ".dirname($_SERVER['SCRIPT_FILENAME'])."/syncmail %%s ".$unix_group_name."-commits@".$sys_lists_host;
	writeFile($maincvsroot.'/CVSROOT/loginfo',$pathsyncmail);
}

function addProjectRepositories() {
	global $maincvsroot;
	global $use_cvs_acl;

	$res = db_query("select groups.group_id,groups.unix_group_name,groups.enable_anonscm,groups.enable_pserver 
	FROM groups, plugins, group_plugin
    WHERE groups.status != 'P'
    AND groups.group_id=group_plugin.group_id
    AND group_plugin.plugin_id=plugins.plugin_id
    AND plugins.plugin_name='scmcvs'");
	
	for($i = 0; $i < db_numrows($res); $i++) {
		/*
			Simply call cvscreate.sh
		*/
		$repositoryPath = $maincvsroot.db_result($res,$i,'unix_group_name');
		if (is_dir($repositoryPath)) {
			$writersContent = '';
			$readersContent = '';
			$passwdContent = '';
			if(db_result($res,$i,'enable_anonscm')) {
				$repositoryMode = 02775;
				if (db_result($res,$i,'enable_pserver')) {
					$readersContent = 'anonymous';
					$passwdContent = 'anonymous:8Z8wlZezt48mY';
				}
			} else {
				$repositoryMode = 02770;
			}
			chmod($repositoryPath, $repositoryMode);
			writeFile($repositoryPath.'/CVSROOT/writers', $writersContent);
			writeFile($repositoryPath.'/CVSROOT/readers', $readersContent);
			writeFile($repositoryPath.'/CVSROOT/passwd', $passwdContent);
			addsyncmail(db_result($res,$i,'unix_group_name'));
		} elseif (is_file($repositoryPath)) {
			$err .= $repositoryPath.' already exists as a file';
		} else {
			system('./cvscreate.sh '.db_result($res,$i,'unix_group_name').' '.(db_result($res,$i,'group_id')+50000).' '.db_result($res,$i,'enable_anonscm').' '.db_result($res,$i,'enable_pserver'));
			addsyncmail(db_result($res,$i,'unix_group_name'));

 			if ($use_cvs_acl == true) {
 				system ("cp ".dirname($_SERVER['SCRIPT_FILENAME'])."/aclconfig.default ".$repositoryPath.'/CVSROOT/aclconfig');
 				$res_admins = db_query("SELECT users.user_name FROM users,user_group WHERE users.user_id=user_group.user_id AND user_group.group_id='".db_result($res,$i,'group_id')."'");
 				$useradmin_group = db_result($res_admins,0,'user_name');
 				system("cvs -d ".$repositoryPath." racl ".$useradmin_group.":p -r ALL -d ALL");
 			}
		}
	}
}

cron_entry(13,$err);

?>
