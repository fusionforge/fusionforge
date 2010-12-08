<?php
 
require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class TarSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

		system("scp -r ../tests root@".HOST.":/opt");

		if (getenv('BUILDRESULT')) {
			system("scp ".getenv('BUILDRESULT')."/fusionforge-*.tar.bz2 root@".HOST.":");
		} else {
			system("scp ../../build/packages/fusionforge-*.tar.bz2 root@".HOST.":");
		}
		system("ssh root@centos52 'tar jxf fusionforge-*.tar.bz2'");

		if (is_file("/tmp/timedhosts.txt")) {
			system("scp -p /tmp/timedhosts.txt root@".HOST.":/var/cache/yum/timedhosts.txt");
		}

		system("ssh root@centos52 'cd fusionforge-*; FFORGE_RPM_REPO=http://buildbot.fusionforge.org/job/fusionforge-trunk-build-and-test-rpm/ws/build/packages/ FFORGE_DB=fforge FFORGE_USER=gforge FFORGE_ADMIN_USER=ffadmin FFORGE_ADMIN_PASSWORD=ffadmin ./install.sh centos52.local'");

		system("scp -p root@".HOST.":/var/cache/yum/timedhosts.txt /tmp/timedhosts.txt");

		// Install a fake sendmail to catch all outgoing emails.
		system("ssh root@".HOST." 'perl -spi -e s#/usr/sbin/sendmail#/opt/tests/scripts/catch_mail.php# /etc/gforge/local.inc'");

		system("ssh root@".HOST." 'service crond stop'");
	}
}
?>
