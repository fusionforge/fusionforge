#include "soapH.h"
#include "soapGForgeAPI.nsmap"
main()
{
	struct soap soap;
	soap_init(&soap);
	xsd__string  parm ="test";
	tns__helloResponse * out;

	if (soap_call_tns__hello ( &soap, "http://cougaar.org/soap/SoapAPI.php", "hello", parm, out ))
{
		soap_print_fault(&soap,stderr);
}
}
