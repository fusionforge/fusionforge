<?php   
/**
 * Base theme class.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

class Theme extends Layout {

	/**
	 * Theme() - Constructor
	 */
	function Theme() {
		// Parent constructor
		$this->Layout();

		// The root location for images
		$this->imgroot = '/themes/gforge/images/';

		// The content background color
		// sky blue
		$this->COLOR_CONTENT_BACK= 'white';

		// The background color
		$this->COLOR_BACK= '#FFFFFF';

		// The primary light background color
		// Alternate list
		$this->COLOR_LTBACK1= '#FFEDCF';

		// The secondary light background color
		$this->COLOR_LTBACK2= '#E0E0E0';

		// The HTML box title color
		$this->COLOR_HTMLBOX_TITLE = '#F57900';

		// The HTML box background color
		$this->COLOR_HTMLBOX_BACK = '#FFEDCF';

		// Font Face Constants
		// The content font
		$this->FONT_CONTENT = 'Helvetica';
		// The HTML box title font
		$this->FONT_HTMLBOX_TITLE = 'Helvetica';
		// The HTML box title font color
		$this->FONTCOLOR_HTMLBOX_TITLE = '#C6BCBF';
		// The content font color
		$this->FONTCOLOR_CONTENT = '#000000';
		//The smaller font size
		$this->FONTSIZE_SMALLER='x-small';
		//The smallest font size
		$this->FONTSIZE_SMALLEST='xx-small';
		//The HTML box title font size
		$this->FONTSIZE_HTMLBOX_TITLE = 'small';

	}

	/**
	 *	header() - "steel theme" top of page
	 *
	 * @param	array	Header parameters array
	 */
	function header($params) {
		if (!$params['title']) {
			$params['title'] = "GForge";
		} else {
			$params['title'] = "GForge: " . $params['title'];
		}
		echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo _('en') ?>">
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo $params['title']; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['sys_urlprefix']; ?>/themes/lite/css/theme.css" />
	<script type="text/javascript">
	<!--
	function help_window(helpurl) {
		HelpWin = window.open( '<?php echo ((session_issecure()) ? 'https://'.
			$GLOBALS['sys_default_domain'] : 'http://'.$GLOBALS['sys_default_domain']); ?>' + helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=400');
	}
	// -->
	<?php plugin_hook ("javascript",false) ; ?>
	</script>
<?php
	      if (_('default_font') != 'default_font') {
		      $site_fonts = _('default_font');
	      } else {
		      $site_fonts = 'helvetica' ;
	      }
	?>

<style type="text/css">
	<!--
	OL,UL,P,BODY,TD,TR,TH,FORM { font-family: <?php echo $site_fonts; ?>; font-size:<?php echo $this->FONTSIZE; ?>; color: <?php echo $this->FONTCOLOR_CONTENT ?>; }

	H1 { font-size: x-large; font-family: <?php echo $site_fonts; ?>; }
	H2 { font-size: large; font-family: <?php echo $site_fonts; ?>; }
	H3 { font-size: medium; font-family: <?php echo $site_fonts; ?>; }
	H4 { font-size: small; font-family: <?php echo $site_fonts; ?>; }
	H5 { font-size: x-small; font-family: <?php echo $site_fonts; ?>; }
	H6 { font-size: xx-small; font-family: <?php echo $site_fonts; ?>; }

	PRE,TT { font-family: courier,sans-serif }
	-->
</style>

</head>

<body>
<div class="header">
<table border="0px" width="100%" cellspacing="0px" cellpadding="0px" class="content">

	<tr>
		<td><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/"><img src="/themes/lite/images/gforge_logo.png" border="0" alt="Gforge Logo" width="200px" /></a></td>
		<td align="right"><?php echo $this->searchBox(); ?></td>
		<td align="right"><?php
			if (session_loggedin()) {
				?>
				<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/account/logout.php">Logout</a><br />
				<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/account/">My Account</a>
				<?php
			} else {
				?>
				<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/account/login.php">Login</a><br />
				<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/account/register.php">New Account</a>
				<?php
			}

		?></td>
		<td>&nbsp;&nbsp;</td>
	</tr>

</table>
</div>
<div class="menu">

<?php echo $this->mainMenu($params); ?>

		<!-- Inner Tabs / Shell -->
<?php


if ($params['group']) {
			?>
				<div class="union_menu" >
				</div>	
				<?php
				echo $this->projectTabs($params['toptab'],$params['group']);
				?>
			<?php

}

?>
</div>
<div class="content">
	<?php

	}

	function footer($params) {

	?>

			<!-- end main body row -->


		<!-- end inner body row -->
</div> <!-- end of content -->
<!-- PLEASE LEAVE "Powered By GForge" on your site -->
<br />
<center>
<a href="http://gforge.org/"><img src="<?php echo $GLOBALS['sys_urlprefix']; ?>/images/pow-gforge.png" alt="Powered By GForge Collaborative Development En
vironment" border="0" /></a>
</center>

</body>
</html>
</xml>
<?php

	}


	/**
	 * boxTop() - Top HTML box
	 *
	 * @param   string  Box title
	 * @param   bool	Whether to echo or return the results
	 * @param   string  The box background color
	 */
	function boxTop($title) {
		return '
		<table cellspacing="0" cellpadding="1" width="100%" border="0" bgcolor="' .$this->COLOR_HTMLBOX_TITLE.'">
		<tr><td>
			<table cellspacing="0" cellpadding="2" width="100%" border="0" bgcolor="'. $this->COLOR_HTMLBOX_BACK.'">
				<tr bgcolor="'.$this->COLOR_HTMLBOX_TITLE.'" align="center">
					<td colspan="2"><span class=titlebar>'.$title.'</span></td>
				</tr>
				<tr align=left>
					<td colspan="2">';
	}

	/**
	 * boxMiddle() - Middle HTML box
	 *
	 * @param   string  Box title
	 * @param   string  The box background color
	 */
	function boxMiddle($title) {
		return '
					</td>
				</tr>
				<tr bgcolor="'.$this->COLOR_HTMLBOX_TITLE.'" align="center">
					<td colspan="2"><SPAN class=titlebar>'.$title.'</SPAN></td>
				</tr>
				<tr align=left bgcolor="'. $this->COLOR_HTMLBOX_BACK .'">
					<td colspan="2">';
	}

	/**
	 * boxGetAltRowStyle() - Get an alternating row style for tables
	 *
	 * @param			   int			 Row number
	 */
	function boxGetAltRowStyle($i) {
		if ($i % 2 == 0) {
			return ' BGCOLOR="#FFFFFF"';
		} else {
			return ' BGCOLOR="' . $this->COLOR_LTBACK1 . '"';
		}
	}

	/**
	 * boxBottom() - Bottom HTML box
	 *
	 * @param   bool	Whether to echo or return the results
	 */
	function boxBottom() {
		return '
					</td>
				</tr>
			</table>
		</td></tr>
		</table><p>';
	}

       function mainMenu($params) {
		global $sys_use_trove,$sys_use_snippet,$sys_use_people;
		
		$TABS_DIRS[]='/';
		$TABS_DIRS[]='/my/';
		if ($sys_use_trove) {
		        $TABS_DIRS[]='/softwaremap/';
		}
		if ($sys_use_snippet) {
		        $TABS_DIRS[]='/snippet/';
		}
		if ($sys_use_people) {
		        $TABS_DIRS[]='/people/';
		}
		$TABS_TITLES[]=_('Home');
		$TABS_TITLES[]=_('My&nbsp;Page');
		if ($sys_use_trove) {
		        $TABS_TITLES[]=_('Project&nbsp;Tree');
		}
		if ($sys_use_snippet) {
		        $TABS_TITLES[]=_('Code&nbsp;Snippets');
		}
		if ($sys_use_people) {
		        $TABS_TITLES[]=_('Project&nbsp;Openings');
		}
		
		// outermenu hook
		$PLUGIN_TABS_DIRS = Array();
		$hookParams['DIRS'] = &$PLUGIN_TABS_DIRS;
		$hookParams['TITLES'] = &$TABS_TITLES;
		plugin_hook ("outermenu", $hookParams) ;
		$TABS_DIRS = array_merge($TABS_DIRS, $PLUGIN_TABS_DIRS);
		
		if (user_ismember(1,'A')) {
		        $TABS_DIRS[]='/admin/';
		        $TABS_TITLES[]=_('Admin');
		}
		if (user_ismember($GLOBALS['sys_stats_group'])) {
		        $TABS_DIRS[]='/reporting/';
		        $TABS_TITLES[]=_('Reporting');
		}
		if(isset($params['group']) && $params['group']) {
		        // get group info using the common result set
		        $project =& group_get_object($params['group']);
		        if ($project && is_object($project)) {
		                if ($project->isError()) {
		
		                } elseif (!$project->isProject()) {
		
		                } else {
		                        $TABS_DIRS[]='/projects/'.$project->getUnixName().'/';
		                        $TABS_TITLES[]=$project->getPublicName();
		                        $selected=count($TABS_DIRS)-1;
		                }
		        }
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/my/') || strstr(getStringFromServer('REQUEST_URI'),'/account/') || strstr(getStringFromServer('REQUEST_URI'),'/themes/') ) {
		        $selected=array_search("/my/", $TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'softwaremap')) {
		        $selected=array_search("/softwaremap/", $TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/snippet/')) {
		        $selected=array_search("/snippet/", $TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/people/')) {
		        $selected=array_search("/people/", $TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/reporting/')) {
		        $selected=array_search('/reporting/',$TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/admin/') && user_ismember(1,'A')) {
		        $selected=array_search('/admin/',$TABS_DIRS);
		} elseif (count($PLUGIN_TABS_DIRS)>0) {
		        foreach ($PLUGIN_TABS_DIRS as $PLUGIN_TABS_DIRS_VALUE) {
		                if (strstr($GLOBALS['REQUEST_URI'],$PLUGIN_TABS_DIRS_VALUE)) {
		                        $selected=array_search($PLUGIN_TABS_DIRS_VALUE,$TABS_DIRS);
		                        break;
		                }
		        }
		} else {
		        $selected=0;
		}
		if (!$this->COLOR_SELECTED_TAB) {
		        $this->COLOR_SELECTED_TAB= '#e0e0e0';
		}
		echo $this->tabGenerator($TABS_DIRS,$TABS_TITLES,false,$selected,$this->COLOR_SELECTED_TAB,'100%');
	}

        function tabGenerator($TABS_DIRS,$TABS_TITLES,$nested=false,$selected=false,$sel_tab_bgcolor='WHITE',$total_width='100%') {

                $count=count($TABS_DIRS);
                $width=intval((100/$count));
                $return = '';
                if ($nested) {
                        $inner='bottomtab';
                } else {
                        $inner='toptab';
                }
		$return .= "
			<!-- start tabs -->
			<div class='".$inner."'>";	
                $rowspan = '';
                for ($i=0; $i<$count; $i++) {
                	$wassel=($selected==$i-1);
			$issel=($selected==$i);
                        $return .= '
                              <a class="'. (($issel)?'tabsellink':'tablink') .'" href="'.$TABS_DIRS[$i].'">'.$TABS_TITLES[$i].'</a>';
                                //
                                //      Last graphic on right-side
                                //
                }
                //
                //      Building a bottom row in this table, which will be darker
                //
                if ($selected == 0) {
                        $beg_cols=0;
                        $end_cols=((count($TABS_DIRS)*3)-3);
                } elseif ($selected == (count($TABS_DIRS)-1)) {
                        $beg_cols=((count($TABS_DIRS)*3)-3);
                        $end_cols=0;
                } else {
                        $beg_cols=($selected*3);
                        $end_cols=(((count($TABS_DIRS)*3)-3)-$beg_cols);
                }

                return $return.'</div>
                <!-- end tabs -->';
        }

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
