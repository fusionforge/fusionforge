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
	<a href="http://www.osdn.com"><?php echo html_image("images/osdn_logo_grey.png","135","33",array("hspace"=>"10","alt"=>" OSDN - Open Source Development Network ","border"=>"0")); ?></A><br>
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


	print '<!-- 

OSDN navbar 

-->
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

<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr> 
		<td bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%">';
	echo html_blankimage(1,100). '</TD><TD bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="60%">';

	srand((double)microtime()*1000000);
	$random_num=rand(0,100000);

	if (substr($GLOBALS['REQUEST_URI'], 0, 16) == "/foundry/storage") {

    // storage foundry pages use doubleclick for HP ads
    $ad_protocol = 'http';
    if (session_issecure()) {
      $ad_protocol .= 's';
    } // if

    print "<IFRAME SRC=\"$ad_protocol://ad.doubleclick.net/adi/N2613.osdn/B37181.2;sz=468x60;ord=$random_num\" WIDTH=468 HEIGHT=60 MARGINWIDTH=0 MARGINHEIGHT=0 HSPACE=0 VSPACE=0 FRAMEBORDER=0 SCROLLING=no BORDERCOLOR=\"#000000\">
  <SCRIPT language=\"JavaScript1.1\" SRC=\"$ad_protocol://ad.doubleclick.net/adj/N2613.osdn/B37181.2;abr=!ie;sz=468x60;ord=$random_num\"></SCRIPT>
  <NOSCRIPT>
    <A HREF=\"$ad_protocol://ad.doubleclick.net/jump/N2613.osdn/B37181.2;abr=!ie4;abr=!ie5;sz=468x60;ord=$random_num\">
    <IMG SRC=\"$ad_protocol://ad.doubleclick.net/ad/N2613.osdn/B37181.2;abr=!ie4;abr=!ie5;sz=468x60;ord=$random_num\" BORDER=0 WIDTH=468 HEIGHT=60></A>
  </NOSCRIPT>
</IFRAME>
";

  } else if (session_issecure() || $GLOBALS['IS_DEBUG']) {

		// secure pages use VA's community ad server (www2.valinux.com)
		print '<a href="https://www2.valinux.com/adbouncer.phtml?f_s=468x60&f_p=1&f_RzXx='.$random_num.'">'.
		'<img src="https://www2.valinux.com/adserver.phtml?f_s=468x60&f_p=1&f_RzXx='.$random_num.
		'" width="468" height="60" border="0" alt=" Advertisement "></a>';

	} else {

		// insecure pages use OSDN's ad server
    print '
    <ilayer id="adlayer" visibility="hide" width=468 height=60></ilayer>

    <NOLAYER>
      <IFRAME SRC="http://sfads.osdn.com/1.html" width="468" height="60" '.
      'frameborder="no" border="0" MARGINWIDTH="0" MARGINHEIGHT="0" SCROLLING="no">'.
      '<A HREF="http://sfads.osdn.com/cgi-bin/ad_default.pl?click">'.
      '<IMG SRC="http://sfads.osdn.com/cgi-bin/ad_default.pl?display" border=0 height="60" width="468"></A>
      </IFRAME>
    </NOLAYER>';

	} // OSDN

	print '</td>
		<td valign="center" align="left" bgcolor="#d5d7d9" background="/images/steel3.jpg" WIDTH="20%"><a href="http://www.osdn.com">' . html_image("images/OSDN-lc.gif","100","40",array("hspace"=>"10","border"=>"0","alt"=>" OSDN - Open Source Development Network ")) . '</a>
	</td>
	</tr>
</table>
';

echo '<!-- 


End OSDN NavBar 


-->';
}

?>
