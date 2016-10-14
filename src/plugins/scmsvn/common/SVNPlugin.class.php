<?php
/**
 * FusionForge Subversion plugin
 *
 * Copyright 2003-2010, Roland Mas, Franck Villaume
 * Copyright 2004, GForge, LLC
 * Copyright 2010, Alain Peyrat <aljeux@free.fr>
 * Copyright 2012-2014,2016, Franck Villaume - TrivialDev
 * Copyright 2013, French Ministry of National Education
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

forge_define_config_item('default_server', 'scmsvn', forge_get_config ('scm_host'));
forge_define_config_item('repos_path', 'scmsvn', forge_get_config('chroot').'/scmrepos/svn');
forge_define_config_item('serve_path', 'scmsvn', forge_get_config('repos_path'));
forge_define_config_item('use_ssh', 'scmsvn', false);
forge_set_config_item_bool('use_ssh', 'scmsvn');
forge_define_config_item('use_dav', 'scmsvn', true);
forge_set_config_item_bool('use_dav', 'scmsvn');
forge_define_config_item('use_ssl', 'scmsvn', true);
forge_set_config_item_bool('use_ssl', 'scmsvn');
forge_define_config_item('anonsvn_login','scmsvn', 'anonsvn');
forge_define_config_item('anonsvn_password','scmsvn', 'anonsvn');

class SVNPlugin extends SCMPlugin {
	function __construct() {
		parent::__construct();
		$this->name = 'scmsvn';
		$this->text = _('Subversion');
		$this->pkg_desc =
_("This plugin contains the Subversion subsystem of FusionForge. It allows
each FusionForge project to have its own Subversion repository, and gives
some control over it to the project's administrator.");
		$this->svn_root_fs = forge_get_config('repos_path',
											  $this->name);
		$this->svn_root_dav = '/svn';
		$this->_addHook('scm_browser_page');
		$this->_addHook('scm_update_repolist');
		$this->_addHook('scm_regen_apache_auth');
		$this->_addHook('scm_generate_snapshots');
		$this->_addHook('scm_gather_stats');
		$this->_addHook('activity');

		$this->provides['svn'] = true;

		$this->register();
	}

	function getDefaultServer() {
		return forge_get_config('default_server', 'scmsvn') ;
	}

	function printShortStats($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return;
		}

		if ($project->usesPlugin($this->name) && forge_check_perm('scm', $project->getID(), 'read')) {
			$result = db_query_params('SELECT sum(updates) AS updates, sum(adds) AS adds FROM stats_cvs_group WHERE group_id=$1',
						  array ($project->getID())) ;
			$commit_num = db_result($result,0,'updates');
			$add_num    = db_result($result,0,'adds');
			if (!$commit_num) {
				$commit_num=0;
			}
			if (!$add_num) {
				$add_num=0;
			}
			$params['result'] .= ' (Subversion: '.sprintf(_('<strong>%1$s</strong> updates, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}

	function getBlurb() {
		return '<p>'
				. sprintf(_('Documentation for %1$s is available at <a href="%2$s">%2$s</a>.'),
							'Subversion (“SVN”)',
							'http://svnbook.red-bean.com/')
				. '</p>';
	}

	function topModule($project) {
		// Check toplevel module presence
		$repo = 'file://' . forge_get_config('repos_path', $this->name).'/'.$project->getUnixName().'/';
		$res = array ();
		$module = 'trunk';
		if (!(exec("svn ls '$repo'", $res) && in_array($module.'/', $res))) {
			$module = '';
		}

		return '/'.$module;
	}

	function getInstructionsForAnon($project) {
		$b = '<h2>' . _('Anonymous Subversion Access') . '</h2>';
		$b .= '<p>';
		$b .= _("This project's SVN repository can be checked out through anonymous access with the following command(s).");
		$b .= '</p>';

		$b .= '<p>' ;
		$module = $this->topModule($project);
		if (forge_get_config('use_ssh', 'scmsvn')) {
			$b .= '<tt>svn checkout svn://'.forge_get_config('scm_host').$this->svn_root_fs.'/'.$project->getUnixName().$module.'</tt><br />';
		}
		if (forge_get_config('use_dav', 'scmsvn')) {
				$b .= '<p><tt>svn checkout http'.((forge_get_config('use_ssl', 'scmsvn')) ? 's' : '').'://'. forge_get_config('scm_host'). '/anonscm/svn/'.$project->getUnixName().$module.'</tt></p>' ;
		}
		$b .= '</p>';
		return $b;
	}

	function getInstructionsForRW($project) {
		$b = '';

		$module = $this->topModule($project);
		$b .= sprintf(_('Only project developers can access the %s tree via this method.'), 'Subversion');
		$b .= '<div id="tabber">';
		$b .= '<ul>';
		if (forge_get_config('use_ssh', 'scmsvn')) {
			$b .= '<li><a href="#tabber-ssh">'._('via SSH').'</a></li>';
			$configuration = 1;
		}
		if (forge_get_config('use_dav', 'scmsvn')) {
			$b .= '<li><a href="#tabber-dav">'._('via DAV').'</a></li>';
			$configuration = 1;
		}
		$b .= '</ul>';
		if (session_loggedin()) {
			$u = user_get_object(user_getid());
			$d = $u->getUnixName() ;
			if (forge_get_config('use_ssh', 'scmsvn')) {
				$b .= '<div id="tabber-ssh" class="tabbertab" >';
				$b .= '<p>';
				$b .= _('SSH must be installed on your client machine.');
				$b .= ' ';
				$b .= _('Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>svn checkout svn+ssh://'.$d.'@' . forge_get_config('scm_host') . $this->svn_root_fs .'/'. $project->getUnixName().$module.'</tt></p>' ;
				$b .= '</div>';
			}
			if (forge_get_config('use_dav', 'scmsvn')) {
				$b .= '<div id="tabber-dav" class="tabbertab" >';
				$b .= '<p>';
				$b .= _('Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>svn checkout --username '.$d.' http'.((forge_get_config('use_ssl', 'scmsvn')) ? 's' : '').'://'. forge_get_config('scm_host'). '/authscm/'.$d.'/svn/'.$project->getUnixName().$module.'</tt></p>' ;
				$b .= '</div>';
			}
		} else {
			if (forge_get_config('use_ssh', 'scmsvn')) {
				$b .= '<div id="tabber-ssh" class="tabbertab" >';
				$b .= '<p>';
				$b .= _('SSH must be installed on your client machine.');
				$b .= ' ';
				$b .= _('Substitute <em>developername</em> with the proper value.');
				$b .= ' ';
				$b .= _('Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>svn checkout svn+ssh://<i>'._('developername').'</i>@' . forge_get_config('scm_host') . $this->svn_root_fs .'/'. $project->getUnixName().$module.'</tt></p>' ;
				$b .= '</div>';
			}
			if (forge_get_config('use_dav', 'scmsvn')) {
				$b .= '<div id="tabber-dav" class="tabbertab" >';
				$b .= '<p>';
				$b .= _('Substitute <em>developername</em> with the proper value.');
				$b .= ' ';
				$b .= _('Enter your site password when prompted.');
				$b .= '</p>';
				$b .= '<p><tt>svn checkout --username <i>'._('developername').'</i> http'.((forge_get_config('use_ssl', 'scmsvn')) ? 's' : '').'://'. forge_get_config('scm_host'). '/authscm/<i>'._('developername').'</i>/svn/'.$project->getUnixName().$module.'</tt></p>' ;
				$b .= '</div>';
			}
		}
		$b .= '</div>';
		$b .= '<script type="text/javascript">//<![CDATA[
			jQuery(document).ready(function() {
				jQuery("#tabber").tabs();
			});
			//]]></script>';
		return $b;
	}

	function getSnapshotPara($project) {
		return ;
	}

	function getBrowserLinkBlock($project) {
		$b = html_e('h2', array(), _('Subversion Repository Browser'));
		$b .= '<p>';
		$b .= sprintf(_("Browsing the %s tree gives you a view into the current status of this project's code."), 'Subversion');
		$b .= ' ';
		$b .= _('You may also view the complete histories of any file in the repository.');
		$b .= '</p>';
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID(),
								sprintf(_('Browse %s Repository'), 'Subversion')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}

	function getStatsBlock($project) {
		global $HTML ;
		$b = '' ;

		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(updates) as updates, sum(adds) as adds, sum(adds+updates) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (updates>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
					  array ($project->getID()));

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

	function printBrowserPage($params) {
		global $HTML;
		$useautoheight = 0;
		$project = $this->checkParams($params);
		if (!$project) {
			return;
		}

		if ($project->usesPlugin($this->name)) {
			$iframe_src = '/scm/viewvc.php?root='.$project->getUnixName();
			if ($params['commit']) {
				$iframe_src .= '&view=rev&revision='.$params['commit'];
			}
			htmlIframe($iframe_src, array('id'=>'scmsvn_iframe'));
		}
	}

	function createOrUpdateRepo($params) {
		$project = $this->checkParams($params);
		if (!$project) return false;
		if (!$project->isActive()) return false;
		if (!$project->usesPlugin($this->name)) return false;

		$repo_prefix = forge_get_config('repos_path', 'scmsvn');
		if (!is_dir($repo_prefix) && !mkdir($repo_prefix, 0755, true)) {
			return false;
		}

		$repo = $repo_prefix . '/' . $project->getUnixName();

		if (!is_dir ($repo) || !is_file ("$repo/format")) {
			if (!mkdir($repo, 0700, true)) {
				return false;
			}
			$ret = 0;
			system ("svnadmin create $repo", $ret);
			if ($ret != 0) {
				return false;
			}
			system ("sed -i '/enable-rep-sharing = false/s/^. //' $repo/db/fsfs.conf") ;
			// dav/ and dav/activities.d directories are required by old svn clients (eg. svn 1.6.17 on ubuntu 12.04)
			if (!is_dir ("$repo/dav")) {
				mkdir("$repo/dav");
			}
			if (!is_dir ("$repo/dav/activities.d")) {
				mkdir("$repo/dav/activities.d");
			}
			system ("svn mkdir -m'Init' file:///$repo/trunk file:///$repo/tags file:///$repo/branches >/dev/null") ;
			system ("find $repo -type d -print0 | xargs -r -0 chmod g+s") ;
			// Allow read/write users to modify the SVN repository
			$rw_unix_group = $project->getUnixName() . '_scmrw';
			system("chgrp -R $rw_unix_group $repo");
			// Allow read-only users to enter the (top-level) directory
			$ro_unix_group = $project->getUnixName() . '_scmro';
			system("chgrp $ro_unix_group $repo");
			// open permissions to allow switching private/public easily
			// see after to restrict the top-level directory
			system ("chmod -R g+rwX,o+rX-w $repo") ;
		}

		if ($project->enableAnonSCM()) {
			system("chmod g+rX-w,o+rX-w $repo") ;
		} else {
			system("chmod g+rX-w,o-rwx $repo") ;
		}
	}

	function updateRepositoryList(&$params) {
	}

	function regenApacheAuth(&$params) {
		# Enable /authscm/$user/svn URLs
		$config_fname = forge_get_config('data_path').'/scmsvn-auth.inc';
		$config_f = fopen($config_fname.'.new', 'w');

		$res = db_query_params("SELECT login, passwd FROM nss_passwd WHERE status=$1", array('A'));
		while ($arr = db_fetch_array($res)) {
			fwrite($config_f, 'Use ScmsvnUser '.$arr['login']."\n");
		}

		fclose($config_f);
		chmod($config_fname.'.new', 0644);
		rename($config_fname.'.new', $config_fname);
	}

	function gatherStats($params) {
		global $last_user, $last_time, $last_tag, $time_ok, $start_time, $end_time,
			$adds, $deletes, $updates, $commits, $date_key,
			$usr_adds, $usr_deletes, $usr_updates, $usr_commits;

		$time_ok = true;

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
			$month_string = sprintf("%04d%02d", $year, $month);
			$start_time = gmmktime(0, 0, 0, $month, $day, $year);
			$end_time = $start_time + 86400;

			$adds    = 0;
			$updates = 0;
			$deletes = 0;
			$commits = 0;

			$usr_adds    = array();
			$usr_updates = array();
			$usr_deletes = array();
			$usr_commits = array();

			$repo = forge_get_config('repos_path', 'scmsvn') . '/' . $project->getUnixName();
			if (!is_dir ($repo) || !is_file ("$repo/format")) {
				db_rollback();
				return false;
			}

			$d1 = date('Y-m-d', $start_time - 150000);
			$d2 = date('Y-m-d', $end_time + 150000);

			$pipe = popen ("svn log file://$repo --xml -v -q -r '".'{'.$d2.'}:{'.$d1.'}'."' 2> /dev/null", 'r' ) ;

			// cleaning stats_cvs_* table for the current day
			$res = db_query_params('DELETE FROM stats_cvs_group WHERE month=$1 AND day=$2 AND group_id=$3',
						array($month_string,
						       $day,
						       $project->getID()));
			if(!$res) {
				echo "Error while cleaning stats_cvs_group\n" ;
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

			$xml_parser = xml_parser_create();
			xml_set_element_handler($xml_parser, "SVNPluginStartElement", "SVNPluginEndElement");
			xml_set_character_data_handler($xml_parser, "SVNPluginCharData");

			// Analyzing history stream
			while (!feof($pipe) &&
				$data = fgets ($pipe, 4096)) {
				if (!xml_parse ($xml_parser, $data, feof ($pipe))) {
					$this->setError("Unable to parse XML with error " .
					      xml_error_string(xml_get_error_code($xml_parser)) .
					      " on line " .
					      xml_get_current_line_number($xml_parser));
					db_rollback () ;
					return false ;
					break;
				}
			}

			xml_parser_free($xml_parser);

			// inserting group results in stats_cvs_groups
			if ($updates > 0 || $adds > 0 || $deletes > 0 || $commits > 0) {
				if (!db_query_params('INSERT INTO stats_cvs_group (month,day,group_id,checkouts,commits,adds,updates,deletes) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
						      array ($month_string,
							     $day,
							     $project->getID(),
							     0,
							     $commits,
							     $adds,
							     $updates,
							     $deletes))) {
					echo "Error while inserting into stats_cvs_group\n" ;
					db_rollback();
					return false;
				}
			}

			// building the user list
			$user_list = array_unique( array_merge( array_keys( $usr_adds ), array_keys( $usr_updates ),  array_keys( $usr_deletes ), array_keys( $usr_commits )) );

			foreach ( $user_list as $user ) {
				// trying to get user id from user name
				$u = user_get_object_by_name ($user) ;
				if ($u) {
					$user_id = $u->getID();
				} else {
					continue;
				}

				$uc = isset($usr_commits[$user]) ? $usr_commits[$user] : 0 ;
				$uu = isset($usr_updates[$user]) ? $usr_updates[$user] : 0 ;
				$ua = isset($usr_adds[$user]) ? $usr_adds[$user] : 0 ;
				$ud = isset($usr_deletes[$user]) ? $usr_deletes[$user] : 0 ;
				if ($uu > 0 || $ua > 0 || $uc > 0 || $ud > 0) {
					if (!db_query_params ('INSERT INTO stats_cvs_user (month,day,group_id,user_id,commits,adds, updates, deletes) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
							      array ($month_string,
								     $day,
								     $project->getID(),
								     $user_id,
								     $uc,
								     $ua,
								     $uu,
								     $ud))) {
						echo "Error while inserting into stats_cvs_user\n" ;
						db_rollback () ;
						return false ;
					}
				}
			}
			db_commit();
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

		if (! $project->usesPlugin($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			if (is_file($snapshot)) {
				unlink ($snapshot);
			}
			if (is_file($tarball)) {
				unlink ($tarball);
			}
			return false;
		}

		$toprepo = forge_get_config('repos_path', 'scmsvn');
		$repo = $toprepo . '/' . $project->getUnixName();

		if (!is_dir ($repo) || !is_file ("$repo/format")) {
			if (is_file($snapshot)) {
				unlink ($snapshot) ;
			}
			if (is_file($tarball)) {
				unlink ($tarball) ;
			}
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		$today = date ('Y-m-d') ;
		$dir = $project->getUnixName ()."-$today" ;
		system ("mkdir -p $tmp") ;
		$code = 0 ;
		system ("svn ls file://$repo/trunk > /dev/null 2> /dev/null", $code) ;
		if ($us) {
			if ($code == 0) {
				system ("cd $tmp ; svn export file://$repo/trunk $dir > /dev/null 2>&1") ;
				system ("tar cCf $tmp - $dir |".forge_get_config('compression_method')."> $tmp/snapshot") ;
				chmod ("$tmp/snapshot", 0644) ;
				copy ("$tmp/snapshot", $snapshot) ;
				unlink ("$tmp/snapshot") ;
				system ("rm -rf $tmp/$dir") ;
			} else {
				if (is_file($snapshot)) {
					unlink ($snapshot) ;
				}
			}
		}

		if ($ut) {
			system ("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball") ;
			chmod ("$tmp/tarball", 0644) ;
			copy ("$tmp/tarball", $tarball) ;
			unlink ("$tmp/tarball") ;
			system ("rm -rf $tmp") ;
		}
	}

	function activity($params) {
		global $last_user, $last_time, $last_tag, $time_ok, $start_time, $end_time,
			$adds, $deletes, $updates, $commits, $date_key,
			$messages, $last_message, $times, $revisions, $users, $xml_parser;
		$commits = 0;
		$adds = 0;
		$updates = 0;
		$deletes = 0;
		$users = array();
		$messages = array();
		$times = array();
		$revisions = array();

		$group_id = $params['group'];
		$project = group_get_object($group_id);
		if (! $project->usesPlugin($this->name)) {
			return false;
		}

		if (in_array('scmsvn', $params['show']) || (count($params['show']) < 1)) {
			$start_time = $params['begin'];
			$end_time = $params['end'];

			$xml_parser = xml_parser_create();
			xml_set_element_handler($xml_parser, "SVNPluginStartElement", "SVNPluginEndElement");
			xml_set_character_data_handler($xml_parser, "SVNPluginCharData");

			// Grab&parse commit log
			$protocol = forge_get_config('use_ssl', 'scmsvn') ? 'https://' : 'http://';
			if ($project->enableAnonSCM()) {
				$server_script = '/anonscm/svnlog';
			} else {
				$u = session_get_user();
				if ($u && !$u->isError()) {
					$server_script = '/authscm/'.$u->getUnixName().'/svnlog';
				} else {
					return false;
				}
			}
			$script_url = $protocol . forge_get_config('scm_host')
				. $server_script
				.'?unix_group_name='.$project->getUnixName()
				.'&mode=date_range'
				.'&begin='.$params['begin']
				.'&end='.$params['end'];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $script_url);
			curl_setopt($ch, CURLOPT_WRITEFUNCTION, 'curl2xml');
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

			// final checks
			if (!xml_parse($xml_parser, '', true))
				$this->setError('Unable to parse XML with error '
						   . xml_error_string(xml_get_error_code($xml_parser))
						   . ' on line ' . xml_get_current_line_number($xml_parser));
			xml_parser_free($xml_parser);

			if ($adds > 0 || $updates > 0 || $commits > 0 || $deletes > 0) {
				$i = 0;
				foreach ($messages as $message) {
					$result = array();
					$result['section'] = 'scm';
					$result['group_id'] = $group_id;
					$result['ref_id'] = 'browser.php?group_id='.$group_id;
					$result['description'] = htmlspecialchars($message).' (r'.$revisions[$i].')';
					$userObject = user_get_object_by_name($users[$i]);
					if (is_a($userObject, 'FFUser')) {
						$result['realname'] = util_display_user($userObject->getUnixName(), $userObject->getID(), $userObject->getRealName());
					} else {
						$result['realname'] = '';
					}
					$result['activity_date'] = $times[$i];
					$result['subref_id'] = '&commit='.$revisions[$i];
					$params['results'][] = $result;
					$i++;
				}
			}
		}
		if (!in_array($this->name, $params['ids'])) {
			$params['ids'][] = $this->name;
			$params['texts'][] = _('Subversion Commits');
		}
		return true;
	}

	// Get latest commits for inclusion in a widget
	function getCommits($project, $user = null, $nbCommits) {
		global $commits, $users, $adds, $updates, $messages, $times, $revisions, $deletes, $time_ok, $user_list, $last_message, $notimecheck, $xml_parser;
		$commits = 0;
		$users = array();
		$adds = 0;
		$updates = 0;
		$messages = array();
		$times = array();
		$revisions = array();
		$deletes = 0;
		$time_ok = false;
		$user_list = array();
		$last_message = '';
		$notimecheck = true;
		$revisionsArr = array();
		if ($project->usesPlugin($this->name) && forge_check_perm('scm', $project->getID(), 'read')) {
			$xml_parser = xml_parser_create();
			xml_set_element_handler($xml_parser, "SVNPluginStartElement", "SVNPluginEndElement");
			xml_set_character_data_handler($xml_parser, "SVNPluginCharData");

			// Grab&parse commit log
			$protocol = forge_get_config('use_ssl', 'scmsvn') ? 'https://' : 'http://';
			$u = session_get_user();
			if ($project->enableAnonSCM())
				$server_script = '/anonscm/svnlog';
			else
				$server_script = '/authscm/'.$u->getUnixName().'/svnlog';
			if ($user) {
				$userunixname = $user->getUnixName();
				$params = '&mode=latest_user&user_name='.$userunixname;
			} else {
				$params = '&mode=latest';
			}
			$script_url = $protocol . forge_get_config('scm_host')
				. $server_script
				.'?unix_group_name='.$project->getUnixName()
				. $params
				.'&limit='.$nbCommits;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $script_url);
			curl_setopt($ch, CURLOPT_WRITEFUNCTION, 'curl2xml');
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

			// final checks
			if (!xml_parse($xml_parser, '', true))
				$this->setError('Unable to parse XML with error '
						   . xml_error_string(xml_get_error_code($xml_parser))
						   . ' on line ' . xml_get_current_line_number($xml_parser));
			xml_parser_free($xml_parser);

			if ($adds > 0 || $updates > 0 || $commits > 0 || $deletes > 0) {
				$i = 0;
				foreach ($messages as $message) {
					if ($user && ($users[$i] == $userunixname)) {
						$revisionsArr[$i]['pluginName'] = 'scmsvn';
						$revisionsArr[$i]['description'] = htmlspecialchars($message);
						$revisionsArr[$i]['commit_id'] = $revisions[$i];
						$revisionsArr[$i]['date'] = $times[$i];
					} else {
						$revisionsArr[$i]['pluginName'] = 'scmsvn';
						$revisionsArr[$i]['description'] = htmlspecialchars($message);
						$revisionsArr[$i]['commit_id'] = $revisions[$i];
						$revisionsArr[$i]['date'] = $times[$i];
					}
					$i++;
				}
			}
		}
		return $revisionsArr;
	}
}

// End of class, helper functions now

function SVNPluginCharData($parser, $chars) {
	global $last_tag, $last_user, $last_time, $start_time, $end_time, $usr_commits, $commits,
		$time_ok, $user_list, $last_message, $messages, $times, $users, $notimecheck;
	switch ($last_tag) {
		case "AUTHOR": {
			$last_user = preg_replace('/[^a-z0-9_-]/', '', strtolower(trim($chars)));
			$users[] = $last_user;
			$usr_commits[$last_user] = isset($usr_commits[$last_user]) ? ($usr_commits[$last_user]+1) : 1 ;
			$commits++;
			break;
		}
		case "DATE": {
			$chars = preg_replace('/T(\d\d:\d\d:\d\d)\.\d+Z?$/', ' ${1}', $chars);
			$last_time = strtotime($chars);
			if ($start_time <= $last_time && $last_time < $end_time) {
				$time_ok = true;
			} else {
				$time_ok = false;
				if (!isset($notimecheck)) {
					if ($last_user !== '') // empty in e.g. tags from cvs2svn
						$usr_commits[$last_user]--;
					$commits--;
				}
			}
			$times[] = $last_time;
			break;
		}
		case "MSG": {
			if ($time_ok === true || isset($notimecheck)) {
				$messages[count($messages)-1] .= $chars;
			}
                        /* note: there may be more than one msg
			 * (happen when the message contain accents).
			 */
			break;
		}
	}
}

function SVNPluginStartElement($parser, $name, $attrs) {
	global $last_user, $last_time, $last_tag, $time_ok, $commits,
		$adds, $updates, $usr_adds, $usr_updates, $last_message, $messages, $times, $revisions, $deletes, $usr_deletes, $notimecheck;
	$last_tag = $name;
	switch($name) {
		case "LOGENTRY": {
			// Make sure we clean up before doing a new log entry
			$last_user = "";
			$last_time = "";
			$revisions[] = $attrs['REVISION'];
			break;
		}
		case "PATH": {
			if ($time_ok === true || isset($notimecheck)) {

				if ($attrs['ACTION'] == "M") {
					$updates++;
					if ($last_user) {
						$usr_updates[$last_user] = isset($usr_updates[$last_user]) ? ($usr_updates[$last_user]+1) : 1 ;
					}
				} elseif ($attrs['ACTION'] == "A") {
					$adds++;
					if ($last_user) {
						$usr_adds[$last_user] = isset($usr_adds[$last_user]) ? ($usr_adds[$last_user]+1) : 1 ;
					}
				} elseif ($attrs['ACTION'] == 'D') {
					$deletes++;
					if ($last_user) {
						$usr_deletes[$last_user] = isset($usr_deletes[$last_user]) ? ($usr_deletes[$last_user]+1) : 1 ;
					}
				}
			}
			break;
		}
                case "MSG": {
			if ($time_ok === true || isset($notimecheck)) {
				$messages[] = "";
			}
			break;
                }
	}
}

function SVNPluginEndElement($parser, $name) {
	global $last_tag;
	$last_tag = "";
}

function curl2xml($ch, $data) {
	global $xml_parser;
	if (!xml_parse($xml_parser, $data, false))
		exit_error('Unable to parse XML with error '
				   . xml_error_string(xml_get_error_code($xml_parser))
				   . ' on line ' . xml_get_current_line_number($xml_parser),
				   'activity');
	return strlen($data);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
