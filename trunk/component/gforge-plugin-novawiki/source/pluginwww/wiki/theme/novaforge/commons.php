<?php


function printPageHeader($strTitle, $addRobotFilter, $preTitle)
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="&Lang.Code;" xml:lang="&Lang.Code;">
<head>
<title>&Config.Title; <?php echo $strTitle; ?> &Page.Name;</title>
<?php 
if( $addRobotFilter ) 
{?>
<meta name="robots" content="noindex,nofollow"/>
<?php }
?>
<meta name="Generator" content="&Config.Version;"/>
<link rel="stylesheet" type="text/css" title="ChuWiki" href="&Config.URI;&Config.ThemePath;/ChuWiki.css"/>
</head>

<body>
<p id="Logo">&Config.Title;</p>

<h1>&NovaForge.Project; : <?php echo $preTitle; ?>&Page.Name;</h1>

<?php
}


function printPageContent($options)
{ ?>
<div id="Content"<?php echo $options; ?>>
&Page.Html;
</div>
<?php
}

function printPageFooter($isHistory,$isEdit)
{
?>
<hr id="UtilsSeparator"/>
<ul id="Utils">
	<li><a href="&Config.WikiURI;&Lang.DefaultPage;">&Lang.DefaultPage;</a></li>
	<li><a href="&Config.WikiURI;&Lang.ListPage;">&Lang.ListPage;</a></li>
	<li><a href="&Config.WikiURI;&Lang.ChangesPage;">&Lang.ChangesPage;</a></li>
<?php
if($isEdit){?>
	<li><a href="&Config.WikiURI;&Page.Name;">&Lang.Back;</a></li>
<?php } else { ?>
	<li><a href="&Config.EditURI;&Page.Name;#Wiki">&Lang.Edit;</a></li>
<?php } ?>
<?php
if($isHistory){?>
	<li><a href="&Config.WikiURI;&Page.Name;">&Lang.Back;</a></li>
<?php } else { ?>
	<li><a href="&Config.HistoryURI;&Page.Name;#Date">&Lang.History;</a></li>
<?php } ?>
</ul>

</body>
</html>
<?php
}
?>
