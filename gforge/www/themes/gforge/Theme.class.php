<?php

require_once $gfwww.'include/Layout.class.php';

define('TOP_TAB_HEIGHT', 30);
define('BOTTOM_TAB_HEIGHT', 22);

class Theme extends Layout {

        function Theme() {
                // Parent constructor
                $this->Layout();
                $this->doctype = 'strict';
		$this->cssurls = array(
			'http://yui.yahooapis.com/2.6.0/build/reset-fonts-grids/reset-fonts-grids.css',
			'http://yui.yahooapis.com/2.6.0/build/base/base-min.css',
			util_make_url ('/themes/css/fusionforge.css'),
			$this->cssbaseurl .'theme.css',
			$this->cssbaseurl .'theme-pages.css',
			);
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
                $this->searchBox();
                echo '
					</td>
					<td id="header-col3">
			';
                $items = $this->navigation->getUserLinks();
                for ($j = 0; $j < count($items['titles']); $j++) {
                        $links[] = util_make_link($items['urls'][$j], $items['titles'][$j], 
                                                  array('class'=>'userlink'), true);
                }
                echo implode(' | ', $links);
                
                plugin_hook ('headermenu', $params);

                $this->quickNav();
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
                       ' . $this->navigation->getPoweredBy() . '
			</div>
                       ' . $this->navigation->getShowSource() . '
			';

                echo '
		</body>
		</html>
		';
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


	function tabGenerator($TABS_DIRS, $TABS_TITLES, $nested=false, 
			      $selected=false, $sel_tab_bgcolor='WHITE', 
			      $total_width='100%') {
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
 
/*		$folder = $this->imgurl.($nested ? 'bottomtab-new/' : 'toptab-new/');*/

		$accumulated_width = 0;
		for ($i=0; $i<$count; $i++) {
			$tabwidth = intval(ceil(($i+1)*100/$count)) - $accumulated_width ;
			$accumulated_width += $tabwidth ;
/*
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
            
			$clear_img = $this->imgurl.'clear.png';
*/
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
