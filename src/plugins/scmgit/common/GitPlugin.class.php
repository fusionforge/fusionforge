<?php
/**
 * FusionForge Git plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2009, Mehdi Dogguy <mehdi@debian.org>
 * Copyright 2012-2014,2016, Franck Villaume - TrivialDev
 * Copyright © 2013
 *	Thorsten Glaser <t.glaser@tarent.de>
 * http://fusionforge.org
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

forge_define_config_item('default_server', 'scmgit', forge_get_config('scm_host'));
forge_define_config_item('repos_path', 'scmgit', forge_get_config('chroot').'/scmrepos/git');
forge_define_config_item('use_ssh', 'scmgit', false);
forge_set_config_item_bool('use_ssh', 'scmgit');
forge_define_config_item('use_ssl', 'scmgit', true);
forge_set_config_item_bool('use_ssl', 'scmgit');

class GitPlugin extends SCMPlugin {
	function __construct() {
		parent::__construct();
		$this->name = 'scmgit';
		$this->text = _('Git');
		$this->pkg_desc =
_("This plugin contains the Git subsystem of FusionForge. It allows each
FusionForge project to have its own Git repository, and gives some
control over it to the project's administrator.");
		$this->_addHook('scm_browser_page');
		$this->_addHook('scm_update_repolist');
		$this->_addHook('scm_generate_snapshots');
		$this->_addHook('scm_gather_stats');
		$this->_addHook('scm_admin_form');
		$this->_addHook('scm_add_repo');
		$this->_addHook('scm_delete_repo');
		$this->_addHook('widget_instance', 'myPageBox', false);
		$this->_addHook('widgets', 'widgets', false);
		$this->_addHook('activity');
		$this->_addHook('weekly');
		$this->register();
	}

	function getDefaultServer() {
		return forge_get_config('default_server', 'scmgit');
	}

	function printShortStats($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return;
		}

		if ($project->usesPlugin($this->name) && forge_check_perm('scm', $project->getID(), 'read')) {
			$result = db_query_params('SELECT sum(updates) AS updates, sum(adds) AS adds FROM stats_cvs_group WHERE group_id=$1',
						array($project->getID()));
			$commit_num = db_result($result,0,'updates');
			$add_num = db_result($result, 0, 'adds');
			if (!$commit_num) {
				$commit_num = 0;
			}
			if (!$add_num) {
				$add_num = 0;
			}
			$params['result'] .= ' (Git: '.sprintf(_('<strong>%1$s</strong> updates, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).')';
		}
	}

	function getBlurb() {
		return html_e('p', array(), sprintf(_('Documentation for %1$s is available at <a href="%2$s">%2$s</a>.'),
							'Git',
							'http://git-scm.com/'));
	}

	function getInstructionsForAnon($project) {
		$repo_list = array($project->getUnixName());
		$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
		$result = db_query_params('SELECT repo_name FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3 ORDER BY repo_name',
					   array($project->getID(),
						  SCM_EXTRA_REPO_ACTION_UPDATE,
						  $this->getID()));
		$rows = db_numrows($result);
		for ($i=0; $i<$rows; $i++) {
			$repo_list[] = db_result($result,$i,'repo_name');
		}
		$clone_commands = array();
		foreach ($repo_list as $repo_name) {
			if (forge_get_config('use_smarthttp', 'scmgit')) {
				$clone_commands[] = 'git clone '.$protocol.'://'.forge_get_config('scm_host').'/anonscm/git/'.$project->getUnixName().'/'.$repo_name.'.git';
			}
		}

		$b = html_e('h2', array(), _('Anonymous Access'));

		$b .= html_e('p', array(),
			ngettext("This project's Git repository can be checked out through anonymous access with the following command.",
				"This project's Git repositories can be checked out through anonymous access with the following commands.",
				count($repo_list)));

		$htmlRepo = '';
		foreach ($clone_commands as $cmd) {
			$htmlRepo .= html_e('tt', array(), $cmd).html_e('br');;
		}
		$b .= html_e('p', array(), $htmlRepo);

		$result = db_query_params('SELECT u.user_id, u.user_name, u.realname FROM scm_personal_repos p, users u WHERE p.group_id=$1 AND u.user_id=p.user_id AND u.unix_status=$2 AND plugin_id=$3',
					   array($project->getID(),
						  'A',
						  $this->getID()));
		$rows = db_numrows($result);

		if ($rows > 0) {
			$b .= html_e('h3', array(),
					ngettext("Member repository",
						"Members repositories",
						$rows));
			$b .= html_e('p', array(),
					ngettext("One of this project's members also has a personal Git repository that can be checked out anonymously.",
						"Some of this project's members also have personal Git repositories that can be checked out anonymously.",
						$rows));
			$htmlRepo = '';
			for ($i=0; $i<$rows; $i++) {
				$user_id = db_result($result, $i, 'user_id');
				$user_name = db_result($result, $i, 'user_name');
				$real_name = db_result($result, $i, 'realname');
				$htmlRepo .= html_e('tt', array(), 'git clone '.$protocol.'://'.forge_get_config('scm_host').'/anonscm/git/'.$project->getUnixName().'/users/'.$user_name.'.git')
					. ' ('.util_make_link_u($user_name, $user_id, $real_name).')'
					. html_e('br');
			}
			$b .= html_e('p', array(), $htmlRepo);
		}

		return $b;
	}

	function getInstructionsForRW($project) {
		global $HTML;
		$repo_list = array($project->getUnixName());

		$result = db_query_params('SELECT repo_name FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3 ORDER BY repo_name',
					   array($project->getID(),
						  SCM_EXTRA_REPO_ACTION_UPDATE,
						  $this->getID()));
		$rows = db_numrows($result);
		for ($i=0; $i<$rows; $i++) {
			$repo_list[] = db_result($result, $i, 'repo_name');
		}

		$b = '';
		$b .= html_e('h2', array(), _('Developer Access'));
		$b .= html_e('p', array(),
				ngettext('Only project developers can access the Git repository via this method.',
				'Only project developers can access the Git repositories via this method.',
				count($repo_list)));
		$b .= '<div id="tabber">';
		$b .= '<ul>';
		if (forge_get_config('use_ssh', 'scmgit')) {
			$b .= '<li><a href="#tabber-ssh">'._('via SSH').'</a></li>';
			$configuration = 1;
		}
		if (forge_get_config('use_smarthttp', 'scmgit')) {
			$b .= '<li><a href="#tabber-smarthttp">'._('via "smart HTTP"').'</a></li>';
			$configuration = 1;
		}
		$b .= '</ul>';
		if (!isset($configuration)) {
			return $HTML->error_msg(_('Error')._(': ')._('No access protocol has been allowed for the Git plugin in scmgit.ini: use_ssh and use_smarthttp are disabled'));
		}
		if (session_loggedin()) {
			$u = user_get_object(user_getid());
			$d = $u->getUnixName();
			if (forge_get_config('use_ssh', 'scmgit')) {
				$b .= '<div id="tabber-ssh" class="tabbertab" >';
				$b .= html_e('p', array(), _('SSH must be installed on your client machine.'));
				$htmlRepo = '';
				foreach ($repo_list as $repo_name) {
						$htmlRepo .= html_e('tt', array(), 'git clone git+ssh://'.$d.'@' . forge_get_config('scm_host') . forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/'. $repo_name .'.git').html_e('br');
				}
				$b .= html_e('p', array(), $htmlRepo);
				$b .= '</div>';
			}
			if (forge_get_config('use_smarthttp', 'scmgit')) {
				$b .= '<div id="tabber-smarthttp" class="tabbertab" >';
				$b .= html_e('p', array(), _('Enter your site password when prompted.'));
				$htmlRepo = '';

				$protocol = forge_get_config('use_ssl', 'scmgit') ? 'https' : 'http';
				foreach ($repo_list as $repo_name) {
					$htmlRepo .= '<tt>git clone '.$protocol.'://'.$d.'@' . forge_get_config('scm_host').'/authscm/'.$d.'/git/'.$project->getUnixName() .'/'. $repo_name .'.git</tt><br />';
				}
				$b .= html_e('p', array(), $htmlRepo);
				$b .= '</div>';
			}
		} else {
			if (forge_get_config('use_ssh', 'scmgit')) {
				$b .= '<div id="tabber-ssh" class="tabbertab" >';
				$b .= html_e('p', array(),
					ngettext('Only project developers can access the Git repository via this method.',
						'Only project developers can access the Git repositories via this method.',
						count($repo_list)).
					' '. _('SSH must be installed on your client machine.').
					' '. _('Substitute <em>developername</em> with the proper value.'));
				$htmlRepo = '';
				foreach ($repo_list as $repo_name) {
					$htmlRepo .= html_e('tt', array(), 'git clone git+ssh://'.html_e('i', array(), _('developername'), true, false).'@' . forge_get_config('scm_host') . forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/'. $repo_name .'.git').html_e('br');
				}
				$b .= html_e('p', array(), $htmlRepo);
				$b .= '</div>';
			}
			if (forge_get_config('use_smarthttp', 'scmgit')) {
				$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
				$b .= '<div id="tabber-smarthttp" class="tabbertab" >';
				$b .= html_e('p', array(),
					ngettext('Only project developers can access the Git repository via this method.',
						'Only project developers can access the Git repositories via this method.',
						count($repo_list)).
					' '. _('Enter your site password when prompted.'));
				$htmlRepo = '';
				foreach ($repo_list as $repo_name) {
					$b .= '<tt>git clone '.$protocol.'://<i>'._('developername').'</i>@' . forge_get_config('scm_host').'/authscm/<i>'._('developername').'</i>/git/'.$project->getUnixName() .'/'. $repo_name .'.git</tt><br />';
				}
				$b .= html_e('p', array(), $htmlRepo);
				$b .= '</div>';
			}
		}
		$b .= '</div>';
		$b .= '<script type="text/javascript">//<![CDATA[
			jQuery(document).ready(function() {
				jQuery("#tabber").tabs();
			});
			//]]></script>';
		if (session_loggedin()) {
			$u = user_get_object(user_getid());
			if ($u->getUnixStatus() == 'A') {
				$result = db_query_params('SELECT * FROM scm_personal_repos p WHERE p.group_id=$1 AND p.user_id=$2 AND plugin_id=$3',
							  array($project->getID(),
								 $u->getID(),
								 $this->getID()));
				if ($result && db_numrows($result) > 0) {
					$b .= html_e('h3', array(), _('Access to your personal repository'));
					$b .= html_e('p', array(), _('You have a personal repository for this project, accessible through the following methods. Enter your site password when prompted.'));
					if (forge_get_config('use_ssh', 'scmgit')) {
						$b .= html_e('tt', array(), 'git clone git+ssh://'.$u->getUnixName().'@' . forge_get_config('scm_host') . forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/users/'. $u->getUnixName() .'.git').html_e('br');
					}
					if (forge_get_config('use_smarthttp', 'scmgit')) {
						$b .= html_e('tt', array(), 'git clone '.$protocol.'://'.$u->getUnixName().'@' . forge_get_config('scm_host').'/authscm/'.$u->getUnixName().'/git/'.$project->getUnixName() .'/users/'. $u->getUnixName() .'.git').html_e('br');
					}
				} else {
					$glist = $u->getGroups();
					foreach ($glist as $g) {
						if ($g->getID() == $project->getID()) {
							$b .= html_e('h3', array(), _('Request a personal repository'));
							$b .= html_e('p', array(), _("You can clone the project repository into a personal one into which you alone will be able to write.  Other members of the project will only have read access.  Access for non-members will follow the same rules as for the project's main repository.  Note that the personal repository may take some time before it is created (less than an hour in most situations)."));
							$b .= html_e('p', array(), util_make_link('/plugins/scmgit/index.php?func=request-personal-repo&group_id='.$project->getID(), _('Request a personal repository')));
						}
					}
				}
			}
		}
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

	function printBrowserPage($params) {
		global $HTML;
		$useautoheight = 0;
		$project = $this->checkParams($params);
		if (!$project) {
			return;
		}

		if ($this->browserDisplayable($project)) {
			if ($params['user_id']) {
				$repo_user = user_get_object($params['user_id']);
				$repo = $project->getUnixName().'/users/'.$repo_user->getUnixName().'.git';
			} else if ($params['extra']) {
				$repo = $project->getUnixName().'/'.$params['extra'].'.git';
			} else {
				$repo = $project->getUnixName().'/'.$project->getUnixName().'.git';
			}

			if ($project->enableAnonSCM()) {
				$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
				$box = forge_get_config('scm_host');
				$iframesrc = "$protocol://$box/anonscm/gitweb/?p=$repo";
			} elseif (session_loggedin()) {
				$logged_user = user_get_object(user_getid())->getUnixName();
				$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
				$box = forge_get_config('scm_host');
				$iframesrc = "$protocol://$box/authscm/$logged_user/gitweb/?p=$repo";
			}
			if ($params['commit']) {
				$iframesrc .= ';a=log;h='.$params['commit'];
			}
			htmlIframeResizer($iframesrc, array('id'=>'scmgit_iframe', 'absolute'=>true));
		}
	}

	function getBrowserLinkBlock($project) {
		$b = html_e('h2', array(), _('Git Repository Browser'));
		$b .= html_e('p', array(), _("Browsing the Git tree gives you a view into the current status"
									 . " of this project's code. You may also view the complete"
									 . " history of any file in the repository."));
		$b .= html_e('p', array(), '['.util_make_link('/scm/browser.php?group_id='.$project->getID(),
													  _('Browse main git repository')).']');

		# Extra repos
		$result = db_query_params('SELECT repo_name FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3 ORDER BY repo_name',
								  array($project->getID(),
										SCM_EXTRA_REPO_ACTION_UPDATE,
										$this->getID()));
		$rows = db_numrows($result);
		$repo_list = array();
		for ($i=0; $i<$rows; $i++) {
			$repo_list[] = db_result($result,$i,'repo_name');
		}
		foreach ($repo_list as $repo_name) {
			if (forge_get_config('use_smarthttp', 'scmgit')) {
				$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
				$b .= '['.util_make_link('/scm/browser.php?group_id='.$project->getID().'&extra='.$repo_name, _('Browse extra git repository')._(': ').$repo_name).']'.html_e('br');
			}
		}

		$result = db_query_params('SELECT u.user_id, u.user_name FROM scm_personal_repos p, users u WHERE p.group_id=$1 AND u.user_id=p.user_id AND u.unix_status=$2 AND plugin_id=$3',
								  array($project->getID(),
										'A',
										$this->getID()));
		$rows = db_numrows($result);
		for ($i=0; $i<$rows; $i++) {
			$user_id = db_result($result,$i,'user_id');
			$user_name = db_result($result,$i,'user_name');
			$b .= '['.util_make_link('/scm/browser.php?group_id='.$project->getID().'&user_id='.$user_id, _('Browse personal git repository')._(': ').$user_name).']'.html_e('br');
		}

		return $b;
	}

	function getStatsBlock($project) {
		global $HTML;
		$b = '';

		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(updates) as updates, sum(adds) as adds, sum(adds+updates) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (updates>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
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

			while ($data = db_fetch_array($result)) {
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

	/**
	 * Create user repository under non-privileged uid
	 **/
	static function createUserRepo($params) {
		$project = $params['project'];
		$project_name = $project->getUnixName();
		$user_name = $params['user_name'];
		$main_repo = $params['main_repo'];
		$root = $params['root'];

		// dir was already created by root
		$repodir = $root . '/users/' .  $user_name . '.git';
		chmod($repodir, 00755);
		if (!is_file("$repodir/HEAD") && !is_dir("$repodir/objects") && !is_dir("$repodir/refs")) {
			// 'cd $root' because git will abort if e.g. we're in a 0700 /root after setuid
			system("cd $root; LC_ALL=C git clone --bare --quiet --no-hardlinks $main_repo $repodir 2>&1 >/dev/null | grep -v 'warning: You appear to have cloned an empty repository.' >&2");
			system("GIT_DIR=\"$repodir\" git update-server-info");
			system("GIT_DIR=\"$repodir\" git config http.receivepack true");
			if (is_file("$repodir/hooks/post-update.sample")) {
				rename("$repodir/hooks/post-update.sample",
					"$repodir/hooks/post-update");
			}
			if (!is_file("$repodir/hooks/post-update")) {
				$f = fopen("$repodir/hooks/post-update","x+");
				fwrite($f, "exec git-update-server-info\n");
				fclose($f);
			}
			if (is_file("$repodir/hooks/post-update")) {
				system("chmod +x $repodir/hooks/post-update");
			}
			system("echo \"Git repository for user $user_name in project $project_name\" > $repodir/description");
			system("chmod -R go=rX $repodir");
		}
	}

	function createOrUpdateRepo($params) {
		$output = '';

		$project = $this->checkParams($params);
		if (!$project) return false;
		if (!$project->isActive()) return false;
		if (!$project->usesPlugin($this->name)) return false;

		$project_name = $project->getUnixName();
		$unix_group_ro = $project_name . '_scmro';
		$unix_group_rw = $project_name . '_scmrw';

		$root = forge_get_config('repos_path', 'scmgit') . '/' . $project_name;
		if (!is_dir($root)) {
			system("mkdir -p $root");
			system("chgrp $unix_group_ro $root");
		}
		if ($project->enableAnonSCM()) {
			system("chmod 2755 $root");
		} else {
			system("chmod 2750 $root");
		}

		// Create main repository
		$main_repo = $root . '/' .  $project_name . '.git';
		if (!is_dir($main_repo) || (!is_file("$main_repo/HEAD") &&
		    !is_dir("$main_repo/objects") && !is_dir("$main_repo/refs"))) {
			$tmp_repo = util_mkdtemp('.git', $project_name);
			if ($tmp_repo == false) {
				return false;
			}
			$result = '';
			exec("GIT_DIR=\"$tmp_repo\" git init --bare --shared=group", $result);
			$output .= join("<br />", $result);
			$result = '';
			exec("GIT_DIR=\"$tmp_repo\" git update-server-info", $result);
			exec("GIT_DIR=\"$tmp_repo\" git config http.receivepack true", $result);
			$output .= join("<br />", $result);
			if (is_file("$tmp_repo/hooks/post-update.sample")) {
				rename("$tmp_repo/hooks/post-update.sample",
					"$tmp_repo/hooks/post-update");
			}
			if (!is_file("$tmp_repo/hooks/post-update")) {
				$f = fopen("$tmp_repo/hooks/post-update", 'w');
				fwrite($f, "exec git-update-server-info\n");
				fclose($f);
			}
			if (is_file("$tmp_repo/hooks/post-update")) {
				system("chmod +x $tmp_repo/hooks/post-update");
			}
			system("echo \"Git repository for $project_name\" > $tmp_repo/description");
			system("find $tmp_repo -type d | xargs chmod g+s");
			system("chgrp -R $unix_group_rw $tmp_repo");
			system("chmod -R g=rwX,o=rX $tmp_repo");
			$ret = true;
			/*
			 * $main_repo can already exist, for example if it’s
			 * not a directory or doesn’t contain a HEAD file or
			 * an objects or refs subdirectory… move it out of
			 * the way in these cases
			 */
			system("if test -e $main_repo || test -h $main_repo; then d=\$(mktemp -d $main_repo.scmgit-moved.XXXXXXXXXX) && mv -f $main_repo \$d/; fi");
			/* here’s still a TOCTOU but we check $ret below */
			system("mv $tmp_repo $main_repo", $ret);
			if ($ret != 0) {
				return false;
			}
			system("echo \"Git repository for $project_name\" > $main_repo/description");
		}

		// Create project-wide secondary repositories
		$result = db_query_params('SELECT repo_name, description, clone_url FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3',
					   array($project->getID(),
						  SCM_EXTRA_REPO_ACTION_UPDATE,
						  $this->getID()));
		$rows = db_numrows($result);
		for ($i=0; $i<$rows; $i++) {
			$repo_name = db_result($result, $i, 'repo_name');
			$description = db_result($result, $i, 'description');
			$clone_url = db_result($result, $i, 'clone_url');
			// Clone URLs need to be validated to prevent a potential arbitrary command execution
			if (!preg_match('|^[-a-zA-Z0-9:./_]+$|', $clone_url)) {
				$clone_url = '';
			}
			$repodir = $root . '/' . $repo_name . '.git';
			if (!is_file("$repodir/HEAD") && !is_dir("$repodir/objects") && !is_dir("$repodir/refs")) {
				if ($clone_url != '') {
					system("cd $root; LC_ALL=C git clone --quiet --bare $clone_url $repodir 2>&1 >/dev/null | grep -v 'warning: You appear to have cloned an empty repository.' >&2");
				} else {
					system("GIT_DIR=\"$repodir\" git init --quiet --bare --shared=group");
				}
				system("GIT_DIR=\"$repodir\" git update-server-info");
				system("GIT_DIR=\"$repodir\" git config http.receivepack true");
				if (is_file("$repodir/hooks/post-update.sample")) {
					rename("$repodir/hooks/post-update.sample",
						"$repodir/hooks/post-update");
				}
				if (!is_file("$repodir/hooks/post-update")) {
					$f = fopen("$repodir/hooks/post-update", 'w');
					fwrite($f, "exec git-update-server-info\n");
					fclose($f);
				}
				if (is_file("$repodir/hooks/post-update")) {
					system("chmod +x $repodir/hooks/post-update");
				}
				$f = fopen("$repodir/description", "w");
				fwrite($f, $description."\n");
				fclose($f);
				system("chgrp -R $unix_group_rw $repodir");
				system("chmod -R g=rwX,o=rX $repodir");
			}
		}

		// Delete project-wide secondary repositories
		$result = db_query_params ('SELECT repo_name FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3',
					   array($project->getID(),
						  SCM_EXTRA_REPO_ACTION_DELETE,
						  $this->getID()));
		$rows = db_numrows ($result);
		for ($i=0; $i<$rows; $i++) {
			$repo_name = db_result($result, $i, 'repo_name');
			$repodir = $root . '/' . $repo_name . '.git';
			if (util_is_valid_repository_name($repo_name)) {
				system("rm -rf $repodir");
			}
			db_query_params ('DELETE FROM scm_secondary_repos WHERE group_id=$1 AND repo_name=$2 AND next_action = $3 AND plugin_id=$4',
					 array($project->getID(),
						$repo_name,
						SCM_EXTRA_REPO_ACTION_DELETE,
						$this->getID()));
		}

		// Create users' personal repositories
		$result = db_query_params ('SELECT u.user_name FROM scm_personal_repos p, users u WHERE p.group_id=$1 AND u.user_id=p.user_id AND u.unix_status=$2 AND plugin_id=$3',
					   array($project->getID(),
						  'A',
						  $this->getID()));
		$rows = db_numrows ($result);
		if (!is_dir($root.'/users')) {
			system("mkdir -p $root/users");
			chgrp("$root/users", 'root');  // make it clear group members don't have write access
			system("chmod 00755 $root/users");
		}
		for ($i=0; $i<$rows; $i++) {
			$user_name = db_result($result, $i, 'user_name');
			$repodir = $root . '/users/' . $user_name . '.git';

			if (!is_dir($repodir) && mkdir ($repodir, 0700)) {
				chown ($repodir, $user_name);
				chgrp ($repodir, 'root');  // make it clear group members don't have write access

				$params = array();
				$params['project'] = $project;
				$params['user_name'] = $user_name;
				$params['root'] = $root;
				$params['main_repo'] = $main_repo;

				util_sudo_effective_user($user_name,
							 array("GitPlugin", "createUserRepo"),
							 $params);
			}
		}
		$params['output'] = $output;
	}

	function updateRepositoryList($params) {
		$config_dir = forge_get_config('config_path').'/plugins/scmgit';
		if (!is_dir($config_dir)) {
			mkdir($config_dir, 0755, true);
		}
		$fname = $config_dir . '/gitweb.conf';
		$f = fopen($fname.'.new', 'w');
		$rootdir = forge_get_config('repos_path', 'scmgit');
		fwrite($f, "\$projectroot = '$rootdir';\n");
		fwrite($f, "\$projects_list = '$config_dir/gitweb.list';\n");
		fwrite($f, "\$anon_clone_url = '". util_make_url('/anonscm/git') . "';\n");
		fwrite($f, "\$logo = '". util_make_url('/plugins/scmgit/git-logo.png') . "';\n");
		fwrite($f, "\$favicon = '". util_make_url('/plugins/scmgit/git-favicon.png')."';\n");
		fwrite($f, "\$stylesheet = '". util_make_url('/plugins/scmgit/gitweb.css')."';\n");
		fwrite($f, "\$javascript = '". util_make_url('/plugins/scmgit/gitweb.js')."';\n");
		fwrite($f, "\$site_html_head_string = '<script type=\"text/javascript\" src=\"". util_make_url('/scripts/iframe-resizer/iframeResizer.contentWindow.js'). "\" />';\n");
		fwrite($f, "\$prevent_xss = 'true';\n");
		fwrite($f, "\$site_footer = '".forge_get_config('source_path')."/plugins/scmgit/www/gitweb_footer.html';\n");
		fwrite($f, "\$feature{'actions'}{'default'} = [('project home', '" .
		       util_make_url('/plugins/scmgit/?func=grouppage/%n') .
		       "', 'summary')];\n");

		fwrite($f, "\$per_request_config = sub {\n");

		fwrite($f, "push @git_base_url_list, qq,". util_make_url('/anonscm/git') .",;\n");

		$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
		if (forge_get_config('use_smarthttp', 'scmgit')) {
			fwrite($f, "if (defined \$ENV{ITKUID} && \$ENV{ITKUID} ne '".forge_get_config('apache_user')."') { push @git_base_url_list, qq,$protocol://\$ENV{ITKUID}\@".forge_get_config('scm_host')."/authscm/\$ENV{ITKUID}/git,; }\n");
		}

		if (forge_get_config('use_ssh', 'scmgit')) {
				fwrite($f, "if (defined \$ENV{ITKUID} && \$ENV{ITKUID} ne '".forge_get_config('apache_user')."') { push @git_base_url_list, qq,git+ssh://\$ENV{ITKUID}\@".forge_get_config('scm_host').forge_get_config('repos_path', 'scmgit').",; }\n");
		}

		fwrite($f, "};\n");

		fclose($f);
		chmod($fname.'.new', 0644);
		rename($fname.'.new', $fname);

		# Optimized gitweb.list generation
		# Useful to list all a project's repos: /gitweb?a=project_list;pf=project_name
		$fname = $config_dir . '/gitweb.list';
		$f = fopen($fname.'.new', 'w');
		$res = db_query_params("SELECT unix_group_name FROM groups
			JOIN group_plugin ON (groups.group_id=group_plugin.group_id)
			WHERE groups.status=$1 AND group_plugin.plugin_id=$2
			ORDER BY unix_group_name", array('A', $this->getID()));
		while ($arr = db_fetch_array($res)) {
			fwrite($f, $arr['unix_group_name'].'/'.$arr['unix_group_name'].".git\n");
		}
		$res = db_query_params("SELECT unix_group_name, repo_name
			FROM groups
			JOIN group_plugin ON (groups.group_id=group_plugin.group_id)
			JOIN scm_secondary_repos ON (groups.group_id=scm_secondary_repos.group_id)
			WHERE groups.status=$1 AND group_plugin.plugin_id=$2
			ORDER BY unix_group_name, repo_name", array('A', $this->getID()));
		while ($arr = db_fetch_array($res)) {
			fwrite($f, $arr['unix_group_name'].'/'.$arr['repo_name'].".git\n");
		}
		$res = db_query_params("SELECT unix_group_name, user_name
			FROM groups
			JOIN group_plugin ON (groups.group_id=group_plugin.group_id)
			JOIN scm_personal_repos ON (groups.group_id=scm_personal_repos.group_id)
			JOIN users ON (scm_personal_repos.user_id=users.user_id)
			WHERE groups.status=$1 AND group_plugin.plugin_id=$2 AND users.status=$3
			ORDER BY unix_group_name, user_name", array('A', $this->getID(), 'A'));
		while ($arr = db_fetch_array($res)) {
			fwrite($f, $arr['unix_group_name'].'/users/'.$arr['user_name'].".git\n");
		}
		fclose($f);
		chmod($fname.'.new', 0644);
		rename($fname.'.new', $fname);
	}

	function gatherStats($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}

		if (!$project->usesPlugin($this->name)) {
			return false;
		}

		if ($params['mode'] == 'day') {
			$year = $params['year'];
			$month = $params['month'];
			$day = $params['day'];
			$month_string = sprintf("%04d%02d", $year, $month);
			$start_time = gmmktime(0, 0, 0, $month, $day, $year);
			$end_time = $start_time + 86400;

			$usr_adds = array();
			$usr_updates = array();
			$usr_deletes = array();
			$usr_commits = array();

			$adds = 0;
			$updates = 0;
			$deletes = 0;
			$commits = 0;

			$repo = forge_get_config('repos_path', 'scmgit') . '/' . $project->getUnixName() . '/' . $project->getUnixName() . '.git';
			if (!is_dir($repo) || !is_dir("$repo/refs")) {
				// echo "No repository $repo\n";
				return false;
			}

			# For each commit, get committer full name and e-mail (respecting git .mailmap file),
			# and a list of files prefixed by their status (A/M/D)
			$pipe = popen("GIT_DIR=\"$repo\" git log --since=@$start_time --until=@$end_time --all --pretty='format:%n%aN <%aE>' --name-status 2>/dev/null", 'r' );

			db_begin();

			// cleaning stats_cvs_* table for the current day
			$res = db_query_params('DELETE FROM stats_cvs_group WHERE month=$1 AND day=$2 AND group_id=$3',
						array($month_string,
						       $day,
						       $project->getID()));
			if (!$res) {
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

			$last_user = "";
			while (!feof($pipe) && $data = fgets($pipe)) {
				$line = trim($data);
				// Replace bad UTF-8 with '?' - it's quite hard to make git output non-UTF-8
				// (e.g. with i18n.commitEncoding = unknown) - but some users do!
				// and this makes PostgreSQL choke (SQL> ERROR:  invalid byte sequence for encoding "UTF8": 0xf9)
				$line = mb_convert_encoding($line, 'UTF-8', 'UTF-8');
				if (strlen($line) > 0) {
					$result = preg_match("/^(?P<name>.+) <(?P<mail>.+)>/", $line, $matches);
					if ($result) {
						// Author line
						$last_user = $matches['name'];
						$user2email[$last_user] = $matches['mail'];
						if (!isset($usr_adds[$last_user])) {
							$usr_adds[$last_user] = 0;
							$usr_updates[$last_user] = 0;
							$usr_deletes[$last_user] = 0;
							$usr_commits[$last_user] = 0;
						}
						$commits++;
						$usr_commits[$last_user]++;
					} else {
						// Short-commit stats line
						$result = preg_match("/^(?P<mode>[AMD])\s+(?P<file>.+)$/", $line, $matches);
						if (!$result)
							continue;
						if ($last_user == "")
							continue;
						if (!isset($usr_adds[$last_user]))
							$usr_adds[$last_user] = 0;
						if (!isset($usr_updates[$last_user]))
							$usr_updates[$last_user] = 0;
						if (!isset($usr_deletes[$last_user]))
							$usr_deletes[$last_user] = 0;
						if ($matches['mode'] == 'A') {
							$usr_adds[$last_user]++;
							$adds++;
						} elseif ($matches['mode'] == 'M') {
							$usr_updates[$last_user]++;
							$updates++;
						} elseif ($matches['mode'] == 'D') {
							$usr_deletes[$last_user]++;
							$deletes++;
						}
					}
				}
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
			$user_list = array_unique(array_merge(array_keys($usr_adds), array_keys($usr_updates), array_keys($usr_deletes), array_keys($usr_commits)));

			foreach ($user_list as $user) {
				// Trying to get user id from user name or email
				$u = user_get_object_by_name($user);
				if ($u) {
					$user_id = $u->getID();
				} else {
					$res = db_query_params('SELECT user_id FROM users WHERE lower(realname)=$1 OR lower(email)=$2',
						array(strtolower($user), strtolower($user2email[$user])));
					if ($res && db_numrows($res) > 0) {
						$user_id = db_result($res, 0, 'user_id');
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

		if (!$project->usesPlugin($this->name)) {
			return false;
		}

		if (!$project->enableAnonSCM()) {
			if (is_file($snapshot)) {
				unlink($snapshot);
			}
			if (is_file($tarball)) {
				unlink($tarball);
			}
			return false;
		}

		// TODO: ideally we generate one snapshot per git repository
		$toprepo = forge_get_config('repos_path', 'scmgit');
		$repo = $toprepo . '/' . $project->getUnixName() . '/' . $project->getUnixName() . '.git';

		if (!is_dir($repo)) {
			if (is_file($snapshot)) {
				unlink($snapshot);
			}
			if (is_file($tarball)) {
				unlink($tarball);
			}
			return false;
		}

		// Skip empty repo (no HEAD present in repository)
		$ref = trim(`GIT_DIR=$repo git symbolic-ref HEAD`);
		if (!file_exists($repo.'/'.$ref)) {
			return false;
		}

		$tmp = trim(`mktemp -d`);
		if ($tmp == '') {
			return false;
		}
		if ($us) {
			$today = date('Y-m-d');
			system("GIT_DIR=\"$repo\" git archive --format=tar --prefix=$group_name-scm-$today/ HEAD |".forge_get_config('compression_method')." > $tmp/snapshot");
			chmod("$tmp/snapshot", 0644);
			copy("$tmp/snapshot", $snapshot);
			unlink("$tmp/snapshot");
		}
		if ($ut) {
			system("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball");
			chmod("$tmp/tarball", 0644);
			copy("$tmp/tarball", $tarball);
			unlink("$tmp/tarball");
			system("rm -rf $tmp");
		}
	}

	/**
	 * widgets - 'widgets' hook handler
	 *
	 * @param array $params
	 * @return boolean
	 */
	function widgets($params) {
		require_once 'common/widget/WidgetLayoutManager.class.php';
		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_GROUP) {
			$params['fusionforge_widgets'][] = 'plugin_scmgit_project_latestcommits';
		}
		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_USER) {
			$params['fusionforge_widgets'][] = 'plugin_scmgit_user_myrepositories';
		}
		return true;
	}

	/**
	 * Process the 'widget_instance' hook to create instances of the widgets
	 *
	 * @param array $params
	 */
	function myPageBox($params) {
		global $gfplugins;
		$user = UserManager::instance()->getCurrentUser();
		require_once 'common/widget/WidgetLayoutManager.class.php';
		if ($params['widget'] == 'plugin_scmgit_user_myrepositories') {
			require_once $gfplugins.$this->name.'/common/scmgit_Widget_MyRepositories.class.php';
			$params['instance'] = new scmgit_Widget_MyRepositories(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
	}

	function weekly(&$params) {
		$res = db_query_params('SELECT group_id FROM groups WHERE status=$1 AND use_scm=1 ORDER BY group_id DESC',
				array('A'));
		if (!$res) {
			$params['output'] .= 'ScmGit Plugin: Unable to get list of projects using SCM: '.db_error();
			return false;
		}

		$params['output'] .= 'ScmGit Plugin: Running "git gc --quiet" on '.db_numrows($res).' repositories.'."\n";
		while ($row = db_fetch_array($res)) {
			$project = group_get_object($row['group_id']);
			if (!$project || !is_object($project)) {
				continue;
			} elseif ($project->isError()) {
				continue;
			}
			if (!$project->usesPlugin($this->name)) {
				continue;
			}

			$project_name = $project->getUnixName();
			$repo = forge_get_config('repos_path', 'scmgit') . '/' . $project_name . '/' . $project_name . '.git';
			if (is_dir($repo)) {
				chdir($repo);
				$params['output'] .= $project_name.': '.`git gc --quiet 2>&1`;
			}
		}
	}

	function activity($params) {
		$group_id = $params['group'];
		$project = group_get_object($group_id);
		if (!$project->usesPlugin($this->name)) {
			return false;
		}
		if (in_array('scmgit', $params['show']) || (count($params['show']) < 1)) {
			if ($project->enableAnonSCM()) {
				$server_script = '/anonscm/gitlog';
			} elseif (session_loggedin()) {
				$u = session_get_user();
				$server_script = '/authscm/'.$u->getUnixName().'/gitlog';
			} else {
				return false;
			}
			// Grab commit log
			$protocol = forge_get_config('use_ssl', 'scmgit') ? 'https://' : 'http://';
			$script_url = $protocol . forge_get_config('scm_host')
				. $server_script
				.'?unix_group_name='.$project->getUnixName()
				.'&mode=date_range'
				.'&begin='.$params['begin']
				.'&end='.$params['end'];
			$filename = tempnam('/tmp', 'gitlog');
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
					$result['group_id'] = $group_id;
					$result['ref_id'] = 'browser.php?group_id='.$group_id.'&commit='.$splitedLine[3];
					$result['description'] = htmlspecialchars($splitedLine[2]).' (commit '.$splitedLine[3].')';
					$userObject = user_get_object_by_email($splitedLine[1]);
					if (is_a($userObject, 'FFUser')) {
						$result['realname'] = util_display_user($userObject->getUnixName(), $userObject->getID(), $userObject->getRealName());
					} else {
						$result['realname'] = '';
					}
					$splitedDate = explode(' ', $splitedLine[0]);
					$result['activity_date'] = $splitedDate[0];
					$result['subref_id'] = '';
					$params['results'][] = $result;
				}
			}
		}
		if (!in_array($this->name, $params['ids'])) {
			$params['ids'][] = $this->name;
			$params['texts'][] = _('Git Commits');
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
			$params['error_msg'] = _('Cannot create a secondary repository with the same name as the primary');
			return false;
		}

		if (!util_is_valid_repository_name($params['repo_name'])) {
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
			} elseif ((preg_match('|^git://|', $url) || preg_match('|^https?://|', $url))
				&& preg_match('|^[-a-zA-Z0-9:./_]+$|', $url)) {
				// External URLs: OK, but they need to be validated to prevent a potential arbitrary command execution
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
			$description = "Git repository $params[repo_name] for project ".$project->getUnixName();
		}

		$result = db_query_params('INSERT INTO scm_secondary_repos (group_id, repo_name, description, clone_url, plugin_id) VALUES ($1, $2, $3, $4, $5)',
					   array($params['group_id'],
						  $params['repo_name'],
						  $description,
						  $clone,
						  $this->getID()));
		if (!$result) {
			$params['error_msg'] = db_error();
			return false;
		}

		plugin_hook ("scm_admin_update", $params);
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
			echo $HTML->information(_('No extra Git repository for project').' '.$project_name);
		} else {
			echo html_e('h2', array(), sprintf(ngettext('Extra Git repository for project %1$s',
									'Extra Git repositories for project %1$s',
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

		echo html_e('h2', array(), _('Create new Git repository for project').' '.$project_name);
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

	function getCommits($project, $user = null, $nb_commits) {
		$commits = array();
		if ($project->usesPlugin($this->name) && forge_check_perm('scm', $project->getID(), 'read')) {
			// Grab&parse commit log
			$protocol = forge_get_config('use_ssl', 'scmgit') ? 'https://' : 'http://';
			$u = session_get_user();
			if ($project->enableAnonSCM())
				$server_script = '/anonscm/gitlog';
			else
				$server_script = '/authscm/'.$u->getUnixName().'/gitlog';
			if ($user) {
				$email = $user->getEmail();
				$realname = $user->getFirstName().' '.$user->getLastName();
				$userunixname = $user->getUnixName();
				$params = '&mode=latest_user'
					.'&email='.urlencode($email)
					.'&realname='.urlencode($realname)
					.'&user_name='.$userunixname;
			} else {
				$params = '&mode=latest';
			}
			$script_url = $protocol . forge_get_config('scm_host')
				. $server_script
				.'?unix_group_name='.$project->getUnixName()
				. $params
				.'&limit='.$nb_commits;
			$filename = tempnam('/tmp', 'gitlog');
			$f = fopen($filename, 'w');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $script_url);
			curl_setopt($ch, CURLOPT_FILE, $f);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']);  // for session validation
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

			$i = 0;
			while (!feof($f) && $data = fgets($f)) {
				$line = trim($data);
				$splitedLine = explode('||', $line);
				if (sizeof($splitedLine) == 4) {
					$commits[$i]['pluginName'] = $this->name;
					$commits[$i]['description'] = htmlspecialchars($splitedLine[2]);
					$commits[$i]['commit_id'] = $splitedLine[3];
					$splitedDate = explode(' ', $splitedLine[0]);
					$commits[$i]['date'] = $splitedDate[0];
					$i++;
				}
			}
		}
		return $commits;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
