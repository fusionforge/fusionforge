<?php
/**
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */


// Export software version in RSS 2.0
// Author: Christian Bayle <bayle@debian.org>

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';
require_once $gfcommon.'include/FusionForge.class.php';

$forge=new FusionForge();
$vers=$forge->software_version;
$name=$forge->software_name;
$date=rss_date(time());
$link="http://".forge_get_config('web_host').'/';
$title=forge_get_config ('forge_name').' - Software version';

header("Content-Type: text/xml; charset=utf-8");
print '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
<channel>
	<copyright><?php echo $name; ?></copyright>
        <pubDate><?php echo $date; ?></pubDate>
        <description><?php echo "$name $vers"; ?></description>
        <link><?php echo $link; ?></link>
        <title><?php echo $title; ?></title>
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
