<?php
/** External authentication via LDAP for FusionForge Config
*
* Define the location of your user acconuts and user dn prefix.
* Example: If user full rdn is: "uid=joe,ou=users,dc=example,dc=com"
* 	then: $base_dn = "ou=users,dc=example,dc=com"
*	and: $user_dn = "uid="
* For AD use: $user_dn = "sAMAccountName="
* Note LDAP Search Call: ldap_search($this->ldap_conn, $this->base_dn, $this->user_dn . $loginname)
*/
$base_dn = "ou=users,dc=example,dc=com" ;
$user_dn = "uid=" ;
// $user_dn = "sAMAccountName=" ;

// Define LDAP server hostname or IP, and port.
$ldap_server = "ldap.example.com" ;
//$ldap_server = "127.0.0.1" ;
$ldap_port=389;

// Define a backup LDAP server.
//$ldap_altserver = '';
//ldap_altport = '';

// Define privileged user for bind before user dn search, such as a httpd search-only account.
//$ldap_bind_dn = '';
//$ldap_bind_pwd = '';

// Use TLS security.
//$ldap_start_tls = false;

// Array of login not managed by LDAP (local accounts).
//$ldap_skip_users = array('ffadmin');

?>
