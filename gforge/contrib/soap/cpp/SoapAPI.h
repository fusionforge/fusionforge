//gsoap xsi schema namespace: http://www.w3.org/2001/XMLSchema-instance
//gsoap tns schema namespace: http://cougaar.org
//gsoap si schema namespace: http://soapinterop.org/xsd
//gsoap soap schema namespace: http://schemas.xmlsoap.org/wsdl/soap/
//gsoap SOAP-ENV schema namespace: http://schemas.xmlsoap.org/soap/envelope/
//gsoap xsd schema namespace: http://www.w3.org/2001/XMLSchema
//gsoap SOAP-ENC schema namespace: http://schemas.xmlsoap.org/soap/encoding/
//gsoap wsdl schema namespace: http://schemas.xmlsoap.org/wsdl/

//gsoap tns service namespace: http://cougaar.org

//gsoap tns service location: http://cougaar.org/soap/SoapAPI.php
//gsoap tns service name: soapGForgeAPI

/*start primitive data types*/
typedef char * xsd__integer;
typedef char * xsd__string;

/*end primitive data types*/

class tns__bugUpdateResponse {
   public: 
	xsd__string  _bugUpdateResponse;
};

class tns__userResponse {
   public: 
	class tns__ArrayOfstring * _userResponse;
};

class tns__ArrayOfstring {
   public: 
};

class tns__bugFetchResponse {
   public: 
	class tns__Bug * _bugFetchResponse;
};

class tns__logoutResponse {
   public: 
	xsd__string  _logoutResponse;
};

class tns__getPublicProjectNamesResponse {
   public: 
	tns__ArrayOfstring * _projectNames;
};

class tns__getSiteStatsResponse {
   public: 
	class ArrayOfSiteStatsDataPoint * _siteStats;
};

class tns__GroupObject {
   public: 
	xsd__integer  group_USCORE_id;
	xsd__string  group_USCORE_name;
	xsd__integer  is_USCORE_public;
	xsd__string  status;
	xsd__string  unix_USCORE_group_USCORE_name;
};

class ArrayOfBug {
   public: 
	class tns__Bug * __ptr;
	int  __size;
	int  __offset;
};

class ArrayOfGroupObject {
   public: 
	tns__GroupObject * __ptr;
	int  __size;
	int  __offset;
};

class tns__getNumberOfActiveUsersResponse {
   public: 
	xsd__string  _activeUsers;
};

class tns__groupResponse {
   public: 
	ArrayOfGroupObject * _groupResponse;
};

class tns__loginResponse {
   public: 
	xsd__string  _loginResponse;
};

class ArrayOfSiteStatsDataPoint {
   public: 
	class tns__SiteStatsDataPoint * __ptr;
	int  __size;
	int  __offset;
};

class tns__bugListResponse {
   public: 
	tns__ArrayOfstring * _bugListResponse;
};

class tns__Bug {
   public: 
	xsd__string  id;
	xsd__string  summary;
};

class tns__bugAddResponse {
   public: 
	xsd__string  _bugAddResponse;
};

class tns__helloResponse {
   public: 
	xsd__string  _helloResponse;
};

class tns__SiteStatsDataPoint {
   public: 
	xsd__string  date;
	xsd__string  users;
	xsd__string  pageviews;
	xsd__string  sessions;
};

class tns__getNumberOfHostedProjectsResponse {
   public: 
	xsd__string  _hostedProjects;
};

//gsoap tns service method-action: user "http://cougaar.org/soap/SoapAPI.php"
tns__user( xsd__string  func, tns__ArrayOfstring * params, tns__userResponse * out );
//gsoap tns service method-action: logout "http://cougaar.org/soap/SoapAPI.php"
tns__logout( tns__logoutResponse * out );
//gsoap tns service method-action: hello "http://cougaar.org/soap/SoapAPI.php"
tns__hello( xsd__string  parm, tns__helloResponse * out );
//gsoap tns service method-action: getNumberOfActiveUsers "http://cougaar.org/soap/SoapAPI.php"
tns__getNumberOfActiveUsers( tns__getNumberOfActiveUsersResponse * out );
//gsoap tns service method-action: bugList "http://cougaar.org/soap/SoapAPI.php"
tns__bugList( xsd__string  sessionkey, xsd__string  project, tns__bugListResponse * out );
//gsoap tns service method-action: bugUpdate "http://cougaar.org/soap/SoapAPI.php"
tns__bugUpdate( xsd__string  sessionkey, xsd__string  project, xsd__string  bugid, xsd__string  comment, tns__bugUpdateResponse * out );
//gsoap tns service method-action: group "http://cougaar.org/soap/SoapAPI.php"
tns__group( xsd__string  func, tns__ArrayOfstring * params, tns__groupResponse * out );
//gsoap tns service method-action: getPublicProjectNames "http://cougaar.org/soap/SoapAPI.php"
tns__getPublicProjectNames( tns__getPublicProjectNamesResponse * out );
//gsoap tns service method-action: getSiteStats "http://cougaar.org/soap/SoapAPI.php"
tns__getSiteStats( tns__getSiteStatsResponse * out );
//gsoap tns service method-action: login "http://cougaar.org/soap/SoapAPI.php"
tns__login( xsd__string  userid, xsd__string  passwd, tns__loginResponse * out );
//gsoap tns service method-action: bugAdd "http://cougaar.org/soap/SoapAPI.php"
tns__bugAdd( xsd__string  sessionkey, xsd__string  project, xsd__string  summary, xsd__string  details, tns__bugAddResponse * out );
//gsoap tns service method-action: getNumberOfHostedProjects "http://cougaar.org/soap/SoapAPI.php"
tns__getNumberOfHostedProjects( tns__getNumberOfHostedProjectsResponse * out );
//gsoap tns service method-action: bugFetch "http://cougaar.org/soap/SoapAPI.php"
tns__bugFetch( xsd__string  sessionkey, xsd__string  project, xsd__string  bugid, tns__bugFetchResponse * out );
