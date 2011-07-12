<?php
/**
 * GForge Plugin SVNTracker Class
 *
 * Copyright 2004 (c) Francisco Gimeno <kikov @nospam@ kikov.org>
 * Copyright 2005 (c) Guillaume Smet <guillaume-gforge@smet.org>
 *
 * This file is part of GForge-plugin-svntracker
 *
 * GForge-plugin-svntracker is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge-plugin-svntracker is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
/**
 * The svntrackerPlugin class. It implements the Hooks for the presentation
 *  of table in tracker and task in detailed mode.
 *
 */

class svntrackerPlugin extends Plugin {
	function svntrackerPlugin () {
		$this->Plugin() ;
		$this->name = "svntracker" ;
		$this->text = "Source Code and Tracker Integration";
		$this->hooks[] = "groupisactivecheckbox";
		$this->hooks[] = "groupisactivecheckboxpost";
		$this->hooks[] = "artifact_extra_detail";
		$this->hooks[] = "task_extra_detail";
		$this->hooks[] = "update_svn_repository";
		$this->hooks[] = "cmd_for_post_commit_hook";
	}

	function groupisactivecheckbox (&$params) {
		$group = group_get_object($params['group']);
		if ($group->usesPlugin('scmsvn') || $group->usesPlugin('websvn')) {
			parent::groupisactivecheckbox($params);
		}
	}

	/**
	* It display a table with commit related to this tracker or task_extra_detail
	*
	* @param   string   $Query Query to be executed to get the commit entries.
	* @param   integer  $group_id Group_id of the actual Group_id
	*
	*/
	function getCommitEntries($DBResult,$group_id) {
		$group = &group_get_object($group_id);
		$Rows= db_numrows($DBResult);

		if ($Rows > 0) {
			echo '<tr><td colspan="2">';
			echo '<h4>'._('Links to related SVN commits').':</h4>';

			$title_arr=$this->getTitleArr();
			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i<$Rows; $i++) {
				$Row = db_fetch_array($DBResult);
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
				'<td>'. $this->getFileLink($group->getUnixName(),
						$Row['file'],$Row['actual_version']). '</td>'.
				'<td>'. date(_('Y-m-d'), $Row['svn_date']).'</td>'.
				'<td>'. $this->getDiffLink($group->getUnixName(),
						$Row['file'],
						$Row['prev_version'],
						$Row['actual_version']).'</td>'.
				'<td>'. $this->getActualVersionLink($group->getUnixName(),
					$Row['file'], $Row['actual_version']).
				'</td>
				<td>'. htmlspecialchars($Row['log_text']).'</td>
				<td>'. util_make_link_u ($Row['author'],
							 user_get_object_by_name ($Row['author'])->getId(),
							 $Row['author']).'</td>
				</tr>';
			}
			echo $GLOBALS['HTML']->listTableBottom();
			echo '</td></tr>';
		}
	}

	/**
	* Return an array with titles of Box to display the entries
	*
	* @return   Array  The array containing the titles
	*
	*/
	function getTitleArr() {
		$title_arr=array();
		$title_arr[]=_('Filename');
		$title_arr[]=_('Date');
		$title_arr[]=_('Previous Version');
		$title_arr[]=_('Current Version');
		$title_arr[]=_('Log Message');
		$title_arr[]=_('Author');
		return $title_arr;
	}

	/**
	* Return a link to the File in svnweb
	*
	* @param    String  $GroupName is the Name of the project
	* @param    String  $FileName  is the FileName ( with path )
	* @param 	Int		$LatestRevision	is the last revision for the file
	*
	* @return   String  The string containing a link to the File in the svnweb
	*
	*/
	function getFileLink($GroupName, $FileName, $LatestRevision) {
		return util_make_link ('/scm/viewvc.php'.$FileName .
				       '?root='.$GroupName.'&view=log&rev=' .
				       $LatestRevision,
				       $FileName) ;
	}

	/**
	* Return a link to the File in viewcvs in the specified Version
	*
	* @param    String  $GroupName is the Name of the project
	* @param    String  $FileName  is the FileName ( with path )
	* @param    String  $Version   the version to retrieve
	*
	* @return   String  The string containing a link to the File in the viewcvs
	*
	*/
	function getActualVersionLink($GroupName, $FileName, $Version) {
		return util_make_link ('/scm/viewvc.php'.$FileName .
				       '?root='.$GroupName.'&rev='.$Version,
				       $Version);
	}

	/**
	* Return a link to the diff between two versions of a File in viewcvs
	*
	* @param    String  $GroupName is the Name of the project
	* @param    String  $FileName  is the FileName ( with path )
	* @param    String  $PrevVersion   First version to retrieve
	* @param    String  $ActualVersion Second version to retrieve
	*
	* @return   String  The string containing a link to the File in the cvsweb
	*
	*/
	function getDiffLink($GroupName, $FileName, $PrevVersion, $ActualVersion) {
		return util_make_link ('/scm/viewvc.php'.$FileName .
				       '?root='.$GroupName.'&r1='.$PrevVersion .
				       '&r2='.$ActualVersion,
				       _('Diff To').' '.$PrevVersion);
	}


	/**
	* Function to add svntracker lines to a loginfo file
	*
	* @param   string  $path The filename of loginfo
	*
	*/
	/*function addCvsTrackerToFile($path) {
		global  $cvs_binary_version;

		$FOut = fopen($path, "a");
		if($FOut) {
			fwrite($FOut, "# BEGIN added by gforge-plugin-cvstracker\n");
			if ( $cvs_binary_version == "1.12" ) {
				$Line = "ALL ( php -q -d include_path=".ini_get('include_path').
					" ".forge_get_config('plugins_path')."/cvstracker/bin/post.php".
					" %r %p %{sVv} )\n";
			}
			if ( $cvs_binary_version == "1.11") {
				$Line = "ALL ( php -q -d include_path=".ini_get('include_path').
					" ".forge_get_config('plugins_path')."/cvstracker/bin/post.php".
					" %r %{sVv} )\n";
			}
			fwrite($FOut,$Line);
			fwrite($FOut, "# END added by gforge-plugin-cvstracker\n");
			fclose($FOut);
		}
	}*/

	/**
	* Retrieve a file into a temporary directory from a SVN server
	*
	* @param String $repos Repository Name
	* @param String $file File Name
	*
	* return String the FileName in the working repository
	*/
	/*function getCvsFile($repos,$file) {
		$actual_dir = getcwd();
		$tempdirname = tempnam("/tmp","cvstracker");
		if (!$tempdirname)
			return false;
		if (!unlink($tempdirname))
			return false;

		// Create the temporary directory and returns its name.
		if (!mkdir($tempdirname))
			return false;

		if (!chdir($tempdirname))
			return false;
		system("cvs -d ".$repos." co ".$file);

		chdir($actual_dir);
		return $tempdirname.$file;
	}*/

	/**
	* putCvsFile commit a file to the repository
	*
	* @param String $repos Repository
	* @param String $file to commit
	* @param String $message to commit
	*/
	/*function putCvsFile($repos,$file,$message="Automatic updated by cvstracker") {
		system("cvs -d ".$repos." ci -m \"".$message."\" ".$file);
		unlink ($file);
	}*/

	/**
	* The function to be called for a Hook
	*
	* @param    String  $hookname  The name of the hookname that has been happened
	* @param    String  $params    The params of the Hook
	*
	*/
	function CallHook ($hookname, &$params) {
		global $group_id, $G_SESSION, $HTML, $use_svntrackerplugin,$aid ;

		if ($hookname == "artifact_extra_detail") {
			$DBResult = db_query_params ('SELECT * FROM plugin_svntracker_data_master,
plugin_svntracker_data_artifact WHERE plugin_svntracker_data_artifact.group_artifact_id=$1 AND plugin_svntracker_data_master.holder_id=plugin_svntracker_data_artifact.id ORDER BY svn_date',
						     array ($aid));
			$this->getCommitEntries($DBResult, $group_id);
		} elseif ($hookname == "task_extra_detail") {
			$DBResult = db_query_params ('SELECT * FROM plugin_svntracker_data_master,
plugin_svntracker_data_artifact WHERE plugin_svntracker_data_artifact.project_task_id=$1 AND plugin_svntracker_data_master.holder_id=plugin_svntracker_data_artifact.id ORDER BY svn_date',
						     array ($params['task_id']));
			$this->getCommitEntries($DBResult, $group_id);
		} elseif ($hookname == 'cmd_for_post_commit_hook') {
			$params['hooks'][$this->name] = '/usr/bin/php -d include_path='.ini_get('include_path').
				' '.forge_get_config('plugins_path').'/svntracker/bin/post.php '.$params['repos'].' "$2"';
		} /*elseif ($hookname == "update_svn_repository") {
			$Group = group_get_object($params["group_id"]);
			if ($Group->usesPlugin("cvstracker")) {
				$LineFound=FALSE;
				$FIn = fopen(getCvsFile( $params["file_name"],
					"CVSROOT/loginfo"),"r");
				if ($FIn) {
					while (!feof($FIn))  {
						$Line = fgets ($FIn);
						if(!preg_match("/^#/", $Line) &&
							preg_match("/cvstracker/",$Line)) {
								$LineFound = TRUE;
							}
				 	}
				}
				fclose($FIn);
				if($LineFound==FALSE) {
					$newfile=getCvsFile($params["file_name"],
						 "CVSROOT/loginfo");
					$this->addCvsTrackerToFile($newfile);
					$this->putCvsFile($params["file_name"],
						$newfile);
				}
			}
		}*/
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
