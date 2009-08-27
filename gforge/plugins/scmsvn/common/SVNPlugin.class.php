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
		require_once $gfconfig.'plugins/scmsvn/config.php' ;

		$this->name = 'scmsvn';
		$this->text = 'SVN';
		$this->SCMPlugin () ;

		$this->default_svn_server = $default_svn_server ;
		$this->enabled_by_default = $enabled_by_default ;
		$this->use_ssh = $use_ssh;
		$this->use_dav = $use_dav;
		$this->use_ssl = $use_ssl;
		$this->svn_root = $svn_root;

	}
	
	function getDefaultServer() {
		return $this->default_svn_server ;
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

	function AdminUpdate ($params) {
		$group =& group_get_object($params['group_id']);
		if (!$group || !is_object($group)) {
			return false;
		} elseif ($group->isError()) {
			return false;
		}
		
		if ( $group->usesPlugin ( $this->name ) ) {
			if ($params['scmsvn_enable_anon_svn']) {
				$group->SetUsesAnonSCM(true);
			} else {
				$group->SetUsesAnonSCM(false);
			}
		}
	}
	
	// This function is used to render checkboxes below
	function getAdminPage ($params) {
		$group =& group_get_object($params['group_id']);
		if ( $group->usesPlugin ( $this->name ) && $group->isPublic()) {
			print '<p><input type="checkbox" name="scmsvn_enable_anon_svn" value="1" '.$this->c($group->enableAnonSCM()).' /><strong>'._('Enable Anonymous Access').'</strong></p>';
		}
	}
	
	function getStats ($params) {
		$group_id = $params['group_id'] ;
		$project =& group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
		}
		
		if ($project->usesPlugin ($this->name)) {
			list($commit_num, $add_num) = $this->getTotalStats($group_id);
			echo ' (SVN: '.sprintf(_('<strong>%1$s</strong> updates, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).')';
		}
	}
	
	// Get the total stats for a group
	function getTotalStats($group_id) {
		$result = db_query_params ('
			SELECT SUM(commits) AS commits, SUM(adds) AS adds
			FROM stats_cvs_group
			WHERE group_id=$1',
			array($group_id));
		$commit_num = db_result($result,0,0);
		$add_num	= db_result($result,0,1);
		if (!$commit_num) {
			$commit_num=0;
		}
		if (!$add_num) {
			$add_num=0;
		}
		return array($commit_num, $add_num);
	}
	
	function getDetailedStats ($params) {
		global $HTML;
		$group_id = $params['group_id'] ;
		
		$result = db_query('
			SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined
			FROM stats_cvs_user s, users u
			WHERE group_id=\''.$group_id.'\' AND s.user_id=u.user_id AND (commits>0 OR adds >0)
			GROUP BY group_id, realname, user_name, u.user_id
			ORDER BY combined DESC, realname;
		');

		if (db_numrows($result) > 0) {
			$tableHeaders = array(
				_('Name'),
				_('Adds'),
				_('Updates')
				);
			echo $HTML->listTableTop($tableHeaders);
			
			$i = 0;
			$total = array('adds' => 0, 'commits' => 0);
			
			while($data = db_fetch_array($result)) {
				echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
				echo '<td width="50%">' ;
				echo util_make_link_u ($data['user_name'], $data['user_id'], $data['realname']) ;
				echo '</td><td width="25%" align="right">'.$data['adds']. '</td>'.
					'<td width="25%" align="right">'.$data['commits'].'</td></tr>';
				$total['adds'] += $data['adds'];
				$total['commits'] += $data['commits'];
				$i++;
			}
			list($commit_num, $add_num) = $this->getTotalStats($group_id);
			if ($commit_num > $total['commits'] ||
			    $add_num > $total['adds']) {
				echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
				echo '<td width="50%">' .
					_('Unknown') .
					'</td><td width="25%" align="right">'.
					($add_num - $total['adds']) . '</td>'.
					'<td width="25%" align="right">'.
					($commit_num - $total['commits']) .
					'</td></tr>';
				$i++;
			}
			echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
			echo '<td width="50%"><strong>'._('Total').':</strong></td>'.
				'<td width="25%" align="right"><strong>'.$add_num. '</strong></td>'.
				'<td width="25%" align="right"><strong>'.$commit_num.'</strong></td>';
			echo '</tr>';
			echo $HTML->listTableBottom();
			echo '<hr size="1" />';
		}
	}

	function createOrUpdateRepo ($params) {
		$group_id = $params['group_id'] ;

		$project =& group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
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
