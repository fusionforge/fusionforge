#!/usr/bin/php
<?php
/**
 * FusionForge Installation Dependency Setup
 *
 * Copyright 2006 GForge, LLC
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
 *
 * @version
 *
 * This file is part of GInstaller. It is be called by install.sh.
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 * Francisco Gimeno
 */

define ("VERBOSE", TRUE);
define ("GREEN", "\033[01;32m" );
define ("NORMAL", "\033[00m" );
define ("RED", "\033[01;31m" );

function printUsage() {
	echo "Usage: fusionforge-install-1-deps [RHEL5|DEBIAN|FEDORA|CENTOS|ARK|SUSE|OPENSUSE]\n";
}

function INFO($message)
{
    global $depth, $myLog;
    if(VERBOSE) for ($i=0; $i < $depth; $i++) echo " ";
        if(VERBOSE) echo $message."\n";
    for($i=0; $i < $depth; $i++ ) $myLog.=" ";
    $myLog.=$message;
}

function installRedhat() {
	addFusionForgeYumRepo();
	addDagRPMForgeYumRepo();
	INFO("Installing packages: Executing YUM. Please wait...\n\n\n");
	passthru("yum -y install httpd php mailman cvs postgresql postgresql-libs postgresql-server postgresql-contrib perl-URI php-pgsql subversion mod_dav_svn postfix rcs php-gd mod_ssl wget openssh which liberation-fonts php-htmlpurifier php-mbstring php-jpgraph-1.5.2 poppler-utils php-pecl-zip php-pear-HTTP_WebDAV_Server antiword php-pecl-Fileinfo shared-mime-info rsync");
}

function installDebian() {

	INFO("Installing Packages with apt-get");
	passthru("apt-get -y install apache2 php5 php5-cli php5-pgsql cvs postgresql postgresql-contrib libipc-run-perl liburi-perl libapache2-svn libapache2-mod-php5 subversion subversion-tools php5-curl curl ssh lsb-release php-htmlpurifier");
	passthru("a2enmod headers");
	passthru("a2enmod proxy");
	passthru("a2enmod ssl");
	passthru("a2enmod rewrite");
	passthru("a2enmod vhost_alias");

	INFO(RED."You Must Install Mailman Manually: apt-get install mailman postfix".NORMAL);
}

function installSUSE() {

	INFO("Installing Packages with yast");
	passthru("yast -i apache2-prefork php mailman cvs postgresql postgresql-libs postgresql-server postgresql-contrib perl-URI php4-pgsql subversion apache-mod_dav_svn ssh postfix rcs php4-gd mod_ssl perl-IPC-Run php4-curl wget subversion-server apache2-mod_php4");

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

	INFO(RED."You Must Install htmlpurifier manually.".NORMAL);
}

function installOPENSUSE() {

	INFO("Installing Packages with yast");
	passthru("yast -i apache2-prefork apache2-mod_php5 cvs mailman perl-IPC-Run perl-URI php5 php5-curl php5-gd php5-gettext php5-pgsql postfix postgresql postgresql-contrib postgresql-libs postgresql-server rcs openssh subversion subversion-server wget viewvc");

	INFO("Restarting PostgreSQL...");
	passthru("rcpostgresql restart");
	INFO("Restarting Apache...");
	passthru("rcapache2 restart");

	INFO(RED."You Must Install htmlpurifier manually.".NORMAL);
}

function installArk() {
	INFO("Installing packages: Executing apt-get. Please wait...\n\n\n");
	passthru("apt-get update");
	passthru("apt-get -y install httpd php mailman cvs postgresql postgresql-libs postgresql-server postgresql-contrib perl-URI php-pgsql subversion subversion-server-httpd postfix rcs mod_ssl wget ssh");

	INFO("Restarting PostgreSQL\n");
	passthru("/sbin/service postgresql restart");

	INFO(RED."You Must Install htmlpurifier manually.".NORMAL);
}

function addFusionForgeYumRepo() {
	if(!is_file("/etc/yum.repos.d/fusionforge.repo")){
	INFO("Adding FusionForge YUM repository\n");

	if (getenv('FFORGE_RPM_REPO')) {
		$rpm_repo = getenv('FFORGE_RPM_REPO');
	} else {
		$rpm_repo = 'http://fusionforge.fusionforge.org/rpm/5.1';
	}

	$repo = '
# Name: FusionForge RPM Repository
# URL: http://fusionforge.org/
[fusionforge]
name = Red Hat Enterprise $releasever - fusionforge.org
baseurl = '.$rpm_repo.'
enabled = 1
protect = 0
gpgcheck = 0';
	file_put_contents('/etc/yum.repos.d/fusionforge.repo', $repo);
	}
}
function addDagRPMForgeYumRepo() {
	if(!is_file("/etc/yum.repos.d/dag-rpmforge.repo")){
	INFO("Adding Dag RPMForge YUM repository\n");
	$repo = '
# Name: RPMforge RPM Repository for Red Hat Enterprise 5 - dag
# URL: http://rpmforge.net/
[dag-rpmforge]
name = Red Hat Enterprise $releasever - RPMforge.net - dag
baseurl = http://apt.sw.be/redhat/el5/en/$basearch/dag
#mirrorlist = http://apt.sw.be/redhat/el5/en/mirrors-rpmforge
enabled = 1
protect = 0
gpgcheck = 0';
	file_put_contents('/etc/yum.repos.d/dag-rpmforge.repo', $repo);
	}
}

if (count($argv) < 2) {
	if ( is_file('/etc/SuSE-release') ) {
		if ( exec('grep openSUSE /etc/SuSE-release') ) {
			$platform = 'OPENSUSE';
			echo "detected OPENSUSE platform\n";
		} else {
			$platform = 'SUSE';
			echo "detected SUSE platform\n";
		}
	} else {
		printUsage();
	}
} else {
	if ($argv[1] == '-h' || $argv[1] == '--help') {
		printUsage();
		exit();
	} else {
		$platform = $argv[1];
		echo "setting up dependencies for $platform\n";
	}
}

if ($platform == 'FEDORA' || $platform == 'CENTOS' || $platform == 'RHEL5') {
	installRedhat();
} elseif ($platform == 'DEBIAN') {
	installDebian(); /* Debian and friends */
} elseif ($platform == 'SUSE') {
	installSUSE();
} elseif ($platform == 'OPENSUSE') {
	installOPENSUSE();
} elseif ($platform == 'ARK') {
	installArk();
} else {
	echo 'UNSUPPORTED PLATFORM\n';
}

?>
