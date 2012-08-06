<?php

require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/tests/func/Testing/SeleniumGforge.php';

// This will analize the HTML report generated out of the OSLC
// provider JUnit test suite by the Maven Surefire plugin to detect if
// tests are passed as before (non-regression)

class SureFireReports extends FForge_SeleniumTestCase
{

	// Test the ServiceProviderCatalogTests results
	public function testServiceProviderCatalogTestsResults()
	{
		system('mvn --version &>/dev/null', $retval);
        if ($retval) {
            $this->markTestSkipped('Maven (mvn) is not available.');
        }

		// Make sure there's a project with a tracker
		$this->populateStandardTemplate('trackers');
		$this->init();

		// start the OSLC test suite
		echo "\nStarting OSLC test suite\n";
		$directory = dirname(dirname(__FILE__));
		echo "Executing " . $directory . "/run-provider-tests.sh\n";
		system('cd ' . $directory . '; ./run-provider-tests.sh');
		echo "OSLC test suite executed\n\n";

		// Check the OSLC JUnit testsuite results in the generated HTML report
		$this->open("/plugins/oslc/surefire/surefire-report.html");
		$this->assertEquals("net.openservices.provider.test.oslcv2tests", $this->getTable("//div[@id='contentBox']/div[3]/table.1.0"));
		$this->assertEquals("ServiceProviderCatalogXmlTests", $this->getTable("//div[@id='contentBox']/div[3]/div/table.1.1"));
		$this->assertEquals("100%", $this->getTable("//div[@id='contentBox']/div[3]/div/table.1.6"));
		$this->assertEquals("ServiceProviderXmlTests", $this->getTable("//div[@id='contentBox']/div[3]/div/table.3.1"));
		$this->assertEquals("100%", $this->getTable("//div[@id='contentBox']/div[3]/div/table.3.6"));
		$this->assertEquals("ServiceProviderCatalogRdfXmlTests", $this->getTable("//div[@id='contentBox']/div[3]/div/table.4.1"));
		$this->assertEquals("100%", $this->getTable("//div[@id='contentBox']/div[3]/div/table.4.6"));
	}
}
?>
