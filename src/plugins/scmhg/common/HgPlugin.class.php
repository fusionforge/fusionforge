<?php
/**
 * FusionForge Mercurial (Hg) plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2012, Denise Patzker
 * Copyright 2012-2014, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/plugins_utils.php';

forge_define_config_item('default_server', 'scmhg', forge_get_config('scm_host'));
forge_define_config_item('repos_path', 'scmhg', forge_get_config('chroot').'/scmrepos/hg');

class HgPlugin extends SCMPlugin {
	function __construct() {
		parent::__construct();
		$this->name = 'scmhg';
		$this->text = _('Mercurial');
		$this->pkg_desc =
_("This plugin contains the Mercurial (Hg) subsystem of FusionForge. It
allows each FusionForge project to have its own Mercurial repository,
and gives some control over it to the project's administrator.
Offer DAV or SSH access.");
		$this->_addHook('scm_browser_page');
		$this->_addHook('scm_update_repolist');
		$this->_addHook('scm_generate_snapshots');
		$this->_addHook('scm_gather_stats');
		$this->_addHook('activity');
		$this->_addHook('scm_admin_form');
		$this->_addHook('scm_delete_repo');
		$this->_addHook('scm_add_repo');
		$this->register();
	}

	/**
	 * getPluginDescription - display the description of this plugin in pluginman admin page
	 *
	 * @return	string	the description
	 */
	function getPluginDescription() {
		return _('Use Mercurial as Source Code Management tool. Offer DAV or SSH access.');
	}

	function getDefaultServer() {
		return forge_get_config('default_server', 'scmhg');
	}

	function getBlurb() {
		return html_e('p', array(), sprintf(_('Documentation for %1$s is available at <a href="%2$s">%2$s</a>.'),
							'Mercurial',
							'http://hgbook.red-bean.com/')).
			html_e('p', array(), _('Another short Introduction can be found at <a href="http://hginit.com/">http://hginit.com/</a>'));
	}

	function getInstructionsForAnon($project) {
		global $HTML;
		$b = html_e('h2', array(), _('Anonymous Mercurial Access'));

		if (forge_get_config('use_dav', 'scmhg')) {
			$protocol = forge_get_config('use_ssl', 'scmhg')? 'https' : 'http';
			$b .= html_e('p', array(), _("This project's Mercurial repository can be checked out through anonymous access with the following command")._(':'));
			$b .= html_e('p', array(), html_e('tt', array(), 'hg clone '.$protocol.'://'.forge_get_config('anonhg_login', 'scmhg').'@'.$this->getBoxForProject($project).'/'.'hg'.'/'.$project->getUnixName().'/').
						html_e('br').
						_('The password is ').forge_get_config('anonhg_password', 'scmhg'));
		} else {
			$b .= $HTML->warning_msg(_('Please contact forge administrator, scmhg plugin is not correctly configured'));
		}
		return $b;
	}

	function getInstructionsForRW($project) {
		$protocol = forge_get_config('use_ssl', 'scmhg')? 'https' : 'http';
		if (session_loggedin()) {
			$u = user_get_object(user_getid());
			$d = $u->getUnixName();
			$b = '';
			if (forge_get_config('use_ssh', 'scmhg')) {
				$b .= html_e('h2', array(), sprintf(_('Developer %s Access via SSH'), 'Mercurial'));
				$b .= html_e('p', array(), _('Read/write access to Mercurial tree is allowed for authenticated users.').
					' '._('SSH must be installed on your client machine.').
					' '._('Enter your site password when prompted.'));
				// Warning : the ssh uri MUST be this form : ssh://username@scmbox//path/reponame
				//            HAVE YOU SEEN THE // starting the path ? Keep in mind the double /
				$b .= html_e('p', array(), html_e('tt', array(), 'hg clone ssh://'.$d.'@'.$this->getBoxForProject($project).'/'.forge_get_config('repos_path', 'scmhg').'/'.$project->getUnixName()));
			}
			if (forge_get_config('use_dav', 'scmhg')) {
				$b .= html_e('h2', array(), _('Developer Mercurial Access via HTTP'));
				$b .= html_e('p', array(), _('Only project developers can access the Mercurial tree via this method.').
					' '._('Enter your site password when prompted.'));
				$b .= html_e('p', array(), html_e('tt', array(), 'hg clone '.$protocol.'://<i>'.$d.'</i>@'.$this->getBoxForProject($project) .'/hg/'. $project->getUnixName()));
			}
		} else {
			if (forge_get_config('use_ssh', 'scmhg')) {
				$d = html_e('em', array(), _('developername'));
				$b = html_e('h2', array(), sprintf(_('Developer %s Access via SSH'), 'Mercurial'));
				$b .= html_e('p', array(), sprintf(_('Only project developers can access the %s tree via this method.'), 'Mercurial').
						' '._('SSH must be installed on your client machine.').
						' '._('Substitute <em>developername</em> with the proper value.').
						' '._('Enter your site password when prompted.'));
				// Warning : the ssh uri MUST be this form : ssh://username@scmbox//path/reponame
				//            HAVE YOU SEEN THE // starting the path ? Keep in mind the double /
				$b .= html_e('p', array(), html_e('tt', array(), 'hg clone ssh://'.$d.'@'.$this->getBoxForProject($project).'/'.forge_get_config('repos_path', 'scmhg').'/'.$project->getUnixName()));
			} else {
				$b = html_e('h2', array(), _('Developer Mercurial Access via HTTP'));
				$b .= html_e('p', array(), _('Only project developers can access the Mercurial tree via this method.').
					' '._('Enter your site password when prompted.'));
				$b .= html_e('p', array(), html_e('tt', array(), 'hg clone '.$protocol.'://'.html_e('i', array(), _('developername')).'@'.$this->getBoxForProject($project).'/hg/'.$project->getUnixName()));
			}
		}
		return $b;
	}

	function getSnapshotPara($project) {
		return;
	}

	function getBrowserLinkBlock($project) {
		global $HTML;
		$b = html_e('h2', array(), _('Mercurial Repository Browser'));
		$b .= html_e('p', array(), _('Browsing the Mercurial tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.'));
		$b .= html_e('p', array(), '['.util_make_link('/scm/browser.php?group_id='.$project->getID(), _('Browse Hg Repository')).']');
		return $b;
	}

	function getStatsBlock($project) {
		global $HTML;
		$b = '';

		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(updates) as updates, sum(adds) as adds, sum(adds+commits) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
			array($project->getID()));

		if (db_numrows($result) > 0) {
			$tableHeaders = array(
			_('Name'),
			_('Adds'),
			_('Updates')
			);
			$b .= $HTML->listTableTop($tableHeaders, false, '', 'repo-history');

			$i = 0;
			$total = array('adds' => 0, 'updates' => 0);

			while($data = db_fetch_array($result)) {
				$cells = array();
				$cells[] = array(util_make_link_u($data['user_name'], $data['user_id'], $data['realname']), 'class' => 'halfwidth');
				$cells[] = array($data['adds'], 'class' => 'onequarterwidth align-right');
				$cells[] = array($data['updates'], 'class' => 'onequarterwidth align-right');
				$b .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i, true)), $cells);
				$total['adds'] += $data['adds'];
				$total['updates'] += $data['updates'];
				$i++;
			}
			$cells = array();
			$cells[] = array(html_e('strong', array(), _('Total')._(':')), 'class' => 'halfwidth');
			$cells[] = array($total['adds'], 'class' => 'onequarterwidth align-right');
			$cells[] = array($total['updates'], 'class' => 'onequarterwidth align-right');
			$b .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i, true)), $cells);
			$b .= $HTML->listTableBottom();
		} else {
			$b .= $HTML->information(_('No history yet'));
		}

		return $b;
	}

	function printShortStats($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if ($project->usesPlugin($this->name)  && forge_check_perm('scm', $project->getID(), 'read')) {
			$result = db_query_params('SELECT sum(updates) AS updates, sum(adds) AS adds FROM stats_cvs_group WHERE group_id=$1',
						array ($project->getID())) ;
			$update_num = db_result($result,0,'updates');
			$add_num    = db_result($result,0,'adds');
			if (!$update_num) {
				$update_num=0;
			}
			if (!$add_num) {
				$add_num=0;
			}
			$params['result'] .= ' (Mercurial: '.sprintf(_('<strong>%1$s</strong> updates, <strong>%2$s</strong> adds'), number_format($update_num, 0), number_format($add_num, 0)).")";
		}
	}

	function printBrowserPage($params) {
		global $HTML;
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if ($project->usesPlugin($this->name)) {
			if ($this->browserDisplayable($project)) {
				$iframesrc = '/plugins/scmhg/cgi-bin/'.$project->getUnixName().'.cgi';
				if ($params['commit']) {
					$iframesrc .= '/rev/'.$params['commit'];
				} else {
					$iframesrc .=  '?p='.$project->getUnixName();
				}
				htmlIframe($iframesrc,array('id'=>'scmhg_iframe'));
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
			if (!forge_get_config('use_ssl', 'scmhg')) {
				$conf .= "\npush_ssl = 0";
			}
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

	function updateRepositoryList($params) {
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
				if (!forge_get_config('use_ssl', 'scmhg')) {
					$hgrc .= "\n".'push_ssl = 0';
				}
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
		$us = forge_get_config('use_scm_snapshots') ;
		$ut = forge_get_config('use_scm_tarballs') ;
		if (!$us && !$ut) {
			return false ;
		}

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
		if ($ut) {
			system("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball") ;
			chmod("$tmp/tarball", 0644);
			copy("$tmp/tarball", $tarball);
			unlink("$tmp/tarball");
			system("rm -rf $tmp");
		}
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
			$usr_commits = array();
			$adds    = 0;
			$updates = 0;
			$deletes = 0;
			$commits = 0;
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

			$res = db_query_params ('DELETE FROM stats_cvs_user WHERE month=$1 AND day=$2 AND group_id=$3',
						array ($month_string,
						       $day,
						       $project->getID())) ;
			if(!$res) {
				echo "Error while cleaning stats_cvs_user\n" ;
				db_rollback () ;
				return false ;
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
						$result = preg_match("/^(?P<name>.+) <(?P<mail>.+)>/", $line, $matches);
						if ($result) {
							// Author line
							$last_user = $matches['name'];
							$user2email[$last_user] = strtolower($matches['mail']);
							if (!isset($usr_adds[$last_user])) {
								$usr_adds[$last_user] = 0;
								$usr_updates[$last_user] = 0;
								$usr_deletes[$last_user] = 0;
								$usr_commits[$last_user] = 0;
							}
							$commits++;
							$usr_commits[$last_user]++;
						}
					}
				}
				pclose($pipe);
			}

			// inserting group results in stats_cvs_groups
			if ($updates > 0 || $adds > 0 || $deletes > 0 || $commits > 0) {
				if (!db_query_params('INSERT INTO stats_cvs_group (month,day,group_id,checkouts,commits,adds,updates,deletes) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
						      array($month_string,
							     $day,
							     $project->getID(),
							     0,
							     $commits,
							     $adds,
							     $updates,
							     $deletes))) {
					echo "Error while inserting into stats_cvs_group\n";
					db_rollback();
					return false;
				}
			}

			// building the user list
			$user_list = array_unique( array_merge( array_keys( $usr_adds ), array_keys( $usr_updates ), array_keys( $usr_deletes ), array_keys( $usr_commits ) ) );

			foreach ($user_list as $user) {
				// Trying to get user id from user name or email
				$u = user_get_object_by_name($user);
				if ($u) {
					$user_id = $u->getID();
				} else {
					$res=db_query_params('SELECT user_id FROM users WHERE lower(realname)=$1 OR email=$2',
						array(strtolower($user), $user2email[$user]));
					if ($res && db_numrows($res) > 0) {
						$user_id = db_result($res,0,'user_id');
					} else {
						continue;
					}
				}

				$uc = isset($usr_commits[$user]) ? $usr_commits[$user] : 0;
				$uu = isset($usr_updates[$user]) ? $usr_updates[$user] : 0;
				$ua = isset($usr_adds[$user]) ? $usr_adds[$user] : 0;
				$ud = isset($usr_deletes[$user]) ? $usr_deletes[$user] : 0;
				if ($uu > 0 || $ua > 0 || $uc > 0 || $ud > 0) {
					if (!db_query_params('INSERT INTO stats_cvs_user (month,day,group_id,user_id,commits,adds,updates,deletes) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
							      array($month_string,
								     $day,
								     $project->getID(),
								     $user_id,
								     $uc,
								     $ua,
								     $uu,
								     $ud))) {
						echo "Error while inserting into stats_cvs_user\n";
						db_rollback();
						return false;
					}
				}
			}
		}
		db_commit();
	}

	function activity($params) {
		$group_id = $params['group'];
		$project = group_get_object($group_id);
		if (!$project->usesPlugin($this->name)) {
			return false;
		}
		if (in_array('scmhg', $params['show']) || (count($params['show']) < 1)) {
			$repo = forge_get_config('repos_path', 'scmhg') . '/' . $project->getUnixName();
			if (is_dir($repo) && is_dir($repo.'/.hg') && chdir($repo)) {
				$start_time = $params['begin'];
				$end_time = $params['end'];
				$pipe = popen("hg log --template '{date|shortdate}||{author|email}||{desc}||{node}\n' -d '$start_time 0 to $end_time 0'", 'r');
				while (!feof($pipe) && $data = fgets($pipe)) {
					$line = trim($data);
					$splitedLine = explode('||', $line);
					if (sizeof($splitedLine) == 4) {
						$result = array();
						$result['section'] = 'scm';
						$result['group_id'] = $group_id;
						$result['ref_id'] = 'browser.php?group_id='.$group_id.'&commit='.$splitedLine[3];
						$result['description'] = htmlspecialchars($splitedLine[2]).' (changeset '.$splitedLine[3].')';
						$userObject = user_get_object_by_email($splitedLine[1]);
						if (is_a($userObject, 'FFUser')) {
							$result['realname'] = util_display_user($userObject->getUnixName(), $userObject->getID(), $userObject->getRealName());
						} else {
							$result['realname'] = '';
						}
						$splitedDate = explode('-', $splitedLine[0]);
						$result['activity_date'] = $splitedDate[0];
						$result['subref_id'] = '';
						$params['results'][] = $result;
					}
				}
			}
		}
		if (!in_array($this->name, $params['ids'])) {
			$params['ids'][] = $this->name;
			$params['texts'][] = _('Hg Commits');
		}
		return true;
	}

	function scm_add_repo(&$params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if (!$project->usesPlugin($this->name)) {
			return false;
		}

		if (!isset($params['repo_name'])) {
			return false;
		}

		if ($params['repo_name'] == $project->getUnixName()) {
			$params['error_msg'] = sprintf(_('A repository %s already exists'), $params['repo_name']);
			return false;
		}

		if (! util_is_valid_repository_name($params['repo_name'])) {
			$params['error_msg'] = _('This repository name is not valid');
			return false;
		}

		$result = db_query_params('SELECT count(*) AS count FROM scm_secondary_repos WHERE group_id=$1 AND repo_name = $2 AND plugin_id=$3',
					  array($params['group_id'],
						 $params['repo_name'],
						 $this->getID()));
		if (!$result) {
			$params['error_msg'] = db_error();
			return false;
		}
		if (db_result($result, 0, 'count')) {
			$params['error_msg'] = sprintf(_('A repository %s already exists'), $params['repo_name']);
			return false;
		}

		$description = '';
		$clone = '';
		if (isset($params['clone'])) {
			$url = $params['clone'];
			if ($url == '') {
				// Start from empty
				$clone = $url;
			} elseif (preg_match('|^https?://|', $url)) {
				// External URLs: OK
				$clone = $url;
			} elseif ($url == $project->getUnixName()) {
				$clone = $url;
			} elseif (($result = db_query_params('SELECT count(*) AS count FROM scm_secondary_repos WHERE group_id=$1 AND repo_name = $2 AND plugin_id=$3',
							     array($project->getID(),
								    $url,
								    $this->getID())))
				  && db_result($result, 0, 'count')) {
				// Local repo: try to clone from an existing repo in same project
				// Repository found
				$clone = $url;
			} else {
				$params['error_msg'] = _('Invalid URL from which to clone');
				$clone = '';
				return false;
			}
		}
		if (isset($params['description'])) {
			$description = $params['description'];
		}
		if ($clone && !$description) {
			$description = sprintf(_('Clone of %s'), $params['clone']);
		}
		if (!$description) {
			$description = "Hg repository $params[repo_name] for project ".$project->getUnixName();
		}

		$result = db_query_params('INSERT INTO scm_secondary_repos (group_id, repo_name, description, clone_url, plugin_id) VALUES ($1, $2, $3, $4, $5)',
					   array($params['group_id'],
						  $params['repo_name'],
						  $description,
						  $clone,
						  $this->getID()));
		if (! $result) {
			$params['error_msg'] = db_error();
			return false;
		}

		plugin_hook('scm_admin_update', $params);
		return true;
	}

	function scm_admin_form(&$params) {
		global $HTML;
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if (!$project->usesPlugin($this->name)) {
			return false;
		}

		session_require_perm('project_admin', $params['group_id']);

		$project_name = $project->getUnixName();
		$result = db_query_params('SELECT repo_name, description, clone_url FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3 ORDER BY repo_name',
					  array($params['group_id'],
						 SCM_EXTRA_REPO_ACTION_UPDATE,
						 $this->getID()));
		if (!$result) {
			$params['error_msg'] = db_error();
			return false;
		}
		$existing_repos = array();
		while($data = db_fetch_array($result)) {
			$existing_repos[] = array('repo_name' => $data['repo_name'],
						  'description' => $data['description'],
						  'clone_url' => $data['clone_url']);
		}
		if (count($existing_repos) == 0) {
			echo $HTML->information(_('No extra Hg repository for project').' '.$project_name);
		} else {
			echo html_e('h2', array(), sprintf(ngettext('Extra Hg repository for project %1$s',
									'Extra Hg repositories for project %1$s',
									count($existing_repos)), $project_name));
			$titleArr = array(_('Repository name'), ('Initial repository description'), _('Initial clone URL (if any)'), _('Delete'));
			echo $HTML->listTableTop($titleArr);
			foreach ($existing_repos as $key => $repo) {
				$cells = array();
				$cells[][] = html_e('tt', array(), $repo['repo_name']);
				$cells[][] = $repo['description'];
				$cells[][] = $repo['clone_url'];
				$deleteForm = $HTML->openForm(array('name' => 'form_delete_repo_'.$repo['repo_name'], 'action' => getStringFromServer('PHP_SELF'), 'method' => 'post'));
				$deleteForm .= html_e('input', array('type' => 'hidden', 'name' => 'group_id', 'value' => $params['group_id']));
				$deleteForm .= html_e('input', array('type' => 'hidden', 'name' => 'delete_repository', 'value' => 1));
				$deleteForm .= html_e('input', array('type' => 'hidden', 'name' => 'repo_name', 'value' => $repo['repo_name']));
				$deleteForm .= html_e('input', array('type' => 'hidden', 'name' => 'scm_enable_anonymous', 'value' => ($project->enableAnonSCM()? 1 : 0)));
				$deleteForm .= html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Delete')));
				$deleteForm .= $HTML->closeForm();
				$cells[][] = $deleteForm;
				echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($key, true)), $cells);
			}
			echo $HTML->listTableBottom();
		}

		echo html_e('h2', array(), sprintf(_('Create new Hg repository for project %s'), $project_name));
		echo $HTML->openForm(array('name' => 'form_create_repo', 'action' => getStringFromServer('PHP_SELF'), 'method' => 'post'));
		echo html_e('input', array('type' => 'hidden', 'name' => 'group_id', 'value' => $params['group_id']));
		echo html_e('input', array('type' => 'hidden', 'name' => 'create_repository', 'value' => 1));
		echo html_e('p', array(), html_e('strong', array(), _('Repository name')._(':')).utils_requiredField().html_e('br').
				html_e('input', array('type' => 'text', 'required' => 'required', 'size' => 20, 'name' => 'repo_name', 'value' => '')));
		echo html_e('p', array(), html_e('strong', array(), _('Description')._(':')).html_e('br').
				html_e('input', array('type' => 'text', 'size' => 60, 'name' => 'description', 'value' => '')));
		echo html_e('p', array(), html_e('strong', array(), _('Initial clone URL (or name of an existing repository in this project; leave empty to start with an empty repository)')._(':')).html_e('br').
				html_e('input', array('type' => 'text', 'size' => 60, 'name' => 'clone', 'value' => $project_name)));
		echo html_e('input', array('type' => 'hidden', 'name' => 'scm_enable_anonymous', 'value' => ($project->enableAnonSCM()? 1 : 0)));
		echo html_e('input', array('type' => 'submit', 'name' => 'cancel', 'value' => _('Cancel')));
		echo html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Submit')));
		echo $HTML->closeForm();
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
