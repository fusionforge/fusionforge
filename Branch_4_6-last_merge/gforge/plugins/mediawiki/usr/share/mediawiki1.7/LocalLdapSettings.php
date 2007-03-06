<?php
require_once( 'LdapAuthentication.php' );
$wgAuth = new LdapAuthenticationPlugin();

//The names of one or more domains you wish to use
//These names will be used for the other options, it is freely choosable and not dependend
//on your system. You might as well use the name of your dog. These names will show in the 
//Login-Screen, so it is important, the user understands the meaning.
$wgLDAPDomainNames = array(
  "testADdomain","testLDAPdomain"
  );

//The fully qualified name of one or more servers per domain you wish to use
$wgLDAPServerNames = array(
  "testADdomain"=>"testADserver.AD.example.com",
  "testLDAPdomain"=>"testLDAPserver.LDAP.example.com testLDAPserver2.LDAP.example.com"
  );

//The search string to be used for straight binds to the directory; USER-NAME will be
//replaced by the username of the user logging in.
//This option is not required if you are using a proxyagent and proxyagent password
$wgLDAPSearchStrings = array(
  "testADdomain"=>"TDOMAIN\\USER-NAME",
  "testLDAPdomain"=>"uid=USER-NAME,ou=people,dc=LDAP,dc=example,dc=com"
  );

//Use LDAPS (your system may default to using TLS over LDAP instead of LDAPS)
//Recommended!!
$wgLDAPUseSSL = true; 

//Use LDAP with StartTLS
//I believe using LDAPS is a deprecated method. If this works for you, use it instead of SSL.
$wgLDAPUseTLS = false;

//Shortest password a user is allowed to login using. Notice that 1 is the minimum so that
//when using a local domain, local users cannot login as domain users (as domain user's
//passwords are not stored)
$wgMinimalPasswordLength = 1;

//Allow the use of the local database as well as the LDAP database.
//Good for transitional purposes.
$wgLDAPUseLocal = false;

//Options for adding users, and/or updating user preferences. If you use these options
//you must set $wgLDAPWriterDN and $wgLDAPWriterPassword
$wgLDAPAddLDAPUsers = true;
$wgLDAPUpdateLDAP = true;

//A location to add users to if you are using $wgLDAPSearchAttributes
//This option requires $wgLDAPWriterDN and $wgLDAPWriterPassword to be set
$wgLDAPWriteLocation = array(
  "testADdomain"=>"ou=Users,dc=tdomain,dc=com",
  "testLDAPdomain"=>"ou=people,dc=LDAP,dc=example,dc=com"
  );

//User and password used for writing to the directory
//Please use a user with limited access, NOT your directory manager
//You are able to use clear text passwords, but please try not to
$wgLDAPWriterDN = "uid=priviledgedUser,ou=people,dc=LDAP,dc=example,dc=com";
$wgLDAPWriterPassword = "{SHA}KqYKj/f81HPTIeAUav2eJt85UUc=";

//User and password used for proxyagent access
//Please use a user with limited access, NOT your directory manager
//You are able to use clear text passwords, but please try not to
$wgLDAPProxyAgent =  "cn=proxyagent,ou=profile,dc=LDAP,dc=example,dc=com";
$wgLDAPProxyAgentPassword = "{SHA}KqYKj/f81HPTIeAUav2eJt85UUc=";

//Search filter. These
// options are only needed if you want to search for users to bind with them. In otherwords,
// if you cannot do direct binds based upon $wgLDAPSearchStrings, then you'll need these two 
// options.
//If you need a proxyagent to search, remember to set $wgLDAPProxyAgent, and $wgLDAPProxyAgentPassword.
//Anonymous searching is supported.
$wgLDAPSearchAttributes = array(
  "testADdomain"=>"sAMAccountName",
  "testLDAPdomain"=>"uid"
  );
$wgLDAPBaseDNs = array(
  "testADdomain"=>"dc=AD,dc=example,dc=com",
  "testLDAPdomain"=>"dc=LDAP,dc=example,dc=com"
  );

//Option for mailing temporary passwords to users
//(notice, this will store the temporary password in the local directory
// if you cannot write LDAP passwords because writing is turned off,
// this probably won't help you much since users will not be able to change
// their password)
//This requires $wgLDAPWriterDN, $wgLDAPWriterPassword and $wgLDAPUpdateLDAP!
$wgLDAPMailPassword = true;

//Option for allowing the retreival of user preferences from LDAP
//Only pulls a small amount of info currently
$wgLDAPRetrievePrefs = true;

//Option for getting debug output from the plugin. 1-3 available. 1 will show
//non-sensitive info, 2 will show possibly sensitive user info, 3 will show
//sensitive system info. Setting this on a live public site is probably a bad
//idea.
$wgLDAPDebug = 1;

//Change the hashing algorithm that is used when changing passwords or creating
//user accounts. The default (not setting this variable) will use a base64 encoded
//SHA encrypted password. I recommend not setting this variable unless you need to
//store clear text or crypt passwords.
$wgLDAPPasswordHash = "crypt";

//Group based login restriction; this deprecates the old method.
//This requires $wgLDAPBaseDNs to be set!

//The groups the user is required to be a member of.
//All groups should be lowercase
$wgLDAPRequiredGroups = array( "testLDAPdomain"=>array("cn=testgroup,ou=groups,dc=ldap,dc=example,dc=com") );

//Whether the username in the group is a full DN (AD generally does this), or
//just the username (posix groups generally do this)
$wgLDAPGroupUseFullDN = array( "testLDAPdomain"=>true );

//The objectclass of the groups we want to search for
$wgLDAPGroupObjectclass = array( "testLDAPdomain"=>"groupofuniquenames" );

//The attribute used for group members
$wgLDAPGroupAttribute = array( "testLDAPdomain"=>"uniquemember" );

//Whether or not the plugin should search in nested groups
$wgLDAPGroupSearchNestedGroups = array( "testLDAPdomain"=>false );
?>
