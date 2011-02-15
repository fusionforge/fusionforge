#! /bin/sh

# This should setup an OSLC Provider test suite environment

# Fist, make sure the plugin is installed
aptitude install fusionforge-plugin-oslc

# Dependencies : installed Maven 2 and JDK (aptitude install maven2 default-jdk)
aptitude install maven2 default-jdk

# We don't embedd a copy of the test suite and instead refer to the latest version on SF.net
if [ ! -d provider-test ] ; then
    svn checkout https://oslc-tools.svn.sourceforge.net/svnroot/oslc-tools/provider-test
fi

cd provider-test
svn update

# The tests config is in the properties file
if [ ! -f config/fusionforge/ffsandbox.properties ]; then
    cat <<EOF >config/fusionforge/ffsandbox.properties
##GENERAL PROPERTIES##
#The location of the top level ServiceProviderCatalog or Service Description Document
baseUri=https://forge.local/plugins/oslc/cm/oslc-services/
#Implementation name (for identification purposes)
implName=FusionForge
#The authentication style (currently supports only BASIC, not FORM)
authMethod=BASIC
#formUri=https://quagmire.rtp.raleigh.ibm.com:9443/ccm/authenticated/j_security_check
#The authentication credentials
userId=oslctest
pw=oslctest

# Values: http://open-services.net/xmlns/cm/1.0/ | http://open-services.net/ns/cm# | both
#testVersions=http://open-services.net/xmlns/cm/1.0/
testVersions=http://open-services.net/ns/core#

##QUERY PROPERTIES##
#The query parameter that should be used to test equality
queryEqualityProperty=dcterms:title
#The parameter value used with the parameter to test equality (record with this value should exist in the system)
queryEqualityValue=another ticket
#The query parameter that should be used to test equality
queryComparisonProperty=dcterms:modified
#The parameter value used with the parameter to test comparisons (should split the results into two non-empty sets)
queryComparisonValue=2010-08-16T20:16:03.578Z
#Additional non-OSLC parameters that need to be included to run queries
queryAdditionalParameters=
#A value to test full text search against (should return a non-empty record list)
fullTextSearchTerm=templatedDefect

##CREATION AND UPDATION PROPERTIES##
#Location of properly formatted xml which will be used to create a record.
createTemplateXmlFile=config/rtc/rtc-template.xml
#Location of properly formatted json which will be used to create a record.
createTemplateJsonFile=config/rtc/rtc-json-template.json
#Location of properly formatted xml which will be used to update a record.
updateTemplateXmlFile=config/rtc/rtc-update.xml
#Location of properly formatted json which will be used to update a record.
updateTemplateJsonFile=config/rtc/rtc-json-update.json

##OAUTH PROPERTIES##
#The URL corresponding to issuing request tokens
#OAuthRequestTokenUrl=https://localhost:9443/jazz/oauth-request-token
#The URL corresponding to OAuth user authorization
#OAuthAuthorizationUrl=https://localhost:9443/jazz/oauth-authorize
#Parameters to be POSTed to the authorization URL along with the acquired request token
#OAuthAuthorizationParameters=oauth_callback=&authorize=true
#The URL corresponding to issuing access tokens
#OAuthAccessTokenUrl=https://localhost:9443/jazz/oauth-access-token
#The consumer token and secret used to make the OAuth accesses
#OAuthConsumerToken=7bcf944e7f224096b448a17fdd1da57e
#OAuthConsumerSecret=secret
EOF

fi

mvn clean

##surefire reports for the OSLC testsuite results##
ln -s /root/fusionforge-Branch_5_1/src/plugins/oslc/tests/provider-test/target/site /usr/share/gforge/plugins/oslc/www/surefire

