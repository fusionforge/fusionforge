<?php

class Theme extends Layout {
	/**
	 * Theme() - Constructor
	 */
	function Theme() {
	}

	/**
	 *	header() - "steel theme" top of page
	 *
	 * @param	array	Header parameters array
	 */
	function header($params) {
		if (isset($_POST['selectmenu']) && ($_POST['selectmenu'] == "yes"))
		{
		header("Location:".$_POST['menuList']);		
		}
		if (!$params['title']) {
			$params['title'] = forge_get_config ('forge_name');
		} else {
			$params['title'] = forge_get_config ('forge_name').": " . $params['title'];
		}
		print '<?xml version="1.0" encoding="utf-8"?>';
		?>
		<!DOCTYPE html
		PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo _('en') ?>" lang="<?php echo _('en') ?>">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo $params['title']; ?></title>
		<?php $this->headerLink(); ?>
		</head>
		
		<body>
		<p align=left>
		<?php echo util_make_link ('/',_('Home')); ?></h2></a>
		</p>
		<p align=right>
		<?php
		if (session_loggedin()) {
		?>
		<b><?php echo util_make_link ('/account/logout.php',_('Log Out')); ?></b>
		<b><?php echo util_make_link ('/account/',_('My Account')); ?></b>
		<?php
		} else {
		?>
		<b><?php echo util_make_link ('/account/login.php',_('Log In')); ?></b>
		<b><?php echo util_make_link ('/account/register.php',_('New Account')); ?></b>
		<?php
		}
		$params['template'] = '<b>{menu}</b>';
		plugin_hook ('headermenu', $params);
		?>
		</p>

		<p align=left>
		<?php echo $this->searchBox(); ?>
		</p>

		<p align=left>
		<?php echo $this->outerTabs($params); ?>
		<?php
		if (isset($params['group']) && $params['group']) {
		?>
		<?php
		echo $this->projectTabs($params['toptab'],$params['group']);
		?>
		<?php
		}
		?>
		</p>
		<?php
	}

	function searchBox() {
		global $words,$forum_id,$group_id,$group_project_id,$atid,$exact,$type_of_search;

		// if there is no search currently, set the default
		if ( ! isset($type_of_search) ) {
			$exact = 1;
		}

		print '
		<form action="/search/" method="post">
		<select name="type_of_search">';
		if ($atid && $group_id) {
			$group =& group_get_object($group_id);
			if ($group && is_object($group)) {
				$ath = new ArtifactTypeHtml($group,$atid);
				if ($ath && is_object($ath)) {
				print '
				<option value="'.SEARCH__TYPE_IS_ARTIFACT.'"'.( $type_of_search == SEARCH__TYPE_IS_ARTIFACT ? ' selected="selected"' : '' ).'>'. $ath->getName() .'</option>';
				}
			}
		} else if ($group_id && $forum_id) {
			print '
			<option value="'.SEARCH__TYPE_IS_FORUM.'"'.( $type_of_search == SEARCH__TYPE_IS_FORUM ? ' selected="selected"' : '' ).'>'._('This forum').'</option>';
		} else if ($group_id && $group_project_id) {
			print '
			<option value="task"'. ( $type_of_search == 'tasks' ? ' selected="selected"' : '').'>'._('Tasks').'</option>';
		}

		print '
			<option value="'.SEARCH__TYPE_IS_SOFTWARE.'"'.( $type_of_search == SEARCH__TYPE_IS_SOFTWARE ? ' selected="selected"' : '' ).'>'._('Project').'</option>';
		print '
			<option value="'.SEARCH__TYPE_IS_SKILL.'"'.( $type_of_search == SEARCH__TYPE_IS_SKILL ? ' selected="selected"' : '' ).'>'._('Skill').'</option>';
		print '
			<option value="'.SEARCH__TYPE_IS_PEOPLE.'"'.( $type_of_search == SEARCH__TYPE_IS_PEOPLE ? ' selected="selected"' : '' ).'>'._('People').'</option>';

		print '</select>';
		
		if ( isset($forum_id) ) {
			print '
			<input type="hidden" value="'.$forum_id.'" name="forum_id" />';
		}
		if ( isset($group_id) ) {
			print '
			<input type="hidden" value="'.$group_id.'" name="group_id" />';
		}
		if ( isset($atid) ) {
			print '
			<input type="hidden" value="'.$atid.'" name="atid" />';
		}
		if ( isset($group_project_id) ){
			print '
			<input type="hidden" value="'.$group_project_id.'" name="group_project_id" />';
		}
		print '
		<input type="text" size="12" name="words" value="'.$words.'" />';
		print '<input type="submit" name="Search" value="'._('Search').'" />';
		print '</form>';
	}



	function footer($params) {
		?>
		<!-- end main body row -->
		<!-- PLEASE LEAVE "Powered By FusionForge" on your site -->
		<br />
		<div align="right">
		<a href="http://fusionforge.org/">Powered By FusionForge</a><br />
		</div>
<?php		

		if (forge_get_config('show_source')) {
			echo util_make_link ('/source.php?file='.getStringFromServer('SCRIPT_NAME'),_('Show Source'),array('class'=>'showsource'));
		}
		?>
		</body>
		</html>
		<?php
	}

	

	/**
	 * boxTop() - Top HTML box
	 *
	 * @param   string  Box title
	 */
	function boxTop($title) {
		return '<!-- boxTop --><br />'.$title.'<br />';
	}

	
	/**
	 * boxMiddle() - Middle HTML box
	 *
	 * @param   string  Box title
	 */
	function boxMiddle($title) {
		return '<!-- boxMiddle --><br />'.$title.'<br />';
	}

	/**
	 * boxBottom() - Bottom HTML box
	 *
	 * @param   bool	Whether to echo or return the results
	 */
	function boxBottom() {
		return '
			<!-- Box Bottom Start -->
			<br />
		<!-- Box Bottom End -->';
	}

	/**
	 * listTableTop() - Takes an array of titles and builds the first row of a new table.
	 *
	 * @param	   array   The array of titles
	 * @param	   array   The array of title links
	 */
	function listTableTop ($title_arr,$links_arr=false) {
		$return = '
		<!-- listTableTop -->
		<table>
        <tr><td>
		<table>
		<tr>';
		$count=count($title_arr);
		if ($links_arr) {
			for ($i=0; $i<$count; $i++) {
				$return .= '
				<td align="left"><a  href="'.$links_arr[$i].'">'.$title_arr[$i].'</a></td>';
			}
		} else {
			for ($i=0; $i<$count; $i++) {
				$return .= '
				<td align="left">'.$title_arr[$i].'</td>';
			}
		}
		$return .= '
		</tr>
		<tr align="left">
		<td colspan="'.$count.'" height="1"></td>
		</tr>';
		return $return;
	}

	function listTableBottom() {
		return '</table></td>
			<!-- <td valign="top" align="right" width="10"></td> -->
			</tr></table>';
	}



/**
	 * boxGetAltRowStyle() - Get an alternating row style for tables
	 *
	 * @param			   int			 Row number
	 */
	function boxGetAltRowStyle($i) {
	}


	function tabGenerator($TABS_DIRS,$TABS_TITLES,$nested=false,$selected=false,$sel_tab_bgcolor='WHITE',$total_width='100%') {
		$count=count($TABS_DIRS);
		$return = '
		<form name="menuForm" method="post" action="/">
		<select name="menuList">';
		for ($i=0; $i<$count; $i++) {
		$return .= '
		<option '. (($selected==$i)?'selected':'').' value="'.$TABS_DIRS[$i].'"> '.$TABS_TITLES[$i].'</option>';
		}
		$return .= '</select> 
		<input type="hidden" name="selectmenu" value="yes">
		<input type="submit" value="GO"></form>
		';
		return $return;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
