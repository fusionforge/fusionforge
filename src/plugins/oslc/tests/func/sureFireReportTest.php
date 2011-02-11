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

    // Make sure there's a project with a tracker
    $this->populateStandardTemplate('trackers');
    $this->init();

    // start the OSLC test suite
    echo "\nStarting OSLC test suite\n";
    $directory = dirname(dirname(__FILE__));
    echo "Executing " . $directory . "/run-provider-tests.sh\n";
    system('cd '. $directory . '; ./run-provider-tests.sh');
    echo "OSLC test suite executed\n\n";

    // Check the results in the generated HTML report
    $this->open("/plugins/oslc/surefire/surefire-report.html");
    $this->click("link=net.openservices.provider.test.oslcv1tests");

    $this->assertEquals("net.openservices.provider.test.oslcv1tests", $this->getText("//div[@id='contentBox']/div[3]/div/h3"));

    $this->assertEquals("ServiceProviderCatalogTests", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[2]"));

    // Tests
    $this->assertEquals("56", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[3]"));
    // Errors
    $this->assertEquals("0", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[4]"));
    // Failures
    $this->assertEquals("8", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[5]"));
    // Skipped
    $this->assertEquals("0", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[6]"));
  }
}

?>