#!/usr/bin/php
<?php
/**
 * FusionForge Installation Dependency Setup
 *
 * Copyright 2006 GForge, LLC
 * http://gforge.org/
 *
 * @version
 *
 * This file is part of GInstaller, it is called by install.sh.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 * Francisco Gimeno
 */

	define ('GREEN', "\033[01;32m" );
	define ('NORMAL', "\033[00m" );
	define ('RED', "\033[01;31m" );

	$args = $_SERVER['argv'];

	echo "Validating arguments  ";
	if (count($args) != 4) {
		echo "FAIL\n  Usage: $args[0]  gforge.company.com  apacheuser  apachegroup\n";
		exit(127);
	}
	echo "OK\n";

	//validate hostname
	echo "Validating hostname  ";
	if (!preg_match("/^([[:alnum:]._-])*$/" , $args[1])) {
		echo "FAIL\n  invalid hostname\n";
		exit(2);
	}
	echo "OK\n";

	// #validate apache user
	//getent passwd $2 > /dev/null
	//found_apacheuser=$?
	//if [ $found_apacheuser -ne 0 ]; then
	//	echo 1>&2 "invalid apache user"
	//	exit 2
	//fi

	//ARREGLAR ESTO
	exec("getent passwd $args[2] > /dev/null", $arr, $t);
	if ($t != 0) {
	 	echo "invalid apache user\n";
	 	exit(2);
	}




	// #validate apache group
	//getent group $3 > /dev/null
	//found_apachegroup=$?
	//if [ $found_apachegroup -ne 0 ]; then
	//     echo 1>&2 "invalid apache group"
	//     exit 2
	//fi
	

	exec("getent group $args[3] > /dev/null", $arr, $t);
	if ($t != 0) {
	 	echo "invalid apache group";
	 	exit(2);
	}

require_once 'install-common.inc' ;

	echo "Creating $fusionforge_src_dir";
	system("mkdir -p $fusionforge_src_dir");
	if (!is_dir("$fusionforge_src_dir"))
	{
		echo "FAIL\n  $fusionforge_src_dir didn't exist - error - make sure you've got permission";
		exit(2);
	}
	echo "OK\n";

	echo "Creating $fusionforge_data_dir  ";
	system("mkdir -p $fusionforge_data_dir  ");
	if (!is_dir("$fusionforge_data_dir"))
	{
		echo "FAIL\n  $fusionforge_data_dir didn't exist - error - make sure you've got permission";
		exit(2);
	}
	echo "OK\n";


	system("cp -r * $fusionforge_src_dir");

	chdir("$fusionforge_data_dir");
	system("mkdir -p uploads");
	system("mkdir -p scmtarballs");
	system("mkdir -p scmsnapshots");

	//#project vhost space
	system("mkdir -p homedirs");
	system("mkdir -p /home/groups");
	if (!is_dir("homedirs/groups"))
	{
		symlink("/home/groups", "homedirs/groups");
	}

	// Create default location for SVN repositories
	system("mkdir -p svnroot");
	if (!is_dir("/svnroot"))
	{
		symlink("$fusionforge_data_dir/svnroot", "/svnroot");
	}

	// Create default location for CVS repositories
	system("mkdir -p cvsroot");
	if (!is_dir("/cvsroot"))
	{
		symlink("$fusionforge_data_dir/cvsroot", "/cvsroot");
	}

	// Create default dumps dir
	system("mkdir -p $fusionforge_data_dir/dumps");

	chdir("$fusionforge_src_dir");

	//#restricted shell for cvs accounts
	//echo "linea 1\n";
	system("cp plugins/scmcvs/bin/cvssh.pl /bin/");
	//echo "linea 2\n";
	system("chmod 755 /bin/cvssh.pl");

	// Create default location for gforge config files
	system("mkdir -p $fusionforge_etc_dir");
	if (!is_file("$fusionforge_etc_dir/local.inc")) {
		system("cp etc/local.inc.example $fusionforge_etc_dir/local.inc");
	}
	if (!is_file("$fusionforge_etc_dir/httpd.conf")) {
		system("cp etc/gforge-httpd.conf.example $fusionforge_etc_dir/httpd.conf");
	}

	system("mkdir -p $fusionforge_etc_dir/httpd.d");
	system("cp plugins/*/etc/httpd.d/*.conf $fusionforge_etc_dir/httpd.d");

	// Install default configuration files for all plugins.
	system("mkdir -p $fusionforge_etc_dir/plugins/");
	chdir("$fusionforge_src_dir/plugins");
	foreach( glob("*") as $plugin) {
		$source = "$fusionforge_src_dir/plugins/$plugin/etc/plugins/$plugin";
		if (is_dir($source)) {
			system("cp -r $source $fusionforge_etc_dir/plugins/");
		}
	}

	$apacheconffiles=array();
	if (is_file('/etc/httpd/conf/httpd.conf')) {
		$apacheconffiles[]='/etc/httpd/conf/httpd.conf';
	} elseif (is_file('/opt/csw/apache2/etc/httpd.conf')) {
		$apacheconffiles[]='/opt/csw/apache2/etc/httpd.conf';
	} elseif (is_file('/etc/apache2/httpd.conf')) {
		$apacheconffiles[]='/etc/apache2/httpd.conf';
	} else {
		$apacheconffiles[]='/etc/apache2/sites-enabled/000-default';
	}

	foreach ($apacheconffiles as $apacheconffile) {
		echo('Setting FusionForge Include For Apache...');
		system("grep \"^Include $fusionforge_etc_dir/httpd.conf\" $apacheconffile > /dev/null", $ret);
		if ($ret == 1) {
			system("echo \"Include $fusionforge_etc_dir/httpd.conf\" >> $apacheconffile");
		}
	}

	// Create symlink for the wiki plugin.
	if (!is_dir("$fusionforge_src_dir/www/wiki"))
	{
		symlink ("../plugins/wiki/www/", "$fusionforge_src_dir/www/wiki");
	}

	//#symlink plugin www's
	chdir("$fusionforge_src_dir/www");
	if (!is_dir("plugins"))
	{
		system("mkdir -p plugins");
	}

	chdir("plugins");
	if (!is_dir("cvstracker"))
	{
		symlink ("../../plugins/cvstracker/www/", "cvstracker");
	}
	if (!is_dir("svntracker"))
	{
		symlink ("../../plugins/svntracker/www/", "svntracker");
	}
	if (!is_dir("scmcvs"))
	{
		symlink ("../../plugins/scmcvs/www", "scmcvs");
	}
	if (!is_dir("fckeditor"))
	{
		symlink ("../../plugins/fckeditor/www", "fckeditor");
	}

	chdir("$fusionforge_src_dir");
	system("chown -R root:$args[3] $fusionforge_src_dir");
	system("chmod -R 644 $fusionforge_src_dir/");
	system("cd $fusionforge_src_dir && find -type d | xargs chmod 755");
	system("chown -R $args[2]:$args[3] $fusionforge_data_dir/uploads");
	system("chmod -R 755 $fusionforge_src_dir/cronjobs/");
	system("chmod 755 $fusionforge_src_dir/www/scm/viewvc/bin/cgi/viewvc.cgi");
	
	if (!is_dir("$fusionforge_etc_dir"))
	{
		echo "$fusionforge_etc_dir didn't exist - error - make sure you've got permission";
		exit(2);
	}
	system("chown -R root:$args[3] $fusionforge_etc_dir/");
	system("chmod -R 644 $fusionforge_etc_dir/");
	system("cd $fusionforge_etc_dir && find -type d | xargs chmod 755");
	system("cd $fusionforge_etc_dir && find -type f -exec perl -pi -e \"s/apacheuser/$args[2]/\" {} \;");
	system("cd $fusionforge_etc_dir && find -type f -exec perl -pi -e \"s/apachegroup/$args[3]/\" {} \;");
	system("cd $fusionforge_etc_dir && find -type f -exec perl -pi -e \"s/gforge\.company\.com/$args[1]/\" {} \;");
	system("echo \"noreply:	/dev/null\" >> /etc/aliases");

// Generate a random hash for the session_key
	$hash = md5(microtime());
	system("perl -spi -e \"s/sys_session_key = 'foobar'/sys_session_key = '$hash'/\" $fusionforge_etc_dir/local.inc");

// Use liberation fonts if jpgraph provided in the archive.
	if (is_dir("$fusionforge_src_dir/jpgraph")) {
		system("perl -spi -e \"s!//(.gantt_title_font_family)='FF_ARIAL';!\\$1='FF_LIBERATION_SANS';!\" $fusionforge_etc_dir/local.inc");
		system("perl -spi -e \"s!//(.gantt_title_font_style=.*)!\\$1!\" $fusionforge_etc_dir/local.inc");
		system("perl -spi -e \"s!//(.gantt_title_font_size=.*)!\\$1!\" $fusionforge_etc_dir/local.inc");
		system("perl -spi -e \"s!//(.gantt_task_font_family)='FF_ARIAL';!\\$1='FF_LIBERATION_SANS';!\" $fusionforge_etc_dir/local.inc");
		system("perl -spi -e \"s!//(.gantt_task_font_style=.*)!\\$1!\" $fusionforge_etc_dir/local.inc");
		system("perl -spi -e \"s!//(.gantt_task_font_size=.*)!\\$1!\" $fusionforge_etc_dir/local.inc");
	}
	print "\n";
