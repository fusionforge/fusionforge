<?php
/**
 * scmhook commitEmail Plugin Class
 * Copyright 2012, Denise Patzker <denise.patzker@tu-dresden.de>
 * Copyright 2012,2018, Franck Villaume - TrivialDev
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

class HgCommitEmail extends scmhook {
	var $group;
	var $disabledMessage;

	function __construct() {
		$this->group = $GLOBALS['group'];
		$this->name = "Commit Email";
		$this->description = _('Commit message log is pushed to commit mailing-list of the project (which you need to create)')
							. "\n"
							. _('The hook is triggered after "serve push pull bundle" on the projects repository.');
		$this->classname = "commitEmail";
		$this->label = "scmhg";
		$this->hooktype = "serve-push-pull-bundle";
		$this->unixname = "commitemail";
		$this->needcopy = 0;
		$this->command = 'exit 0';
	}

	function getDisabledMessage() {
		return $this->disabledMessage;
	}

	function getParams() {
		return array(
			'dest' => array(
				'description' => _('Send commit e-mail notification to'),
				'type'        => 'emails',
				'default'     => $this->group->getUnixName().'-commits@'.forge_get_config('lists_host'),
			)
		);
	}

	/**
	 * This function activates e-mail notification for pushed commits.
	 * This is done by adding the needed entries to the projects hgrc file.
	 */
	function enable($project, $scmdir_root) {
		if (!$project) {
			return false;
		}

		$project_name = $project->getUnixName();
		$unix_group = forge_get_config('apache_group');
		$unix_user = forge_get_config('apache_user');
		$sendmail = forge_get_config('sendmail_path');
		$main_repo = $scmdir_root.'/.hg';
		if (is_dir($main_repo)) {
			$mail = $project_name.'-commits@'.forge_get_config('web_host');
			$table = 'plugin_scmhook_scmhg_'.strtolower($this->classname);
			if (db_check_table_exists($table)) {
				$res = db_query_params('SELECT * FROM '.$table.' WHERE group_id = $1 and repository_name = $2', array($group_id, $repository));
				$values = db_fetch_array($res);
				foreach ($this->getParams() as $pname => $pconf) {
					$mail = ($values[$pname] != null) ? $values[$pname] : $pconf['default'];
				}
			}
			$hgrc = "";

			/*strip of repository path for subject line*/
			$delim = "/";
			$strip = count(explode($delim, $scmdir_root))-1;

			/*create new hgrc with default values*/
			$hgrc .= "[web]\n";
			$hgrc .= "baseurl = /hg";
			$hgrc .= "\ndescription = ".$project_name;
			$hgrc .= "\nstyle = paper";
			$hgrc .= "\nallow_read = *";
			$hgrc .= "\nallow_push = *\n";
			if (!forge_get_config('use_ssl', 'scmhg')) {
				$hgrc .= "push_ssl = 0\n";
			}
			$hgrc .= "\n";
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

			$f = fopen ("$main_repo/hgrc.new", 'w');
			fwrite($f, $hgrc);
			fclose($f);
			rename($main_repo.'/hgrc.new', $main_repo.'/hgrc');
			system("chown $unix_user:$unix_group $main_repo/hgrc");
			system("chmod 660 $main_repo/hgrc");
		}
		return true;
	}

	/**
	 * This function deactivates e-mail notification.
	 * This is done by removing the needed entries from the projects hgrc file.
	 *
	 * @param	$project	object containing project data
	 * @return bool
	 */
	function disable($project, $scmdir_root) {
		if (!$project) {
			return false;
		}

		$unix_group = forge_get_config('apache_group');
		$unix_user = forge_get_config('apache_user');

		$project_name = $project->getUnixName();
		$main_repo = $scmdir_root.'/.hg';
		if (is_dir($main_repo)) {
			/*create new hgrc with default values*/
			$hgrc .= "[web]\n";
			$hgrc .= "baseurl = /hg";
			$hgrc .= "\ndescription = ".$project_name;
			$hgrc .= "\nstyle = paper";
			$hgrc .= "\nallow_read = *";
			$hgrc .= "\nallow_push = *\n\n";
			if (!forge_get_config('use_ssl', 'scmhg')) {
				$hgrc .= "push_ssl = 0\n";
			}

			$f = fopen ("$main_repo/hgrc.new", 'w');
			fwrite($f, $hgrc);
			fclose($f);
			rename($main_repo.'/hgrc.new', $main_repo.'/hgrc');
			system("chown $unix_user:$unix_group $main_repo/hgrc");
			system("chmod 660 $main_repo/hgrc");
		}
		return true;
	}

}
