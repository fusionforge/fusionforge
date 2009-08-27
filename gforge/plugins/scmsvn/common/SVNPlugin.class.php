<?php
/** FusionForge Subversion plugin
 *
 * Copyright 2003-2009, Roland Mas
 * Copyright 2004, GForge, LLC
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

class SVNPlugin extends SCMPlugin {
	function SVNPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmsvn';
		$this->text = 'SVN';

		require_once $gfconfig.'plugins/scmsvn/config.php' ;
		
		$this->default_svn_server = $default_svn_server ;
		$this->enabled_by_default = $enabled_by_default ;
		$this->use_ssh = $use_ssh;
		$this->use_dav = $use_dav;
		$this->use_ssl = $use_ssl;
		$this->svn_root = $svn_root;

		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_svn_server ;
	}

	function echoShortStats ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin ($this->name)) {
			list($commit_num, $add_num) = $this->getTotalStats($project->getID());
			echo ' (SVN: '.sprintf(_('<strong>%1$s</strong> updates, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).')';
		}
	}
	
	function getBlurb () {
		return _('<p>Documentation for Subversion (sometimes referred to as "SVN") is available <a href="http://svnbook.red-bean.com/">here</a>.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  _('<p><b>Anonymous Subversion Access</b></p><p>This project\'s SVN repository can be checked out through anonymous access with the following command(s).</p>');
		$b .= '<p>' ;
		if ($this->use_ssh) {
			$b .= '<tt>svn checkout svn://'.$project->getSCMBox().'/'.$this->svn_root.'/'.$project->getUnixName().'</tt><br />';
		}
		if ($this->use_dav) {
			$b .= '<tt>svn checkout --username anonsvn http'.(($this->use_ssl) ? 's' : '').'://' . $project->getSCMBox(). '/' . $this->svn_root .'/'. $project->getUnixName() .'</tt><br/><br/>';
			$b .= _('The password is \'anonsvn\'').'<br/>';
		}
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = '' ;
		if ($this->use_ssh) {
			$b .= _('<p><b>Developer Subversion Access via SSH</b></p><p>Only project developers can access the SVN tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
			$b .= '<p><tt>svn checkout svn+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . '/'. $this->svn_root .'/'. $project->getUnixName().'</tt></p>' ;
		}
		if ($this->use_dav) {
			$b .= _('<p><b>Developer Subversion Access via DAV</b></p><p>Only project developers can access the SVN tree via this method. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
			$b .= '<p><tt>svn checkout --username <i>'._('developername').'</i> http'.(($this->use_ssl) ? 's' : '').'://'. $project->getSCMBox() .'/'. $this->svn_root .'/'.$project->getUnixName().'</tt></p>' ;
		}

		return $b ;
	}

	function getSnapshotPara ($project) {
		return ;
	}

	function getBrowserBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Subversion Repository Browser'));
		$b = _('<p>Browsing the Subversion tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/viewvc.php/?root=".$project->getUnixName(),
				      _('Browse Subversion Repository')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}
	
	function getStatsBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Repository Statistics'));
		
		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
					  array ($project->getID()));
		
		if (db_numrows($result) > 0) {
			$tableHeaders = array(
				_('Name'),
				_('Adds'),
				_('Updates')
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

			$res2 = db_query("
			SELECT SUM(commits) AS commits, SUM(adds) AS adds
			FROM stats_cvs_group
			WHERE group_id='$group_id'");
			$commit_num = db_result($res2,0,0);
			$add_num = db_result($res2,0,1);
			if (!$commit_num) {
				$commit_num=0;
			}
			if (!$add_num) {
				$add_num=0;
			}
			if ($commit_num > $total['commits'] ||
			    $add_num > $total['adds']) {
				$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
				$b .= '<td width="50%">' .
					_('Unknown') .
					'</td><td width="25%" align="right">'.
					($add_num - $total['adds']) . '</td>'.
					'<td width="25%" align="right">'.
					($commit_num - $total['commits']) .
					'</td></tr>';
				$i++;
			}
			$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
			$b .= '<td width="50%"><strong>'._('Total').':</strong></td>'.
				'<td width="25%" align="right"><strong>'.$add_num. '</strong></td>'.
				'<td width="25%" align="right"><strong>'.$commit_num.'</strong></td>';
			$b .= '</tr>';
			$b .= $HTML->listTableBottom();
			$b .= '<hr size="1" />';
		}

		return $b ;
	}

	function createOrUpdateRepo ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		$repo = $this->svn_root . '/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project->getUnixName() ;

		$repo_exists = false ;
		if (is_dir ($repo) && is_file ("$repo/format")) {
			$repo_exists = true ;
		}
               
		if (!$repo_exists) {
			system ("svnadmin create --fs-type fsfs $repo") ;
		}

		system ("chgrp -R $unix_group $repo") ;
		if ($project->enableAnonSCM()) {
			system ("chmod -R g+wXs,o+rX-w $repo") ;
		} else {
			system ("chmod -R g+wXs,o-rwx $repo") ;
		}
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
