<?php
/**
 *
 * This file is part of Fusionforge.
 * Copyright 2011, Franck Villaume - Capgemini
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

/**
 * you need to implement only function updateScmRepo($params)
 * $params is an array containing :
 *	$params['group_id'] = $group_id
 *	$params['hooksString'] = list of hooks to be deploy, separator is |
 *	$params['scm_root'] = directory containing the scm repository
 */

/**
 * scmsvn_updateScmRepo - update the scmrepo with the new hooks
 *
 * @params	Array	the complete array description
 * @return	boolean	success or not
 */
function updateScmRepo($params) {
	global $sys_gforge_user;
	global $sys_apache_user;
	global $sys_apache_group;
	$group_id = $params['group_id'];
	$hooksString = $params['hooksString'];
	$svndir_root = $params['scm_root'];
	$group = &group_get_object($group_id);
	$scmhookPlugin = new scmhookPlugin;
	$hooksAvailable = $scmhookPlugin->getAvailableHooks($group_id);
	$scm_box = $group->getSCMBox();
	$unixname = $group->getUnixName();

	$cr = 0;
	// clean-up
	$cr = passthru("ssh $sys_gforge_user@$scm_box \"[ -d $svndir_root/$unixname ]\"", $cr);
	if ($cr == 0) {
		$cr = passthru("ssh $sys_gforge_user@$scm_box \"sudo rm $svndir_root/$unixname/hooks/pre-commit\"", $cr);
		foreach($hooksAvailable as $hookAvailable) {
			$cr = passthru("ssh $sys_gforge_user@$scm_box \"sudo rm $svndir_root/$unixname/hooks/$hookAvailable\"", $cr);
		}
	}
	// deploy new hooks
	$newHooks = explode('|', $hooksString);
	$cr = passthru("ssh $sys_gforge_user@$scm_box \"mkdir -p /tmp/hooks/$unixname\"", $cr);
	if ($cr == 0) {
		foreach($newHooks as $newHook) {
			exec('scp '.dirname(__FILE__).'/../hooks/'.$newHook.' '.$sys_gforge_user.'@'.$scm_box.':/tmp/hooks/'.$unixname.'/');
		}
		$cr = passthru("ssh $sys_gforge_user@$scm_box sudo mv /tmp/hooks/$unixname/* $svndir_root/$unixname/hooks/", $cr);
		$cr = passthru("ssh $sys_gforge_user@$scm_box sudo chown -R apache:apache $svndir_root/$unixname/hooks", $cr);
		foreach($newHooks as $newHook) {
			$cr = passthru("ssh $sys_gforge_user@$scm_box sudo chmod 755 $svndir_root/$unixname/hooks/$newHook", $cr);
		}
	}
	// prepare the pre-commit
	$file = fopen("/tmp/pre-commit-$unixname.tmp", "w");
	fwrite($file, file_get_contents(dirname(__FILE__).'/../skel/pre-commit.head'));
	$loopid = 0;
	$string = '';
	foreach($newHooks as $newHook) {
		if ($loopid) {
			//insert && \ between commands
			$string .= ' && ';
		}
		$string .= rtrim(file_get_contents(dirname(__FILE__).'/../skel/pre-commit.'.$newHook));
		$loopid = 1;
	}
	$string .= "\n";
	fwrite($file,$string);
	fclose($file);
	logger ("INFO", "pre-commit file generated for project $unixname");
	// deploy pre-commit
	exec('scp /tmp/pre-commit-'.$unixname.'.tmp '.$sys_gforge_user.'@'.$scm_box.':/tmp/hooks/'.$unixname.'/pre-commit');
	$cr = passthru("ssh $sys_gforge_user@$scm_box sudo mv /tmp/hooks/$unixname/pre-commit $svndir_root/$unixname/hooks/", $cr);
	$cr = passthru("ssh $sys_gforge_user@$scm_box sudo chown -R apache:apache $svndir_root/$unixname/hooks", $cr);
	$cr = passthru("ssh $sys_gforge_user@$scm_box sudo chmod 755 $svndir_root/$unixname/hooks/pre-commit", $cr);
	// clean the tmp dirs
 	passthru("ssh $sys_gforge_user@$scm_box \"rm -rf /tmp/hooks/$unixname\"");
	exec("rm /tmp/pre-commit-$unixname.tmp");
	return true;
}

?>
