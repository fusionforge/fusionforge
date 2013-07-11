<?php
/**
 * FusionForge Git plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2009, Mehdi Dogguy <mehdi@debian.org>
 * Copyright 2012, Franck Villaume - TrivialDev
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

forge_define_config_item('default_server', 'scmgit', forge_get_config ('web_host')) ;
forge_define_config_item('repos_path', 'scmgit', forge_get_config('chroot').'/scmrepos/git') ;

class GitPlugin extends SCMPlugin {
	function GitPlugin() {
		$this->SCMPlugin();
		$this->name = 'scmgit';
		$this->text = 'Git';
		$this->_addHook('scm_browser_page');
		$this->_addHook('scm_update_repolist');
		$this->_addHook('scm_generate_snapshots');
		$this->_addHook('scm_gather_stats');
		$this->_addHook('widget_instance', 'myPageBox', false);
		$this->_addHook('widgets', 'widgets', false);
		$this->register();
	}

	function getDefaultServer() {
		return forge_get_config('default_server', 'scmgit') ;
	}

	function printShortStats ($params) {
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
			echo ' (Git: '.sprintf(_('<strong>%1$s</strong> commits, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}

	function getBlurb() {
		return '<p>' . _('Documentation for Git is available at <a href="http://git-scm.com/">http://git-scm.com/</a>.') . '</p>';
	}

	function getInstructionsForAnon($project) {
		$b = '<h2>' . _('Anonymous Git Access') . '</h2>';
		$b .= '<p>';
		$b .= _('This project\'s Git repository can be checked out through anonymous access with the following command.');
		$b .= '</p>';

		$b .= '<p>' ;
		$b .= '<tt>git clone '.util_make_url ('/anonscm/git/'.$project->getUnixName().'/'.$project->getUnixName().'.git').'</tt><br />';
		$b .= '</p>';

		$result = db_query_params('SELECT u.user_id, u.user_name, u.realname FROM plugin_scmgit_personal_repos p, users u WHERE p.group_id=$1 AND u.user_id=p.user_id AND u.unix_status=$2',
					   array ($project->getID(),
						  'A'));
		$rows = db_numrows($result);

		if ($rows > 0) {
			$b .= '<h2>';
			$b .= _('Developer\'s repository');
			$b .= '</h2>'."\n";
			$b .= '<p>';
			$b .= ngettext('One of this project\'s members also has a personal Git repository that can be checked out anonymously.',
					'Some of this project\'s members also have personal Git repositories that can be checked out anonymously.',
				$rows);
			$b .= '</p>';
			$b .= '<p>' ;
			for ($i=0; $i<$rows; $i++) {
				$user_id = db_result($result,$i,'user_id');
				$user_name = db_result($result,$i,'user_name');
				$real_name = db_result($result,$i,'realname');
				$b .= '<tt>git clone '.util_make_url('/anonscm/git/'.$project->getUnixName().'/users/'.$user_name.'.git').'</tt> ('.util_make_link_u ($user_name, $user_id, $real_name).')<br />';
			}
			$b .= '</p>';
		}

		return $b ;
	}

	function getInstructionsForRW($project) {

		if (session_loggedin()) {
			$u = user_get_object(user_getid());
			$d = $u->getUnixName();
			if (forge_get_config('use_ssh', 'scmgit')) {
				$b = '<h2>';
				$b .= _('Developer Git Access via SSH');
				$b .= '</h2>';
				$b .= '<p>';
				$b .= _('Only project developers can access the Git tree via this method. SSH must be installed on your client machine. Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>git clone git+ssh://'.$d.'@' . $this->getBoxForProject($project) . '/'. forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/'. $project->getUnixName() .'.git</tt></p>' ;
			} elseif (forge_get_config('use_dav', 'scmgit')) {
				$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
				$b = '<h2>';
				$b .= _('Developer Git Access via HTTP');
				$b .= '</h2>';
				$b .= '<p>';
				$b .= _('Only project developers can access the Git tree via this method. Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>git clone '.$protocol.'://'.$d.'@' . $this->getBoxForProject($project) . '/'. forge_get_config('scm_root', 'scmgit') .'/'. $project->getUnixName() .'/'. $project->getUnixName() .'.git</tt></p>' ;
			} else {
				$b = '<p class="warning">'._('Missing configuration for access in scmgit.ini : use_ssh and use_dav disabled').'</p>';
			}
		} else {
			if (forge_get_config('use_ssh', 'scmgit')) {
				$b = '<h2>';
				$b .= _('Developer Git Access via SSH');
				$b .= '</h2>';
				$b .= '<p>';
				$b .= _('Only project developers can access the Git tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper value. Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>git clone git+ssh://<i>'._('developername').'</i>@' . $this->getBoxForProject($project) . '/'. forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/'. $project->getUnixName() .'.git</tt></p>' ;
			} elseif (forge_get_config('use_dav', 'scmgit')) {
				$protocol = forge_get_config('use_ssl', 'scmgit')? 'https' : 'http';
				$b = '<h2>';
				$b .= _('Developer Git Access via HTTP');
				$b .= '</h2>';
				$b .= '<p>';
				$b .= _('Only project developers can access the Git tree via this method. Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>git clone '.$protocol.'://<i>'._('developername').'</i>@' . $this->getBoxForProject($project) . '/'. forge_get_config('scm_root', 'scmgit') .'/'. $project->getUnixName() .'/'. $project->getUnixName() .'.git</tt></p>' ;
			}
		}

		if (session_loggedin()) {
                        $u =& user_get_object(user_getid()) ;
			if ($u->getUnixStatus() == 'A') {
				$result = db_query_params('SELECT * FROM plugin_scmgit_personal_repos p WHERE p.group_id=$1 AND p.user_id=$2',
							   array ($project->getID(),
								  $u->getID()));
				if ($result && db_numrows ($result) > 0) {
					$b .= '<h2>';
					$b .= _('Access to your personal repository');
					$b .= '</h2>';
					$b .= '<p>';
					$b .= _('You have a personal repository for this project, accessible through SSH with the following method. Enter your site password when prompted.');
					$b .= '</p>';
					$b .= '<p><tt>git clone git+ssh://'.$u->getUnixName().'@' . $this->getBoxForProject($project) . '/'. forge_get_config('repos_path', 'scmgit') .'/'. $project->getUnixName() .'/users/'. $u->getUnixName() .'.git</tt></p>' ;
				} else {
					$glist = $u->getGroups();
					foreach ($glist as $g) {
						if ($g->getID() == $project->getID()) {
							$b .= '<h2>';
							$b .= _('Request a personal repository');
							$b .= '</h2>';
							$b .= '<p>';
							$b .= _('You can clone the project repository into a personal one into which you alone will be able to write.  Other members of the project will only have read access.  Access for non-members will follow the same rules as for the project\'s main repository.  Note that the personal repository may take some time before it is created (less than an hour in most situations).');
							$b .= '</p>';
							$b .= '<p>';
							$b .= sprintf (_('<a href="%s">Request a personal repository</a>.'),
								       util_make_url ('/plugins/scmgit/index.php?func=request-personal-repo&group_id='.$project->getID()));
							$b .= '</p>';
						}
					}
				}
			}
		}
		return $b;
	}

	function getSnapshotPara($project) {

		$b = "" ;
		$filename = $project->getUnixName().'-scm-latest.tar'.util_get_compressed_file_extension();
		if (file_exists(forge_get_config('scm_snapshots_path').'/'.$filename)) {
			$b .= '<p>[' ;
			$b .= util_make_link("/snapshots.php?group_id=".$project->getID(),
					      _('Download the nightly snapshot')
				);
			$b .= ']</p>';
		}
		return $b ;
	}

	function printBrowserPage($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}

		if ($project->usesPlugin($this->name)) {
			if ($this->browserDisplayable($project)) {
				print '<iframe src="'.util_make_url("/plugins/scmgit/cgi-bin/gitweb.cgi?p=".$project->getUnixName().'/'.$project->getUnixName().'.git').'" frameborder="0" width=100% height=700></iframe>' ;
			}
		}
	}

	function getBrowserLinkBlock($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Git Repository Browser'));
		$b .= '<p>';
		$b .= _('Browsing the Git tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.');
		$b .= '</p>';
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID(),
				      _('Browse Git Repository')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}

	function getStatsBlock ($project) {
		global $HTML ;
		$b = '' ;

		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
			array ($project->getID()));

		if (db_numrows($result) > 0) {
//			$b .= $HTML->boxMiddle(_('Repository Statistics'));

			$tableHeaders = array(
			_('Name'),
			_('Adds'),
			_('Updates')
			);
			$b .= $HTML->listTableTop($tableHeaders, false, '', 'repo-history');

			$i = 0;
			$total = array('adds' => 0, 'commits' => 0);

			while($data = db_fetch_array($result)) {
				$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
				$b .= '<td class="halfwidth">' ;
				$b .= util_make_link_u ($data['user_name'], $data['user_id'], $data['realname']) ;
				$b .= '</td><td class="onequarterwidth align-right">'.$data['adds']. '</td>'.
					'<td class="onequarterwidth align-right">'.$data['commits'].'</td></tr>';
				$total['adds'] += $data['adds'];
				$total['commits'] += $data['commits'];
				$i++;
			}
			$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
			$b .= '<td class="halfwidth"><strong>'._('Total').':</strong></td>'.
				'<td class="onequarterwidth align-right"><strong>'.$total['adds']. '</strong></td>'.
				'<td class="onequarterwidth align-right"><strong>'.$total['commits'].'</strong></td>';
			$b .= '</tr>';
			$b .= $HTML->listTableBottom();
		}

		return $b ;
	}

	static function createUserRepo($params) {
		$project = $params['project'];
		$project_name = $project->getUnixName();
		$user_name = $params['user_name'];
		$unix_group = $params['unix_group'];
		$main_repo = $params['main_repo'];
		$root = $params['root'];

		$repodir = $root . '/users/' .  $user_name . '.git' ;
		chgrp($repodir, $unix_group);
		if ($project->enableAnonSCM()) {
			chmod ($repodir, 02755);
		} else {
			chmod ($repodir, 02750);
		}
		if (!is_file ("$repodir/HEAD") && !is_dir("$repodir/objects") && !is_dir("$repodir/refs")) {
			system ("git clone --bare $main_repo $repodir") ;
			system ("GIT_DIR=\"$repodir\" git update-server-info") ;
			if (is_file ("$repodir/hooks/post-update.sample")) {
				rename ("$repodir/hooks/post-update.sample",
					"$repodir/hooks/post-update") ;
			}
			if (!is_file ("$repodir/hooks/post-update")) {
				$f = fopen ("$repodir/hooks/post-update","x+") ;
				fwrite ($f, "exec git-update-server-info\n") ;
				fclose ($f) ;
			}
			if (is_file ("$repodir/hooks/post-update")) {
				system ("chmod +x $repodir/hooks/post-update") ;
			}
			system("echo \"Git repository for user $user_name in project $project_name\" > $repodir/description");
		}
	}

	function createOrUpdateRepo($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		$project_name = $project->getUnixName();
		$root = forge_get_config('repos_path', 'scmgit') . '/' . $project_name ;
		system ("mkdir -p $root");
		$output = '';

		if (forge_get_config('use_ssh','scmgit')) {
			$unix_group = 'scm_' . $project_name ;
		} else {
			$unix_group = forge_get_config('apache_group');
		}
		system ("chgrp $unix_group $root") ;

		$main_repo = $root . '/' .  $project_name . '.git' ;
		if (!is_dir($main_repo) || (!is_file("$main_repo/HEAD") &&
		    !is_dir("$main_repo/objects") && !is_dir("$main_repo/refs"))) {
			$tmp_repo = util_mkdtemp('.git', $project_name);
			if ($tmp_repo == false) {
				return false;
			}
			$result = system ("GIT_DIR=\"$tmp_repo\" git init --bare --shared=group") ;
			$output .= join("<br />", array($result));
			$result = system ("GIT_DIR=\"$tmp_repo\" git update-server-info") ;
			$output .= join("<br />", array($result));
			if (is_file ("$tmp_repo/hooks/post-update.sample")) {
				rename ("$tmp_repo/hooks/post-update.sample",
					"$tmp_repo/hooks/post-update") ;
			}
			if (!is_file ("$tmp_repo/hooks/post-update")) {
				$f = fopen ("$tmp_repo/hooks/post-update") ;
				fwrite ($f, "exec git-update-server-info\n") ;
				fclose ($f) ;
			}
			if (is_file ("$tmp_repo/hooks/post-update")) {
				system ("chmod +x $tmp_repo/hooks/post-update") ;
			}
			system ("echo \"Git repository for $project_name\" > $tmp_repo/description") ;
			system ("find $tmp_repo -type d | xargs chmod g+s") ;
			system ("chgrp -R $unix_group $tmp_repo") ;
			system ("chmod -R g+wX,o+rX-w $tmp_repo") ;
			if ($project->enableAnonSCM()) {
				system ("chmod g+wX,o+rX-w $root") ;
			} else {
				system ("chmod g+wX,o-rwx $root") ;
				system ("chmod g+wX,o-rwx $tmp_repo") ;
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
		}
		if (forge_get_config('use_ssh','scmgit')) {
			if ($project->enableAnonSCM()) {
				system ("chmod g+wX,o+rX-w $root") ;
				system ("chmod g+rwX,o+rX-w $main_repo") ;
			} else {
				system ("chmod g+wX,o-rwx $root") ;
				system ("chmod g+rwX,o-rwx $main_repo") ;
			}
		} else {
			$unix_user = forge_get_config('apache_user');
			system ("chown $unix_user:$unix_group $main_repo") ;
			system ("chmod g-rwx,o-rwx $main_repo") ;
		}

		$result = db_query_params ('SELECT u.user_name FROM plugin_scmgit_personal_repos p, users u WHERE p.group_id=$1 AND u.user_id=p.user_id AND u.unix_status=$2',
					   array ($project->getID(),
						  'A')) ;
		$rows = db_numrows ($result) ;
		for ($i=0; $i<$rows; $i++) {
			system ("mkdir -p $root/users") ;
			$user_name = db_result($result,$i,'user_name');
			$repodir = $root . '/users/' .  $user_name . '.git' ;
			
			if (!is_dir($repodir) && mkdir ($repodir, 0700)) {
				chown ($repodir, $user_name) ;

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
				system ("chmod g+rX-w,o+rX-w $root/users") ;
			} else {
				system ("chmod g+rX-w,o-rwx $root/users") ;
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
		$fname = $config_dir . '/gitweb.conf' ;
		$config_f = fopen($fname.'.new', 'w') ;
		$rootdir = forge_get_config('repos_path', 'scmgit');
		fwrite($config_f, "\$projectroot = '$rootdir';\n");
		fwrite($config_f, "\$projects_list = '$config_dir/gitweb.list';\n");
		fwrite($config_f, "@git_base_url_list = ('". util_make_url('/anonscm/git') . "');\n");
		fwrite($config_f, "\$logo = '". util_make_url('/plugins/scmgit/git-logo.png') . "';\n");
		fwrite($config_f, "\$favicon = '". util_make_url('/plugins/scmgit/git-favicon.png')."';\n");
		fwrite($config_f, "\$stylesheet = '". util_make_url('/plugins/scmgit/gitweb.css')."';\n");
		fwrite($config_f, "\$javascript = '". util_make_url('/plugins/scmgit/gitweb.js')."';\n");
		fwrite($config_f, "\$prevent_xss = 'true';\n");
		fclose($config_f);
		chmod ($fname.'.new', 0644) ;
		rename ($fname.'.new', $fname) ;

		$fname = $config_dir . '/gitweb.list' ;

		$f = fopen ($fname.'.new', 'w');
		foreach ($list as $project) {
                        $repos = $this->getRepositories($rootdir . "/" .  $project->getUnixName());
                        foreach ($repos as $repo) {
                                $reldir = substr($repo, strlen($rootdir) + 1);
			        fwrite($f, $reldir . "\n");
                        }
		}
		fclose($f);
		chmod($fname.'.new', 0644);
		rename($fname.'.new', $fname);
	}

	function getRepositories($path) {
		if (! is_dir($path)) {
			return array();
		}
		$list = array();
		$entries = scandir($path);
		foreach ($entries as $entry) {
			$fullname = $path . "/" . $entry;
			if (($entry == ".") or ($entry == ".."))
				continue;
			if (is_dir($fullname)) {
				if (is_link($fullname))
					continue;
				$result = $this->getRepositories($fullname);
				$list = array_merge($list, $result);
			} else if ($entry == "HEAD") {
				$list[] = $path;
			}
		}
		return $list;
	}

	function gatherStats ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if ($params['mode'] == 'day') {
			$year = $params ['year'] ;
			$month = $params ['month'] ;
			$day = $params ['day'] ;
			$month_string = sprintf( "%04d%02d", $year, $month );
			$start_time = gmmktime( 0, 0, 0, $month, $day, $year);
			$end_time = $start_time + 86400;

			$usr_adds    = array () ;
			$usr_updates = array () ;
			$usr_deletes = array () ;

			$adds    = 0 ;
			$updates = 0 ;

			$repo = forge_get_config('repos_path', 'scmgit') . '/' . $project->getUnixName() . '/' . $project->getUnixName() . '.git';
			if (!is_dir ($repo) || !is_dir ("$repo/refs")) {
				// echo "No repository $repo\n" ;
				return false ;
			}

			$pipe = popen ("GIT_DIR=\"$repo\" git log --since=@$start_time --until=@$end_time --all --pretty='format:%n%an <%ae>' --name-status 2>/dev/null", 'r' ) ;

			db_begin();

			// cleaning stats_cvs_* table for the current day
			$res = db_query_params ('DELETE FROM stats_cvs_group WHERE month=$1 AND day=$2 AND group_id=$3',
						array ($month_string,
						       $day,
						       $project->getID())) ;
			if(!$res) {
				echo "Error while cleaning stats_cvs_group\n" ;
				db_rollback () ;
				return false ;
			}

			$last_user    = "";
			while (!feof($pipe) && $data = fgets ($pipe)) {
				$line = trim($data);
				if (strlen($line) > 0) {
					$result = preg_match("/^(?P<name>.+) <(?P<mail>.+)>/", $line, $matches);
					if ($result) {
						// Author line
						$last_user = $matches['name'];
						$user2email[$last_user] = strtolower($matches['mail']);
					} else {
						// Short-commit stats line
						preg_match("/^(?P<mode>[AM])\s+(?P<file>.+)$/", $line, $matches);
						if ($last_user == "") continue;
						if (!isset ($usr_adds[$last_user])) $usr_adds[$last_user] = 0;
						if (!isset ($usr_updates[$last_user])) $usr_updates[$last_user] = 0;
						if (!isset ($usr_deletes[$last_user])) $usr_deletes[$last_user] = 0;
						if ($matches['mode'] == 'A') {
							$usr_adds[$last_user]++;
							$adds++;
						} elseif ($matches['mode'] == 'M') {
							$usr_updates[$last_user]++;
							$updates++;
						} elseif ($matches['mode'] == 'D') {
							$usr_deletes[$last_user]++;
						}
					}
				}
			}

			// inserting group results in stats_cvs_groups
			if ($updates > 0 || $adds > 0) {
				if (!db_query_params ('INSERT INTO stats_cvs_group (month,day,group_id,checkouts,commits,adds) VALUES ($1,$2,$3,$4,$5,$6)',
						      array ($month_string,
							     $day,
							     $project->getID(),
							     0,
							     $updates,
							     $adds))) {
					echo "Error while inserting into stats_cvs_group\n" ;
					db_rollback () ;
					return false ;
				}
			}

			// building the user list
			$user_list = array_unique( array_merge( array_keys( $usr_adds ), array_keys( $usr_updates ) ) );

			foreach ( $user_list as $user ) {
				// Trying to get user id from user name or email
				$u = &user_get_object_by_name ($user) ;
				if ($u) {
					$user_id = $u->getID();
				} else {
					$res=db_query_params('SELECT user_id FROM users WHERE lower(realname)=$1 OR email=$2',
						array (strtolower($user), $user2email[$user]));
					if ($res && db_numrows($res) > 0) {
						$user_id = db_result($res,0,'user_id');
					} else {
						continue;
					}
				}

				$uu = isset ($usr_updates[$user]) ? $usr_updates[$user] : 0 ;
				$ua = isset ($usr_adds[$user]) ? $usr_adds[$user] : 0 ;
				if ($uu > 0 || $ua > 0) {
					if (!db_query_params ('INSERT INTO stats_cvs_user (month,day,group_id,user_id,commits,adds) VALUES ($1,$2,$3,$4,$5,$6)',
							      array ($month_string,
								     $day,
								     $project->getID(),
								     $user_id,
								     $uu,
								     $ua))) {
						echo "Error while inserting into stats_cvs_user\n" ;
						db_rollback () ;
						return false ;
					}
				}
			}
		}
		db_commit();
	}

	function generateSnapshots ($params) {

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		$group_name = $project->getUnixName() ;

		$snapshot = forge_get_config('scm_snapshots_path').'/'.$group_name.'-scm-latest.tar'.util_get_compressed_file_extension();
		$tarball = forge_get_config('scm_tarballs_path').'/'.$group_name.'-scmroot.tar'.util_get_compressed_file_extension();

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			if (is_file($snapshot)) {
				unlink ($snapshot) ;
			}
			if (is_file($tarball)) {
				unlink ($tarball) ;
			}
			return false;
		}

		// TODO: ideally we generate one snapshot per git repository
		$toprepo = forge_get_config('repos_path', 'scmgit') ;
		$repo = $toprepo . '/' . $project->getUnixName() . '/' .  $project->getUnixName() . '.git' ;

		if (!is_dir ($repo)) {
			if (is_file($snapshot)) {
				unlink ($snapshot) ;
			}
			if (is_file($tarball)) {
				unlink ($tarball) ;
			}
			return false ;
		}

		// Skip empty repo (no HEAD present in repository)
		$ref = trim(`GIT_DIR=$repo git symbolic-ref HEAD`);
		if (!file_exists($repo.'/'.$ref)) {
			return false;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		$today = date ('Y-m-d') ;
		system ("GIT_DIR=\"$repo\" git archive --format=tar --prefix=$group_name-scm-$today/ HEAD |".forge_get_config('compression_method')." > $tmp/snapshot");
		chmod ("$tmp/snapshot", 0644) ;
		copy ("$tmp/snapshot", $snapshot) ;
		unlink ("$tmp/snapshot") ;

		system ("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball") ;
		chmod ("$tmp/tarball", 0644) ;
		copy ("$tmp/tarball", $tarball) ;
		unlink ("$tmp/tarball") ;
		system ("rm -rf $tmp") ;
	}

	/**
	 * widgets - 'widgets' hook handler
	 * @param array $params
	 * @return boolean
	 */
	function widgets($params) {
 		require_once('common/widget/WidgetLayoutManager.class.php');
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
		require_once('common/widget/WidgetLayoutManager.class.php');
		if ($params['widget'] == 'plugin_scmgit_user_myrepositories') {
			require_once $gfplugins.$this->name.'/common/scmgit_Widget_MyRepositories.class.php';
			$params['instance'] = new scmgit_Widget_MyRepositories(WidgetLayoutManager::OWNER_TYPE_USER, $user->getId());
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
