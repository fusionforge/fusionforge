<?php
 
require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class TarSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

		system("scp -r ../tests root@".HOST.":/opt");

		system("scp ../../build/packages/fusionforge-*.tar.bz2 root@centos52:");
		system("ssh root@centos52 'tar jxf fusionforge-*.tar.bz2'");
		system("ssh root@centos52 'cd fusionforge-*; FFORGE_RPM_REPO=http://buildbot.fusionforge.org/job/fusionforge-trunk-build-and-test-rpm/ws/build/packages/ FFORGE_DB=fforge FFORGE_USER=gforge FFORGE_ADMIN_USER=ffadmin FFORGE_ADMIN_PASSWORD=ffadmin ./install.sh centos52.local'");

		// Install a fake sendmail to catch all outgoing emails.
		system("ssh root@".HOST." 'perl -spi -e s#/usr/sbin/sendmail#/opt/tests/scripts/catch_mail.php# /etc/gforge/local.inc'");
	}
}
?>
