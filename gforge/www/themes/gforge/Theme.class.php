<?php

require_once $gfwww.'include/Layout.class.php';

define('THEME_DIR', util_make_url ('/themes/gforge'));

define('TOP_TAB_HEIGHT', 30);
define('BOTTOM_TAB_HEIGHT', 22);

class Theme extends Layout {

    function Theme() {
        // Parent constructor
        $this->Layout();

        $this->imgroot = THEME_DIR.'/images/';
        $this->COLOR_CONTENT_BACK= '#ffffff';
        $this->COLOR_LTBACK1= '#eeeeef';
        $this->COLOR_LTBACK2= '#fafafa';
        $this->COLOR_SELECTED_TAB= '#e0e0e0';
        $this->COLOR_HTMLBOX_TITLE = '#bbbbbb';
        $this->COLOR_HTMLBOX_BACK = '#eaecef';
        $this->FONT_CONTENT = 'helvetica';
        $this->FONT_HTMLBOX_TITLE = 'helvetica';
        $this->FONTCOLOR_HTMLBOX_TITLE = '#333333';
        $this->FONTCOLOR_CONTENT = '#333333';
        $this->FONTSIZE = 'small';
        $this->FONTSIZE_SMALLER='x-small';
        $this->FONTSIZE_SMALLEST='xx-small';
        $this->FONTSIZE_HTMLBOX_TITLE = 'small';
        $this->bgpri = array();
    }

    /**
     * Layout() - Constructor
     */
    function Layout() {
        GLOBAL $bgpri;
        // Constructor for parent class...
        if ( file_exists($GLOBALS['sys_custom_path'] . '/index_std.php') )
            $this->rootindex = $GLOBALS['sys_custom_path'] . '/index_std.php';
        else
            $this->rootindex = $GLOBALS['gfwww'].'index_std.php';
        
        $this->Error();

        /*
        Set up the priority color array one time only
        */
        $bgpri[1] = '#dadada';
        $bgpri[2] = '#dacaca';
        $bgpri[3] = '#dababa';
        $bgpri[4] = '#daaaaa';
        $bgpri[5] = '#da8a8a';

        //determine font for this platform
        if (browser_is_windows() && browser_is_ie()) {

            //ie needs smaller fonts
            $this->FONTSIZE='x-small';
            $this->FONTSIZE_SMALLER='xx-small';
            $this->FONTSIZE_SMALLEST='7pt';

        } else if (browser_is_windows()) {

            //netscape on wintel
            $this->FONTSIZE='small';
            $this->FONTSIZE_SMALLER='x-small';
            $this->FONTSIZE_SMALLEST='x-small';

        } else if (browser_is_mac()){

            //mac users need bigger fonts
            $this->FONTSIZE='medium';
            $this->FONTSIZE_SMALLER='small';
            $this->FONTSIZE_SMALLEST='x-small';

        } else {

            //linux and other users
            $this->FONTSIZE='small';
            $this->FONTSIZE_SMALLER='x-small';
            $this->FONTSIZE_SMALLEST='xx-small';

        }

        $this->FONTSIZE_HTMLBOX_TITLE = $this->FONTSIZE;
    }

    /**
     *    header() - "steel theme" top of page
     *
     * @param    array    Header parameters array
     */
    function header($params) {
        if (!isset($params['title'])) {
            $params['title'] =  $GLOBALS['sys_name'];
        } else {
            $params['title'] =  $GLOBALS['sys_name'] . ': ' . $params['title'];
        }

        print '<?xml version="1.0" encoding="utf-8"?>';
        ?>

<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="<?php echo _('en'); ?>">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $params['title']; ?></title>
    <link rel="icon" type="image/png" href="<?php echo $this->imgroot; ?>icon.png"/>
    <link rel="shortcut icon" href="<?php echo $this->imgroot; ?>icon.png"/>
    <link rel="alternate" title="<?php echo $GLOBALS['sys_name']; ?> - Project News Highlights RSS" href="<?php echo util_make_url ('/export/rss_sfnews.php'); ?>" type="application/rss+xml"/>
    <link rel="alternate" title="<?php echo $GLOBALS['sys_name']; ?> - Project News Highlights RSS" href="<?php echo util_make_url ('/export/rss20_news.php'); ?>" type="application/rss+xml"/>
    <link rel="alternate" title="<?php echo $GLOBALS['sys_name']; ?> - New Projects RSS" href="<?php echo util_make_url ('/export/rss_sfprojects.php'); ?>" type="application/rss+xml"/>

    <script language="JavaScript" type="text/javascript">
    <!--

    function admin_window(adminurl) {
        AdminWin = window.open( adminurl, 'AdminWindow','scrollbars=yes,resizable=yes, toolbar=yes, height=400, width=400, top=2, left=2');
        AdminWin.focus();
    }
    function help_window(helpurl) {
        HelpWin = window.open( helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=600');
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

    $this->headerCSS();
?>
</head>

<body>

  <?php
  $this->bodyHeader($params);
  }

  function bodyHeader($params){
	global $user_guide;

  ?>
<div class="header">
  <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
		  <td class="topLeft"><?php echo util_make_link ('/', html_image('header/top-logo.gif',205,54,array('border'=>'0'))); ?></td>
        <td class="middleRight"><?php echo $this->searchBox(); ?></td>
        <td class="middleRight"><?php
        if (session_loggedin()) {
        	echo util_make_link ('/account/logout.php', _('Log Out'));
        	echo ' | ';
        	echo util_make_link ('/account/', _('My Account'));
        } else {
        	echo util_make_link ('/account/login.php', _('Log In'));
        	echo ' | ';
        	if (!$GLOBALS['sys_user_reg_restricted']) {
        		echo util_make_link ('/account/register.php', _('New Account'));
        	}
        }

        plugin_hook ('headermenu', $params);

        echo $this->quickNav();
        ?></td>
        <td>&nbsp;&nbsp;</td>
      </tr>
  
  </table>
</div>

<!-- outer tabs -->
<table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td><?php echo $this->outerTabs($params); ?></td>
    </tr>
</table>

<!-- inner tabs -->
<table border="0" width="100%" cellspacing="0" cellpadding="0">
    <?php
    if (isset($params['group']) && $params['group']) {
    ?>
      <tr>
        <td>
           <?php
           echo $this->projectTabs($params['toptab'],$params['group']);
           ?>
        </td>
      </tr>
    <?php
    }
    ?>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0">  
  <tr>
    <td class="mainCanvas"> <!-- main body area -->
    <?php
    }

    function footer($params) {
    ?>
    </td> <!-- end main body area -->
  </tr>
</table>  
 
<!-- PLEASE LEAVE "Powered By GForge" on your site -->
<br />
<center>
<a href="http://gforge.org/"><img src="/images/pow-gforge.png" alt="Powered By GForge Collaborative Development Environment" border="0" /></a>
</center>

<?php
global $sys_show_source;
if ($sys_show_source) {
    global $SCRIPT_NAME;
    print util_make_link ('/source.php?file=' . $SCRIPT_NAME, _('Show source'), array ("class" => "showsource"));
}
?>

</body>
</html>

<?php

    }

    function headerCSS(){
?>
    <link rel="stylesheet" type="text/css" href="<?php echo util_make_url ('/themes/css/gforge.css') ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo THEME_DIR ?>/css/theme.css" />
<?php
       plugin_hook ('cssfile',$this);
    }

    function getRootIndex() {
        return $this->rootindex;
    }

    /**
     * boxTop() - Top HTML box
     *
     * @param   string  Box title
     * @param   bool    Whether to echo or return the results
     * @param   string  The box background color
     */
    function boxTop($title) {
        return '
        <!-- Box Top Start -->

        <table cellspacing="0" cellpadding="0" width="100%" border="0" background="'.$this->imgroot.'vert-grad.png">
        <tr align="center">
            <td valign="top" style="text-align:right" width="10" background="'.$this->imgroot.'box-topleft.png"><img src="'.$this->imgroot.'clear.png" width="10" height="20" /></td>
            <td width="100%" background="'.$this->imgroot.'box-grad.png"><span class="titlebar">'.$title.'</span></td>
            <td valign="top" width="10" background="'.$this->imgroot.'box-topright.png"><img src="'.$this->imgroot.'clear.png" width="10" height="20" /></td>
        </tr>
        <tr>
            <td colspan="3">
            <table cellspacing="2" cellpadding="2" width="100%" border="0">
                <tr align="left">
                    <td colspan="2">

        <!-- Box Top End -->';
    }

    /**
     * boxMiddle() - Middle HTML box
     *
     * @param   string  Box title
     * @param   string  The box background color
     */
    function boxMiddle($title) {
        return '
        <!-- Box Middle Start -->
                    </td>
                </tr>
                <tr align="center">
                    <td colspan="2" background="'.$this->imgroot.'box-grad.png"><span class="titlebar">'.$title.'</span></td>
                </tr>
                <tr align="left">
                    <td colspan="2">
        <!-- Box Middle End -->';
    }

    /**
     * boxBottom() - Bottom HTML box
     *
     * @param   bool    Whether to echo or return the results
     */
    function boxBottom() {
        return '
            <!-- Box Bottom Start -->
                    </td>
                </tr>
            </table>
            </td>
        </tr>
        </table><br />
        <!-- Box Bottom End -->';
    }

    /**
     * boxGetAltRowStyle() - Get an alternating row style for tables
     *
     * @param               int             Row number
     */
    function boxGetAltRowStyle($i) {
        if ($i % 2 == 0) {
            return ' bgcolor="#EAEAEA"';
        } else {
            return ' bgcolor="#E0E0E0"';
        }
    }

    /**
     * listTableTop() - Takes an array of titles and builds the first row of a new table.
     *
     * @param       array   The array of titles
     * @param       array   The array of title links
     */
    function listTableTop ($title_arr,$links_arr=false) {
        $return = '
        <table cellspacing="0" cellpadding="0" width="100%" border="0">
        <tr align="center">
    <!--        <td valign="top" style="text-align:right" width="10" background="'.$this->imgroot.'box-grad.png"><img src="'.$this->imgroot.'box-topleft.png" width="10" height="75" /></td> -->
            <td background="'.$this->imgroot.'box-grad.png">
        <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr>';

        $count=count($title_arr);
        if ($links_arr) {
            for ($i=0; $i<$count; $i++) {
                $return .= '
                <td style="text-align:center"><a class="sortbutton" href="'.util_make_url ($links_arr[$i]).'"><span style="color:'.
                $this->FONTCOLOR_HTMLBOX_TITLE.'"><strong>'.$title_arr[$i].'</strong></span></a></td>';
            }
        } else {
            for ($i=0; $i<$count; $i++) {
                $return .= '
                <td style="text-align:center"><span style="color:'.
                $this->FONTCOLOR_HTMLBOX_TITLE.'"><strong>'.$title_arr[$i].'</strong></span></td>';
            }
        }
        return $return.'</tr>';
    }

    function listTableBottom() {
        return '</table></td>
            <!-- <td valign="top" style="text-align:right" width="10" background="'.$this->imgroot.'box-grad.png"><img src="'.$this->imgroot.'box-topright.png" width="10" height="75" /></td> -->
            </tr></table>';
    }

    function outerTabs($params) {
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

	$user_is_super=false;
	if (session_loggedin()) {
		$projectmaster =& group_get_object(GROUP_IS_MASTER);
		$projectstats =& group_get_object(GROUP_IS_STATS);
		$permmaster =& $projectmaster->getPermission( session_get_user() );
		$permstats =& $projectstats->getPermission( session_get_user() );

		if ($permmaster->isAdmin()) {
			$user_is_super=true;
			$TABS_DIRS[]='/admin/';
			$TABS_TITLES[]=_('Admin');
		}
		if ($permstats->isMember()) {
			$TABS_DIRS[]='/reporting/';
			$TABS_TITLES[]=_('Reporting');
		}
	}
        if(isset($params['group']) && $params['group']) {
            // get group info using the common result set
            $project =& group_get_object($params['group']);
            if ($project && is_object($project)) {
                if ($project->isError()) {

                } elseif (!$project->isProject()) {

                } else {
		    if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
		    	$TABS_DIRS[]='/project/?group_id='. $params['group'];
		    } else {
                    	$TABS_DIRS[]='/projects/'.$project->getUnixName().'/';
		    }
                    $TABS_TITLES[]=$project->getPublicName();
                    $selected=count($TABS_DIRS)-1;
                }
            }
        } elseif (strstr(getStringFromServer('REQUEST_URI'),'/my/') || strstr(getStringFromServer('REQUEST_URI'),'/account/') ||
        strstr(getStringFromServer('REQUEST_URI'),'/themes/') ) {
            $selected=array_search("/my/", $TABS_DIRS);
        } elseif (strstr(getStringFromServer('REQUEST_URI'),'softwaremap')) {
            $selected=array_search("/softwaremap/", $TABS_DIRS);
        } elseif (strstr(getStringFromServer('REQUEST_URI'),'/snippet/')) {
            $selected=array_search("/snippet/", $TABS_DIRS);
        } elseif (strstr(getStringFromServer('REQUEST_URI'),'/people/')) {
            $selected=array_search("/people/", $TABS_DIRS);
        } elseif (strstr(getStringFromServer('REQUEST_URI'),'/reporting/')) {
            $selected=array_search('/reporting/',$TABS_DIRS);
        } elseif (strstr(getStringFromServer('REQUEST_URI'),'/admin/') && $user_is_super) {
            $selected=array_search('/admin/',$TABS_DIRS);
        } elseif (count($PLUGIN_TABS_DIRS)>0) {
            foreach ($PLUGIN_TABS_DIRS as $PLUGIN_TABS_DIRS_VALUE) {
               if (strstr(getStringFromServer('REQUEST_URI'),$PLUGIN_TABS_DIRS_VALUE)) {
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

    /**
     *    projectTabs() - Prints out the project tabs, contained here in case
     *        we want to allow it to be overriden
     *
     *    @param    string    Is the tab currently selected
     *    @param    string    Is the group we should look up get title info
     */
    function projectTabs($toptab,$group) {
        // get group info using the common result set
        $project =& group_get_object($group);
        if (!$project || !is_object($project)) {
            return;
        }
        if ($project->isError()) {
            //wasn't found or some other problem
            return;
        }
        if (!$project->isProject()) {
            return;
        }

        // Summary
	if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
		$TABS_DIRS[]='/project/?group_id='. $group;
	} else {
        	$TABS_DIRS[]='/projects/'. $project->getUnixName() .'/';
	}
        $TABS_TITLES[]=_('Summary');
        (($toptab == 'home') ? $selected=(count($TABS_TITLES)-1) : '' );

        // Project Admin
        $perm =& $project->getPermission( session_get_user() );
        if ($perm->isAdmin()) {
            $TABS_DIRS[]='/project/admin/?group_id='. $group;
            $TABS_TITLES[]=_('Admin');
            (($toptab == 'admin') ? $selected=(count($TABS_TITLES)-1) : '' );
        }
        /* Homepage
        $TABS_DIRS[]='http://'. $project->getHomePage();
        $TABS_TITLES[]=_('Home Page');
        */

        // Project Activity tab 
        $TABS_DIRS[]='/activity/?group_id='. $group;
        $TABS_TITLES[]=_('Activity');
        (($toptab == 'activity') ? $selected=(count($TABS_TITLES)-1) : '' );

        // Forums
        if ($project->usesForum()) {
            $TABS_DIRS[]='/forum/?group_id='.$group;
            $TABS_TITLES[]=_('Forums');
            (($toptab == 'forums') ? $selected=(count($TABS_TITLES)-1) : '' );
        }

        // Artifact Tracking
        if ($project->usesTracker()) {
            $TABS_DIRS[]='/tracker/?group_id='.$group;
            $TABS_TITLES[]=_('Tracker');
            (($toptab == 'tracker' || $toptab == 'bugs' || $toptab == 'support' || $toptab == 'patch')
            ? $selected=(count($TABS_TITLES)-1) : '' );
        }

        // Mailing Lists
        if ($project->usesMail()) {
            $TABS_DIRS[]='/mail/?group_id='.$group;
            $TABS_TITLES[]=_('Lists');
            (($toptab == 'mail') ? $selected=(count($TABS_TITLES)-1) : '' );
        }

        // Project Manager
        if ($project->usesPm()) {
            $TABS_DIRS[]='/pm/?group_id='.$group;
            $TABS_TITLES[]=_('Tasks');
            (($toptab == 'pm') ? $selected=(count($TABS_TITLES)-1) : '' );
        }

        // Doc Manager
        if ($project->usesDocman()) {
            $TABS_DIRS[]='/docman/?group_id='.$group;
            $TABS_TITLES[]=_('Docs');
            (($toptab == 'docman') ? $selected=(count($TABS_TITLES)-1) : '' );
        }

        // Surveys
        if ($project->usesSurvey()) {
            $TABS_DIRS[]='/survey/?group_id='.$group;
            $TABS_TITLES[]=_('Surveys');
            (($toptab == 'surveys') ? $selected=(count($TABS_TITLES)-1) : '' );
        }

        //newsbytes
        if ($project->usesNews()) {
            $TABS_DIRS[]='/news/?group_id='.$group;
            $TABS_TITLES[]=_('News');
            (($toptab == 'news') ? $selected=(count($TABS_TITLES)-1) : '' );
        }

        // SCM systems
        if ($project->usesSCM()) {
            $TABS_DIRS[]='/scm/?group_id='.$group;
            $TABS_TITLES[]=_('SCM');
            (($toptab == 'scm') ? $selected=(count($TABS_TITLES)-1) : '' );
        }

        // groupmenu_after_scm hook
        $hookParams['DIRS'] = &$TABS_DIRS;
        $hookParams['TITLES'] = &$TABS_TITLES;
        $hookParams['toptab'] = &$toptab;
        $hookParams['selected'] = &$selected;
        $hookParams['group_id'] = $group ;

        plugin_hook ("groupmenu_scm", $hookParams) ;

        // Downloads
        if ($project->usesFRS()) {
            $TABS_DIRS[]='/frs/?group_id='.$group;
            $TABS_TITLES[]=_('Files');
            (($toptab == 'frs') ? $selected=(count($TABS_TITLES)-1) : '' );
        }

        // groupmenu hook
        $hookParams['DIRS'] = &$TABS_DIRS;
        $hookParams['TITLES'] = &$TABS_TITLES;
        $hookParams['toptab'] = &$toptab;
        $hookParams['selected'] = &$selected;
        $hookParams['group'] = $group;

        plugin_hook ("groupmenu", $hookParams) ;

        echo $this->tabGenerator($TABS_DIRS,$TABS_TITLES,true,$selected,'white','100%');

    }

    function tabGenerator($TABS_DIRS,$TABS_TITLES,$nested=false,$selected=false,$sel_tab_bgcolor='WHITE',$total_width='100%') {
        $count=count($TABS_DIRS);
        $width=intval((100/$count));

        $return = '

        <!-- start tabs -->

        <table border="0" cellpadding="0" cellspacing="0" width="'.$total_width.'">
        <tr>';

        $folder = $this->imgroot.($nested ? 'bottomtab-new/' : 'toptab-new/');

        for ($i=0; $i<$count; $i++) {
            if ($selected == $i) {
                $left_img   = $folder.'selected-left.gif';
                $middle_img = $folder.'selected-middle.gif';
                $right_img  = $folder.'selected-right.gif';
                $separ_img  = $folder.'selected-separator.gif';
                $css_class  = $nested ? 'bottomTabSelected' : 'topTabSelected';
            } else {
                $left_img   = $folder.'left.gif';
                $middle_img = $folder.'middle.gif';
                $right_img  = $folder.'right.gif';
                $separ_img  = $folder.'separator.gif';
                $css_class  = $nested ? 'bottomTab' : 'topTab';
            }
            
            $clear_img = $this->imgroot.'clear.png';
            
            if ($nested) {
                $tab_height = BOTTOM_TAB_HEIGHT;
                $return .= sprintf(
                    '<td valign="top" width="5" background="%s">
      			<img src="%s" height="%d" width="5" alt="" />
                	</td>', $middle_img, $clear_img, $tab_height );
                $return .= sprintf(
                    '<td background="%s" width="'.$width.'%%" style="text-align:center">
            		<a class="%s" href="%s">%s</a>
    		</td>', $middle_img, $css_class, util_make_url ($TABS_DIRS[$i]), $TABS_TITLES[$i]);
    
                // if the next tab is not last, insert a separator
                if ($i < $count-1) {
                    $return .= sprintf(
                        '<td valign="top" width="2" background="%s">
          			<img src="%s" height="%d" width="2" alt="" />
                    	  </td>', $separ_img, $clear_img, $tab_height );
                }
            }
            else {
                $tab_height = TOP_TAB_HEIGHT;
                
                $return .= sprintf(
                    '<td valign="top" width="3" background="%s">
      			<img src="%s" height="%d" width="3" alt="" />
                	</td>', $left_img, $clear_img, $tab_height );
                    
                $return .= sprintf(
                    '<td background="%s" width="'.$width.'%%" style="text-align:center">
            		<a class="%s" href="%s">%s</a>
    		</td>', $middle_img, $css_class, util_make_url ($TABS_DIRS[$i]), $TABS_TITLES[$i]);
    
                // if the next tab is not selected, close this tab
                if ($selected != $i+1) {
                  $return .= sprintf(
                      '<td valign="top" width="9" background="%s">
        			<img src="%s" height="%d" width="9" alt="" />
                  	  </td>', $right_img, $clear_img, $tab_height );
                }
            }
        }
                
        //
        //    Building a bottom row in this table, which will be darker
        //
        /*
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
        
        $return .= '<tr>';
        
        if ($beg_cols > 0) {
            $return .= 
                '<td colspan="'.$beg_cols.'" height="1" bgcolor="#909090">
			<img src="'.$this->imgroot.'clear.png" height="1" width="10" />
		</td>';
        }
        $return .= 
                '<td colspan="3" height="1" bgcolor="'.$sel_tab_bgcolor.'">
			<img src="'.$this->imgroot.'clear.png" height="1" width="10" />
		</td>';
        if ($end_cols > 0) {
            $return .= 
                '<td colspan="'.$end_cols.'" height="1" bgcolor="#909090">
			  <img src="'.$this->imgroot.'clear.png" height="1" width="10" />
		</td>';
        }
	    */
        
        $return .= '</tr>';

        return $return.'
        </table> 

        <!-- end tabs -->
';
    }

    function searchBox() {
        global $words,$forum_id,$group_id,$group_project_id,$atid,$exact,$type_of_search;

        if(get_magic_quotes_gpc()) {
            $defaultWords = stripslashes($words);
        } else {
            $defaultWords = $words;
        }
        
        // if there is no search currently, set the default
        if ( ! isset($type_of_search) ) {
            $exact = 1;
        }

        print '
        <form action="'.util_make_url ('/search/').'" method="get">
        <table border="0" cellpadding="0" cellspacing="0">
        <tr><td>
        <div align="center" style="font-size:smaller">';
        $parameters = array(
        SEARCH__PARAMETER_GROUP_ID => $group_id,
        SEARCH__PARAMETER_ARTIFACT_ID => $atid,
        SEARCH__PARAMETER_FORUM_ID => $forum_id,
        SEARCH__PARAMETER_GROUP_PROJECT_ID => $group_project_id
        );

        $searchManager =& getSearchManager();
        $searchManager->setParametersValues($parameters);
        $searchEngines =& $searchManager->getAvailableSearchEngines();

        echo '<select name="type_of_search">';
        for($i = 0, $max = count($searchEngines); $i < $max; $i++) {
            $searchEngine =& $searchEngines[$i];
            echo '<option value="'.$searchEngine->getType().'"'.( $type_of_search == $searchEngine->getType() ? ' selected="selected"' : '' ).'>'.$searchEngine->getLabel($parameters).'</option>'."\n";
        }
        echo '</select></div>';

//        print '<br />';
//        print '
//        <input type="CHECKBOX" name="exact" value="1"'.( $exact ? ' CHECKED' : ' UNCHECKED' ).'> Require All Words';

        print '</td><td>&nbsp;';
        $parameters = $searchManager->getParameters();
        foreach($parameters AS $name => $value) {
            print '<input type="hidden" value="'.$value.'" name="'.$name.'" />';
        }
        print '</td><td>';
        print '<input type="text" size="12" name="words" value="'.$defaultWords.'" />';

        print '</td><td>&nbsp;</td><td>';
        print '<input type="submit" name="Search" value="'._('Search').'" />';
        print '</td>';

        if (isset($group_id) && $group_id) {
            print '
                    <td width="10">&nbsp;</td>
                    <td>'.util_make_link ('/search/advanced_search.php?group_id='.$group_id, _('Advanced search')).'</td>';
        }
        print '</tr></table>';
        print '</form>';

    }
    
    function advancedSearchBox($sectionsArray, $group_id, $words, $isExact) {
         // display the searchmask
        print '
        <form name="advancedsearch" action="'.getStringFromServer('PHP_SELF').'" method="post">
        <input type="hidden" name="search" value="1"/>
        <input type="hidden" name="group_id" value="'.$group_id.'"/>
        <div align="center"><br />
            <table border="0">
                <tr>
                    <td colspan ="2">
                        <input type="text" size="60" name="words" value="'.stripslashes(htmlspecialchars($words)).'" />
                        <input type="submit" name="submitbutton" value="'._('Search').'" />
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <input type="radio" name="mode" value="'.SEARCH__MODE_AND.'" '.($isExact ? 'checked="checked"' : '').' />'._('with all words').'
                    </td>
                    <td>
                        <input type="radio" name="mode" value="'.SEARCH__MODE_OR.'" '.(!$isExact ? 'checked="checked"' : '').' />'._('with one word').'
                    </td>
                </tr>
            </table><br /></div>'
        .$this->createUnderSections($sectionsArray).'
        </form>';


        //create javascript methods for select none/all
        print '
        <script type="text/javascript">
            <!-- method for disable/enable checkboxes
            function setCheckBoxes(parent, checked) {


                for (var i = 0; i < document.advancedsearch.elements.length; i++)
                    if (document.advancedsearch.elements[i].type == "checkbox") 
                            if (document.advancedsearch.elements[i].name.substr(0, parent.length) == parent)
                                document.advancedsearch.elements[i].checked = checked;
                }
            //-->
        </script>
        ';

    }
    
    function createUnderSections($sectionsArray) {
    	global $group_subsection_names;
        $countLines = 0;
        foreach ($sectionsArray as $section) {
            if(is_array($section)) {
                $countLines += (3 + count ($section));
            } else {
                //2 lines one for section name and one for checkbox
                $countLines += 3;
            }
        }
        $breakLimit = round($countLines/3);
        $break = $breakLimit;
        $countLines = 0;
        $return = '
            <table width="99%" border="0" cellspacing="0" cellpadding="1" style="background-color:'. $this->COLOR_LTBACK2.'">
                <tr>
                    <td>
                        <table width="100%" cellspacing="0" border="0" style="background-color:'. $this->COLOR_LTBACK1.'">
                            <tr style="font-weight: bold;background-color:'. $this->COLOR_LTBACK2 .'">
                                <td colspan="2">'._('Search in').'</td>
                                <td style="text-align:right">'._('Select').' <a href="javascript:setCheckBoxes(\'\', true)">'._('all').'</a> / <a href="javascript:setCheckBoxes(\'\', false)">'._('none').'</a></td>
                            </tr>
                            <tr height="20">
                                <td colspan="3">&nbsp;</td>
                            </tr>
                            <tr align="center" valign="top">
                                <td>';
        foreach($sectionsArray as $key => $section) {
            $oldcountlines = $countLines;
            if (is_array($section)) {
                $countLines += (3 + count ($section));
            } else {
                $countLines += 3;
            }
            
            if ($countLines >= $break) {
                //if the next block is so large that shifting it to the next column hits the breakpoint better
                //the second part of statement (behind &&) proofs, that no 4th column is added
                if ((($countLines - $break) >= ($break - $countLines)) && ((($break + $breakLimit)/$breakLimit) <= 3)) {
                    $return .= '</td><td>';
                    $break += $breakLimit;
                }
            }
            
            $return .= '<table width="90%" border="0" cellpadding="1" cellspacing="0" style="background-color:'. $this->COLOR_LTBACK2.'">
                            <tr><td><table width="100%" border="0" cellspacing="0" cellpadding="3">
                            <tr style="background-color:'. $this->COLOR_LTBACK2 .'; font-weight: bold">
                                <td cellspacing="0">
                                    <a href="#'.$key.'">'.$group_subsection_names[$key].'</a>'
            .'    </td>
                                <td style="text-align:right">'
            ._('Select').' <a href="javascript:setCheckBoxes(\''.$key.'\', true)">'._('all').'</a> / <a href="javascript:setCheckBoxes(\''.$key.'\', false)">'._('none').'</a>
                                </td>
                            </tr>
                            <tr style="background-color:'. $this->COLOR_LTBACK1.'">
                                <td colspan="2">';

            if (!is_array($section)) {
                $return .= '        <input type="checkbox" name="'.urlencode($key).'"';
                if (isset($GLOBALS[urlencode($key)]))
                $return .= ' checked="checked" ';
                $return .= ' /></input>'.$group_subsection_names[$key].'<br />';
            }
            else
            foreach($section as $underkey => $undersection) {
                $return .= '    <input type="checkbox" name="'.urlencode($key.$underkey).'"';
                if (isset($GLOBALS[urlencode($key.$underkey)]))
                $return .= ' checked ';
                $return .= '></input>'.$undersection.'<br />';

            }
            
            $return .=        '    </td>
                            </tr>
                        </table></td></tr></table><br />';

            if ($countLines >= $break) {
                if (($countLines - $break) < ($break - $countLines)) {
                    $return .= '</td><td width="33%">';
                    $break += $breakLimit;
                }
            }
        }
        
        return $return.'        </td>
                            </tr>
                        </table></td></tr></table>';
    }

    /**
     * beginSubMenu() - Opening a submenu.
     *
     * @return    string    Html to start a submenu.
     */
    function beginSubMenu () {
        $return = '
            <p><strong>';
        return $return;
    }

    /**
     * endSubMenu() - Closing a submenu.
     *
     * @return    string    Html to end a submenu.
     */
    function endSubMenu () {
        $return = '</strong></p>';
        return $return;
    }

    /**
     * printSubMenu() - Takes two array of titles and links and builds the contents of a menu.
     *
     * @param       array   The array of titles.
     * @param       array   The array of title links.
     * @return    string    Html to build a submenu.
     */
    function printSubMenu ($title_arr,$links_arr) {
        $count=count($title_arr);
        $count--;

        $return = '';

        for ($i=0; $i<$count; $i++) {
		$return .= util_make_link ($links_arr[$i], $title_arr[$i]) . ' | ';
        }
	$return .= util_make_link ($links_arr[$i], $title_arr[$i]);
        return $return;
    }

    /**
     * subMenu() - Takes two array of titles and links and build a menu.
     *
     * @param       array   The array of titles.
     * @param       array   The array of title links.
     * @return    string    Html to build a submenu.
     */
    function subMenu ($title_arr,$links_arr) {
        $return  = $this->beginSubMenu () ;
        $return .= $this->printSubMenu ($title_arr,$links_arr) ;
        $return .= $this->endSubMenu () ;
        return $return;
    }

    /**
     * multiTableRow() - create a mutlilevel row in a table
     *
     * @param    string    the row attributes
     * @param    array    the array of cell data, each element is an array,
     *                      the first item being the text,
     *                    the subsequent items are attributes (dont include
     *                    the bgcolor for the title here, that will be
     *                    handled by $istitle
     * @param    boolean is this row part of the title ?
     *
     */
    function multiTableRow($row_attr, $cell_data, $istitle) {
        $return= '
        <tr '.$row_attr;
        if ( $istitle ) {
            $return .=' align="center" bgcolor="'. $this->COLOR_HTMLBOX_TITLE .'"';
        }
        $return .= '>';
        for ( $c = 0; $c < count($cell_data); $c++ ) {
            $return .='<td ';
            for ( $a=1; $a < count($cell_data[$c]); $a++) {
                $return .= $cell_data[$c][$a].' ';
            }
            $return .= '>';
            if ( $istitle ) {
                $return .='<font color="'.$this->FONTCOLOR_HTMLBOX_TITLE.'"><strong>';
            }
            $return .= $cell_data[$c][0];
            if ( $istitle ) {
                $return .='</strong></font>';
            }
            $return .= '</td>';

        }
        $return .= '</tr>
        ';

        return $return;
    }

    /**
     * feedback() - returns the htmlized feedback string when an action is performed.
     *
     * @param string feedback string
     * @return string htmlized feedback
     */
    function feedback($feedback) {
        if (!$feedback) {
            return '';
        } else {
            return '
                <h3 style="color:red">'.strip_tags($feedback, '<br>').'</h3>';
        }
    }

    /**
     * getThemeIdFromName()
     *
     * @param    string  the dirname of the theme
     * @return    integer the theme id    
     */
    function getThemeIdFromName($dirname) {
        $res=db_query("SELECT theme_id FROM themes WHERE dirname='$dirname'");
        return db_result($res,0,'theme_id');
    }

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
