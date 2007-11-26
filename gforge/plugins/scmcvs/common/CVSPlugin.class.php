<?php

class CVSPlugin extends SCM {
	function CVSPlugin () {
		global $cvs_root;
		$this->SCM () ;
		$this->name = 'scmcvs';
		$this->text = 'CVS';
		$this->hooks[] = 'scm_page';
		$this->hooks[] = 'scm_admin_update';
		$this->hooks[] = 'scm_admin_page';
		$this->hooks[] = 'scm_stats';

		$this->hooks[] = 'scm_plugin';

		require_once('plugins/scmcvs/config.php') ;

		$this->default_cvs_server = $default_cvs_server ;
		if ($cvs_root) {
			$this->cvs_root = $cvs_root;
		} else {
			$this->cvs_root = "/cvsroot";
		} 
		//$this->default_cvs_server = $default_cvs_server ;
		//$this->this_server = $this_server ;
		$this->enabled_by_default = $enabled_by_default ;

		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_cvs_server;
	}

	function CallHook ($hookname, $params) {
		global $HTML;
		
		switch ($hookname) {
		case 'scm_page':
			$group_id = $params['group_id'] ;
			$this->getPage ($group_id) ;
			break ;
		case 'scm_admin_update':
			$this->adminUpdate ($params) ;
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
		global $HTML ;

		$project =& group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
		}
		
		if ($project->usesPlugin($this->name)) {
		
			print _('Some CVS documentation is available <a href="http://www.nongnu.org/cvs/">Here</a>');

			$cvsrootend=$project->getSCMBox().':/cvsroot/'.$project->getUnixName();
			$cvsrootend = $project->getSCMBox().':'.$this->cvs_root.'/'.$project->getUnixName();

			// CVS browser links must be displayed if
			// project enables anon CVS or if logged-in
			// user is a member of the group
			$displayCvsBrowser = $project->enableAnonSCM();
			if(session_loggedin()) {
				$perm =& $project->getPermission(session_get_user());
				if ($perm && is_object($perm) && !$perm->isError() && $perm->isMember()) {
					$displayCvsBrowser = true;
				}
			}

			// ######################## Table for summary info
			?>
			<table width="100%">
			<tr valign="top">
				<td width="65%">
			 	<?php
				// ######################## Anonymous CVS Instructions
				if ($project->enableAnonSCM()){
					echo _('<p><b>Anonymous CVS Access</b></p><p>This project\'s CVS repository can be checked out through anonymous (pserver) CVS with the following instruction set. The module you wish to check out must be specified as the <i>modulename</i>. When prompted for a password for <i>anonymous</i>, simply press the Enter key.</p>');
					print '
						<p>
						<tt>cvs -d :pserver:anonymous@' . $cvsrootend.' login</tt><br/>
						<tt>cvs -d :pserver:anonymous@' . $cvsrootend.' checkout <em>'._('modulename').'</em></tt>
						</p>';
				}

				// ######################## Developer Access
				echo _('<p><b>Developer CVS Access via SSH</b></p><p>Only project developers can access the CVS tree via this method. SSH must be installed on your client machine. Substitute <i>modulename</i> and <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
				print '
					<p>
					<tt>export CVS_RSH=ssh</tt><br/>
					<tt>cvs -d :ext:<em>'._('developername').'</em>@'.$cvsrootend.' checkout <em>'._('modulename').'</em></tt>
					</p>';

				// ######################## CVS Snapshot
				if ($displayCvsBrowser) {
					print '
						<p>
						[<a href="/snapshots.php?group_id='.$group_id.'">'._('Download The Nightly CVS Tree Snapshot').'</a>]
						</p>';
	 			}
				?>
				</td>

				<td width="35%" valign="top">
				<?php
				// ######################## CVS Browsing 
				echo $HTML->boxTop(_('Repository History'));
				echo $this->getDetailedStats(array('group_id'=>$group_id)).'<p>';
				if ($displayCvsBrowser){
					echo _('<b>Browse the CVS Tree</b><p>Browsing the CVS tree gives you a great view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
					echo '<p>[<a href="/scm/viewvc.php/?root='.$project->getUnixName().'">'._('Browse CVS Repository').'</a>]</p>' ;
					$hook_params['project_name'] = $project->getUnixName();
					plugin_hook ("cvs_stats", $hook_params) ;
				}
				echo $HTML->boxBottom();
				?>
				</td>
			</tr>
			</table>
			<?php
		}	
	}

	function adminUpdate ($params) {
		$group =& group_get_object($params['group_id']);
		if (!$group || !is_object($group)) {
			return false;
		} elseif ($group->isError()) {
			return false;
		}
		if ($group->usesPlugin($this->name)) {
 			if ($params['scmcvs_enable_anoncvs']) {
				$group->SetUsesAnonSCM(true);
			} else {
				$group->SetUsesAnonSCM(false);
 			}
 			if ($params['scmcvs_enable_pserver']) {
				$group->SetUsesPserver(true);
			} else {
				$group->SetUsesPserver(false);
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

		if ($group->usesPlugin($this->name)) {
			?>
			<p>
			<input type="checkbox" name="scmcvs_enable_anoncvs" value="1" <?php echo $this->c($group->enableAnonSCM()); ?> /><strong><?php echo _('Enable Anonymous Access') ?></strong>
			<br />
			<input type="checkbox" name="scmcvs_enable_pserver" value="1" <?php echo $this->c($group->enablePserver()); ?> /><strong><?php echo _('Enable pserver') ?></strong>
			</p>
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
		
		if ($project->usesPlugin($this->name)) {
			$result = db_query("
				SELECT sum(commits) AS commits, sum(adds) AS adds
				FROM stats_cvs_group
				WHERE group_id='$group_id'");
			$commit_num = db_result($result,0,'commits');
			$add_num    = db_result($result,0,'adds');
			if (!$commit_num) {
				$commit_num=0;
			}
			if (!$add_num) {
				$add_num=0;
			}
			echo ' (CVS: '.sprintf(_('<strong>%1$s</strong> commits, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
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
				_('Commits')
			);
			echo $HTML->listTableTop($tableHeaders);

			$i = 0;
			$total = array('adds' => 0, 'commits' => 0);

			while($data = db_fetch_array($result)) {
				echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
				echo '<td width="50%"><a href="/users/'.$data['user_name'].'/">'.$data['realname'].'</a></td>'.
					'<td width="25%" align="right">'.$data['adds']. '</td>'.
					'<td width="25%" align="right">'.$data['commits'].'</td></tr>';
				$total['adds'] += $data['adds'];
				$total['commits'] += $data['commits'];
				$i++;
			}
			echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
			echo '<td width="50%"><strong>'._('Total').':</strong></td>'.
				'<td width="25%" align="right"><strong>'.$total['adds']. '</strong></td>'.
				'<td width="25%" align="right"><strong>'.$total['commits'].'</strong></td>';
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
