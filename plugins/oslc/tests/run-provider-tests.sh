#! /bin/sh

cd provider-test

baseUri=`grep baseUri config/fusionforge/ffsandbox.properties | sed 's/^.*=//g'`

echo
echo "Starting the OSLC provider test suite on $baseUri."
echo

# Run the tests and build the "site" which contains the HTML test suite reports
mvn -Dtest=DynamicSuiteBuilder -DargLine="-Dprops=config/fusionforge/ffsandbox.properties" site
