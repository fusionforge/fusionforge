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

class DarcsPlugin extends SCMPlugin {
	function DarcsPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmdarcs';
		$this->text = 'Darcs';
		$this->hooks[] = 'scm_generate_snapshots' ;
		$this->hooks[] = 'scm_darcs_do_nothing' ;
		
		require_once $gfconfig.'plugins/scmdarcs/config.php' ;
		
		$this->default_darcs_server = $default_darcs_server ;
		$this->darcs_root = $darcs_root;
		
		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_darcs_server ;
	}

	function getBlurb () {
		return _('<p>This Darcs plugin is not fully implemented yet.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  _('<p><b>Anonymous Darcs Access</b></p><p>This project\'s Darcs repository can be checked out through anonymous access with the following command.</p>');
		$b .= '<p>' ;
		$b .= '<tt>darcs get '.util_make_url ('/anonscm/darcs/'.$project->getUnixName().'/').'</tt><br />';
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = _('<p><b>Developer Darcs Access via SSH</b></p><p>Only project developers can access the Darcs tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
		$b .= '<p><tt>darcs get '.$project->getSCMBox() . ':'. $this->darcs_root .'/'. $project->getUnixName().'/ .</tt></p>' ;
		return $b ;
	}

	function getSnapshotPara ($project) {
		return ;
	}

	function getBrowserLinkBlock ($project) {
		return ;
	}

	function getStatsBlock ($project) {
		return ;
	}

	function createOrUpdateRepo ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
				
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		$repo = $this->darcs_root . '/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project->getUnixName() ;

		if (!is_dir ($repo."_darcs")) {
			system ("mkdir -p $repo") ;
			system ("cd $repo ; darcs init >/dev/null") ;
		}
		
		system ("chgrp -R $unix_group $repo") ;
		if ($project->enableAnonSCM()) {
			system ("chmod -R g+wXs,o+rX-w $repo") ;
		} else {
			system ("chmod -R g+wXs,o-rwx $repo") ;
		}
	}

	function generateSnapshots ($params) {
		global $sys_scm_tarballs_path ;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		$group_name = $project->getUnixName() ;

		$tarball = $sys_scm_tarballs_path.'/'.$group_name.'-scmroot.tar.gz';

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			unlink ($tarball) ;
			return false;
		}

		$toprepo = $this->darcs_root ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

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
		system ("tar czCf $tmp $tmp/snapshot.tar.gz $dir") ;
		chmod ("$tmp/snapshot.tar.gz", 0644) ;
		copy ("$tmp/snapshot.tar.gz", $snapshot) ;
		unlink ("$tmp/snapshot.tar.gz") ;
		system ("rm -rf $tmp/$dir") ;

		system ("tar czCf $toprepo $tmp/tarball.tar.gz " . $project->getUnixName()) ;
		chmod ("$tmp/tarball.tar.gz", 0644) ;
		copy ("$tmp/tarball.tar.gz", $tarball) ;
		unlink ("$tmp/tarball.tar.gz") ;
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
		
			$repo = $this->svn_root . '/' . $project->getUnixName() ;
			if (!is_dir ($repo) || !is_dir ("$repo/_darcs")) {
				echo "No repository\n" ;
				db_rollback () ;
				return false ;
			}
		
			$from_date = date("c", $start_time);
			$to_date   = date("c", $end_time);
			$pipe = popen("darcs changes --repodir='$repo' "
				      ."--match 'date \"between $from_date and $to_date\"' "
				      ."--xml -s\n", 'r');
		
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
			
			//..................
		
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
		
	function DarcsPluginStartElement($parser, $name, $attrs) {
		global $last_user, $commits, 
		       $adds, $updates, $deletes,
		       $usr_adds, $usr_updates, $usr_deletes;
		switch($name) {
		case "PATCH":
			$last_user = $attrs['AUTHOR'];
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
  }
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
