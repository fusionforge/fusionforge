<?php
/** FusionForge Darcs plugin
 *
 * Copyright 2009, Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

forge_define_config_item ('default_server', 'scmdarcs', forge_get_config ('web_host')) ;
forge_define_config_item ('repos_path', 'scmdarcs', forge_get_config('chroot').'/scmrepos/darcs') ;

class DarcsPlugin extends SCMPlugin {
	function DarcsPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmdarcs';
		$this->text = 'Darcs';
		$this->hooks[] = 'scm_generate_snapshots' ;
		$this->hooks[] = 'scm_update_repolist' ;
		$this->hooks[] = 'scm_browser_page' ;
		$this->hooks[] = 'scm_gather_stats' ;
		
		$this->register () ;
	}
	
	function getDefaultServer() {
		return forge_get_config('default_server', 'scmdarcs') ;
	}

	function getRootRepositories ($project) {
		return (forge_get_config('repos_path', 'scmdarcs') . '/' . $project->getUnixName());
	}

	function getRepositories ($project) {
		$res = array();
		$toprepo = $this->getRootRepositories($project);
		if (is_dir($toprepo))
		{
			foreach (scandir($toprepo) as $repo_name)
			{
				$repo = $toprepo . '/' . $repo_name;
				if (is_dir($repo) && is_dir($repo . '/_darcs'))
				{
					$res[] = $repo_name;
				}
			}
		}
		return $res;
	}

	function printShortStats ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
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
			echo ' (Darcs: '.sprintf(_('<strong>%1$s</strong> commits, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}
	
	function getBlurb () {
		return '<p>' . _('Documentation for Darcs is available <a href="http://darcs.net/">here</a>.') . '</p>';
	}

 	function getInstructionForDarcs ($project, $rw) {
 		$repo_names = $this->getRepositories($project);
 		if (count($repo_names) > 0)
 		{
 			$default_repo = "REPO";
 			if (count($repo_names) == 1)
 			{
 				$default_repo = $repo_names[0];
 			}
 			if ($rw)
 			{
 				$url = $project->getSCMBox() . ':'. $this->getRootRepositories($project) . '/' . $default_repo;
 			}
 			else
 			{
 				$url = util_make_url ('/anonscm/darcs/'.$project->getUnixName().'/' . $default_repo);
			}
			$b = '<p><tt>darcs get ' . $url . '</tt></p>';
 			if (count($repo_names) > 1)
 			{
 				$b .= _('<p>where REPO can be: ') . implode(_(', '), $repo_names) . '</p>';
 			}
 		}
 		else if (is_dir($this->getRootRepositories($project)))
 		{
 			$b = _('<p><em>No repositories defined.</em></p>');
 		}
 		else
 		{
 			$b = _('<p><em>Repository not yet created, wait an hour.</em></p>');
 		};
 		return $b ;
 	}
 
	function getInstructionsForAnon ($project) {
		$b = '<h2>';
		$b .=  _('Anonymous Darcs Access');
		$b .= '</h2>';
		$b .= '<p>';
		$b .=  _('This project\'s Darcs repository can be checked out through anonymous access with the following command.');
		$b .= '</p>';
		$b .= $this->getInstructionForDarcs($project, false);
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = '<h2>';
		$b .= _('Developer Darcs Access via SSH');
		$b .= '</h2>';
		$b .= '<p>';
		$b .= ('Only project developers can access the Darcs tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.');
		$b .= '</p>';
		$b .= $this->getInstructionForDarcs($project, true);
		return $b ;
	}

	function getSnapshotPara ($project) {

		$b = "" ;
		$filename = $project->getUnixName().'-scm-latest.tar'.util_get_compressed_file_extension();
		if (file_exists(forge_get_config('scm_snapshots_path').'/'.$filename)) {
			$b .= '<p>[' ;
			$b .= util_make_link ("/snapshots.php?group_id=".$project->getID(),
					      _('Download the nightly snapshot')
				) ;
			$b .= ']</p>';
		}
		return $b ;
	}

	function getBrowserLinkBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Darcs Repository Browser'));
		$b .= '<p>';
		$b .= _('Browsing the Darcs tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.');
		$b .= '</p>';
 		$repo_names = $this->getRepositories($project);
 		if (count($repo_names) > 0)
 		{
 			foreach ($repo_names as $repo_name)
 			{
 				$b .= '<p>[' ;
 				$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID()."&repo_name=".$repo_name,
 									_('Browse Darcs Repository '. $repo_name));
 				$b .= ']</p>' ;
 			}
 		}
 		else
 		{
 			$b .= _('<p>No repositories to browse</p>');
 		}
		return $b ;
	}

	function getStatsBlock ($project) {
		global $HTML ;
		$b = '' ;

		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
					  array ($project->getID()));
		
		if (db_numrows($result) > 0) {
			$b .= $HTML->boxMiddle(_('Repository Statistics'));

			$tableHeaders = array(
				_('Name'),
				_('Adds'),
				_('Commits')
				);
			$b .= $HTML->listTableTop($tableHeaders);
			
			$i = 0;
			$total = array('adds' => 0, 'commits' => 0);
			
			while($data = db_fetch_array($result)) {
				$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
				$b .= '<td width="50%">' ;
				$b .= util_make_link_u ($data['user_name'], $data['user_id'], $data['realname']) ;
				$b .= '</td><td width="25%" align="right">'.$data['adds']. '</td>'.
					'<td width="25%" align="right">'.$data['commits'].'</td></tr>';
				$total['adds'] += $data['adds'];
				$total['commits'] += $data['commits'];
				$i++;
			}
			$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
			$b .= '<td width="50%"><strong>'._('Total').':</strong></td>'.
				'<td width="25%" align="right"><strong>'.$total['adds']. '</strong></td>'.
				'<td width="25%" align="right"><strong>'.$total['commits'].'</strong></td>';
			$b .= '</tr>';
			$b .= $HTML->listTableBottom();
		}

		return $b ;
	}

	function printBrowserPage ($params) {
		global $HTML;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin ($this->name)) {
			if ($this->browserDisplayable ($project)) {
				print '<iframe src="'.
					util_make_url ("/plugins/scmdarcs/cgi-bin/darcsweb.cgi?r=".
					$project->getUnixName()). '/' . $params['repo_name'] .
					'" frameborder="0" width=100% height=700></iframe>' ;
			}
		}
	}

	function createOrUpdateRepo ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
				
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		$toprepo = $this->getRootRepositories($project);
		$unix_group = 'scm_' . $project->getUnixName() ;

		system("chmod g+ws $toprepo");

		$result = db_query_params(
			"SELECT repo_name, clone_repo_name FROM plugin_scmdarcs_create_repos WHERE group_id=$1",
			array($project->getID()));
		if (!$result)
		{
			echo "Error while retrieving darcs repository to create\n";
		}
		else
		{
			while ($res = db_fetch_array($result))
			{
				$repo = $toprepo . '/' . $res['repo_name'];
				$clone_repo = NULL;
				if ($res['clone_repo_name'] != '')
				{
					$clone_repo = $toprepo . '/' . $res['clone_repo_name'];
				}
				if (!is_dir ($repo."/_darcs")) {
					system ("mkdir -p '$repo'") ;
					system ("cd $repo ; darcs init >/dev/null") ;
					if ($clone_repo)
					{
						system ("darcs fetch '$clone_repo'") ;
					}
					system ("find $repo -type d | xargs chmod g+s") ;
				}
				$result1 = db_query_params(
					"DELETE FROM plugin_scmdarcs_create_repos WHERE group_id=$1 AND repo_name=$2",
					array($project->getID(), $res['repo_name']));
				if (!$result1)
				{
					echo "Cannot remove scheduling of darcs repository creation ".$res['repo_name']."\n";
				}
			}
		}
	
			
		foreach ($this->getRepositories($project) as $repo_name)
		{
			$repo =  $toprepo . '/' . $repo_name ;

			system ("chgrp -R $unix_group $repo") ;
			if ($project->enableAnonSCM()) {
				system ("chmod -R g+wX,o+rX-w $repo") ;
			} else {
				system ("chmod -R g+wX,o-rwx $repo") ;
			}
		}
	}

	function darcswebRepository ($project, $repo_name, $repo_url, $repo_dir) {
			$classname = preg_replace ('/\W/', '_', 'repo_' . $repo_name) ;
			return ("class $classname:\n"
				."\trepodir = '$repo_dir'\n"
				."\treponame = '$repo_name'\n"
				."\t".'repodesc = """Repository ' . $repo_name . ' of '.$project->getPublicName().'"""'."\n"
				."\trepourl = '" . util_make_url ('/anonscm/darcs/' . $repo_url) . "'\n"
				."\trepoprojurl = '" . util_make_url ('/projects/' . $repo_url) . "'\n"
				."\trepoencoding = 'utf8'\n"
				. "\n") ;
	}


	function updateRepositoryList ($params) {
		$groups = $this->getGroups () ;
		$list = array () ;
		foreach ($groups as $project) {
			if ($this->browserDisplayable ($project)) {
				$list[] = $project ;
			}
		}

		$fname = '/etc/gforge/plugins/scmdarcs/config.py' ;

		$f = fopen ($fname.'.new', 'w') ;

		fwrite ($f, "class base:\n"
			."\tdarcslogo = '".util_make_url ('/plugins/scmdarcs/darcsweb/darcs.png')."'\n"
			."\tdarcsfav = '".util_make_url ('/plugins/scmdarcs/darcsweb/minidarcs.png')."'\n"
			."\tcssfile = '".util_make_url ('/plugins/scmdarcs/darcsweb/style.css')."'\n"
			. "\n") ;

		foreach ($list as $project) {
			$unix_name = $project->getUnixName();
			$toprepo = $this->getRootRepositories($project);
			$repo_names = $this->getRepositories($project);
			foreach ($repo_names as $repo_name) {
				if ($repo_name == $unix_name)
				{
					# Default repository name, we create a default entry for it
					fwrite ($f,
						$this->darcswebRepository($project, 
																			"$unix_name", 
																			"$unix_name/$repo_name", 
																			"$toprepo/$repo_name"));
				}
				fwrite ($f, 
					$this->darcswebRepository($project, 
																		"$unix_name/$repo_name", 
																		"$unix_name/$repo_name", 
																		"$toprepo/$repo_name"));
			}
		}
		fclose ($f) ;
		chmod ($fname.'.new', 0644) ;
		rename ($fname.'.new', $fname) ;
	}

	function generateSnapshots ($params) {


		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		$group_name = $project->getUnixName() ;

		$tarball = forge_get_config('scm_tarballs_path').'/'.$group_name.'-scmroot.tar'.util_get_compressed_file_extension();

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			unlink ($tarball) ;
			return false;
		}

		# TODO: multi dir
		$toprepo = forge_get_config('repos_path', 'scmdarcs') ;
		$repo = $this->getRootRepositories($project);

		if (!is_dir ($repo)) {
			unlink ($tarball) ;
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		$today = date ('Y-m-d') ;
		$dir = $project->getUnixName ()."-$today" ;
		system ("mkdir -p $tmp/$dir") ;
		system ("cd $tmp ; darcs $repo $dir > /dev/null 2>&1") ;
		system ("tar cCf $tmp - $dir |".forge_get_config('compression_method')."> snapshot") ;
		chmod ("$tmp/snapshot", 0644) ;
		copy ("$tmp/snapshot", $snapshot) ;
		unlink ("$tmp/snapshot") ;
		system ("rm -rf $tmp/$dir") ;

		system ("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball") ;
		chmod ("$tmp/tarball", 0644) ;
		copy ("$tmp/tarball", $tarball) ;
		unlink ("$tmp/tarball") ;
		system ("rm -rf $tmp") ;
	}

	function gatherStats ($params) {
		global  $adds, $deletes, $updates, $commits,
			$usr_adds, $usr_deletes, $usr_updates;
		
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}
		
		if ($params['mode'] == 'day') {
			db_begin();
		
			$year = $params ['year'] ;
			$month = $params ['month'] ;
			$day = $params ['day'] ;
			$month_string = sprintf( "%04d%02d", $year, $month );
			$start_time = gmmktime( 0, 0, 0, $month, $day, $year);
			$end_time = $start_time + 86400;
		
			$updates = 0 ;
			$adds = 0 ;
			$deletes = 0;
			$usr_adds = array () ;
			$usr_updates = array () ;
			$usr_deletes = array ();
		
			$toprepo = $this->getRootRepositories($project) ;
			$from_date = date("c", $start_time);
			$to_date   = date("c", $end_time);

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
		
			$res = db_query_params ('DELETE FROM stats_cvs_user WHERE month=$1 AND day=$2 AND group_id=$3',
						array ($month_string,
						       $day,
						       $project->getID())) ;
			if(!$res) {
				echo "Error while cleaning stats_cvs_user\n" ;
				db_rollback () ;
				return false ;
			}
		
			foreach ($this->getRepositories($project) as $repo_name)
			{
				$repo = $toprepo . '/' . $repo_name;
				if (!is_dir ($repo) || !is_dir ("$repo/_darcs")) {
					echo "No repository $repo\n" ;
					db_rollback () ;
					return false ;
				}
				else
				{
					echo "$repo\n";
				}
		
				$pipe = popen("darcs changes --repodir='$repo' "
								."--match 'date \"between $from_date and $to_date\"' "
								."--xml -s\n", 'r');
			
				$xml_parser = xml_parser_create();
				xml_set_element_handler($xml_parser, "DarcsPluginStartElement", "DarcsPluginEndElement");
			
				// Analyzing history stream
				while (!feof($pipe) &&
							 $data = fgets ($pipe, 4096)) {
					
					if (!xml_parse ($xml_parser, $data, feof ($pipe))) {
						debug("Unable to parse XML with error " .
									xml_error_string(xml_get_error_code($xml_parser)) .
									" on line " .
									xml_get_current_line_number($xml_parser));
						db_rollback () ;
						return false ;
						break;
					}
				}
				
				xml_parser_free ($xml_parser);
			}
			
			// inserting group results in stats_cvs_groups
		
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
				
			// build map for email -> login 
		
			$email_login = array();
			$email_login_fn = $repo."/_darcs/email-login.txt"; 
			if (!file_exists($email_login_fn))
			{
				$email_login_fn = $repo."/.email-login.txt";
			};
			if (!file_exists($email_login_fn))
			{
				unset($email_login_fn);
			};
			
			if (isset($email_login_fn))
			{
				$fh = fopen($email_login_fn, 'r');
				while (!feof($fh))
				{
					$a = explode(" ", fgets($fh));
					if (isset($a[1]))
					{
						$email_login[$a[0]] = rtrim($a[1]);
					};
				};
				fclose($fh);
			};
		
			// building the user list
			$user_list = array_unique( array_merge( array_keys( $usr_adds ), array_keys( $usr_updates ) ) );
		
			foreach ( $user_list as $user ) {
				// trying to get user id from darcs user name
				$id = $user;
				$tmp_email = explode("<", $id, 2);
				if (isset($tmp_email[1]))
				{
				  $tmp_email = explode(">", $tmp_email[1]);
				  $id = $tmp_email[0];
				}
				if (isset($email_login[$id]))
				{
				  $id = $email_login[$id];
				}
		
				$u = &user_get_object_by_name ($id) ;
				if ($u) {
					$user_id = $u->getID();
				} else {
					continue;
				}
					
				if (!db_query_params ('INSERT INTO stats_cvs_user (month,day,group_id,user_id,commits,adds) VALUES ($1,$2,$3,$4,$5,$6)',
						      array ($month_string,
							     $day,
							     $project->getID(),
							     $user_id,
							     $usr_updates[$user] ? $usr_updates[$user] : 0,
							     $usr_adds[$user] ? $usr_adds[$user] : 0))) {
					echo "Error while inserting into stats_cvs_user\n" ;
					db_rollback () ;
					return false ;
				}
			}
		
			db_commit();
		}
	}

	function printAdminPage ($params) {
		parent::printAdminPage($params);

		$project = $this->checkParams($params);
		if (!$project)
		{
			return false;
		}

		if ($project->usesPlugin($this->name))
		{
			$result = db_query_params(
				"SELECT repo_name FROM plugin_scmdarcs_create_repos WHERE group_id=$1",
				array($project->getID()));
			if ($result && db_numrows($result) > 0)
			{
				$nm = array ();
				while ($res = db_fetch_array($result))
				{
					array_push($nm, $res['repo_name']);
				}
				print '<p><strong>'._('Repository to be created: ') . '</strong>' . 
					implode(_(', '), $nm) . '</p>';
			}

			print '<p><strong>Create new repository:</strong></p>';
			print '<p>'._('Repository name: ').
				'<input type="string" name="scm_create_repo_name" size=16 maxlength=128 /></p>';
			print '<p>'._('Clone: ').
				'<select name="scm_clone_repo_name">';
			print '<option value="">&lt;none&gt;</option>';
			foreach ($this->getRepositories($project) as $repo_name)
			{
				print '<option value="'.$repo_name.'">'.$repo_name.'</option>';
			}
			print '</select></p>';
		}
	}

	function adminUpdate ($params) {
		parent::adminUpdate($params);

		$project = $this->checkParams($params);
		if (!$project)
		{
			return false;
		}

		$new_repo_name = $params['scm_create_repo_name'];
		$clone_repo_name = $params['scm_clone_repo_name'];
		if ($new_repo_name != '')
		{
			$repo_names = $this->getRepositories($project);
			if (in_array($new_repo_name, $repo_names))
			{
				html_error_top(_("Repository $new_repo_name already exists"));
				return false;
			}

			if ($clone_repo_name != '' && !in_array($clone_repo_name, $repo_names))
			{
				html_error_top(_("Clone repository $clone_repo_name doesn't exist"));
				return false;
			}
			if ($clone_repo_name == '<none>')
			{
				$clone_repo_name = '';
			}

			if (!preg_match('/^[\w][-_\w\d\.]+$/', $new_repo_name))
			{
				html_error_top("Invalid repository name $new_repo_name");
				return false;
			}

			db_begin ();
			if (!db_query_params ('INSERT INTO plugin_scmdarcs_create_repos (group_id,repo_name,clone_repo_name) 
				VALUES ($1,$2,$3)',
				array($project->getID(), $new_repo_name, $clone_repo_name)))
			{
				html_error_top("SQL error while scheduling new repository $new_repo_name");
				db_rollback () ;
				return false ;
			}
			db_commit ();

			html_feedback_top(_("Repository $new_repo_name schedule for creation"));
		}
	}

}	
		
function DarcsPluginStartElement($parser, $name, $attrs) {
	global $last_user, $commits, 
		$adds, $updates, $deletes,
		$usr_adds, $usr_updates, $usr_deletes;
	switch($name) {
	case "PATCH":
		$last_user = $attrs['AUTHOR'];
		if (!array_key_exists($last_user, $usr_deletes))
		{
			$usr_deletes[$last_user] = 0;
		}
		if (!array_key_exists($last_user, $usr_updates))
		{
			$usr_updates[$last_user] = 0;
		}
		if (!array_key_exists($last_user, $usr_adds))
		{
			$usr_adds[$last_user] = 0;
		}
		$commits++;
		break;
	case "REMOVE_FILE":
	case "REMOVE_DIRECTORY":
		$deletes++;
		if ($last_user) {
			$usr_deletes[$last_user]++;
		}
		break;
	case "MOVE":
	case "MODIFY_FILE":
		$updates++;
		if ($last_user) {
			$usr_updates[$last_user]++;
		}
		break;
	case "ADD_FILE":
	case "ADD_DIRECTORY":
		$adds++;
		if ($last_user) {
			$usr_adds[$last_user]++;
		}
		break;
	}
}
	
function DarcsPluginEndElement ($parser, $name) {
}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
