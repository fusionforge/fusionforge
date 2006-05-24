#!/usr/bin/php -f
<?php
/**
 * GForge Installation Dependency Setup
 *
 * Copyright 2006 GForge, LLC
 * http://gforge.org/
 *
 * @version
 *
 * This file is part of GInstaller
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

define ("VERBOSE", TRUE);
define ("GREEN", "\033[01;32m" );
define ("NORMAL", "\033[00m" );
define ("RED", "\033[01;31m" );

function INFO($message)
{
    global $depth, $myLog;
    if(VERBOSE) for ($i=0; $i < $depth; $i++) echo " ";
        if(VERBOSE) echo $message;
    for($i=0; $i < $depth; $i++ ) $myLog.=" ";
    $myLog.=$message;
}

function installRedhat($version) {
	if ($version == 3) {
		INFO("Installing packages: Executing YUM. Please wait...\n\n\n");
		passthru("yum -y install httpd php mailman cvs postgresql postgresql-libs postgresql-server postgresql-contrib perl-URI php-pgsql subversion mod_dav_svn postfix rcs php-gd mod_ssl wget ssh");
	} else {
		INFO("Installing packages: Executing YUM. Please wait...\n\n\n");
		passthru("yum -y install httpd php mailman cvs postgresql postgresql-libs postgresql-server postgresql-contrib perl-URI php-pgsql subversion mod_dav_svn postfix rcs php-gd mod_ssl wget inetd");
	}

	INFO("Restarting PostgreSQL\n");
	passthru("/etc/init.d/postgresql stop");
	passthru("/etc/init.d/postgresql start");
}

function installRHEL() {

	INFO("Installing packages: Executing UP2DATE. Please wait...\n\n\n");
	passthru("up2date --install php php-gd php-pgsql mailman postgresql-server postgresql-contrib rcs cvs httpd subversion perl-URI mod_dav_svn ssh postfix mod_ssl wget");

	INFO("Restarting PostgreSQL\n");
	passthru("/etc/init.d/postgresql stop");
	passthru("/etc/init.d/postgresql start");
}

function installDebian() {

	INFO("Installing Packages with apt-get");
	passthru("apt-get -y install apache2 php4 php4-cli php4-pgsql cvs postgresql postgresql-contrib libipc-run-perl liburi-perl libapache2-svn libapache2-mod-php4 subversion subversion-tools php4-curl curl ssh lsb-release");

	INFO(RED."You Must Install Mailman Manually: apt-get install mailman postfix");
}

function installSUSE() {

	INFO("Installing Packages with yast");
	passthru("yast -i apache2-prefork php mailman cvs postgresql postgresql-libs postgresql-server postgresql-contrib perl-URI php4-pgsql subversion apache-mod_dav_svn ssh postfix rcs php4-gd mod_ssl perl-IPC-Run php4-curl wget subversion-server xinetd apache2-mod_php4");

	INFO("Fixing php4 installation");
	passthru("cp /usr/lib/apache2-prefork/libphp4.so /usr/lib/apache2/mod_php.so");

	INFO("Restarting APACHE");
	passthru("/etc/init.d/apache2 start");
	passthru("/etc/init.d/apache2 stop");

	INFO("Restarting PostgreSQL");
	passthru("/etc/init.d/postgresql stop");
	passthru("/etc/init.d/postgresql start");
	INFO("Starting Apache");
	passthru("/etc/init.d/apache2 start");
}

if (count($argv) < 2) {
    echo "Usage: pre-install.php [RHEL4|DEBIANSARGE|FC3|FC4]\n";
    check_version();
} else {
    $platform = $argv[1];

	if ($platform == 'FC3') {
		installRedhat(3);
	} elseif ($platform == 'FC4') {
		installRedhat(4);
	} elseif ($platform == 'RHEL4') {
		installRHEL();
	} elseif ($platform == 'DEBIANSARGE') {
		installDebian(); /* Debian and friends */
	} elseif ($platform == 'SUSE') {
		installSUSE();
	} else {
		echo 'UNSUPPORTED PLATFORM';
	}
}

?>
