<?php
/**
 * scmhook commitEmail Plugin Class
 * Copyright 2012, Denise Patzker <denise.patzker@tu-dresden.de>
 * Copyright 2012, Franck Villaume - TrivialDev
 *
 * This class provides hook to activate/deactivate Mercurials e-mail 
 * notification per repository.
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

global $gfplugins;
require_once $gfplugins.'scmhook/common/scmhook.class.php';

class commitEmail extends scmhook {
	var $group;
	var $disabledMessage;

	function __construct() {
		$this->group = $GLOBALS['group'];
		$this->name = "Commit Email";
		$this->description = _('Every commit pushed sends a notification e-mail to the users of the commit-mailinglist.
The hook is triggered after \'serve push pull bundle\' on the projects repository.');
		$this->classname = "commitEmail";
		$this->label = "scmhg";
		$this->hooktype = "serve-push-pull-bundle";
		$this->unixname = "commitemail";
		$this->needcopy = 0;
		$this->command = 'exit 0';
	}

	function isAvailable() {
		global $gfcommon;
		require_once $gfcommon.'mail/MailingList.class.php';
		require_once $gfcommon.'mail/MailingListFactory.class.php';
		if ($this->group->usesMail()) {
			$mlFactory = new MailingListFactory($this->group);
			$mlArray = $mlFactory->getMailingLists();
			$mlCount = count($mlArray);
			for ($j = 0; $j < $mlCount; $j++) {
				$currentList =& $mlArray[$j];
				if ($currentList->getListEmail() == $this->group->getUnixName().'-commits@'.forge_get_config('lists_host'))
					return true;
			}
			$this->disabledMessage = _('Hook not available due to missing dependency : Project has no commit mailing-list: ').$this->group->getUnixName().'-commits';
		} else {
			$this->disabledMessage = _('Hook not available due to missing dependency : Project not using mailing-list.');
		}
		return false;
	}

	function getDisabledMessage() {
		return $this->disabledMessage;
	}

	/**
	 * This function activates e-mail notification for pushed commits.  
	 * This is done by adding the needed entries to the projects hgrc file. 
	 */
	function enable($project) {
		if (!$project) {
			return false;
		}

		$project_name = $project->getUnixName();
		$root = forge_get_config('repos_path', 'scmhg') . '/' . $project_name;
		$unix_group = forge_get_config('apache_group');
		$unix_user = forge_get_config('apache_user');
		$main_repo = $root . '/.hg';
		if (is_dir("$main_repo")) {
			$mail = $project_name.'-commits@'.forge_get_config('web_host');
			$hgrc = "";

			/*strip of repository path for subject line*/
			$delim = "/";
			$strip = count(explode($delim, $root))-1;
			
			if (is_file($main_repo.'/hgrc')) {
				/*set the needed entries within hgrc*/

				$hgrc_val = parse_ini_file("$main_repo/hgrc", true);
				if (!isset( $hgrc_val['extensions'])) { 
					/*makes notify extension usable*/
					$hgrc_val['extensions']['hgext.notify'] = '';
				}
				if (!isset( $hgrc_val['hooks'])) {
					/*activates the notify hook*/
					$hgrc_val['hooks']['changegroup.notify'] = 'python:hgext.notify.hook';
				}
				if (!isset( $hgrc_val['email'])) {
					/*set email parameter*/
					$hgrc_val['email']['from'] = $mail;
					$sendmail = forge_get_config('sendmail_path');
					$hgrc_val['email']['method'] = $sendmail;
				}
				if (!isset( $hgrc_val['notify'])) {
					/*define when notify does something*/
					$hgrc_val['notify']['sources'] = 'serve push pull bundle';
					/*test = true will not deliver the mail, instead you will get command line output*/
					$hgrc_val['notify']['test'] = 'false';
					
					/*configure subscribers*/
					if (!isset( $hgrc_val['reposubs'])) {
						$hgrc_val['reposubs']['**'] = $mail;
					}
					$hgrc_val['notify']['template'] = '"\ndetails:   {webroot}/rev/{node|short}\nchangeset: {rev}:{node|short}\nuser:      {author}\ndate:
  {date|date}\ndescription:\n{desc}\n"' ;
					$hgrc_val['notify']['maxdiff'] = '300';
					$hgrc_val['notify']['strip'] = $strip;
				} else {
					/*parse_ini_file() has problems with boolean and special characters*/
					$hgrc_val['notify']['test'] = 'false';
					$hgrc_val['notify']['template'] = '"\ndetails:   {webroot}/rev/{node|short}\nchangeset: {rev}:{node|short}\nuser:      {author}\ndate:
  {date|date}\ndescription:\n{desc}\n"';
				}
				/* write configuration back to file*/
				foreach ($hgrc_val as $section => $sub) {
					$hgrc .= '['.$section."]\n";
					foreach ($sub as $prop => $value) {
						$hgrc .= "$prop = $value\n";
						if ($value == end($sub)) {
							$hgrc .= "\n";
						}
					}
				}
			} else {
				/*create new hgrc with default values*/
				$hgrc .= "[web]\n";
				$hgrc .= "baseurl = /hg";
				$hgrc .= "\ndescription = ".$project_name;
				$hgrc .= "\nstyle = paper";
				$hgrc .= "\nallow_read = *";
				$hgrc .= "\nallow_push = *\n\n";

				$hgrc .= "[extensions]\n" ;
				$hgrc .= "hgext.notify =\n\n";

				$hgrc .= "[hooks]\n" ;
				$hgrc .= "changegroup.notify = python:hgext.notify.hook\n\n";

				$hgrc .= "[email]\n";
				$hgrc .= "from = $mail\n";
				$hgrc .= "method = $sendmail\n\n";

				$hgrc .= "[notify]\n" ;
				$hgrc .= "sources = serve push pull bundle\n";
				$hgrc .= "test = false\n";
				$hgrc .= 'template = "\ndetails:   {webroot}/rev/{node|short}/\nchangeset: {rev}:{node|short}\nuser:      {author}\ndate:
  {date|date}\ndescription:\n{desc}\n"';
				$hgrc .= "\nmaxdiff = 300\n";
				$hgrc .= "strip = $strip\n\n";
				$hgrc .= "[reposubs]\n";
				$hgrc .= "** = $mail";
			} 
			$f = fopen ("$main_repo/hgrc.new", 'w');
			fwrite ($f, $hgrc);
			fclose ($f);
			rename ($main_repo.'/hgrc.new', $main_repo.'/hgrc');
			system( "chown $unix_user:$unix_group $main_repo/hgrc" );
			system( "chmod 660 $path/hgrc" );
		}
		return true;
	}

	/**
	 * This function deactivates e-mail notification.  
	 * This is done by removing the needed entries from the projects hgrc file. 
	 *
	 * @param	$project	object containing project data
	 */
	function disable($project) {
		if (!$project) {
			return false;
		}

		$unix_group = forge_get_config('apache_group');
		$unix_user = forge_get_config('apache_user');

		$project_name = $project->getUnixName();
		$root = forge_get_config('repos_path', 'scmhg') . '/' . $project_name;
		$main_repo = $root . '/.hg';
		if (is_file($main_repo.'/hgrc')) {
			/*unset extension and hook to unable notification emails*/
			$hgrc_val = parse_ini_file("$main_repo/hgrc", true);
			if ( isset( $hgrc_val['extensions'] )) {
				unset($hgrc_val['extensions']);
			}
			if ( isset( $hgrc_val['hooks'] )) {
				unset($hgrc_val['hooks']);
			}
			if ( isset( $hgrc_val['notify']['test'] )) {
				$hgrc_val['notify']['test'] = "false";
			}
			if ( isset( $hgrc_val['notify']['template'] )) {
				$hgrc_val['notify']['template'] = '"\ndetails:   {webroot}/rev/{node|short}\nchangeset: {rev}:{node|short}\nuser:      {author}\ndate:
  {date|date}\ndescription:\n{desc}\n"' ;
			}

			$hgrc = "" ;
			foreach ($hgrc_val as $section => $sub) {
				$hgrc .= '['.$section."]\n";
				foreach ($sub as $prop => $value) {
					$hgrc .= "$prop = $value\n";
					if ($value == end($sub)) {
						$hgrc .= "\n";
					}
				}
			}

			$f = fopen ("$main_repo/hgrc.new", 'w');
			fwrite($f, $hgrc);
			fclose($f);
			rename($main_repo.'/hgrc.new', $main_repo.'/hgrc');
			system( "chown $unix_user:$unix_group $main_repo/hgrc" );
			system( "chmod 660 $main_repo/hgrc" );
		}
		return true;
	}
	
}
