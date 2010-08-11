#!/usr/bin/php
<?php
/**
 * FusionForge Installation
 *
 * Copyright 2010, Roland Mas
 * http://fusionforge.org/
 */

$fusionforge_etc_dir = getenv('FUSIONFORGE_ETC_DIR');
if (empty($fusionforge_etc_dir))
{
	$fusionforge_etc_dir = '/etc/gforge';
}
$fusionforge_src_dir = getenv('FUSIONFORGE_SRC_DIR');
if (empty($fusionforge_src_dir))
{
	$fusionforge_src_dir = '/opt/gforge';
}
$fusionforge_data_dir = getenv('FUSIONFORGE_DATA_DIR');
if (empty($fusionforge_data_dir))
{
	$fusionforge_data_dir = '/var/lib/gforge';
}
