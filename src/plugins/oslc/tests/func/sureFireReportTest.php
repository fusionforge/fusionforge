<?php

require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/tests/func/Testing/SeleniumGforge.php';

// This will analize the HTML report generated out of the OSLC
// provider JUnit test suite by the Maven Surefire plugin to detect if
// tests are passed as before (non-regression)

class Example extends FForge_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("https://forge.local/plugins/oslc/surefire/surefire-report.html");
  }

  // Test the ServiceProviderCatalogTests results
  public function testServiceProviderCatalogTests()
  {
    $this->open("/plugins/oslc/surefire/surefire-report.html");
    $this->click("link=net.openservices.provider.test.oslcv1tests");

    $this->assertEquals("net.openservices.provider.test.oslcv1tests", $this->getText("//div[@id='contentBox']/div[3]/div/h3"));

    $this->assertEquals("ServiceProviderCatalogTests", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[2]"));

    // Tests
    $this->assertEquals("42", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[3]"));
    // Errors
    $this->assertEquals("0", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[4]"));
    // Failures
    $this->assertEquals("8", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[5]"));
    // Skipped
    $this->assertEquals("0", $this->getText("//div[@id='contentBox']/div[3]/div/h3[contains(.,'net.openservices.provider.test.oslcv1tests')]/../table/tbody/tr[2]/td[6]"));
  }
}
?>