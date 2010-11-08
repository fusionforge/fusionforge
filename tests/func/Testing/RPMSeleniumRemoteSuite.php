<?php

require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class RPMSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

		system("scp -r ../tests root@".HOST.":/usr/share");
		system("ssh root@".HOST." 'ln -s gforge /usr/share/src'");
		
		system("scp -rp ~/fusionforge_repo root@".HOST.":");
		system("scp -rp ".dirname(__FILE__)."/../../../src/rpm-specific/dag-rpmforge.repo root@".HOST.":/etc/yum.repos.d/");

//		system("scp -rp ".dirname(__FILE__)."/../../../src/rpm-specific/fusionforge-ci.repo root@".HOST.":/etc/yum.repos.d/");
		if (getenv('FFORGE_RPM_REPO')) {
			system("ssh root@".HOST." 'cd /etc/yum.repos.d/; wget ".getenv('FFORGE_RPM_REPO')."/fusionforge.repo'");
		}

		sleep(5);
		
		system("ssh root@".HOST." 'yum install -y fusionforge fusionforge-plugin-scmsvn fusionforge-plugin-online_help fusionforge-plugin-extratabs fusionforge-plugin-ldapextauth fusionforge-plugin-scmgit fusionforge-plugin-blocks'");

		// Install a fake sendmail to catch all outgoing emails.
		system("ssh root@".HOST." 'perl -spi -e s#/usr/sbin/sendmail#/usr/share/tests/scripts/catch_mail.php# /etc/gforge/local.inc'");
	}
}
?>
