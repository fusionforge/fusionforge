<?php
/**
  *
  * SourceForge Front Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');    // Initial db and session library, opens session
require_once('www/include/cache.php');
require_once('www/forum/forum_utils.php');
require_once('www/include/features_boxes.php');

$HTML->header(array('title'=>'Welcome','pagename'=>'home'));

?>
<!-- whole page table -->
<TABLE width=100% cellpadding=5 cellspacing=0 border=0>
<TR><TD width="65%" VALIGN="TOP">

	<hr width="100%" size="1" noshade>
	<span class="slogan">
	<div align="center">
	<?php echo $Language->getText('home','breaking_down_barriers'); ?>
	</div>
	</span>
        <hr width="100%" size="1" noshade>
	&nbsp;<br>
<P>
<?php
 
/*

       Temp way of getting

       blurb before the content mgr is ready

*/

echo $Language->getText('home','about_blurb', $GLOBALS[sys_name]);
echo '<P>';
// echo $HTML->box1_top($Language->getText('menu','long_foundries'));
?>

<!--

<br><b><?php echo $GLOBALS['sys_name']; ?> Development Foundries</b><br><br>
<table bgcolor="White" border="0" cellpadding="0" cellspacing="0" valign="top" width="100%">
<tr>
	<td>Essentials:</td>
</tr>
<tr>
	<td><font size="-1"><a href="/foundry/linuxkernel/">Linux Kernel</a>, <a href="/foundry/linuxdrivers/"><b>Linux Drivers</b></a></font></td>
</tr>
<tr>
	<td>Hardware:</td>
	<td>Programming:</td>
</tr>
<tr>
	<td><font size="-1"><a href="/foundry/printing/">Printing</a>, <a href="/foundry/storage/">Storage</a></font></td>
	<td><font size="-1"><a href="/foundry/java/">Java</a>, <a href="/foundry/perl-foundry/">Perl</a>, <a href="/foundry/php-foundry/">PHP</a>, <a href="/foundry/python-foundry/">Python</a>, <a href="/foundry/tcl-foundry/">Tcl/Tk</a>, <a href="/foundry/gnome-foundry/">GNOME</a></font></td>
</tr>
<tr>
	<td>International:</td>
	<td>Services:</td>
</tr>
<tr>
	<td><font size="-1"><a href="/foundry/french/">French</a>, <a href="/foundry/spanish/">Espanol</a>, <a href="/foundry/japanese/">Japanese</a></font></td>
	<td><font size="-1"><a href="/foundry/databases/">Database</a>, <a href="/foundry/web/">Web</a></font></td>
</tr>
<tr>
	<td>Graphics:</td>
	<td>Fun:</td>
</tr>
<tr>
	<td><font size="-1"><a href="/foundry/vectorgraphics/">Vector Graphics</a>, <a href="/foundry/3d/">3D</a></font></td>
	<td><font size="-1"><a href="/foundry/games/">Games</a></font></td>
</tr>
<tr>
		<td>&nbsp;</td><td align="right"><font size="-1"><a href="about_foundries.php">[ More ]</a></font></td>
</tr>
</table>
<br>

-->

<?php
echo $HTML->box1_top($Language->getText('group','long_news'));
echo news_show_latest($sys_news_group,5,true,false,false,5);
echo $HTML->box1_bottom();
?>

</TD>

<TD width="35%" VALIGN="TOP">

<?php

echo cache_display('show_features_boxes','show_features_boxes()',(24*3600));

?>

</TD></TR>
</TABLE>

<?php

$HTML->footer(array());

?>
