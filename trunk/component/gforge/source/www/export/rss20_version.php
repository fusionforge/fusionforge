<?php

// Export software version in RSS 2.0
// Author: Christian Bayle <bayle@debian.org>

include "../env.inc.php";
include "pre.php";
require_once $gfcommon.'include/FusionForge.class.php';

$forge=new FusionForge();
$vers=$forge->software_version;
$name=$forge->software_name;
$date=gmdate('D, d M Y g:i:s',time())." GMT";
$link="http://".$GLOBALS[sys_default_domain];

header("Content-Type: text/xml");
print '<?xml version="1.0"?>';
?>
<rss version="2.0">
<channel>
	<copyright><?php echo $name; ?></copyright>
        <pubDate><?php echo $date; ?></pubDate>
        <description><?php echo "$name $vers"; ?></description>
        <link><?php echo $link; ?></link>
        <title><?php echo $title; ?></title>
        <webMaster><?php echo $webmaster; ?></webMaster>
        <language>en-us</language>
	<item>
              	<title>Name</title>
              	<link><?php echo $link; ?></link>
              	<description><?php echo $name; ?></description>
	</item>
	<item>
              	<title>Version</title>
              	<link><?php echo $link; ?></link>
              	<description><?php echo $vers; ?></description>
	</item>
</channel>
</rss>
