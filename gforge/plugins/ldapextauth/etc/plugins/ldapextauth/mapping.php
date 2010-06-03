<?php
/** External authentication via LDAP for FusionForge Mapping
*
* These are pairs of internal user variables and LDAP attributes used when
* creating new accounts, top half are required, bottom half are optional.
* Note you can use global config variables with forge_get_config()
*/

function plugin_ldapextauth_mapping ($entry) {
	$result = array () ;
	
	$result['firstname'] = $entry['givenname'][0] ;
	$result['lastname'] = $entry['sn'][0] ;

	// Defines new user email address, from LDAP or based on forge domain.
	$result['email'] = $entry['mail'][0] ;
	//$result['email'] = $entry['uid'][0] . '@' . forge_get_config('web_host') ;

	// Defines new user theme, causes error if left blank.
	$result['themeid']=$GLOBALS['sys_default_theme_id'];


	//$result['jabber_address'] = '' ;
	//$result['address'] = '' ;
	//$result['address2'] = '' ;
	//$result['phone'] = $entry['telephonenumber'][0]; //AD
	//$result['fax'] = '' ;
	//$result['title'] = '' ;
	//$result['ccode']=forge_get_config('default_country_code');
	//$result['language_id'] = '' ;
	//$result['timezone']=forge_get_config('default_timezone');
	
	return $result ;
}

?>
