<?php
/** FusionForge Mercurial (Hg) plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2012, Denise Patzker
 * Copyright 2012, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

forge_define_config_item('default_server', 'scmhg', forge_get_config('web_host'));
forge_define_config_item('repos_path', 'scmhg', forge_get_config('chroot').'/scmrepos/hg');

class HgPlugin extends SCMPlugin {
	function HgPlugin() {
		$this->SCMPlugin();
		$this->name = 'scmhg';
		$this->text = 'Mercurial';
		$this->_addHook('scm_browser_page');
		$this->_addHook('scm_update_repolist');
		$this->_addHook('scm_generate_snapshots');
		$this->_addHook('scm_gather_stats');
		$this->register();
	}

	/**
	 * getPluginDescription - display the description of this plugin in pluginman admin page
	 *
	 * @return	string	the description
	 */
	function getPluginDescription() {
		return _('Use Mercuial as Source Code Management tool. Offer DAV or SSH access.');
	}

	function getDefaultServer() {
		return forge_get_config('default_server', 'scmhg');
	}

	function getBlurb() {
		return '<p>' . _('Documentation for Mercurial is available at <a href="http://hgbook.red-bean.com/">http://hgbook.red-bean.com/</a> . ')._(' Another short Introduction can be found at <a href="http://hginit.com/">http://hginit.com/</a>').'</p>';
	}

	function getInstructionsForAnon($project) {
		$b = '<h2>';
		$b .=  _('Anonymous Mercurial Access');
		$b .= '</h2>';

		if (forge_get_config('use_dav', 'scmhg')) {
			$protocol = forge_get_config('use_ssl', 'scmhg')? 'https' : 'http';
			$b .= '<p>';
			$b .= 'This project\'s Mercurial repository can be checked out through anonymous access with the following command.';
			$b .= '</p>';
			$b .= '<p>';
			$b .= '<tt>hg clone '.$protocol.'://'.forge_get_config('anonhg_login', 'scmhg').'@' . $this->getBoxForProject($project) . '/'. 'hg' .'/'. $project->getUnixName() .'/'.'</tt><br />';
			$b .= _('The password is ').forge_get_config('anonhg_password', 'scmhg').'<br/>';	

		} else {
			$b .= '<p class="warning">'._('Please contact forge administrator, scmhg plugin is not correctly configured');
		}
		$b .= '</p>';
		return $b;
	}

	function getInstructionsForRW($project) {
		$protocol = forge_get_config('use_ssl', 'scmhg')? 'https' : 'http';
		if (session_loggedin()) {
			$u = user_get_object(user_getid());
			$d = $u->getUnixName();
			$b = '';
			if (forge_get_config('use_ssh', 'scmhg')) {
				$b .= '<h2>';
				$b .= _('Developer Mercurial Access via SSH');
				$b .= '</h2>';
				$b .= '<p>';
				$b .= _('Only project developers can access the Mercurial tree via this method. SSH must be installed on your client machine. Enter your site password when prompted.');
				$b .= '</p>';
				// Warning : the ssh uri MUST be this form : ssh://username@scmbox//path/reponame
				//            HAVE YOU SEEN THE // starting the path ? Keep in mind the double /
				$b .= '<p><tt>hg clone ssh://'.$d.'@' . $this->getBoxForProject($project) .'/'. forge_get_config('repos_path', 'scmhg') .'/'. $project->getUnixName().'</tt></p>';
			}
			if (forge_get_config('use_dav', 'scmhg')) {
				$b .= '<h2>';
				$b .= _('Developer Mercurial Access via HTTP');
				$b .= '</h2>';
				$b .= '<p>';
				$b .= _('Only project developers can access the Mercurial tree via this method. Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>hg clone '.$protocol.'://<i>'. $d .'</i>@' . $this->getBoxForProject($project) .'/hg/'. $project->getUnixName() . '</tt></p>';
			}
		} else {
			if (forge_get_config('use_ssh', 'scmhg')) {
				$d = '<i>developername</i>';
				$b = '<h2>';
				$b .= _('Developer Mercurial Access via SSH');
				$b .= '</h2>';
				$b .= '<p>';
				$b .= _('Only project developers can access the Mercurial tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper value. Enter your site password when prompted.');
				$b .= '</p>';
				// Warning : the ssh uri MUST be this form : ssh://username@scmbox//path/reponame
				//            HAVE YOU SEEN THE // starting the path ? Keep in mind the double /
				$b .= '<p><tt>hg clone ssh://'.$d.'@' . $this->getBoxForProject($project) .'/'. forge_get_config('repos_path', 'scmhg') .'/'. $project->getUnixName().'</tt></p>';
			} else {
				$b = '<h2>';
				$b .= _('Developer Mercurial Access via HTTP');
				$b .= '</h2>';
				$b .= '<p>';
				$b .= _('Only project developers can access the Mercurial tree via this method. Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>hg clone '.$protocol.'://<i>'. _('developername') .'</i>@' . $this->getBoxForProject($project) .'/hg/'. $project->getUnixName() . '</tt></p>';
			}
		}
		return $b;
	}

	function getSnapshotPara($project) {
		return ;
	}

	function getBrowserLinkBlock($project) {
		global $HTML;
		$b = $HTML->boxMiddle(_('Hg Repository Browser'));
		$b .= '<p>';
		$b .= _('Browsing the Mercurial tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.');
		$b .= '</p>';
		$b .= '<p>[';
		$b .= util_make_link("/scm/browser.php?group_id=".$project->getID(), _('Browse Hg Repository'));
		$b .= ']</p>';
		return $b;
	}

	function getStatsBlock($project) {
		return ;
	}

	function printShortStats($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if ($project->usesPlugin($this->name)) {
			$result = db_query_params('SELECT sum(commits) AS commits, sum(adds) AS adds FROM stats_cvs_group WHERE group_id=$1',
						array ($project->getID())) ;
			$commit_num = db_result($result,0,'commits');
			$add_num    = db_result($result,0,'adds');
			if (!$commit_num) {
				$commit_num=0;
			}
			if (!$add_num) {
				$add_num=0;
			}
			echo ' (Mercurial: '.sprintf(_('<strong>%1$s</strong> commits, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}

	function printBrowserPage($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if ($project->usesPlugin($this->name)) {
			if ($this->browserDisplayable($project)) {
				print '<iframe src="'.util_make_url('/plugins/scmhg/cgi-bin/'.$project->getUnixName().'.cgi?p='.$project->getUnixName()).'" frameborder="0" width=100% height=700></iframe>';
			}
		}
	}

	function createOrUpdateRepo($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if (!$project->usesPlugin($this->name)) {
			return false;
		}

		$repo = forge_get_config('repos_path', 'scmhg') . '/' . $project->getUnixName();
		if (forge_get_config('use_ssh', 'scmhg')) {
			$unix_group = 'scm_' . $project->getUnixName();
		}
		if (forge_get_config('use_dav', 'scmhg')) {
			$unix_group = forge_get_config('apache_group'); 
			$unix_user = forge_get_config('apache_user');
		}
 
		system("mkdir -p $repo");
		/** per project configuration for http **/
		if (forge_get_config('use_dav', 'scmhg')) {
			//get template hgweb.cgi
			$hgweb = forge_get_config('source_path').'/plugins/scmhg/www/cgi-bin/hgweb.cgi';
			$project_hgweb = forge_get_config('source_path').'/www/plugins/scmhg/cgi-bin/'.$project->getUnixName().'.cgi';
			if (!is_file($project_hgweb)) {
				$lines = file($hgweb);
				$repo_config = "";
				foreach ($lines as $line) {
					if (preg_match("/\Aapplication = hgweb/",$line)) {
						//link per project hgweb.cgi to the project repository
						$repo_config .= "application = hgweb(\"".$repo."\",\"".$project->getUnixName()."\")\n";
					} else {
						$repo_config .= $line;
					}
				}
				$f = fopen($project_hgweb, 'w');
				fwrite($f, $repo_config);
				fclose($f);
				system("chown $unix_user:$unix_group $project_hgweb");
				system("chmod u+x $project_hgweb");
			}
		}
		if (!is_dir("$repo/.hg")) {
			system("hg init $repo");
			$f = fopen("$repo/.hg/hgrc",'w');
			$conf = "[web]\n";
			$conf .= "baseurl = /hg";
			$conf .= "\ndescription = ".$project->getUnixName();
			$conf .= "\nstyle = paper";
			$conf .= "\nallow_push = *"; //every user ( see apache configuration) is allowed to push
			$conf .= "\nallow_read = *"; // every user is allowed to clone and pull 
			fwrite($f, $conf);
			fclose($f);
			system("chgrp -R $unix_group $repo");
			system("chmod 770 $repo" );
			system("find $repo -type d | xargs chmod g+s" );
			system("chmod 660 $repo/.hg/hgrc");
		}

		if ($project->enableAnonSCM()) {
			system("chmod -R g+wX,o+rX-w $repo");
		} else {
			system("chmod -R g+wX,o-rwx $repo");
		}
	}

	function updateRepositoryList ($params) {
		$groups = $this->getGroups();
		if (!forge_get_config('use_dav', 'scmhg')) {
			return true;
		}

		$unix_group = forge_get_config('apache_group');
		$unix_user = forge_get_config('apache_user');
		$password_data = '';
		$hgusers = array();
		foreach ($groups as $project) {
			if ( !$project->isActive()) {
				continue;
			}
			if ( !$project->usesSCM()) {
				continue;
			}
			$push = "";
			$read = ""; /*pull,clone*/
			$path = forge_get_config('repos_path', 'scmhg').'/'.$project->getUnixName().'/.hg';
			$prevp = false;
			$prevr = false;
			$users = $project->getMembers();
			$pname = $project->getUnixName();
			foreach ($users as $user) {
				if (forge_check_perm_for_user ($user,
							'scm',
							$project->getID(),
							'write')) {
					if ($prevp){
						$push .= ", ";
					}
					if ($prevr){
						$read .= ", ";
					}
					$push .= $user->getUnixName();
					$read .= $user->getUnixName();
					$prevp = true;
					$prevr = true;
					$hgusers[$user->getID()] = $user;
				}elseif (forge_check_perm_for_user ($user,
									'scm',
									$project->getID(),
									'read')) {
					if ($prevr){
						$read .= ", ";
					}
					$read .= $user->getUnixName();
					$prevr = true;
					$hgusers[$user->getID()] = $user;
				}
			}

			if ($project->enableAnonSCM()) {
				$read = "*";
			}

			/*make new hgrc file*/
			if (is_file($path.'/hgrc')) {
				$hgrc_val = parse_ini_file($path.'/hgrc', true);
				if (isset ($hgrc_val['web'])) {
					$hgrc_val['web']['allow_read'] = $read;
					$hgrc_val['web']['allow_push'] = $push;
				}
				if (isset ($hgrc_val['notify']['test'])) {
					/* Set the value again, because parse_ini_file() converts boolean values to "" or "1" .
					This would break the hgrc file.*/
					$hgrc_val['notify']['test'] = 'false';
				}
				if (isset ($hgrc_val['notify']['template'])) {
					/*Set value again, because special character are not escaped*/
					$hgrc_val['notify']['template'] = '"\ndetails:  {webroot}/rev/{node|short}\nchangeset:  {rev}:{node|short}\nuser:  {author}\ndate:  {date|date}\ndescription:\n{desc}\n"';
				}
				$hgrc = "";
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
				$hgrc = "[web]\n";
				$hgrc .= "baseurl = /hg";
				$hgrc .= "\ndescription = ".$project->getUnixName();
				$hgrc .= "\nstyle = paper";
				$hgrc .= "\nallow_read = ".$read;
				$hgrc .= "\nallow_push = ".$push;
			}

			$f = fopen($path.'/hgrc.new', 'w');
			fwrite($f, $hgrc);
			fclose($f);
			rename($path.'/hgrc.new', $path.'/hgrc');
			system("chown $unix_user:$unix_group $path/hgrc");
			system("chmod 660 $path/hgrc");
		}

		foreach ($hgusers as $user_id => $user) {
			$password_data .= $user->getUnixName().':'.$user->getUnixPasswd()."\n";
		}
		$password_data .= forge_get_config('anonhg_login', 'scmhg').":".htpasswd_apr1_md5(forge_get_config('anonhg_password', 'scmhg'))."\n";

		$fname = forge_get_config('data_path').'/hgroot-authfile';
		$f = fopen($fname.'.new', 'w');
		fwrite($f, $password_data);
		fclose($f);
		chmod($fname.'.new', 0644);
		rename($fname.'.new', $fname);
	}

	function generateSnapshots($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		$group_name = $project->getUnixName();
		$tarball = forge_get_config('scm_tarballs_path').'/'.$group_name.'-scmroot.tar'.util_get_compressed_file_extension();

		if (!$project->enableAnonSCM()) {
			if (is_file($tarball)) {
				unlink ($tarball) ;
			}
			return false;
		}

		$toprepo = forge_get_config('repos_path', 'scmhg');
		$repo = $toprepo . '/' . $project->getUnixName();

		if (!is_dir($repo)) {
			if (is_file($tarball)) {
				unlink($tarball);
			}
			return false;
		}

		$tmp = trim(`mktemp -d`);
		if ($tmp == '') {
			return false;
		}
		system("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball") ;
		chmod("$tmp/tarball", 0644);
		copy("$tmp/tarball", $tarball);
		unlink("$tmp/tarball");
		system("rm -rf $tmp");
	}

	function gatherStats($params) {
		global $last_user, $usr_adds, $usr_deletes, $usr_updates, $updates, $adds;

		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if (! $project->usesPlugin($this->name)) {
			return false;
		}
 
		if ($params['mode'] == 'day') {
			db_begin();
			$year = $params['year'];
			$month = $params['month'];
			$day = $params['day'];
			$month_string = sprintf("%04d%02d", $year, $month );
			$start_time = gmmktime(0, 0, 0, $month, $day, $year);
			$end_time = $start_time + 86400;
			$usr_adds    = array();
			$usr_updates = array();
			$usr_deletes = array();
			$adds    = 0;
			$updates = 0;
			$repo = forge_get_config('repos_path', 'scmhg') . '/' . $project->getUnixName();
			if (!is_dir($repo) || !is_dir("$repo/.hg")) {
				// echo "No repository\n";
				db_rollback();
				return false;
			}
			// cleaning stats_cvs_* table for the current day
			$res = db_query_params('DELETE FROM stats_cvs_group WHERE month = $1 AND day = $2 AND group_id = $3',
						array($month_string,
							$day,
							$project->getID()));
			if(!$res) {
				echo "Error while cleaning stats_cvs_group\n";
				db_rollback();
				return false;
			}
			//switch into scm_repository and take a look at the log informations
			$cdir = chdir($repo);
			if ($cdir) {
				//show customised log informations
				$pipe = popen("hg log --style fflog.tmpl -d '$start_time 0 to $end_time 0'", 'r');
				$last_user = "";
				while (!feof($pipe) && $line = fgets ($pipe)) {
					//determine between author line and file informations
					if (preg_match("/(\A[AMD]) .*/", $line, $matches)) {
						if ($last_user == "") continue;
						switch ($matches[1]) {
							case 'A':
								$usr_adds[$last_user]++;
								$adds++;
								break;
							case 'M':
								$usr_updates[$last_user]++;
								$updates++;
								break;
							case 'D':
								$usr_deletes[$last_user]++;
								break;
						}
					} else {
						$last_user = $this->getUser($line); 
					}
				}
				pclose($pipe);
 
				// inserting group results in stats_cvs_groups
				if ($updates > 0 || $adds > 0) {
					if (!db_query_params('INSERT INTO stats_cvs_group (month, day, group_id, checkouts, commits, adds) VALUES ($1, $2, $3, $4, $5, $6)',
						array($month_string,
							$day,
							$project->getID(),
							0,
							$updates,
							$adds))) {
						echo "Error while inserting into stats_cvs_group\n";
						db_rollback();
						return false;
					}
				}
			}
			// building the user list
			$user_list = array_unique(array_merge(array_keys($usr_adds), array_keys($usr_updates)));
			foreach ( $user_list as $user ) {
				$uu = $usr_updates[$user] ? $usr_updates[$user] : 0;
				$ua = $usr_adds[$user] ? $usr_adds[$user] : 0;
				if ($uu > 0 || $ua > 0) {
					if (!db_query_params('INSERT INTO stats_cvs_user (month, day, group_id, user_id, commits,adds) VALUES ($1, $2, $3, $4, $5, $6)',
							array($month_string,
								$day,
								$project->getID(),
								$user,
								$uu,
								$ua))) {
						echo "Error while inserting into stats_cvs_user\n";
						db_rollback();
						return false;
					}
				}
			}
		}
		db_commit();
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>