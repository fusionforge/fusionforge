#include "soapH.h"
#include "soapGForgeAPI.nsmap"
main()
{
	struct soap soap;
	soap_init(&soap);


	if (soap_call_tns__user ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* xsd__string  func, tns__ArrayOfstring * params, tns__userResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__logout ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* tns__logoutResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__hello ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* xsd__string  parm, tns__helloResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__getNumberOfActiveUsers ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* tns__getNumberOfActiveUsersResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__bugList ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* xsd__string  sessionkey, xsd__string  project, tns__bugListResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__bugUpdate ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* xsd__string  sessionkey, xsd__string  project, xsd__string  bugid, xsd__string  comment, tns__bugUpdateResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__group ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* xsd__string  func, tns__ArrayOfstring * params, tns__groupResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__getPublicProjectNames ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* tns__getPublicProjectNamesResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__getSiteStats ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* tns__getSiteStatsResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__login ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* xsd__string  userid, xsd__string  passwd, tns__loginResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__bugAdd ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* xsd__string  sessionkey, xsd__string  project, xsd__string  summary, xsd__string  details, tns__bugAddResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__getNumberOfHostedProjects ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* tns__getNumberOfHostedProjectsResponse * out*/ ))
		soap_print_fault(&soap,stderr);


	if (soap_call_tns__bugFetch ( &soap, "http://cougaar.org/soap/SoapAPI.php", "http://cougaar.org/soap/SoapAPI.php",/* xsd__string  sessionkey, xsd__string  project, xsd__string  bugid, tns__bugFetchResponse * out*/ ))
		soap_print_fault(&soap,stderr);


}
