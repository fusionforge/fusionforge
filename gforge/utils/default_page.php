<?php
// Default Web Page for groups that haven't setup their page yet
// Please replace this file with your own website
//
// $Id$
//
$headers = getallheaders();
?>
<HTML>
<HEAD>
<TITLE>SourceForge: Welcome</TITLE>
</HEAD>

<BODY bgcolor=#FFFFFF topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" marginheight="0" marginwidth="0">

<!-- top strip -->
<table width="100%" border=0 cellspacing=0 cellpadding=2 bgcolor="737b9c">
  <tr>
    <td><SPAN class=maintitlebar>&nbsp;&nbsp;
      <A class=maintitlebar href="http://sourceforge.net/"><strong>Home</strong></a> | 
      <A class=maintitlebar href="http://sourceforge.net/about.php"><strong>About</strong></a> | 
      <A class=maintitlebar href="http://sourceforge.net/partners.php"><strong>Partners</strong></a> |
      <A class=maintitlebar href="http://sourceforge.net/contact.php"><strong>Contact Us</strong></a> |
      <A class=maintitlebar href="http://sourceforge.net/account/logout.php"><strong>Logout</strong></a></SPAN></td>
    </td>
  </tr>
</TABLE>
<!-- end top strip -->

<!-- top title table -->
<table width="100%" border=0 cellspacing=0 cellpadding=0 bgcolor="" valign="center">
  <tr valign="top" bgcolor="#eeeef8">
    <td>
      <a href="http://sourceforge.net/"><img src="http://sourceforge.net/images/sflogo2-steel.png" vspace="0" border=0 width="215" height="105"></a>
    </td>
    <td width="99%"><!-- right of logo -->
      <a href="http://www.valinux.com"><img src="http://sourceforge.net/images/va-btn-small-light.png" align="right" alt="VA Linux Systems" hspace="5" vspace="7" border=0 width="136" height="40"></a>
    </td><!-- right of logo -->
  </tr>
  <tr><td bgcolor="#543a48" colspan=2><img src="http://sourceforge.net/images/blank.png" height=2 vspace=0></td></tr>
</TABLE>
<!-- end top title table -->

<!-- center table -->
<table width="100%" border="0" cellspacing="0" cellpadding="2" bgcolor="#FFFFFF" align="center">
  <tr>
    <td>
      <CENTER><br />
      <h1>Welcome to http://<?php print $headers['Host']; ?>/</h1>
      <p>We're Sorry but this Project hasn't yet uploaded their personal webpage yet.<br />
      Please check back soon for updates or visit <a href="http://sourceforge.net/">SourceForge</a></P><br />
      </CENTER>
    </td>
  </tr>
</TABLE>
<!-- end center table -->

<!-- footer table -->
<table width="100%" border="0" cellspacing="0" cellpadding="2" bgcolor="737b9c">
  <tr>
    <td align="center"><FONT color="#ffffff"><SPAN class="titlebar">
      All trademarks and copyrights on this page are properties of their respective owners. Forum comments are owned by the poster. The rest is copyright &copy; 1999-2000 VA Linux Systems, Inc.</SPAN></FONT>
    </td>
  </tr>
</TABLE>

<!-- end footer table -->
</BODY>
</HTML>
