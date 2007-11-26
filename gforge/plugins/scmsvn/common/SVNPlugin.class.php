<?php
/** Subversion plugin for Gforge
 * Copyright 2003 Roland Mas <lolando@debian.org>
 * Copyright 2004 Roland Mas <roland@gnurandal.com> 
 *				The Gforge Group, LLC <http://gforgegroup.com/>
 * Based on the CVS plugin, which was derived from Gforge, which was
 * derived from Sourceforge
 *
 * This file is not part of Gforge
 *
 * This plugin, like Gforge, is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

class SVNPlugin extends SCM {
	function SVNPlugin () {
		$this->SCM () ;
		$this->name = 'scmsvn';
		$this->text = 'SVN';
		$this->hooks[] = 'scm_page';
		$this->hooks[] = 'scm_admin_update';
		$this->hooks[] = 'scm_admin_page';
// to be revised		
 		$this->hooks[] = 'scm_stats';
		$this->hooks[] = 'scm_plugin';

		require_once('plugins/scmsvn/config.php') ;
		
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

	function CallHook ($hookname, $params) {
		global $HTML ;
		
		switch ($hookname) {
		case 'scm_page':
			$group_id = $params['group_id'] ;
			$this->getPage ($group_id) ;
			break ;
		case 'scm_admin_update':
			$this->AdminUpdate ($params) ;
			break ;
		case 'scm_admin_page':
			$this->getAdminPage ($params) ;
			break ;
		case 'scm_stats':
			$this->getStats ($params) ;
			break;
		case 'scm_plugin':
			$scm_plugins=& $params['scm_plugins'];
			$scm_plugins[]=$this->name;
			break;
		default:
			// Forgot something
		}
	}

	function getPage ($group_id) {
		global $HTML, $sys_scm_snapshots_path;

		$project =& group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
		}

		if ($project->usesPlugin ($this->name)) {

			// SVN browser links must be displayed if
			// project enables anon SVN or if logged-in
			// user is a member of the group
			$displaySvnBrowser = $project->enableAnonSCM();
			if(session_loggedin()) {
				$perm =& $project->getPermission(session_get_user());
				if ($perm && is_object($perm) && !$perm->isError() && $perm->isMember()) {
					$displaySvnBrowser = true;
				}
			}

			// ######################## Table for summary info
			?>
			<table width="100%">
			<tr valign="top">
				<td width="65%">
				<?php
				print _('<p>Documentation for Subversion (sometimes referred to as "SVN") is available <a href="http://svnbook.red-bean.com/">here</a>.</p>');

				// ######################## Anonymous SVN Instructions
				if ($project->enableAnonSCM()) {
					print _('<p><b>Anonymous Subversion Access</b></p><p>This project\'s SVN repository can be checked out through anonymous access with the following command(s).</p>');
					print '<p>';
					if ($this->use_ssh) {
						print '<tt>svn checkout svn://'.$project->getSCMBox().'/'.$this->svn_root.'/'.$project->getUnixName().'</tt><br />';
					}
					if ($this->use_dav) {
						print '<tt>svn checkout --username anonsvn http'.(($this->use_ssl) ? 's' : '').'://' . $project->getSCMBox(). '/' . $this->svn_root .'/'. $project->getUnixName() .'</tt><br/><br/>';
						print _('The password is \'anonsvn\'').'<br/>';
					}
					print '</p>';
				}
	
				// ######################## Developer Access
				if ($this->use_ssh) {
					echo _('<p><b>Developer Subversion Access via SSH</b></p><p>Only project developers can access the SVN tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
					print '<p><tt>svn checkout svn+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . '/'. $this->svn_root .'/'. $project->getUnixName().'</tt></p>' ;
				}
				if ($this->use_dav) {
					echo _('<p><b>Developer Subversion Access via DAV</b></p><p>Only project developers can access the SVN tree via this method. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
					print '<p><tt>svn checkout --username <i>'._('developername').'</i> http'.(($this->use_ssl) ? 's' : '').'://'. $project->getSCMBox() .'/'. $this->svn_root .'/'.$project->getUnixName().'</tt></p>' ;
				}

				// ######################## SVN Snapshot
				if ($displaySvnBrowser) {
					$filename=$project->getUnixName().'-scm-latest.tar.gz';
					if (file_exists($sys_scm_snapshots_path.'/'.$filename)) {
						print '<p>[<a href="/snapshots.php?group_id='.$group_id.'">'
							._('Download The Nightly SVN Tree Snapshot').
							'</a>]</p>';
					}
				}
				?>
				</td>

				<td width="35%" valign="top">
				<?php
				// ######################## SVN Browsing
				echo $HTML->boxTop(_('Repository History'));
				echo $this->getDetailedStats(array('group_id'=>$group_id)).'<p>';
				if ($displaySvnBrowser) {
					echo _('<b>Browse the Subversion Tree</b><p>Browsing the SVN tree gives you a great view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
					echo '<p>[<a href="/scm/viewvc.php/?root='.$project->getUnixName().'">'._('Browse Subversion Repository').'</a>]</p>' ;
				}

				echo $HTML->boxBottom();
				?>
				</td>
			</tr>
			</table>
			<?php
		}
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
	function c($v) {
		if ($v) {
			return 'checked="checked"';
		} else {
			return '';
		}
	}

	function getAdminPage ($params) {
		$group =& group_get_object($params['group_id']);
		if ( $group->usesPlugin ( $this->name ) && $group->isPublic()) {
			?>
			<p><input type="checkbox" name="scmsvn_enable_anon_svn" value="1" <?php echo $this->c($group->enableAnonSCM()); ?> /><strong><?php echo _('Enable Anonymous Access') ?></strong></p>
			<?php
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
		$result = db_query("
			SELECT SUM(commits) AS commits, SUM(adds) AS adds
			FROM stats_cvs_group
			WHERE group_id='$group_id'");
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
			SELECT u.realname, u.user_name, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined
			FROM stats_cvs_user s, users u
			WHERE group_id=\''.$group_id.'\' AND s.user_id=u.user_id AND (commits>0 OR adds >0)
			GROUP BY group_id, realname, user_name
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
				echo '<td width="50%">' .
					'<a href="/users/'.$data['user_name'].'/">'.$data['realname'].'</a>' .
					'</td><td width="25%" align="right">'.$data['adds']. '</td>'.
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
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
