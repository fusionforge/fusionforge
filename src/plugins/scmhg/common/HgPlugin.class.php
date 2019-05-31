<?php
/**
 * FusionForge Mercurial (Hg) plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2012, Denise Patzker
 * Copyright 2012-2014,2017-2019, Franck Villaume - TrivialDev
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
forge_define_config_item('ssh_port', 'core', 22);

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
		$this->_addHook('get_scm_repo_list');
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
			$repo_list = $this->getRepositories($project);
			$protocol = forge_get_config('use_ssl', 'scmhg')? 'https' : 'http';
			$b .= html_e('p', array(), _("This project's Mercurial repository can be checked out through anonymous access with the following command")._(':'));
			foreach ($repo_list as $repo_name) {
				$b .= html_e('kbd', array(), 'hg clone '.$protocol.'://'.$this->getBoxForProject($project).'/anonscm/'.'hg'.'/'.$project->getUnixName().'/'.$repo_name).html_e('br');
			}
		} else {
			$b .= $HTML->warning_msg(_('Anonymous browsing not available using ssh access.'));
		}
		return $b;
	}

	function getInstructionsForRW($project) {
		global $HTML;
		$repo_list = $this->getRepositories($project);

		$b = '';
		$b .= html_e('h2', array(), _('Developer Access'));
		$b .= html_e('p', array(),
				ngettext('Only project developers can access the Hg repository via this method.',
				'Only project developers can access the Hg repositories via this method.',
				count($repo_list)));
		$b .= '<div id="tabber-hg">';
		$liElements = array();
		if (forge_get_config('use_ssh', 'scmhg')) {
			$liElements[]['content'] = '<a href="#tabber-hgssh">'._('via SSH').'</a>';
			$configuration = 1;
		}
		if (forge_get_config('use_dav', 'scmhg')) {
			$liElements[]['content'] = '<a href="#tabber-hgdav">'._('via "DAV"').'</a>';
			$configuration = 1;
		}
		$b .= $HTML->html_list($liElements);
		if (!isset($configuration)) {
			return $HTML->error_msg(_('Error')._(': ')._('No access protocol has been allowed for the Hg plugin in scmhg.ini: use_ssh and use_dav are disabled'));
		}
		$ssh_port = '';
		if (forge_get_config('ssh_port') != 22) {
			$ssh_port = ':'.forge_get_config('ssh_port');
		}
		if (session_loggedin()) {
			$u = user_get_object(user_getid());
			$d = $u->getUnixName();
			if (forge_get_config('use_ssh', 'scmhg')) {
				$b .= '<div id="tabber-hgssh" class="tabbertab" >';
				$b .= html_e('p', array(), _('SSH must be installed on your client machine.'));
				$htmlRepo = '';
				foreach ($repo_list as $repo_name) {
					// Warning : the ssh uri MUST be this form : ssh://username@scmbox//path/reponame
					//           HAVE YOU SEEN THE // starting the path ? Keep in mind the double /
					if (forge_get_config('use_shell_limited')) {
						$htmlRepo .= html_e('kbd', array(), 'hg clone ssh://'.$d.'@'.$this->getBoxForProject($project).$ssh_port.'/hg/'.$project->getUnixName().'/'.$repo_name).html_e('br');

					} else {
						$htmlRepo .= html_e('kbd', array(), 'hg clone ssh://'.$d.'@'.$this->getBoxForProject($project).$ssh_port.'/'.forge_get_config('repos_path', 'scmhg').'/'.$project->getUnixName().'/'.$repo_name).html_e('br');
					}
				}
				$b .= html_e('p', array(), $htmlRepo);
				$b .= '</div>';
			}
			if (forge_get_config('use_dav', 'scmhg')) {
				$b .= '<div id="tabber-hgdav" class="tabbertab" >';
				$b .= html_e('p', array(), _('Enter your site password when prompted.'));
				$htmlRepo = '';
				$protocol = forge_get_config('use_ssl', 'scmhg') ? 'https' : 'http';
				foreach ($repo_list as $repo_name) {
					$htmlRepo .= html_e('kbd', array(), 'hg clone '.$protocol.'://<i>'.$d.'</i>@'.$this->getBoxForProject($project).'/authscm/'.$d.'/hg/'. $project->getUnixName().'/'.$repo_name).html_e('br');
				}
				$b .= html_e('p', array(), $htmlRepo);
				$b .= '</div>';
			}
		} else {
			if (forge_get_config('use_ssh', 'scmhg')) {
				$b .= '<div id="tabber-hgssh" class="tabbertab" >';
				$b .= html_e('p', array(),
					ngettext('Only project developers can access the Hg repository via this method.',
						'Only project developers can access the Hg repositories via this method.',
						count($repo_list)).
					' '. _('SSH must be installed on your client machine.').
					' '. _('Substitute <em>developername</em> with the proper value.'));
				$htmlRepo = '';
				foreach ($repo_list as $repo_name) {
					// Warning : the ssh uri MUST be this form : ssh://username@scmbox//path/reponame
					//           HAVE YOU SEEN THE // starting the path ? Keep in mind the double /
					if (forge_get_config('use_shell_limited')) {
						$htmlRepo .= html_e('kbd', array(), 'hg clone ssh://'.html_e('i', array(), _('developername'), true, false).'@'.$this->getBoxForProject($project).$ssh_port.'/hg/'.$project->getUnixName().'/'.$repo_name).html_e('br');
					} else {
						$htmlRepo .= html_e('kbd', array(), 'hg clone ssh://'.html_e('i', array(), _('developername'), true, false).'@'.$this->getBoxForProject($project).$ssh_port.'/'.forge_get_config('repos_path', 'scmhg').'/'.$project->getUnixName().'/'.$repo_name).html_e('br');
					}
				}
				$b .= html_e('p', array(), $htmlRepo);
				$b .= '</div>';
			}
			if (forge_get_config('use_dav', 'scmhg')) {
				$protocol = forge_get_config('use_ssl', 'scmhg')? 'https' : 'http';
				$b .= '<div id="tabber-hgdav" class="tabbertab" >';
				$b .= html_e('p', array(),
					ngettext('Only project developers can access the Hg repository via this method.',
						'Only project developers can access the Hg repositories via this method.',
						count($repo_list)).
					' '. _('Enter your site password when prompted.'));
				$htmlRepo = '';
				foreach ($repo_list as $repo_name) {
					$htmlRepo .= html_e('kbd', array(), 'hg clone '.$protocol.'://'.html_e('i', array(), _('developername'), true, false).'@'.$this->getBoxForProject($project).'/authscm/'.html_e('i', array(), _('developername'), true, false).'/hg/'.$project->getUnixName().'/'.$repo_name).html_e('br');
				}
				$b .= html_e('p', array(), $htmlRepo);
				$b .= '</div>';
			}
		}
		$b .= '</div>';
		return $b;
	}

	function getSnapshotPara($project) {
		$b = '';
		$filename = $project->getUnixName().'-scm-latest.tar'.util_get_compressed_file_extension();
		if (file_exists(forge_get_config('scm_snapshots_path').'/'.$filename)) {
			$b .= html_e('p', array(), '['.util_make_link('/snapshots.php?group_id='.$project->getID(), _('Download the nightly snapshot')).']');
		}
		return $b;
	}

	function getBrowserLinkBlock($project) {
		global $HTML;
		$b = html_e('h2', array(), _('Mercurial Repository Browser'));
		$b .= html_e('p', array(), _('Browsing the Mercurial tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.'));
		$b .= html_e('p', array(), '['.util_make_link('/scm/browser.php?group_id='.$project->getID().'&scm_plugin='.$this->name, _('Browse Hg Repository')).']');
		$repo_list = $this->getRepositories($project, false);
		foreach ($repo_list as $repo_name) {
			$b .= '['.util_make_link('/scm/browser.php?group_id='.$project->getID().'&extra='.$repo_name.'&scm_plugin='.$this->name, _('Browse extra Hg repository')._(': ').$repo_name).']'.html_e('br');
		}
		return $b;
	}

	function getStatsBlock($project) {
		global $HTML;
		$b = '';

		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(updates) as updates, sum(adds) as adds, sum(adds+commits) as combined, reponame FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id, reponame ORDER BY reponame, combined DESC, realname',
			array($project->getID()));

		if (db_numrows($result) > 0) {
			$tableHeaders = array(
			_('Name'),
			_('Adds'),
			_('Updates')
			);
			$b .= $HTML->listTableTop($tableHeaders, array(), '', 'repo-history-'.$this->name);

			$i = 0;
			$total = array('adds' => 0, 'updates' => 0);

			while($data = db_fetch_array($result)) {
				$cells = array();
				$cells[] = array(util_display_user($data['user_name'], $data['user_id'], $data['realname']), 'class' => 'halfwidth');
				$cells[] = array($data['adds'], 'class' => 'onequarterwidth align-right');
				$cells[] = array($data['updates'], 'class' => 'onequarterwidth align-right');
				$b .= $HTML->multiTableRow(array(), $cells);
				$total['adds'] += $data['adds'];
				$total['updates'] += $data['updates'];
				$i++;
			}
			$cells = array();
			$cells[] = array(html_e('strong', array(), _('Total')._(':')), 'class' => 'halfwidth');
			$cells[] = array($total['adds'], 'class' => 'onequarterwidth align-right');
			$cells[] = array($total['updates'], 'class' => 'onequarterwidth align-right');
			$b .= $HTML->multiTableRow(array(), $cells);
			$b .= $HTML->listTableBottom();
		} else {
			$b .= $HTML->warning_msg(_('No history yet.'));
		}

		return $b;
	}

	function printShortStats($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if (forge_check_perm('scm', $project->getID(), 'read')) {
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
		if ($params['scm_plugin'] != $this->name) {
			return;
		}
		global $HTML;
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if ($this->browserDisplayable($project)) {
			(isset($params['extra']) && $params['extra']) ? $extrarepo = $params['extra'] : $extrarepo = $project->getUnixName();

			$protocol = forge_get_config('use_ssl', 'scmhg')? 'https' : 'http';
			$box = $this->getBoxForProject($project);
                        if ($project->enableAnonSCM()) {
				$iframesrc = $protocol.'://'.$box.'/anonscm/scmhg/cgi-bin/'.$project->getUnixName().'/'.$extrarepo;
			} elseif (session_loggedin()) {
				$logged_user = user_get_object(user_getid())->getUnixName();
				$iframesrc = $protocol.'://'.$box.'/authscm/'.$logged_user.'/scmhg/cgi-bin/'.$project->getUnixName().'/'.$extrarepo.'/';
			}
			if ($params['commit']) {
				$iframesrc .= '/rev/'.$params['commit'];
			}
			htmlIframeResizer($iframesrc, array('id'=>'scmhg', 'absolute'=>true), array('minHeight' => 400));
		}
	}

	function createOrUpdateRepo($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}

		$project_name = $project->getUnixName();
		$unix_group_ro = $project_name . '_scmro';
		$unix_group_rw = $project_name . '_scmrw';

		$root = forge_get_config('repos_path', 'scmhg') . '/' . $project_name;
		if (!is_dir($root)) {
			system("mkdir -p $root");
			system("chgrp $unix_group_ro $root");
		}
		if ($project->enableAnonSCM()) {
			system("chmod 2755 $root");
		} else {
			system("chmod 2750 $root");
		}

		/** per project configuration for http **/
		//get template hgweb.cgi
		$hgweb = forge_get_config('source_path').'/plugins/scmhg/cgi-bin/hgweb.cgi';
		$project_hgweb = forge_get_config('source_path').'/www/plugins/scmhg/cgi-bin/'.$project_name;
		if (!is_file($project_hgweb)) {
			$lines = file($hgweb);
			$repo_config = "";
			foreach ($lines as $line) {
				if (preg_match("/\Aconfig = /",$line)) {
					$repo_config .= 'config = "'.$root.'/config"'."\n";
				} else {
					$repo_config .= $line;
				}
			}
			$f = fopen($project_hgweb, 'w');
			fwrite($f, $repo_config);
			fclose($f);
			$apache_user = forge_get_config('apache_user');
			$apache_group = forge_get_config('apache_group');
			system("chown $apache_user:$apache_group $project_hgweb");
			system("chmod 755 $project_hgweb");
		}
		if (!is_file("$root/config")) {
			$f = fopen("$root/config", 'w');
			$conf = "[paths]\n";
			$conf .= "/ = ".$root.'/*'."\n";
			fwrite($f, $conf);
			fclose($f);
		}
		if (!is_dir("$root/$project_name/.hg")) {
			system("hg init $root/$project_name");
			$f = fopen("$root/$project_name/.hg/hgrc", 'w');
			$conf = "[web]\n";
			$conf .= "baseurl = /hg/".$project_name."/".$project_name."\n";
			$conf .= "description = ".$project_name."\n";
			$conf .= "style = paper\n";
			$conf .= "allow_push = *\n"; // every user (see Apache configuration) is allowed to push
			$conf .= "allow_read = *\n"; // every user is allowed to clone and pull
			if (!forge_get_config('use_ssl', 'scmhg')) {
				$conf .= "push_ssl = 0\n";
			}
			fwrite($f, $conf);
			fclose($f);
			system("chgrp -R $unix_group_rw $root/$project_name");
			system("chmod -R g=rwX,o=rX $root/$project_name");
			system("chmod 660 $root/$project_name/.hg/hgrc");
		}

		// Create project-wide secondary repositories
		$result = db_query_params('SELECT repo_name, description, clone_url FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3',
						array($project->getID(),
						SCM_EXTRA_REPO_ACTION_UPDATE,
						$this->getID()));
		$rows = db_numrows($result);
		for ($i = 0; $i < $rows; $i++) {
			$repo_name = db_result($result, $i, 'repo_name');
			$description = db_result($result, $i, 'description');
			//no support for cloning from any URL, working dir...
			$repodir = $root.'/'.$repo_name;
			if (!is_dir("$repodir/.hg")) {
				system("hg init $repodir");
				$f = fopen("$repodir/.hg/hgrc", 'w');
				$conf = "[web]\n";
				$conf .= 'baseurl = /hg/'.$project_name.'/'.$repo_name."\n";
				$conf .= "description = ".$description."\n";
				$conf .= "style = paper\n";
				$conf .= "allow_push = *\n"; // every user (see Apache configuration) is allowed to push
				$conf .= "allow_read = *\n"; // every user is allowed to clone and pull
				if (!forge_get_config('use_ssl', 'scmhg')) {
					$conf .= "push_ssl = 0\n";
				}
				fwrite($f, $conf);
				fclose($f);
				system("chgrp -R $unix_group_rw $repodir");
				system("chmod -R g=rwX,o=rX $repodir");
				system("chmod 660 $repodir/.hg/hgrc");
			}
		}

		// Delete project-wide secondary repositories
		$result = db_query_params ('SELECT repo_name FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3',
					   array($project->getID(),
						  SCM_EXTRA_REPO_ACTION_DELETE,
						  $this->getID()));
		$rows = db_numrows ($result);
		for ($i = 0; $i < $rows; $i++) {
			$repo_name = db_result($result, $i, 'repo_name');
			$repodir = $root.'/'.$repo_name;
			if (util_is_valid_repository_name($repo_name)) {
				system("rm -rf $repodir");
			}
			db_query_params ('DELETE FROM scm_secondary_repos WHERE group_id=$1 AND repo_name=$2 AND next_action = $3 AND plugin_id=$4',
					 array($project->getID(),
						$repo_name,
						SCM_EXTRA_REPO_ACTION_DELETE,
						$this->getID()));
		}
	}

	function updateRepositoryList($params) {
		$groups = $this->getGroups();
		$unix_group = forge_get_config('apache_group');
		$unix_user = forge_get_config('apache_user');
		foreach ($groups as $project) {
			if (!$project->isActive()) continue;
			if (!$project->usesSCM()) continue;

			$repolist = $this->getRepositories($project);
			foreach ($repolist as $repo_name) {
				$push = "";
				$read = ""; /*pull,clone*/
				$path = forge_get_config('repos_path', 'scmhg').'/'.$project->getUnixName().'/'.$repo_name.'/.hg';
				$prevp = false;
				$prevr = false;
				$users = $project->getMembers();
				$pname = $project->getUnixName();
				foreach ($users as $user) {
					if (forge_check_perm_for_user($user, 'scm', $project->getID(), 'write')) {
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
					} elseif (forge_check_perm_for_user($user, 'scm', $project->getID(), 'read')) {
						if ($prevr){
							$read .= ", ";
						}
						$read .= $user->getUnixName();
						$prevr = true;
					}
				}
			}

			if ($project->enableAnonSCM()) {
				$read = "*";
			}

			/*make new hgrc file*/
			if (is_file($path.'/hgrc')) {
				$hgrc_val = parse_ini_file($path.'/hgrc', true);
				if (isset($hgrc_val['web'])) {
					$hgrc_val['web']['allow_read'] = $read;
					$hgrc_val['web']['allow_push'] = $push;
				}
				if (isset($hgrc_val['notify']['test'])) {
					/* Set the value again, because parse_ini_file() converts boolean values to "" or "1" .
					This would break the hgrc file.*/
					$hgrc_val['notify']['test'] = 'false';
				}
				if (isset($hgrc_val['notify']['template'])) {
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
				$hgrc .= "baseurl = /hg/".$project->getUnixName().'/'.$repo_name;
				$hgrc .= "\ndescription = ".$project->getUnixName().'/'.$repo_name;
				$hgrc .= "\nstyle = paper";
				$hgrc .= "\nallow_push = ".$push;
				$hgrc .= "\nallow_read = ".$read;
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

		$group_name = $project->getUnixName();
		$snapshot = forge_get_config('scm_snapshots_path').'/'.$group_name.'-scm-latest.tar'.util_get_compressed_file_extension();
		$tarball = forge_get_config('scm_tarballs_path').'/'.$group_name.'-scmroot.tar'.util_get_compressed_file_extension();

		if (!$project->enableAnonSCM()) {
			if (is_file($snapshot)) {
				unlink($snapshot);
			}
			if (is_file($tarball)) {
				unlink ($tarball) ;
			}
			return false;
		}

		// TODO: ideally we generate one snapshot per hg repository
		$toprepo = forge_get_config('repos_path', 'scmhg');
		$repo = $toprepo . '/' . $project->getUnixName().  $project->getUnixName();

		if (!is_dir($repo)) {
			if (is_file($snapshot)) {
				unlink($snapshot);
			}
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

		// since cronjobs are running as root, we need to trust apache user
		if (!is_file('/root/.hgrc')) {
			$trustdata = '[trusted]'.PHP_EOL.'users = '.forge_get_config('apache_user').PHP_EOL;
			$f = fopen('/root/.hgrc', 'w');
			fwrite($f, $trustdata);
			fclose($f);
		}

		if ($params['mode'] == 'day') {
			$year = $params['year'];
			$month = $params['month'];
			$day = $params['day'];
			$month_string = sprintf("%04d%02d", $year, $month);
			$start_time = gmmktime(0, 0, 0, $month, $day, $year);
			$end_time = $start_time + 86400;

			$repolist = $this->getRepositories($project);
			foreach ($repolist as $repo_name) {
				$this->gatherStatsRepo($project, $repo_name, $year, $month, $day);
			}
		}
	}

	function gatherStatsRepo($project, $repo_name, $year, $month, $day) {
		$month_string = sprintf("%04d%02d", $year, $month);
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
		$repo = forge_get_config('repos_path', 'scmhg').'/'.$project->getUnixName().'/'.$repo_name;
		if (!is_dir($repo) || !is_dir("$repo/.hg")) {
			// echo "No repository\n";
			db_rollback();
			return false;
		}
		// cleaning stats_cvs_* table for the current day
		$res = db_query_params('DELETE FROM stats_cvs_group WHERE month = $1 AND day = $2 AND group_id = $3 AND reponame = $4',
					array($month_string,
						$day,
						$project->getID(),
						$repo_name));
		if(!$res) {
			echo "Error while cleaning stats_cvs_group\n";
			db_rollback();
			return false;
		}

		$res = db_query_params('DELETE FROM stats_cvs_user WHERE month = $1 AND day = $2 AND group_id = $3 AND reponame = $4',
					array($month_string,
						$day,
						$project->getID(),
						$repo_name));
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
			if (!db_query_params('INSERT INTO stats_cvs_group (month, day, group_id, checkouts, commits, adds, updates, deletes, reponame)
							VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)',
						array($month_string,
							$day,
							$project->getID(),
							0,
							$commits,
							$adds,
							$updates,
							$deletes,
							$repo_name))) {
				echo "Error while inserting into stats_cvs_group\n";
				db_rollback();
				return false;
			}
		}

		// building the user list
		$user_list = array_unique(array_merge(array_keys($usr_adds), array_keys($usr_updates), array_keys($usr_deletes), array_keys($usr_commits)));

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
				if (!db_query_params('INSERT INTO stats_cvs_user (month, day, group_id, user_id, commits, adds, updates, deletes, reponame)
								VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)',
							array($month_string,
								$day,
								$project->getID(),
								$user_id,
								$uc,
								$ua,
								$uu,
								$ud,
								$repo_name))) {
					echo "Error while inserting into stats_cvs_user\n";
					db_rollback();
					return false;
				}
			}
		}
		db_commit();
	}

	function activity($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if (isset($params['exclusive_area']) && ($params['exclusive_area'] != $this->name)) {
			return false;
		}

		if (in_array('scmhg', $params['show']) || (count($params['show']) < 1)) {
			if ($project->enableAnonSCM()) {
				$server_script = '/anonscm/hglog';
			} elseif (session_loggedin()) {
				$u = session_get_user();
				$server_script = '/authscm/'.$u->getUnixName().'/hglog';
			} else {
				return false;
			}
			// Grab commit log
			$protocol = forge_get_config('use_ssl', 'scmhg') ? 'https://' : 'http://';
			$repo_list = $this->getRepositories($project);
			foreach ($repo_list as $repo_name) {
				$script_url = $protocol.$this->getBoxForProject($project)
					. $server_script
					.'?unix_group_name='.$project->getUnixName()
					.'&repo_name='.$repo_name
					.'&mode=date_range'
					.'&begin='.$params['begin']
					.'&end='.$params['end'];
				$filename = tempnam('/tmp', 'hglog');
				$f = fopen($filename, 'w');
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $script_url);
				curl_setopt($ch, CURLOPT_FILE, $f);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_COOKIE, @$_SERVER['HTTP_COOKIE']);  // for session validation
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);  // for session validation
				curl_setopt($ch, CURLOPT_HTTPHEADER,
							array('X-Forwarded-For: '.$_SERVER['REMOTE_ADDR']));  // for session validation
				$body = curl_exec($ch);
				if ($body === false) {
					$this->setError(curl_error($ch));
				}
				curl_close($ch);
				fclose($f); // flush buffer
				$f = fopen($filename, 'r');
				unlink($filename);

				while (!feof($f) && $data = fgets($f)) {
				$line = trim($data);
					$splitedLine = explode('||', $line);
					if (sizeof($splitedLine) == 4) {
						$result = array();
						$result['section'] = 'scm';
						$result['group_id'] = $project->getID();
						$result['ref_id'] = 'browser.php?group_id='.$project->getID().'&extra='.$repo_name.'&scm_plugin='.$this->name.'&commit='.$splitedLine[3];
						$result['description'] = htmlspecialchars($splitedLine[2]).' (repository: '.$repo_name.', changeset: '.$splitedLine[3].')';
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
		if (!in_array($this->name, $params['ids']) && ($project->enableAnonSCM() || session_loggedin())) {
			$params['ids'][] = $this->name;
			$params['texts'][] = _('Hg Commits');
		}
		return true;
	}

	function scm_add_repo(&$params) {
		if ($params['scm_plugin'] != $this->name) {
			return;
		}
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}

		if (!isset($params['repo_name'])) {
			return false;
		}

		if ($params['repo_name'] == $project->getUnixName()) {
			$params['error_msg'] = _('Cannot create a secondary repository with the same name as the primary');
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
		if (isset($params['description'])) {
			$description = $params['description'];
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

		return true;
	}

	function scm_admin_form(&$params) {
		global $HTML;
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}

		session_require_perm('project_admin', $params['group_id']);
		if (forge_get_config('allow_multiple_scm') && ($params['allow_multiple_scm'] > 1)) {
			echo html_ao('div', array('id' => 'tabber-'.$this->name, 'class' => 'tabbertab'));
		}

		$project_name = $project->getUnixName();
		$result = db_query_params('SELECT repo_name, description FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3 ORDER BY repo_name',
					  array($params['group_id'],
						 SCM_EXTRA_REPO_ACTION_UPDATE,
						 $this->getID()));
		if (!$result) {
			$params['error_msg'] = db_error();
			return false;
		}
		$existing_repos = array();
		while ($data = db_fetch_array($result)) {
			$existing_repos[] = array('repo_name' => $data['repo_name'],
						  'description' => $data['description']);
		}
		if (count($existing_repos) == 0) {
			echo $HTML->information(_('No extra Hg repository for project').' '.$project_name);
		} else {
			echo html_e('h2', array(), sprintf(ngettext('Extra Hg repository for project %1$s',
									'Extra Hg repositories for project %1$s',
									count($existing_repos)), $project_name));
			$titleArr = array(_('Repository name'), ('Initial repository description'), _('Delete'));
			echo $HTML->listTableTop($titleArr);
			foreach ($existing_repos as $key => $repo) {
				$cells = array();
				$cells[][] = html_e('kbd', array(), $repo['repo_name']);
				$cells[][] = $repo['description'];
				$deleteForm = $HTML->openForm(array('name' => 'form_delete_repo_'.$repo['repo_name'], 'action' => getStringFromServer('PHP_SELF'), 'method' => 'post'));
				$deleteForm .= html_e('input', array('type' => 'hidden', 'name' => 'group_id', 'value' => $params['group_id']));
				$deleteForm .= html_e('input', array('type' => 'hidden', 'name' => 'delete_repository', 'value' => 1));
				$deleteForm .= html_e('input', array('type' => 'hidden', 'name' => 'repo_name', 'value' => $repo['repo_name']));
				$deleteForm .= $HTML->html_input('scm_plugin_id', '', '', 'hidden', $this->getID());
				$deleteForm .= html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Delete')));
				$deleteForm .= $HTML->closeForm();
				$cells[][] = $deleteForm;
				echo $HTML->multiTableRow(array(), $cells);
			}
			echo $HTML->listTableBottom();
		}

		echo html_e('h2', array(), sprintf(_('Create new Hg repository for project %s'), $project_name));
		echo $HTML->openForm(array('name' => 'form_create_repo_scmhg', 'action' => getStringFromServer('PHP_SELF'), 'method' => 'post'));
		echo $HTML->html_input('group_id', '', '', 'hidden', $params['group_id']);
		echo $HTML->html_input('create_repository', '', '', 'hidden', 1);
		echo $HTML->html_input('scm_plugin', '', '', 'hidden', $this->name);
		echo $HTML->html_input('repo_name', '', html_e('strong', array(), _('Repository name')._(':')).utils_requiredField(), 'text', '', array('required' => 'required', 'size' => 20));
		echo html_e('br');
		echo $HTML->html_input('description', '', html_e('strong', array(), _('Description')._(':')), 'text', '', array('size' => 60));
		echo html_e('br');
		echo $HTML->html_input('cancel', '', '', 'submit', _('Cancel'), array(), array('style' => 'display: inline-block!important'));
		echo $HTML->html_input('submit', '', '', 'submit', _('Submit'), array(), array('style' => 'display: inline-block!important'));
		echo $HTML->closeForm();
		if ($project->usesPlugin('scmhook')) {
			$scmhookPlugin = plugin_get_object('scmhook');
			$scmhookPlugin->displayScmHook($project->getID(), $this->name);
		}
		if (forge_get_config('allow_multiple_scm') && ($params['allow_multiple_scm'] > 1)) {
			echo html_ac(html_ap() - 1);
		}
	}

	function getRepositories($group, $autoinclude = true) {
		$repoarr = array();
		if ($autoinclude) {
			$repoarr[] = $group->getUnixName();
		}
		$result = db_query_params('SELECT repo_name FROM scm_secondary_repos WHERE group_id = $1 AND next_action = $2 AND plugin_id = $3 ORDER BY repo_name',
						   array($group->getID(),
							  SCM_EXTRA_REPO_ACTION_UPDATE,
							  $this->getID()));
		while ($arr = db_fetch_array($result)) {
			$repoarr[] = $arr['repo_name'];
		}
		return $repoarr;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
