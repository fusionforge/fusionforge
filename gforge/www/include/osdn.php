<?php
/**
 * osdn.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: osdn.php,v 1.121 2001/06/27 00:14:30 jbyers Exp $
 */
$osdn_sites[0] = array('Freshmeat' => 'http://www.freshmeat.net/');
$osdn_sites[1] = array('Geocrawler' => 'http://www.geocrawler.com/');
$osdn_sites[2] = array('Linux.Com' => 'http://www.linux.com/');
$osdn_sites[3] = array('NewsForge' => 'http://www.newsforge.com/');
$osdn_sites[4] = array("Open Magazine" => 'http://www.openmagazine.net/');
$osdn_sites[5] = array('Question Exchange' => 'http://www.questionexchange.com/');
$osdn_sites[6] = array('Slashdot.Org' => 'http://www.slashdot.com/');
$osdn_sites[7] = array('Themes.Org' => 'http://www.themes.org/');
$osdn_sites[8] = array('Thinkgeek' => 'http://www.thinkgeek.com/');

/**
 * osdn_nav_dropdown() - Show the nav bar dropdown of all the OSDN sites.
 */
function osdn_nav_dropdown() {
	GLOBAL $osdn_sites;
?>

	<!-- OSDN navdropdown -->


	<script language="JavaScript">
	<!--
	document.write('<form name=form1>'+
	'<font size=-1>'+
	'<a href="http://www.osdn.com"><?php echo html_image("images/osdn_logo_grey.png","135","33",array("hspace"=>"10","alt"=>" OSDN - Open Source Development Network ","border"=>"0")); ?></A><br>'+
	'<select name=navbar onChange="window.location=this.options[selectedIndex].value">'+
	'<option value="http://www.osdn.com/gallery.html">Network Gallery</option>'+
	'<option>------------</option>'+<?php
	reset ($osdn_sites);
	while (list ($key, $val) = each ($osdn_sites)) {
		list ($key, $val) = each ($val);
		print "\n	'<option value=\"$val\">$key</option>'+";
	}
?>
	'</select>'+
	'</form>');
	//-->
	</script>

	<noscript>
	<a href="http://www.osdn.com"><?php echo html_image("/images/osdn_logo_grey.png","135","33",array("hspace"=>"10","alt"=>" OSDN - Open Source Development Network ","border"=>"0")); ?></A><br>
	<a href="http://www.osdn.com/gallery.html"><font size="2" color="#fefefe" face="arial, helvetica">Network Gallery</font></a>
	</noscript>


	<!-- end OSDN navdropdown -->

<?php
}

/**
 * osdn_print_randpick() - Picks random OSDN sites to display
 *
 * @param		array	Array of sites
 * @param		int		Number of sites to print
 */
function osdn_print_randpick($sitear, $num_sites = 1) {
	shuffle($sitear);
	reset($sitear);
	while ( ( $i < $num_sites ) && (list($key,$val) = each($sitear)) ) {
		list($key,$val) = each($val);
		print "\t\t&nbsp;&middot;&nbsp;<a href='$val'style='text-decoration:none'><font color='#ffffff'>$key</font></a>\n";
		$i++;
	}
	print '&nbsp;&middot;&nbsp;';
}

/**
 * osdn_print_navbar() - Show the navbar
 */
function osdn_print_navbar() {
  if ($GLOBALS['sys_show_banner_1']) {
    osdn_print_navbar_1 () ;
  }
  if ($GLOBALS['sys_show_banner_1']) {
    osdn_print_navbar_2 () ;
  }
}

function osdn_print_navbar_1() {
	print '<!-- OSDN navbar part 1 -->

<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">
	<tr> 
		<td valign="middle" align="left" bgcolor="#6C7198">
		<SPAN class="osdn">
			<font face="arial,geneva,helvetica,verdana,sans-serif" size="-2" color="#ffffff">&nbsp;&nbsp;&nbsp;<b><a href="http://osdn.com/" style="text-decoration:none"><font color="#ffffff">O&nbsp;<font color="#9b9b9b">|</font>&nbsp;S&nbsp;<font color="#9b9b9b">|</font>&nbsp;D&nbsp;<font color="#9b9b9b">|</font>&nbsp;N</font></a></b></font>&nbsp;:&nbsp;
';
	osdn_print_randpick($GLOBALS['osdn_sites'], 3);
	print '
		</SPAN>
		</td>
		<td valign="middle" align="right" bgcolor="#6C7198">
		<SPAN class="osdn">
			<b><a href="http://www.osdn.com/index.pl?indexpage=myosdn" style="text-decoration:none"><font color="#ffffff">My OSDN</font></a>&nbsp;&middot;&nbsp;
';
/*
		<a href="" style="text-decoration:none"><font color="#ffffff">JOBS</font></a>&nbsp;&middot;&nbsp;
*/
	print '
		<a href="http://www.osdn.com/partner_programs.shtml" style="text-decoration:none"><font color="#ffffff">PARTNERS</font></a>&nbsp;&middot;&nbsp; 
		<a href="http://www.osdn.com/gallery.pl?type=community" style="text-decoration:none"><font color="#ffffff">AFFILIATES</font></a>&nbsp;&middot;&nbsp; 
		<a href="http://jobs.osdn.com" style="text-decoration:none"><font color="#ffffff">JOBS</font></a>&nbsp;</b></font>
		</SPAN>
		</td>
	</tr>
</table>

<!-- End of OSDN navbar part 1 -->

';
}

function osdn_print_navbar_2() {

  print '<!-- OSDN navbar part 2 -->

<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>' ;
  print '<td bgcolor="'.$GLOBALS['COLOR_CONTENT_BACK'].'" WIDTH="20%">'.html_blankimage(1,100).'</td>';

  print '<td bgcolor="'.$GLOBALS['COLOR_CONTENT_BACK'].'" width="60%"><a href="/"><img src="/images/sf-for-debian.png" alt="Debian Sourceforge"></a></td>';

  print '<td bgcolor="'.$GLOBALS['COLOR_CONTENT_BACK'].'" WIDTH="20%">'.html_blankimage(1,100).'</td>';

	print '</tr>
</table>

<!-- End of OSDN navbar part 2 -->

';
}

?>
