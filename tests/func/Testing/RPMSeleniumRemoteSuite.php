<?php

require dirname(__FILE__).'/SeleniumRemoteSuite.php';

class RPMSeleniumRemoteSuite extends SeleniumRemoteSuite
{
	protected function setUp()
	{
		parent::setUp();

		system("scp -r ../tests root@".HOST.":/usr/share");
		
		system("scp -rp ~/fusionforge_repo root@".HOST.":");
		system("scp -rp ".dirname(__FILE__)."/../../../gforge/rpm-specific/dag-rpmforge.repo root@".HOST.":/etc/yum.repos.d/");
		system("scp -rp ".dirname(__FILE__)."/../../../gforge/rpm-specific/fusionforge-ci.repo root@".HOST.":/etc/yum.repos.d/");

		# Prefill yum cache to reduce real downloads.
		//system("scp -rp ~/yum-cache-prefill/* root@".HOST.":/var/cache/yum/");
		
		# Enable yum cache to get back the cache for next run.
		//sed -i 's/keepcache=0/keepcache=1/' /var/lib/vz/private/$i/etc/yum.conf 

		sleep(5);
		
		system("ssh root@".HOST." 'yum install -y fusionforge fusionforge-scmsvn fusionforge-online_help fusionforge-extratabs fusionforge-ldapextauth fusionforge-scmgit fusionforge-blocks'");

		//cp -rp /var/lib/vz/private/$i/var/cache/yum/* ~/yum-cache-prefill
		//rm -fr ~/yum-cache-prefill/fusionforge
	}
}
?>
