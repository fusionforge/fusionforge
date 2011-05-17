#!/usr/bin/php
<?php
/**
 * FusionForge Installation Dependency Setup
 *
 * Copyright 2006 GForge, LLC
 * http://fusionforge.org/
 *
 * @version
 *
 * This file is part of GInstaller, it is called by install.sh.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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

	define ('GREEN', "\033[01;32m" );
	define ('NORMAL', "\033[00m" );
	define ('RED', "\033[01;31m" );

	$args = $_SERVER['argv'];
	$hostname = $args[1];

	echo "Validating arguments  ";
	if (count($args) != 4) {
		echo "FAIL\n  Usage: $args[0]  gforge.company.com  apacheuser  apachegroup\n";
		exit(127);
	}
	echo "OK\n";

	//validate hostname
	echo "Validating hostname  ";
	if (!preg_match("/^([[:alnum:]._-])*$/" , $hostname)) {
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

	echo "Creating /opt/gforge  ";
	system("mkdir -p /opt/gforge");
	if (!is_dir("/opt/gforge"))
	{
		echo "FAIL\n  /opt/gforge didn't exist - error - make sure you've got permission";
		exit(2);
	}
	echo "OK\n";

	echo "Creating /var/lib/gforge  ";
	system("mkdir -p /var/lib/gforge  ");
	if (!is_dir("/var/lib/gforge"))
	{
		echo "FAIL\n  /var/lib/gforge didn't exist - error - make sure you've got permission";
		exit(2);
	}
	echo "OK\n";

	echo "Creating /var/log/gforge  ";
	system("mkdir -p /var/log/gforge  ");
	if (!is_dir("/var/log/gforge"))
	{
		echo "FAIL\n  /var/log/gforge didn't exist - error - make sure you've got permission";
		exit(2);
	}
	echo "OK\n";

	system("cp -r * /opt/gforge");

	require_once 'install-common.inc' ;

	chdir("/var/lib/gforge");
	system("mkdir -p uploads");
	system("mkdir -p scmtarballs");
	system("mkdir -p scmsnapshots");
	system("mkdir -p scmrepos/svn");
	system("mkdir -p scmrepos/cvs");

	system("mkdir -p etc");
	system("touch etc/httpd.vhosts");

	//#project vhost space
	system("mkdir -p homedirs");
	system("mkdir -p /home/groups");
	if (!is_dir("homedirs/groups"))
	{
		symlink("/home/groups", "homedirs/groups");
	}

	if (!is_dir("/scmrepos"))
	{
		symlink("$fusionforge_data_dir/scmrepos", "/scmrepos");
	}

	// Create the old symlink /svnroot for compatibility.
	if (!is_dir("/svnroot"))
	{
		symlink("$fusionforge_data_dir/scmrepos/svn", "/svnroot");
	}

	// Create the old symlink /cvsroot for compatibility.
	if (!is_dir("/cvsroot"))
	{
		symlink("$fusionforge_data_dir/scmrepos/cvs", "/cvsroot");
	}

	// Create default dumps dir
	system("mkdir -p /var/lib/gforge/dumps");

	//cd /opt/gforge
	chdir("/opt/gforge");

	//#restricted shell for cvs accounts
	//echo "linea 1\n";
	system("cp plugins/scmcvs/bin/cvssh.pl /bin/");
	//echo "linea 2\n";
	system("chmod 755 /bin/cvssh.pl");

	// Create default location for gforge config files
	system("mkdir -p /etc/gforge");
	if (!is_file("/etc/gforge/local.inc")) {
		system("cp etc/local.inc.example /etc/gforge/local.inc");
	}
	if (!is_file("/etc/gforge/httpd.conf")) {
		system("cp etc/httpd.conf-opt /etc/gforge/httpd.conf");
	}
	system("mkdir -p /etc/gforge/httpd.conf.d");
	$h = opendir ('etc/httpd.conf.d-opt') ;
	while (false !== ($file = readdir($h))) {
		if ($file != "." 
		    && $file != ".."
		    && (preg_match ('/^[0-9a-zA-Z_-]+(.conf)?$/', $file)
			|| preg_match ('/^[0-9a-zA-Z_-]+(.inc)?$/', $file))) {
			if (!is_file("/etc/gforge/httpd.conf.d/$file")) {
				system("cp etc/httpd.conf.d-opt/$file /etc/gforge/httpd.conf.d");
			}
		}
	}
	closedir($h);

	system("cp etc/config.ini-opt /etc/gforge/config.ini");
	system("cp -rL etc/config.ini.d /etc/gforge/config.ini.d");

	// Install default configuration files for all plugins.
	system("mkdir -p /etc/gforge/plugins/");
	chdir("/opt/gforge/plugins");
	foreach( glob("*") as $plugin) {
		$source = "/opt/gforge/plugins/$plugin/etc/plugins/$plugin";
		if (is_dir($source)) {
			system("cp -r $source /etc/gforge/plugins/");
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
	if (!is_dir("blocks"))
	{
		symlink ("../../plugins/blocks/www", "blocks");
	}

	//cd /opt/gforge
	chdir("/opt/gforge");
	system("chown -R root:$args[3] /opt/gforge");
	system("chmod -R 644 /opt/gforge/");
	system("cd /opt/gforge && find -type d | xargs chmod 755");
	system("chown -R $args[2]:$args[3] /var/lib/gforge/uploads");
	system("chmod -R 755 /opt/gforge/cronjobs/");
	system("chmod 755 /opt/gforge/www/scm/viewvc/bin/cgi/viewvc.cgi");
	system("chmod 755 /opt/gforge/utils/forge_get_config");
	system("chmod 755 /opt/gforge/utils/manage-apache-config.sh");
	system("chmod 755 /opt/gforge/utils/manage-translations.sh");
	system("chmod 755 /opt/gforge/utils/migrate-to-ini-files.sh");
	
	if (!is_dir("/etc/gforge"))
	{
		echo "/etc/gforge didn't exist - error - make sure you've got permission";
		exit(2);
	}
	system("chown -R root:$args[3] /etc/gforge/");
	system("chmod -R 644 /etc/gforge/");
	system("cd /etc/gforge && find -type d | xargs chmod 755");
	system("cd /etc/gforge && find -type f -exec perl -pi -e \"s/apacheuser/$args[2]/\" {} \;");
	system("cd /etc/gforge && find -type f -exec perl -pi -e \"s/apachegroup/$args[3]/\" {} \;");
	system("cd /etc/gforge && find -type f -exec perl -pi -e \"s/gforge\.company\.com/$hostname/\" {} \;");
	system("echo \"noreply:	/dev/null\" >> /etc/aliases");

	# Generate a random hash for the session_key
	$hash = md5(microtime());
	system("perl -spi -e \"s/sys_session_key = 'foobar'/sys_session_key = '$hash'/\" /etc/gforge/local.inc");

	# Replace /path/to/gforge to /opt/gforge
	$config = file_get_contents('/etc/gforge/local.inc');
	$content = str_replace('/path/to/gforge', '/opt/gforge', $config);
	file_put_contents('/etc/gforge/local.inc', $content);

	# Set jpgraph path.
	if (is_dir("/usr/share/jpgraph")) {
		system("perl -spi -e \"s!^(.sys_path_to_jpgraph)=.*!\\$1='/usr/share/jpgraph';!\" /etc/gforge/local.inc");
		system("perl -spi -e \"s!//(.gantt_title_font_family)='FF_ARIAL';!\\$1='FF_LIBERATION_SANS';!\" /etc/gforge/local.inc");
		system("perl -spi -e \"s!//(.gantt_title_font_style=.*)!\\$1!\" /etc/gforge/local.inc");
		system("perl -spi -e \"s!//(.gantt_title_font_size=.*)!\\$1!\" /etc/gforge/local.inc");
		system("perl -spi -e \"s!//(.gantt_task_font_family)='FF_ARIAL';!\\$1='FF_LIBERATION_SANS';!\" /etc/gforge/local.inc");
		system("perl -spi -e \"s!//(.gantt_task_font_style=.*)!\\$1!\" /etc/gforge/local.inc");
		system("perl -spi -e \"s!//(.gantt_task_font_size=.*)!\\$1!\" /etc/gforge/local.inc");
	}
	print "\n";
