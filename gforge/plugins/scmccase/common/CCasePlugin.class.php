<?php
/** Clear Case plugin for Gforge
 * Copyright 2003 Roland Mas <lolando@debian.org>
 * Copyright 2004 Roland Mas <roland@gnurandal.com> 
 *                The Gforge Group, LLC <http://gforgegroup.com/>
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

class CCasePlugin extends SCM {
	function CCasePlugin () {
		$this->SCM () ;
		$this->name = "scmccase";
		$this->text = 'CCASE';
		$this->hooks[] = "scm_page";
		$this->hooks[] = "scm_admin_update";
		$this->hooks[] = "scm_admin_page";
		$this->hooks[] = "scm_stats";
		$this->hooks[] = "scm_plugin";

		require_once('plugins/scmccase/config.php') ;
		
		$this->default_ccase_server = $default_ccase_server ;
		$this->this_server = $this_server ;
		$this->tag_pattern = $tag_pattern ;
		$this->enabled_by_default = $enabled_by_default ;

		$this->register () ;
	}

	function CallHook ($hookname, $params) {
		global $Language, $HTML ;
		
		switch ($hookname) {
		case "scm_page":
			$group_id = $params['group_id'] ;
			$this->display_scm_page ($group_id) ;
			break ;
		case "scm_admin_update":
			$this->scm_admin_update ($params) ;
			break ;
		case "scm_admin_page":
			$this->display_scm_admin_page ($params) ;
			break ;
		case "scm_stats":
			$this->display_stats ($params) ;
			break;
		case "scm_plugin":
			$scm_plugins=& $params['scm_plugins'];
			$scm_plugins[]=$this->name;
			break;
		default:
			// Forgot something
		}
	}

	function display_scm_page ($group_id) {
		global $Language, $HTML ;

		$project =& group_get_object($group_id);
		
		if ($project->usesPlugin ("scmccase")) {
			$vob_tag = ereg_replace ("GROUPNAME", $project->getUnixName (), $this->tag_pattern) ;
	
		 print '<h2>Clear Case</h2>
		                <p>Documentation for Clear Case is probably available somewhere.
                                </p>' ;


// ######################## table for summary info

?>
<table width="100%">
	<tr valign="top">
		<td width="65%">
<?php
// ############################ developer access
			
	 echo "<b>".print(_('Clear Case Access'))."</b>" ;

	 print "<p>Either mount the VOB with <tt>cleartool mount $vob_tag</tt> or
	 select the <tt>$vob_tag</tt> VOB in your Clear Case Explorer.
	 </p>" ;

// ################## summary info

?>

		</td>
		<td width="35%">

<?php

// ############################## CCase Browsing

$anonymous = 1;
if (session_loggedin()) {
   $perm =& $project->getPermission(session_get_user());
   $anonymous = !$perm->isMember();
}
 
if ($project->enableAnonCVS() || !$anonymous) {
	echo $HTML->boxTop(_('History'));

	echo '<b>'._('Browse the Clear Case tree').'</b><p>'._('Browsing the Clear Case tree gives you a great view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.').'</p>' ;

	$browse_url = "http://" . $this->GetGroupServer($group_id) . "/ccweb" ;
	// $browse_url = $browse_url . "?vob_tag=".$vob_tag ;
	echo "<p><a href=\"$browse_url\">Browse</a> CCase tree</p>" ;

       echo $HTML->boxBottom();
}

?>
		</td>
	</tr>
</table>

<?php

// ************************************************



		}

	}

	function scm_admin_update ($params) {
		$group =& group_get_object($params['group_id']);

		if ($params['scmccase_ccase_server'] && $params['scmccase_ccase_server'] != "") {
			$this->SetGroupServer ($params['group_id'], $params['scmccase_ccase_server']) ;
		} else {
			$this->SetGroupServer ($params['group_id'], $this->GetDefaultServer ()) ;
		}
	}

	function display_scm_admin_page ($params) {
		$group =& group_get_object($params['group_id']);

		if ( $group->usesPlugin ( $this->name ) ) {
?>
			<input type="text" name="scmccase_ccase_server" value="<?php echo $this->GetGroupServer ($params['group_id']) ?>"> <strong>Clear Case server</strong><br /><br />
<?php
		}
	}

	function display_stats ($params) {
		global $Language ;
		$group_id = $params['group_id'] ;
		$result = db_query("
			SELECT commits, adds
			FROM plugin_scmccase_stats
			WHERE group_id='$group_id'");
		$commit_num = db_result($result,0,0);
		$add_num    = db_result($result,0,1);
		if (!$commit_num) {
			$commit_num=0;
		}
		if (!$add_num) {
			$add_num=0;
		}
		$commit_count=number_format($commit_num, 0);
		$add_count=number_format($add_num, 0);
		echo ' (CCase: <strong>'.$commit_count.'</strong> ';
		printf(ngettext("commit","commits",$commit_count),$commit_count);
		echo ', <strong>'.$add_count.'</strong> '; 
		printf(ngettext("add","adds",$add_count),$add_count);
		echo ' )';
	}
	

	function GetDefaultServer () {
		return $this->default_ccase_server ;
	}

	function GetGroupServer ($group_id) {
		$sql = "SELECT ccase_host FROM plugin_scmccase_group_usage WHERE group_id = $group_id" ;
		$res = db_query($sql);
		if (db_numrows($res) == 0) {
			return $this->default_ccase_server ;
		} else {
			return db_result($res,0,'ccase_host');
		}
	}

	function SetGroupServer ($group_id, $server) {
		db_begin () ;
		$sql = "SELECT ccase_host FROM plugin_scmccase_group_usage WHERE group_id = $group_id" ;
		$res = db_query($sql);
		if (db_numrows($res) == 0) {
			$sql = "INSERT INTO plugin_scmccase_group_usage (group_id, ccase_host) VALUES ($group_id, '$server')" ;
		} else {
			$sql = "UPDATE plugin_scmccase_group_usage SET ccase_host = '$server' WHERE group_id = $group_id" ;
			
		}
		$res = db_query($sql);
		db_commit () ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
