<?php
/*
 * Mac OS X like theme.
 *
 * Copyright (c) 2002-2003 Richard Offer. All rights reserved.
 *
 * Permission to use, copy, modify, distribute, and sell this software and its
 * documentation for any purpose is hereby granted without fee, provided that
 * the above copyright notice appear in all copies and that both that
 * copyright notice and this permission notice appear in supporting
 * documentation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN
 * AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * Except as contained in this notice, the name of the author shall not be
 * used in advertising or otherwise to promote the sale, use or other dealings
 * in this Software without prior written authorization from the author.

 *
 * Based on the "debian" theme, which is apparantly :-
 * 		Copyright 1999-2001 (c) VA Linux Systems
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

	}

	/**
	 *	header() - "steel theme" top of page
	 *
	 * @param	array	Header parameters array
	 */
	function header($params) {
		global $Language, $sys_name;

		$this->headerStart($params); ?>

<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['sys_urlprefix']; ?>/themes/osx/css/theme.css" />
<body>

<table border="0" width="100%" cellspacing="0" cellpadding="0">

	<tr>
		<td><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/"
			onmouseover="imgOver('logo')"
			onmouseout="imgOff('logo')"><img name="logo" border="0" height="60" width="180"
				src="<?php echo $this->imgroot.'logo.png'; ?>" alt="GForge Logo" /></a></td>
		<td><?php echo $this->searchBox(); ?></td>
		<td style="text-align:right"><?php
			if (session_loggedin()) {
				?>
				<b><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/account/logout.php"><?php echo _('Log Out'); ?></a></b><br />
				<b><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/account/"><?php echo _('My Account'); ?></a></b>
				<?php
			} else {
				?>
				<b><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/account/login.php"><?php echo _('Log In'); ?></a></b><br />
				<b><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/account/register.php"><?php echo _('New Account'); ?></a></b>
				<?php
			}

		?></td>
		<td>&nbsp;&nbsp;</td>
	</tr>

</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0">

	<tr>
		<td>&nbsp;</td>
		<td colspan="3">

<?php echo $this->outerTabs($params); ?>

		</td>
		<td>&nbsp;</td>

	</tr>
	<tr>
		<td>&nbsp;</td>
		<td valign="top" width="99%" colspan="3">
			<!-- Inner Tabs / Shell -->

			<table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php


		  if ($params['group']) {

?>
			<tr>
				<td>&nbsp;</td>
				<td>
				<?php

				echo $this->projectTabs($params['toptab'],$params['group']);

				?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<?php

}

?>
			<tr>
				<td><img
					src="<?php echo $this->imgroot; ?>clear.png" width="99%" height="10" alt="" /></td>
			</tr>

			<tr>
				<td><img
					src="<?php echo $this->imgroot; ?>clear.png" width="10" height="1" alt="" /></td>
				<td valign="top" width="99%">
	<?php
	}

	function footer($params) {

	?>

			<!-- end main body row -->


				</td>
				<td width="10"><img
					src="<?php echo $this->imgroot; ?>clear.png" width="2" height="1" alt="" /></td>
			</tr>
			<tr>
				<td><img
					src="<?php echo $this->imgroot; ?>clear.png" width="1" height="1" alt="" /></td>
			</tr>
			</table>

		<!-- end inner body row -->

		</td>
		<td width="10"><img src="<?php echo $this->imgroot; ?>clear.png" width="2" height="1" alt="" /></td>
	</tr>
	<tr>
		<!-- some extra space to make it look nicer -->
		<td height="100">&nbsp;</td>
	</tr>
</table>
<?php
		echo $this->footerEnd($params);

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
		<!-- boxTop -->
		<table cellspacing="2" cellpadding="0" width="100%" border="0">
		<tr><td>

			<table cellspacing="2" cellpadding="0" width="100%" border="0" >
				<tr>
					<td colspan="2"><span class="titlebar">'.$title.'</span></td>
				</tr>
				<tr align="left" bgcolor="'. $this->COLOR_HTMLBOX_BACK .'">
					<td colspan="2" height="1"></td></tr><tr style="text-align:left"><td colspan="2">';
	}

	/**
	 * boxMiddle() - Middle HTML box
	 *
	 * @param   string  Box title
	 * @param   string  The box background color
	 */
	function boxMiddle($title) {
		return '
				<!-- boxMiddle -->
				</td>
				</tr>
				<tr><td height="20" colspan="2">&nbsp;</td></tr>
				<tr>
					<td colspan="2"><span class="titlebar">'.$title.'</span></td>
				</tr>
				<tr align="left" bgcolor="'. $this->COLOR_HTMLBOX_BACK .'">
					<td colspan="2" height="1"></td></tr><tr><td colspan="2">';
	}

	/**
	 * boxGetAltRowStyle() - Get an alternating row style for tables
	 *
	 * @param			   int			 Row number
	 */
	function boxGetAltRowStyle($i) {
		switch ($i % 3 ) {
			case 0:
				return ' bgcolor="#FFFFFF"';
			case 1:
				return ' bgcolor="' . $this->COLOR_LTBACK1 . '"';
			case 2:
				return ' bgcolor="' . $this->COLOR_LTBACK2 . '"';
		}
	}

	/**
	 * boxBottom() - Bottom HTML box
	 *
	 * @param   bool	Whether to echo or return the results
	 */
	function boxBottom() {
		return '<!-- boxBottom -->
					</td>
				</tr>
			</table>
		</td></tr>
		<tr><td height="20" colspan="2">&nbsp;</td></tr>
		</table><p />';
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
		<table cellspacing="0" cellpadding="1" width="100%" border="0">
		<tr><td>
		<table width="100%" border="0" cellspacing="2" cellpadding="0">
			<tr>';

		$count=count($title_arr);
		if ($links_arr) {
			for ($i=0; $i<$count; $i++) {
				$return .= '
				<td style="text-align:left"><a class="titlebar" href="'.$links_arr[$i].'">'.$title_arr[$i].'</a></td>';
			}
		} else {
			for ($i=0; $i<$count; $i++) {
				$return .= '
				<td style="text-align:left">'.$title_arr[$i].'</td>';
			}
		}
		$return .= '
		</tr>
		<tr align="left" bgcolor="'. $this->COLOR_HTMLBOX_BACK .'">
			<td colspan="'.$count.'" height="1"><img src="'.$this->imgroot.'clear.png" height="1" width="1" alt="" /></td>
		</tr>';
		return $return;
	}

	function tabGenerator($TABS_DIRS,$TABS_TITLES,$nested=false,$selected=false,$sel_tab_bgcolor='WHITE',$total_width='100%') {

		$deselect_tab='style="background-image: url('.$this->imgroot.'tabs/deselect.png);"';
		$select_tab='style="background-image: url('.$this->imgroot.'tabs/select.png);"';
		$deselect_rule='style="background-image: url('.$this->imgroot.'tabs/ruledeselect.png);"';
		$select_rule='style="background-image: url('.$this->imgroot.'tabs/ruleselect.png);"';

		$count=count($TABS_DIRS);
		$width=intval((100/($count+1)));
		$space=intval($width/2);
		$return .= '

		<!-- tabGenerator -->

		<table border="0" cellpadding="0" cellspacing="0" width="'.$total_width.'">
		<tr>
			<td><img src="'.$this->imgroot.'clear.png" height="'.$this->TAB_HEIGHT.'" width="'.$space.'%" alt="" /></td>';
		if ($nested) {
			$inner='-inner';
		} else {
			$inner='';
		}
		for ($i=0; $i<$count; $i++) {
			$TABS_TITLES[$i] = preg_replace("/ +/", "&nbsp;", $TABS_TITLES[$i]);
			$bgimg=(($selected==$i)?$select_tab:$deselect_tab);
			$cornerimg=(($selected==$i)?'select':'deselect');
			$return .= '
				<td height="'.$this->TAB_HEIGHT.'" width="5"
					style="background-image: url('.$this->imgroot.'tabs/left'.$cornerimg.'.png);"></td>
				<td '.$bgimg.' height="'.$this->TAB_HEIGHT.'" width="'.$width.'%" style="text-align:center">'.
				'<a class="'. (($selected==$i)?'tabsellink':'tablink') .'" href="'. $TABS_DIRS[$i] .'">'. $TABS_TITLES[$i] .'</a></td>
				<td height="'.$this->TAB_HEIGHT.'" width="5"
					style="background-image: url('.$this->imgroot.'tabs/right'.$cornerimg.'.png);"></td>';
		}

		$return .= '
			<td><img src="'.$this->imgroot.'clear.png" height="'.$this->TAB_HEIGHT.'" width="'.$space.'%" alt="" /></td>
		</tr>
		<tr>
		<td style="background-image: url('.$this->imgroot.'tabs/ruleselect.png);" style="text-align:center"><img
			src="'.$this->imgroot.'clear.png" height="8" width="'.$space.'%" alt="" /></td>';
		for ($i=0; $i<$count; $i++) {
			$bgimg=(($selected==$i)?$select_rule:$deselect_rule);
			$blendimg=(($selected==$i)?'select':'deselect');
			$return .= '
				<td style="background-image: url('.$this->imgroot.'tabs/leftblend'.$blendimg.'.png);"><img
					src="'.$this->imgroot . 'clear.png" height="8" width="5" alt="" /></td>
				<td '.$bgimg.' width="'.$width.'%" style="text-align:center"><img
					src="'.$this->imgroot . 'clear.png" height="8" width="1" alt="" /></td>
				<td style="background-image: url('.$this->imgroot.'tabs/rightblend'.$blendimg.'.png);"><img
					src="'.$this->imgroot . 'clear.png" height="8" width="5" alt="" /></td>';
		}

		return $return .= '
		<td style="background-image: url('.$this->imgroot.'tabs/ruleselect.png);" style="text-align:center"><img
			src="'.$this->imgroot.'clear.png" height="8" width="'.$space.'%" alt="" /></td>
		</tr>
		<tr><td height="10">&nbsp;</td></tr>
		</table>
		<!-- end tabGenerator -->';
	}

	/**
	 * multiTableRow() - create a mutlilevel row in a table
	 *
	 * @param	string	the row attributes
	 * @param	array	the array of cell data, each element is an array,
	 *				  	the first item being the text,
	 *					  the subsequent items are attributes
	 * @param	boolean is this row part of the title ?
	 *
	 */
	 function multiTableRow($row_attr, $cell_data, $istitle) {
		$return= '
		<!-- multiTableRow -->
		<tr '.$row_attr;
		if ( $istitle ) {
			$return .=' align="center" ';
		}
		$return .= '>';
		for ( $c = 0; $c < count($cell_data); $c++ ) {
			$return .='<td ';
			for ( $a=1; $a < count($cell_data[$c]); $a++) {
				$return .= $cell_data[$c][$a].' ';
			}
			$return .= '>';
			if ( $istitle ) {
				$return .='<span class="titlebar">';
			}
			$return .= $cell_data[$c][0];
			if ( $istitle ) {
				$return .='</span>';
			}
			$return .= '</td>';

		}
		$return .= '</tr>
		<!-- end multiTableRow -->
		';

		return $return;
	 }

}

?>
