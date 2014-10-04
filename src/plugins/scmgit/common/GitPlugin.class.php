<?php
/**
 * FusionForge Git plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2009, Mehdi Dogguy <mehdi@debian.org>
 * Copyright 2012-2014, Franck Villaume - TrivialDev
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

forge_define_config_item('default_server', 'scmgit', forge_get_config('web_host'));
forge_define_config_item('repos_path', 'scmgit', forge_get_config('chroot').'/scmrepos/git');
forge_define_config_item('use_ssh', 'scmgit', false);
forge_set_config_item_bool('use_ssh', 'scmgit');
forge_define_config_item('use_dav', 'scmgit', true);
forge_set_config_item_bool('use_dav', 'scmgit');
forge_define_config_item('use_ssl', 'scmgit', true);
forge_set_config_item_bool('use_ssl', 'scmgit');

class GitPlugin extends SCMPlugin {
	function GitPlugin() {
		$this->SCMPlugin();
		$this->name = 'scmgit';
		$this->text = 'Git';
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
			$add_num    = db_result($result,0,'adds');
			if (!$commit_num) {
				$commit_num=0;
			}
			if (!$add_num) {
				$add_num=0;
			}
			echo ' (Git: '.sprintf(_('<strong>%1$s</strong> updates, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}

	function getBlurb() {
		return '<p>'
				. sprintf(_('Documentation for %1$s is available at <a href="%2$s">%2$s</a>.'),
							'Git',
							'http://git-scm.com/')
				. '</p>';
	}

	function getInstructionsForAnon($project) {
		$repo_list = array($project->getUnixName());
		$result = db_query_params('SELECT repo_name FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3 ORDER BY repo_name',
					   array($project->getID(),
						  SCM_EXTRA_REPO_ACTION_UPDATE,
						  $this->getID()));
		$rows = db_numrows($result);
		for ($i=0; $i<$rows; $i++) {
			$repo_list[] = db_result($result,$i,'repo_name');
		}

		$b = '<h2>' . ngettext('Anonymous Access to the Git repository',
				       'Anonymous Access to the Git repositories',
				       count($repo_list)) . '</h2>';

		$b .= '<p>';
		$b .= ngettext("This project's Git repository can be checked out through anonymous access with the following command.",
			       "This project's Git repositories can be checked out through anonymous access with the following commands.",
			       count($repo_list));

		$b .= '</p>';

		foreach ($repo_list as $repo_name) {
			$b .= '<p>';
			$b .= '<tt>git clone '.util_make_url('/anonscm/git/'.$project->getUnixName().'/'.$repo_name.'.git').'</tt><br />';
			$b .= '</p>';
		}

		$result = db_query_params('SELECT u.user_id, u.user_name, u.realname FROM scm_personal_repos p, users u WHERE p.group_id=$1 AND u.user_id=p.user_id AND u.unix_status=$2 AND plugin_id=$3',
					   array($project->getID(),
						  'A',
						  $this->getID()));
		$rows = db_numrows($result);

		if ($rows > 0) {
			$b .= '<h2>';
			$b .= ngettext("Developer's repository",
				       "Developer's repositories",
				       $rows);
			$b .= '</h2>'."\n";
			$b .= '<p>';
			$b .= ngettext("One of this project's members also has a personal Git repository that can be checked out anonymously.",
					"Some of this project's members also have personal Git repositories that can be checked out anonymously.",
				$rows);
			$b .= '</p>';
			$b .= '<p>';
			for ($i=0; $i<$rows; $i++) {
				$user_id = db_result($result,$i,'user_id');
				$user_name = db_result($result,$i,'user_name');
				$real_name = db_result($result,$i,'realname');
				$b .= '<tt>git clone '.util_make_url('/anonscm/git/'.$project->getUnixName().'/users/'.$user_name.'.git').'</tt> ('.util_make_link_u($user_name, $user_id, $real_name).') ['.util_make_link('/scm/browser.php?group_id='.$project->getID().'&user_id='.$user_id, _('Browse Git Repository')).']<br />';
			}
			$b .= '</p>';
		}

		return $b;
	}

	function getInstructionsForRW($project) {
		$repo_list = array($project->getUnixName());

		$result = db_query_params('SELECT repo_name FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3 ORDER BY repo_name',
					   array($project->getID(),
						  SCM_EXTRA_REPO_ACTION_UPDATE,
						  $this->getID()));
		$rows = db_numrows($result);
		for ($i=0; $i<$rows; $i++) {
			$repo_list[] = db_result($result,$i,'repo_name');
		}

		if (session_loggedin()) {
			$u = user_get_object(user_getid());
			$d = $u->getUnixName();
			if (forge_get_config('use_ssh', 'scmgit')) {
				$b = '<h2>';
				$b = ngettext('Developer Access to the Git repository via SSH',
						       'Developer Access to the Git repositories via SSH',
						       count($repo_list));
				$b .= '</h2>';
				$b .= '<p>';
				$b .= ngettext('Only project developers can access the Git repository via this method.',
					       'Only project developers can access the Git repositories via this method.',
					       count($repo_list));
				$b .= ' ';
				$b .= _('SSH must be installed on your client machine.');
				$b .= ' ';
				$b .= _('Enter your site password when prompted.');
				$b .= '</p>';
				foreach ($repo_list as $repo_name) {
					$b .= '<p><tt>git clone git+ssh://'.$d.'@' . $project->getSCMBox() . '/'. forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/'. $repo_name .'.git</tt></p>';
				}
			} elseif (forge_get_config('use_dav', 'scmgit')) {
				$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
				$b = '<h2>';
				$b = ngettext('Developer Access to the Git repository via HTTP',
						       'Developer Access to the Git repositories via HTTP',
						       count($repo_list));
				$b .= '</h2>';
				$b .= '<p>';
				$b .= ngettext('Only project developers can access the Git repository via this method.',
					       'Only project developers can access the Git repositories via this method.',
					       count($repo_list));
				$b .= ' ';
				$b .= _('Enter your site password when prompted.');
				$b .= '</p>';
				foreach ($repo_list as $repo_name) {
					$b .= '<p><tt>git clone '.$protocol.'://'.$d.'@' . $project->getSCMBox() . '/'. forge_get_config('scm_root', 'scmgit') .'/'. $project->getUnixName() .'/'. $repo_name .'.git</tt></p>';
				}
			}
		} else {
			if (forge_get_config('use_ssh', 'scmgit')) {
				$b = '<h2>';
				$b = ngettext('Developer Access to the Git repository via SSH',
						       'Developer Access to the Git repositories via SSH',
						       count($repo_list));
				$b .= '</h2>';
				$b .= '<p>';
				$b .= ngettext('Only project developers can access the Git repository via this method.',
					       'Only project developers can access the Git repositories via this method.',
					       count($repo_list));
				$b .= ' ';
				$b .= _('SSH must be installed on your client machine.');
				$b .= ' ';
				$b .= _('Substitute <em>developername</em> with the proper value.');
				$b .= ' ';
				$b .= _('Enter your site password when prompted.');
				$b .= '</p>';
				foreach ($repo_list as $repo_name) {
					$b .= '<p><tt>git clone git+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . '/'. forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/'. $repo_name .'.git</tt></p>';
				}
			} elseif (forge_get_config('use_dav', 'scmgit')) {
				$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
				$b = '<h2>';
				$b .= ngettext('Developer Access to the Git repository via HTTP',
						       'Developer Access to the Git repositories via HTTP',
						       count($repo_list));
				$b .= '</h2>';
				$b .= '<p>';
				$b .= ngettext('Only project developers can access the Git repository via this method.',
					       'Only project developers can access the Git repositories via this method.',
					       count($repo_list));
				$b .= ' ';
				$b .= _('Enter your site password when prompted.');
				$b .= '</p>';
				foreach ($repo_list as $repo_name) {
					$b .= '<p><tt>git clone '.$protocol.'://<i>'._('developername').'</i>@' . $project->getSCMBox() . '/'. forge_get_config('scm_root', 'scmgit') .'/'. $project->getUnixName() .'/'. $repo_name .'.git</tt></p>';
				}
			}
		}

		if (!isset($b)) {
			$b = '<h2>'._('Developer Git Access').'</h2>';
			$b .= '<p class="error">Error: No access protocol has been allowed for the Git plugin in scmgit.ini: : use_ssh and use_dav are disabled</p>';
		}

		if (session_loggedin() && forge_get_config('use_ssh', 'scmgit')) {
			$u = user_get_object(user_getid());
			if ($u->getUnixStatus() == 'A') {
				$result = db_query_params('SELECT * FROM scm_personal_repos p WHERE p.group_id=$1 AND p.user_id=$2 AND plugin_id=$3',
							  array($project->getID(),
								 $u->getID(),
								 $this->getID()));
				if ($result && db_numrows($result) > 0) {
					$b .= '<h2>';
					$b .= _('Access to your personal repository');
					$b .= '</h2>';
					$b .= '<p>';
					$b .= _('You have a personal repository for this project, accessible through SSH with the following method. Enter your site password when prompted.');
					$b .= '</p>';
					$b .= '<p><tt>git clone git+ssh://'.$u->getUnixName().'@' . $this->getBoxForProject($project) . '/'. forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/users/'. $u->getUnixName() .'.git</tt></p>';
				} else {
					$glist = $u->getGroups();
					foreach ($glist as $g) {
						if ($g->getID() == $project->getID()) {
							$b .= '<h2>';
							$b .= _('Request a personal repository');
							$b .= '</h2>';
							$b .= '<p>';
							$b .= _("You can clone the project repository into a personal one into which you alone will be able to write.  Other members of the project will only have read access.  Access for non-members will follow the same rules as for the project's main repository.  Note that the personal repository may take some time before it is created (less than an hour in most situations).");
							$b .= '</p>';
							$b .= '<p>';
							$b .= sprintf(_('<a href="%s">Request a personal repository</a>.'),
								       util_make_url('/plugins/scmgit/index.php?func=request-personal-repo&group_id='.$project->getID()));
							$b .= '</p>';
						}
					}
				}
			}
		}
		return $b;
	}

	function getSnapshotPara($project) {

		$b = "";
		$filename = $project->getUnixName().'-scm-latest.tar'.util_get_compressed_file_extension();
		if (file_exists(forge_get_config('scm_snapshots_path').'/'.$filename)) {
			$b .= '<p>[';
			$b .= util_make_link("/snapshots.php?group_id=".$project->getID(),
					      _('Download the nightly snapshot')
				);
			$b .= ']</p>';
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

		if ($project->usesPlugin($this->name)) {
			if ($params['user_id']) {
				$user = user_get_object($params['user_id']);
				echo $project->getUnixName().'/users/'.$user->getUnixName();
				print '<iframe id="scm_iframe" src="'.util_make_url("/plugins/scmgit/cgi-bin/gitweb.cgi?p=".$project->getUnixName().'/users/'.$user->getUnixName().'.git').'" frameborder="0" width=100% ></iframe>';
				$useautoheight = 1;
			} elseif ($this->browserDisplayable($project)) {
				print '<iframe id="scm_iframe" src="'.util_make_url("/plugins/scmgit/cgi-bin/gitweb.cgi?p=".$project->getUnixName().'/'.$project->getUnixName().'.git').'" frameborder="0" width=100% ></iframe>';
				$useautoheight = 1;
			}
		}
		if ($useautoheight) {
			html_use_jqueryautoheight();
			echo $HTML->getJavascripts();
			echo '<script type="text/javascript">//<![CDATA[
				jQuery(\'#scm_iframe\').iframeAutoHeight({heightOffset: 50});
				jQuery(\'#scm_iframe\').load(function (){
						if (this.contentWindow.location.href == "'.util_make_url('/projects/'.$project->getUnixName()).'/") {
							console.log(this.contentWindow.location.href);
							window.location.href = this.contentWindow.location.href;
						};
					});
				//]]></script>';
		}
	}

	function getBrowserLinkBlock($project) {
		global $HTML;
		$b = $HTML->boxMiddle(_('Git Repository Browser'));
		$b .= '<p>';
		$b .= _("Browsing the Git tree gives you a view into the current status of this project's code. You may also view the complete histories of any file in the repository.");
		$b .= '</p>';
		$b .= '<p>[';
		$b .= util_make_link("/scm/browser.php?group_id=".$project->getID(),
				      _('Browse Git Repository')
			);
		$b .= ']</p>';
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

			while($data = db_fetch_array($result)) {
				$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
				$b .= '<td class="halfwidth">';
				$b .= util_make_link_u($data['user_name'], $data['user_id'], $data['realname']);
				$b .= '</td><td class="onequarterwidth align-right">'.$data['adds']. '</td>'.
					'<td class="onequarterwidth align-right">'.$data['updates'].'</td></tr>';
				$total['adds'] += $data['adds'];
				$total['updates'] += $data['updates'];
				$i++;
			}
			$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
			$b .= '<td class="halfwidth"><strong>'._('Total').':</strong></td>'.
				'<td class="onequarterwidth align-right"><strong>'.$total['adds']. '</strong></td>'.
				'<td class="onequarterwidth align-right"><strong>'.$total['updates'].'</strong></td>';
			$b .= '</tr>';
			$b .= $HTML->listTableBottom();
		}

		return $b;
	}

	static function createUserRepo($params) {
		$project = $params['project'];
		$project_name = $project->getUnixName();
		$user_name = $params['user_name'];
		$unix_group = $params['unix_group'];
		$main_repo = $params['main_repo'];
		$root = $params['root'];

		$repodir = $root . '/users/' .  $user_name . '.git';
		chgrp($repodir, $unix_group);
		if ($project->enableAnonSCM()) {
			chmod($repodir, 02755);
		} else {
			chmod($repodir, 02750);
		}
		if (!is_file("$repodir/HEAD") && !is_dir("$repodir/objects") && !is_dir("$repodir/refs")) {
			// 'cd $root' because git will abort if e.g. we're in a 0700 /root after setuid
			system("cd $root; LC_ALL=C git clone --bare --quiet --no-hardlinks $main_repo $repodir 2>&1 >/dev/null | grep -v 'warning: You appear to have cloned an empty repository.' >&2");
			system("GIT_DIR=\"$repodir\" git update-server-info");
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

		$project_name = $project->getUnixName();
		$root = forge_get_config('repos_path', 'scmgit') . '/' . $project_name;
		if (!is_dir($root)) {
			system("mkdir -p $root");
		}
		$output = '';

		if (forge_get_config('use_ssh','scmgit')) {
			$unix_group = 'scm_' . $project_name;
		} else {
			$unix_group = forge_get_config('apache_group');
		}
		system("chgrp $unix_group $root");

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
			system("chgrp -R $unix_group $tmp_repo");
			system("chmod -R g+wX,o+rX-w $tmp_repo");
			if ($project->enableAnonSCM()) {
				system("chmod g+wX,o+rX-w $root");
			} else {
				system("chmod g+wX,o-rwx $root");
				system("chmod g+wX,o-rwx $tmp_repo");
			}
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
			system("find $main_repo -type d | xargs chmod g+s");
			if (forge_get_config('use_dav','scmgit')) {
				$f = fopen(forge_get_config('config_path').'/httpd.conf.d/plugin-scmgit-dav.inc','a');
				fputs($f,'<IfVersion >= 2.3>
  Use Project '.$project_name.'
</IfVersion>
<IfVersion < 2.3>
<Location "/scmrepos/git/'.$project_name.'">
        DAV on
        Options +Indexes -ExecCGI -FollowSymLinks -MultiViews
        AuthType Basic
        AuthName "Git repository: '.$project_name.'"
        #The AuthUserFile filename is needed in the code. Please do not rename it.
        AuthUserFile '.forge_get_config('data_path').'/gituser-authfile.'.$project_name.'
        Require valid-user
</Location>
</IfVersion>
');
				fclose($f);
				system(forge_get_config('httpd_reload_cmd','scmgit'));
			}
		}
		if (forge_get_config('use_ssh','scmgit')) {
			if ($project->enableAnonSCM()) {
				system("chmod g+wX,o+rX-w $root");
				system("chmod g+rwX,o+rX-w $main_repo");
			} else {
				system("chmod g+wX,o-rwx $root");
				system("chmod g+rwX,o-rwx $main_repo");
			}
		} else {
			$unix_user = forge_get_config('apache_user');
			system("chown $unix_user:$unix_group $main_repo");
			system("chmod g-rwx,o-rwx $main_repo");
		}

		// Create project-wide secondary repositories
		$result = db_query_params('SELECT repo_name, description, clone_url FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3',
					   array($project->getID(),
						  SCM_EXTRA_REPO_ACTION_UPDATE,
						  $this->getID()));
		$rows = db_numrows($result);
		for ($i=0; $i<$rows; $i++) {
			$repo_name = db_result($result,$i,'repo_name');
			$description = db_result($result,$i,'description');
			$clone_url = db_result($result,$i,'clone_url');
			$repodir = $root . '/' .  $repo_name . '.git';
			if (!is_file("$repodir/HEAD") && !is_dir("$repodir/objects") && !is_dir("$repodir/refs")) {
				if ($clone_url != '') {
					system("cd $root; LC_ALL=C git clone --quiet --bare $clone_url $repodir 2>&1 >/dev/null | grep -v 'warning: You appear to have cloned an empty repository.' >&2");
				} else {
					system("GIT_DIR=\"$repodir\" git init --quiet --bare --shared=group");
				}
				system("GIT_DIR=\"$repodir\" git update-server-info");
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
				system("chgrp -R $unix_group $repodir");
				system("chmod g+s $root");
				if ($project->enableAnonSCM()) {
					system("chmod -R g+wX,o+rX-w $main_repo");
				} else {
					system("chmod -R g+wX,o-rwx $main_repo");
				}
			}
		}

		// Delete project-wide secondary repositories
		$result = db_query_params ('SELECT repo_name FROM scm_secondary_repos WHERE group_id=$1 AND next_action = $2 AND plugin_id=$3',
					   array($project->getID(),
						  SCM_EXTRA_REPO_ACTION_DELETE,
						  $this->getID()));
		$rows = db_numrows ($result);
		for ($i=0; $i<$rows; $i++) {
			$repo_name = db_result($result,$i,'repo_name');
			$repodir = $root . '/' .  $repo_name . '.git';
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
			chgrp($root.'/users', $unix_group);
		}
		for ($i=0; $i<$rows; $i++) {
			$user_name = db_result($result,$i,'user_name');
			$repodir = $root . '/users/' .  $user_name . '.git';

			if (!is_dir($repodir) && mkdir ($repodir, 0700)) {
				chown ($repodir, $user_name);

				$params = array();
				$params['project'] = $project;
				$params['user_name'] = $user_name;
				$params['unix_group'] = $unix_group;
				$params['root'] = $root;
				$params['main_repo'] = $main_repo;

				util_sudo_effective_user($user_name,
							 array("GitPlugin","createUserRepo"),
							 $params);
			}
		}
		if (is_dir ("$root/users")) {
			if ($project->enableAnonSCM()) {
				system("chmod g+rX-w,o+rX-w $root/users");
			} else {
				system("chmod g+rX-w,o-rwx $root/users");
			}
		}
		$params['output'] = $output;
	}

	function updateRepositoryList($params) {
		$groups = $this->getGroups();
		$list = array();
		foreach ($groups as $project) {
			if ($this->browserDisplayable($project)) {
				$list[] = $project;
			}
		}

		$config_dir = forge_get_config('config_path').'/plugins/scmgit';
		if (!is_dir($config_dir)) {
			mkdir($config_dir, 0755, true);
		}
		$fname = $config_dir . '/gitweb.conf';
		$config_f = fopen($fname.'.new', 'w');
		$rootdir = forge_get_config('repos_path', 'scmgit');
		fwrite($config_f, "\$projectroot = '$rootdir';\n");
		fwrite($config_f, "\$projects_list = '$config_dir/gitweb.list';\n");
		fwrite($config_f, "@git_base_url_list = ('". util_make_url('/anonscm/git') . "');\n");
		fwrite($config_f, "\$logo = '". util_make_url('/plugins/scmgit/git-logo.png') . "';\n");
		fwrite($config_f, "\$favicon = '". util_make_url('/plugins/scmgit/git-favicon.png')."';\n");
		fwrite($config_f, "\$stylesheet = '". util_make_url('/plugins/scmgit/gitweb.css')."';\n");
		fwrite($config_f, "\$javascript = '". util_make_url('/plugins/scmgit/gitweb.js')."';\n");
		fwrite($config_f, "\$prevent_xss = 'true';\n");
		fwrite($config_f, "\$feature{'actions'}{'default'} = [('project home', '" .
		    util_make_url('/plugins/scmgit/?func=grouppage/%n') .
		    "', 'summary')];\n");
		fclose($config_f);
		chmod($fname.'.new', 0644);
		rename($fname.'.new', $fname);

		$fname = $config_dir . '/gitweb.list';
		$f = fopen($fname.'.new', 'w');

		$engine = RBACEngine::getInstance();
		foreach ($list as $project) {
			$repos = $this->getRepositories($rootdir . "/" .  $project->getUnixName());
			foreach ($repos as $repo) {
				$reldir = substr($repo, strlen($rootdir) + 1);
				fwrite($f, $reldir . "\n");
			}
			$users = $engine->getUsersByAllowedAction('scm',$project->getID(),'write');
			$password_data = '';
			foreach ($users as $user) {
				$password_data .= $user->getUnixName().':'.$user->getUnixPasswd()."\n";
			}
			$faname = forge_get_config('data_path').'/gituser-authfile.'.$project->getUnixName();
			$fa = fopen($faname.'.new', 'w');
			fwrite($fa, $password_data);
			fclose($fa);
			chmod($faname.'.new', 0644);
			rename($faname.'.new', $faname);
			$engine->invalidateRoleCaches();  // caching all roles takes ~1GB RAM for 5K projects/15K users
		}
		fclose($f);
		chmod($fname.'.new', 0644);
		rename($fname.'.new', $fname);
	}

	function getRepositories($path) {
		if (!is_dir($path)) {
			return array();
		}
		if (file_exists("$path/HEAD")) {
			return array($path);
		}
		$list = array();
		$entries = scandir($path);
		foreach ($entries as $entry) {
			if (($entry == ".") or ($entry == ".."))
				continue;
			$fullname = $path . "/" . $entry;
			if (is_dir($fullname)) {
				if (is_link($fullname))
					continue;
				$result = $this->getRepositories($fullname);
				$list = array_merge($list, $result);
			}
		}
		return $list;
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
			$year = $params ['year'];
			$month = $params ['month'];
			$day = $params ['day'];
			$month_string = sprintf( "%04d%02d", $year, $month );
			$start_time = gmmktime( 0, 0, 0, $month, $day, $year);
			$end_time = $start_time + 86400;

			$usr_adds    = array();
			$usr_updates = array();
			$usr_deletes = array();
			$usr_commits = array();

			$adds    = 0;
			$updates = 0;
			$deletes = 0;
			$commits = 0;

			$repo = forge_get_config('repos_path', 'scmgit') . '/' . $project->getUnixName() . '/' . $project->getUnixName() . '.git';
			if (!is_dir($repo) || !is_dir("$repo/refs")) {
				// echo "No repository $repo\n";
				return false;
			}

			$pipe = popen("GIT_DIR=\"$repo\" git log --since=@$start_time --until=@$end_time --all --pretty='format:%n%an <%ae>' --name-status 2>/dev/null", 'r' );

			db_begin();

			// cleaning stats_cvs_* table for the current day
			$res = db_query_params('DELETE FROM stats_cvs_group WHERE month=$1 AND day=$2 AND group_id=$3',
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

			$last_user    = "";
			while (!feof($pipe) && $data = fgets($pipe)) {
				$line = trim($data);
				// Drop bad UTF-8 - it's quite hard to make git output non-UTF-8
				// (e.g. by enforcing an unknown encoding) - but some users do!
				// and this makes PostgreSQL choke
				// this fix removes tabs in line. the regex used in short-commit stats line has been changed accordingly.
				$line = preg_replace('/[^(\x20-\x7F)]/','', $line);
				if (strlen($line) > 0) {
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
					} else {
						// Short-commit stats line
						$result = preg_match("/^(?P<mode>[AMD])(?P<file>.+)$/", $line, $matches);
						if (!$result) continue;
						if ($last_user == "") continue;
						if (!isset($usr_adds[$last_user])) $usr_adds[$last_user] = 0;
						if (!isset($usr_updates[$last_user])) $usr_updates[$last_user] = 0;
						if (!isset($usr_deletes[$last_user])) $usr_deletes[$last_user] = 0;
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

	function generateSnapshots($params) {

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
		$repo = $toprepo . '/' . $project->getUnixName() . '/' .  $project->getUnixName() . '.git';

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
		$today = date('Y-m-d');
		system("GIT_DIR=\"$repo\" git archive --format=tar --prefix=$group_name-scm-$today/ HEAD |".forge_get_config('compression_method')." > $tmp/snapshot");
		chmod("$tmp/snapshot", 0644);
		copy("$tmp/snapshot", $snapshot);
		unlink("$tmp/snapshot");

		system("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball");
		chmod("$tmp/tarball", 0644);
		copy("$tmp/tarball", $tarball);
		unlink("$tmp/tarball");
		system("rm -rf $tmp");
	}

	/**
	 * widgets - 'widgets' hook handler
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
			$repo = forge_get_config('repos_path', 'scmgit') . '/' . $project_name . '/' . $project_name .'.git';
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
			$start_time = $params['begin'];
			$end_time = $params['end'];
			$repo = forge_get_config('repos_path', 'scmgit') . '/' . $project->getUnixName() . '/' . $project->getUnixName() . '.git';
			$pipe = popen("GIT_DIR=\"$repo\" git log --date=raw --since=@$start_time --until=@$end_time --all --pretty='format:%ad||%ae||%s||%h' --name-status", 'r' );
			while (!feof($pipe) && $data = fgets($pipe)) {
				$line = trim($data);
				$splitedLine = explode('||', $line);
				if (sizeof($splitedLine) == 4) {
					$result = array();
					$result['section'] = 'scm';
					$result['group_id'] = $group_id;
					$result['ref_id'] = 'browser.php?group_id='.$group_id.'&commit='.$splitedLine[3];
					$result['description'] = htmlspecialchars($splitedLine[2]).' (commit '.$splitedLine[3].')';
					$userObject = user_get_object_by_email($splitedLine[1]);
					if (is_a($userObject, 'GFUser')) {
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
		$params['ids'][] = $this->name;
		$params['texts'][] = _('Git Commits');
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
			} elseif (preg_match('|^git://|', $url) || preg_match('|^https?://|', $url)) {
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
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		if (!$project->usesPlugin($this->name)) {
			return false;
		}

		session_require_perm('project_admin', $params['group_id']);

		$project_name = $project->getUnixName();

		$select_repo = '<select name="frontpage">' . "\n";
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
			printf('<h2>'._('No extra Git repository for project %1$s').'</h2>', $project_name);
		} else {
			$t = sprintf(ngettext('Extra Git repository for project %1$s',
					      'Extra Git repositories for project %1$s',
					      count($existing_repos)), $project_name);
			print '<h2>'.$t.'</h2>';
			print '<table><thead><tr><th>'._('Repository name').'</th><th>'._('Initial repository description').'</th><th>'._('Initial clone URL (if any)').'</th><th>'._('Delete').'</th></tr></thead><tbody>';
			foreach ($existing_repos as $repo) {
				print "<tr><td><tt>$repo[repo_name]</tt></td><td>$repo[description]</td><td>$repo[clone_url]</td><td>";
?>
<form name="form_delete_repo_<?php echo $repo['repo_name']?>"
	action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $params['group_id'] ?>" />
<input type="hidden" name="delete_repository" value="1" />
<input type="hidden" name="repo_name" value="<?php echo $repo['repo_name']?>" />
<input type="hidden" name="scm_enable_anonymous" value="<?php if ($project->enableAnonSCM()) echo 1 ; else echo 0 ?>" />
<input type="submit" name="submit" value="<?php echo _('Delete') ?>" />
</form>
<?php
				print "</td></tr>\n";
			}
			print '</tbody></table>';
		}

		printf('<h2>'._('Create new Git repository for project %1$s').'</h2>', $project_name);

		?>
<form name="form_create_repo"
	action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $params['group_id'] ?>" />
<input type="hidden" name="create_repository" value="1" />
<p><strong><?php echo _('Repository name')._(':'); ?></strong><?php echo utils_requiredField(); ?><br />
<input type="text" required="required" size="20" name="repo_name" value="" /></p>
<p><strong><?php echo _('Description:'); ?></strong><br />
<input type="text" size="60" name="description" value="" /></p>
<p><strong><?php echo _('Initial clone URL (or name of an existing repository in this project; leave empty to start with an empty repository):') ?></strong><br />
<input type="text" size="60" name="clone" value="<?php echo $project_name; ?>" /></p>
<input type="hidden" name="scm_enable_anonymous" value="<?php if ($project->enableAnonSCM()) echo 1 ; else echo 0 ?>" />
<input type="submit" name="cancel" value="<?php echo _('Cancel') ?>" />
<input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
</form>

		<?php
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
