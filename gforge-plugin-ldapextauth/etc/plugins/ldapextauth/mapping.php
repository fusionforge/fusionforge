<?php

function plugin_ldapextauth_mapping ($entry) {
	global $Language;
	$result = array () ;
	
	$realname = $entry['gecos'][0] ;
	$rnarray = explode (' ', $realname, 2) ;

	$result['firstname'] = $rnarray[0] ;
	$result['lastname'] = $rnarray[1] ;
	$result['email'] = $entry['uid'][0] . '@' . $GLOBALS['sys_default_domain'] ;
	//$result['email'] = $entry['mail'][0] ; // AD
	// You may also want to customise $result['language_id']
	//$result['language_id']=$Language->getLanguageId();
	// You may also want to customise $result['timezone']
	//$result['timezone']=$GLOBALS['sys_default_timezone'];
	// You may also want to customise $result['jabber_address']
	// You may also want to customise $result['address']
	// You may also want to customise $result['address2']
	// You may also want to customise $result['phone']
	//$result['phone'] = $entry['telephonenumber'][0]; //AD
	// You may also want to customise $result['fax']
	// You may also want to customise $result['title']
	// You may also want to customise $result['ccode']
	//$result['ccode']=$GLOBALS['sys_default_country_code'];
	// You may also want to customise $result['themeid']
	$result['themeid']=$GLOBALS['sys_default_theme_id'];
	
	return $result ;
}

function plugin_ldapextauth_getdn ($plugin, $username) {
	return "uid=$username," . $plugin->base_dn ;
	//return 'DOMAIN\\' . "$username" ; // AD
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
