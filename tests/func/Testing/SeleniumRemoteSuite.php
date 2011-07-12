<?php

// The next require_once is just to get HOST
$config = getenv('CONFIG_PHP') ? getenv('CONFIG_PHP'): 'func/config.php';
require_once $config;

class SeleniumRemoteSuite extends PHPUnit_Framework_TestSuite
{
	protected function setUp()
	{
		if (getenv('SELENIUM_RC_DIR') && getenv('SELENIUM_RC_URL')) {
			$this->captureScreenshotOnFailure = true;
			$this->screenshotPath = getenv('SELENIUM_RC_DIR');
			$this->screenshotUrl = getenv('SELENIUM_RC_URL');
		}
	}

	protected function tearDown()
	{
		echo "tearDown\n";
	}
}
?>
