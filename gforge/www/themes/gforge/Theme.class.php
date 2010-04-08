<?php

require_once $gfwww.'include/Layout.class.php';

define('THEME_DIR', util_make_uri ('/themes/gforge'));

define('TOP_TAB_HEIGHT', 30);
define('BOTTOM_TAB_HEIGHT', 22);

class Theme extends Layout {

    function Theme() {
        // Parent constructor
        $this->Layout();

        $this->imgroot = THEME_DIR.'/images/';
        $this->jsroot  = THEME_DIR.'/js/';
    }

    /**
     * Layout() - Constructor
     */
    function Layout() {
        // Constructor for parent class...
        if ( file_exists($GLOBALS['sys_custom_path'] . '/index_std.php') ) {
            $this->rootindex = $GLOBALS['sys_custom_path'] . '/index_std.php';
        } else {
            $this->rootindex = $GLOBALS['gfwww'].'index_std.php';
        }
        $this->Error();
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
        echo '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . _('en') . '" lang="' . _('en') . '">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>'. $params['title'] . '</title>
		<link rel="icon" type="image/png" href="'. util_make_uri('/images/icon.png') .'" />
		<link rel="shortcut icon" type="image/png" href="'. util_make_uri('/images/icon.png') .'" />';

        echo $this->headerLink();

        echo '
		<script type="text/javascript" src="'. util_make_uri('/js/common.js') .'"></script>
		<script type="text/javascript">';
        plugin_hook ("javascript",false);
        echo '</script>';

        $this->headerCSS();

        echo '
		</head>
		<body id="mydoc">
		';

        $this->bodyHeader($params);
    }

    function bodyHeader($params) {
        global $user_guide;

        echo '
			<table id="header" class="width-100p100">
				<tr>
					<td id="header-col1">
					<h1>'.  util_make_link ('/', html_image('header/top-logo.png',192,54,array('alt'=>'FusionForge Home'))) .'</h1>
					</td>
					<td id="header-col2">';
        echo $this->searchBox();
        echo '
					</td>
					<td id="header-col3">
			';
        if (session_loggedin()) {
            $u =& user_get_object(user_getid());
            echo util_make_link ('/account/logout.php', sprintf("%s (%s)", _('Log Out'), $u->getRealName()), array('class'=>'userlink'));
            echo ' ';
            echo util_make_link ('/account/', _('My Account'), array('class'=>'userlink'));
        } else {
		$url = '/account/login.php';
        	if(getStringFromServer('REQUEST_METHOD') != 'POST') {
        		$url .= '?return_to=';
        		$url .= urlencode(getStringFromServer('REQUEST_URI'));
        	}
		
        	echo util_make_link ($url, _('Log In'),array('class'=>'userlink'));
        	echo ' ';
        	if (!$GLOBALS['sys_user_reg_restricted']) {
        		echo util_make_link ('/account/register.php', _('New Account'),array('class'=>'userlink'));
		}
        }

        plugin_hook ('headermenu', $params);

        echo $this->quickNav();
        echo '
					</td>
				</tr>
			</table>
			
			<!-- outer tabs -->
			';
        echo $this->outerTabs($params);
        echo '<!-- inner tabs -->';
        if (isset($params['group']) && $params['group']) {
            echo $this->projectTabs($params['toptab'],$params['group']);
        }
	echo '<div id="maindiv">
';
    }

     function bodyFooter($params) {
        echo '</div> <!-- id="maindiv" -->
';
    }

    function footer($params) {
    	$this->bodyFooter($params);
        echo '
			<!-- PLEASE LEAVE "Powered By FusionForge" on your site -->
			<div class="align-right">
			<a href="http://fusionforge.org/">
			<img src="'. util_make_uri ('/images/pow-fusionforge.png') .'" alt="Powered By FusionForge" />
			</a></div>
			';

        global $sys_show_source;
        if ($sys_show_source) {
            global $SCRIPT_NAME;
            print util_make_link ('/source.php?file=' . $SCRIPT_NAME, _('Show source'), array ("class" => "showsource"));
        }

        echo '
		</body>
		</html>
		';
    }

    function headerCSS() {
        echo '
		<link href="http://yui.yahooapis.com/2.6.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css" rel="stylesheet" />
		<link href="http://yui.yahooapis.com/2.6.0/build/base/base-min.css"	type="text/css" rel="stylesheet" />
		<link rel="stylesheet" type="text/css" href="'. util_make_uri ('/themes/css/fusionforge.css') .'" />
		<link rel="stylesheet" type="text/css" href="'. THEME_DIR .'/css/theme.css" />
		<link rel="stylesheet" type="text/css" href="'. THEME_DIR .'/css/theme-pages.css" />
		';

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
    function boxTop($title, $id = '') {
        $t_result = '
        	<div id="' . $this->toSlug($id) . '" class="box-surround">
            	<div id="'. $this->toSlug($id) . '-title" class="box-title">
            		<div class="box-title-left">
            			<div class="box-title-right">
                			<h3 class="box-title-content" id="'. $this->toSlug($id) .'-title-content">'. $title .'</h3>
                		</div> <!-- class="box-title-right" -->
                	</div> <!-- class="box-title-left" -->
                </div> <!-- class="box-title" -->
            	<div id="'. $this->toSlug($id) .'-content" class="box-content">
            ';
        return $t_result;
    }

    /**
     * boxMiddle() - Middle HTML box
     *
     * @param   string  Box title
     * @param   string  The box background color
     */
    function boxMiddle($title, $id = '') {
	    $t_result ='
	        	</div> <!-- class="box-content" -->
	        <h3 id="title-'. $this->toSlug($id).'" class="box-middle">'.$title.'</h3>
	       	<div class="box-content">
        ';
	    return $t_result;
    }

    /**
     * boxBottom() - Bottom HTML box
     *
     */
    function boxBottom() {
	    $t_result='
                </div> <!-- class="box-content" -->
            </div> <!-- class="box-surround" -->
		';
	    return $t_result;
    }

    /**
     * boxGetAltRowStyle() - Get an alternating row style for tables
     *
     * @param               int             Row number
     */
    function boxGetAltRowStyle($i) {
        if ($i % 2 == 0) {
            return 'class="bgcolor-white"';
        } else {
            return 'class="bgcolor-grey"';
        }
    }

    /**
     * listTableTop() - Takes an array of titles and builds the first row of a new table.
     *
     * @param       array   The array of titles
     * @param       array   The array of title links
     * @param       boolean Whether to highlight or not the entry
     */
    function listTableTop ($title_arr,$links_arr=false,$selected=false) {
	    $return = '<table class="width-100p100 listTable';
	    if ($selected == true) {
		    $return .= ' selected';
	    }
	    $return .= '">
            <tr>';

        $count=count($title_arr);
        if ($links_arr) {
            for ($i=0; $i<$count; $i++) {
                $return .= '
                <th scope="col"><a class="sortbutton" href="'.util_make_url ($links_arr[$i]).'"><strong>'.$title_arr[$i].'</strong></a></th>';
            }
        } else {
            for ($i=0; $i<$count; $i++) {
                $return .= '
                <th scope="col"><strong>'.$title_arr[$i].'</strong></th>';
            }
        }
        return $return.'</tr>';
    }

    function listTableBottom() {
	    return '
            </table>';
    }


    function tabGenerator($TABS_DIRS, $TABS_TITLES, $nested=false, $selected=false, $sel_tab_bgcolor='WHITE', $total_width='100%') {
		$count=count($TABS_DIRS);
		if ($count < 1) {
			return;
		}
        $return = '
		<!-- start tabs -->
		<table class="tabGenerator width-100p100" summary="" ';

        if ($total_width != '100%') {
		$return .= 'style="width:' . $total_width . ';"';
        }
        $return .= ">\n";
        $return .= '<tr>';
 
        $folder = $this->imgroot.($nested ? 'bottomtab-new/' : 'toptab-new/');

	$accumulated_width = 0;
        for ($i=0; $i<$count; $i++) {
		$tabwidth = intval(ceil(($i+1)*100/$count)) - $accumulated_width ;
		$accumulated_width += $tabwidth ;

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

            $return .= "\n";

            // left part
            $return .= '<td class="tg-left">' . "\n";
            $return .= '<div';
            if ($selected == $i) {
                $return .= ' class="selected"';
            }
            $return .= '>';
            $return .= '<div';
            
            if ($nested) {
		    $return .= ' class="nested"';
            }
            $return .= '>' . "\n";
            $return .= '</div>';
            $return .= '</div>' . "\n";
            $return .= '</td>' . "\n";

            // middle part
            $return .= '<td class="tg-middle" style="width:'.$tabwidth.'%;">' . "\n";
            $return .= '<div';
            if ($selected == $i) {
		    $return .= ' class="selected"';
            }
            $return .= '>';
            $return .= '<div';
            if ($nested) {
		    $return .= ' class="nested"';
            }
            $return .= '>' . "\n";
            $return .= '<a href="'.$TABS_DIRS[$i].'">'.$TABS_TITLES[$i].'</a>' . "\n";
            $return .= '</div>';
            $return .= '</div>' . "\n";
            $return .= '</td>' . "\n";

            // right part
            // if the next tab is not selected, close this tab
            if ($selected != $i+1) {
		    $return .= '<td class="tg-right">' . "\n";
		    $return .= '<div';
		    if ($selected == $i) {
			    $return .= ' class="selected"';
		    }
		    $return .= '>';
		    $return .= '<div';
		    if ($nested) {
			    $return .= ' class="nested"';
		    }
		    $return .= '>' . "\n";
		    $return .= '</div>';
		    $return .= '</div>' . "\n";
		    $return .= '</td>' . "\n";
	    }
	}

        $return .= '</tr>
        </table>
        <!-- end tabs -->';

	return $return;
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
        <form id="searchBox" action="'.util_make_url ('/search/').'" method="get">
        <div>';
        $parameters = array(
		SEARCH__PARAMETER_GROUP_ID => $group_id,
		SEARCH__PARAMETER_ARTIFACT_ID => $atid,
		SEARCH__PARAMETER_FORUM_ID => $forum_id,
		SEARCH__PARAMETER_GROUP_PROJECT_ID => $group_project_id
        );

        $searchManager =& getSearchManager();
        $searchManager->setParametersValues($parameters);
        $searchEngines =& $searchManager->getAvailableSearchEngines();

        echo '
        <label for="searchBox-words">
        <select name="type_of_search">';
        for($i = 0, $max = count($searchEngines); $i < $max; $i++) {
            $searchEngine =& $searchEngines[$i];
            echo '<option class="ff" value="'.$searchEngine->getType().'"'.( $type_of_search == $searchEngine->getType() ? ' selected="selected"' : '' ).'>'.$searchEngine->getLabel($parameters).'</option>'."\n";
        }
        echo '</select></label>';

        $parameters = $searchManager->getParameters();
        foreach($parameters AS $name => $value) {
            print '<input class="ff" type="hidden" value="'.$value.'" name="'.$name.'" />';
        }
        print '<input type="text" size="12" id="searchBox-words" name="words" value="'.$defaultWords.'" />';
	print '<input type="submit" name="Search" value="'._('Search').'" />';

        if (isset($group_id) && $group_id) {
		print util_make_link ('/search/advanced_search.php?group_id='.$group_id, _('Advanced search'), array('class'=>'userlink'));
        }
        print '</div>';
        print '</form>';

    }
    
    function advancedSearchBox($sectionsArray, $group_id, $words, $isExact) {
         // display the searchmask
        print '
        <form class="ff" name="advancedsearch" action="'.getStringFromServer('PHP_SELF').'" method="post">
        <input class="ff" type="hidden" name="search" value="1"/>
        <input class="ff" type="hidden" name="group_id" value="'.$group_id.'"/>
        <div align="center"><br />
            <table border="0">
                <tr class="ff">
                    <td class="ff" colspan ="2">
                        <input class="ff" type="text" size="60" name="words" value="'.stripslashes(htmlspecialchars($words)).'" />
                        <input class="ff" type="submit" name="submitbutton" value="'._('Search').'" />
                    </td>
                </tr>
                <tr class="ff">
                    <td class="ff" valign="top">
                        <input class="ff" type="radio" name="mode" value="'.SEARCH__MODE_AND.'" '.($isExact ? 'checked="checked"' : '').' />'._('with all words').'
                    </td>
                    <td class="ff">
                        <input class="ff" type="radio" name="mode" value="'.SEARCH__MODE_OR.'" '.(!$isExact ? 'checked="checked"' : '').' />'._('with one word').'
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
                <tr class="ff">
                    <td class="ff">
                        <table width="100%" cellspacing="0" border="0" style="background-color:'. $this->COLOR_LTBACK1.'">
                            <tr class="ff" style="font-weight: bold;background-color:'. $this->COLOR_LTBACK2 .'">
                                <td class="ff" colspan="2">'._('Search in').'</td>
                                <td class="ff" style="text-align:right">'._('Select').' <a href="javascript:setCheckBoxes(\'\', true)">'._('all').'</a> / <a href="javascript:setCheckBoxes(\'\', false)">'._('none').'</a></td>
                            </tr>
                            <tr class="ff" height="20">
                                <td class="ff" colspan="3">&nbsp;</td>
                            </tr>
                            <tr class="ff" align="center" valign="top">
                                <td class="ff">';
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
                    $return .= '</td><td class="ff">';
                    $break += $breakLimit;
                }
            }
            
            $return .= '<table  style="width:90%; background-color:'. $this->COLOR_LTBACK2.'">
                            <tr class="ff">
                            <td class="ff">
                            	<table style="width:100%;">
                            	<tr class="ff" style="background-color:'. $this->COLOR_LTBACK2 .'; font-weight: bold">
                                <td class="ff">
                                    <a href="#'.$key.'">'.$group_subsection_names[$key].'</a>'
            .'    </td>
                                <td class="ff" style="text-align:right">'
            ._('Select').' <a href="javascript:setCheckBoxes(\''.$key.'\', true)">'._('all').'</a> / <a href="javascript:setCheckBoxes(\''.$key.'\', false)">'._('none').'</a>
                                </td>
                            </tr>
                            <tr class="ff" style="background-color:'. $this->COLOR_LTBACK1.'">
                                <td class="ff" colspan="2">';

            if (!is_array($section)) {
                $return .= '        <input class="ff" type="checkbox" name="'.urlencode($key).'"';
                if (isset($GLOBALS[urlencode($key)]))
			$return .= ' checked="checked" ';
                $return .= ' /></input>'.$group_subsection_names[$key].'<br />';
            }
            else
            foreach($section as $underkey => $undersection) {
                $return .= '    <input class="ff" type="checkbox" name="'.urlencode($key.$underkey).'"';
                if (isset($GLOBALS[urlencode($key.$underkey)]))
                $return .= ' checked ';
                $return .= '></input>'.$undersection.'<br />';

            }
            
            $return .=        '    </td>
                            </tr>
                        </table></td></tr></table><br />';

            if ($countLines >= $break) {
                if (($countLines - $break) < ($break - $countLines)) {
                    $return .= '</td><td class="ff" width="33%">';
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
        <tr class="ff" '.$row_attr;
        if ( $istitle ) {
            $return .=' align="center" bgcolor="'. $this->COLOR_HTMLBOX_TITLE .'"';
        }
        $return .= '>';
        for ( $c = 0; $c < count($cell_data); $c++ ) {
            $return .='<td class="ff" ';
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
        $res=db_query_params ('SELECT theme_id FROM themes WHERE dirname=$1',
			array($dirname));
        return db_result($res,0,'theme_id');
    }
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
