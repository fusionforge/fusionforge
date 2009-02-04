<?php
/** FusionForge Bazaar plugin
 *
 * Copyright 2009, Roland Mas <lolando@debian.org>
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

class BzrPlugin extends SCM {
	function BzrPlugin () {
		global $gfconfig;
		$this->SCM () ;
		$this->name = 'scmbzr';
		$this->text = 'Bazaar';
		$this->hooks[] = 'scm_page';
		$this->hooks[] = 'scm_admin_update';
		$this->hooks[] = 'scm_admin_page';
 		$this->hooks[] = 'scm_stats';
		$this->hooks[] = 'scm_plugin';

		require_once $gfconfig.'plugins/scmbzr/config.php' ;
		
		$this->default_bzr_server = $default_bzr_server ;
		$this->enabled_by_default = $enabled_by_default ;
		$this->bzr_root = $bzr_root;

		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_bzr_server ;
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
			// Bazaar browser links must be displayed if
			// project enables anonymous Bazaar
			$displayBzrBrowser = $project->enableAnonSCM();

			// Table for summary info
			print ('<table width="100%">
				 <tr valign="top">
				 <td width="65%">') ;
			print _('<p>Documentation for Bazaar (sometimes referred to as "bzr") is available <a href="http://doc.bazaar-vcs.org/latest/en/user-guide/index.html">here</a>.</p>');
			
			// Anonymous Bazaar Instructions
			if ($project->enableAnonSCM()) {
				print _("<p><b>Anonymous Bazaar Access</b></p><p>This project's Bazaar repository can be accessed anonymously through HTTP.</p>");
				print '<p>';
				print _("A list of available branches can be obtained with the following command:") ;
				print "<br />" ;
				print '<tt>bzr branches http://' . $project->getSCMBox(). '/' . $this->svn_root .'/'. $project->getUnixName() .'</tt>';
				print '</p>';
				print '<p>';
				print _("To check out one of these branches:") ;
				print "<br />" ;
				print '<tt>bzr checkout http://' . $project->getSCMBox(). '/' . $this->svn_root .'/'. $project->getUnixName() .'<i>'._('branchname').'</i></tt>') ;
			print '</p>';
		}
		
		// Developer Access
		echo _('<p><b>Developer Bazaar Access via SSH</b></p><p>Only project developers can access the Bazaar branches tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
		print '<p><tt>bzr checkout bzr+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . '/'. $this->svn_root .'/'. $project->getUnixName().'/'._('branchname').'</tt></p>' ;
		
		// Bazaar Snapshot
		if ($displaySvnBrowser) {
			$filename=$project->getUnixName().'-scm-latest.tar.gz';
			if (file_exists($sys_scm_snapshots_path.'/'.$filename)) {
				print '<p>[' ;
				print util_make_link ("/snapshots.php?group_id=$group_id",
						      _('Download The Nightly Bazaar Tree Snapshot')
					) ;
				print ']</p>';
			}
		}
		
		print '</td><td width="35%" valign="top">' ;
		// Bazaar Browsing
		
		echo $HTML->boxTop(_('Repository History'));
		echo _('Not implemented yet');
		/*				echo $this->getDetailedStats(array('group_id'=>$group_id)).'<p>';
		 if ($displaySvnBrowser) {
		 echo _('<b>Browse the Bazaar Tree</b><p>Browsing the Bazaar tree gives you a great view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
		 echo '<p>[' ;
		 echo util_make_link ("/scm/viewvc.php/?root=".$project->getUnixName(),
		 _('Browse Bazaar Repository')
		 ) ;
		 echo ']</p>' ;
		 }
		*/
		echo $HTML->boxBottom();
		print '</td></tr></table>' ;
	}
	
	function AdminUpdate ($params) {
		$group =& group_get_object($params['group_id']);
		if (!$group || !is_object($group)) {
			return false;
		} elseif ($group->isError()) {
			return false;
		}

		if ( $group->usesPlugin ( $this->name ) ) {
			if ($params['scmbzr_enable_anon_bzr']) {
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
			<p><input type="checkbox" name="scmbzr_enable_anon_bzr" value="1" <?php echo $this->c($group->enableAnonSCM()); ?> /><strong><?php echo _('Enable Anonymous Access') ?></strong></p>
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
			echo ' (Bazaar: '.sprintf(_('<strong>%1$s</strong> updates, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).')';
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
			SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined
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

	function createOrUpdateRepo ($group_id) {
		$project =& group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
		}
		
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}
		
		
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
