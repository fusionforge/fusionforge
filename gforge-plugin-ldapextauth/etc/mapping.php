<?php

function plugin_ldapextauth_mapping ($entry) {
	$result = array () ;
	
	$realname = $entry['gecos'][0] ;
	$rnarray = explode (' ', $realname, 2) ;

	$result['firstname'] = $rnarray[0] ;
	$result['lastname'] = $rnarray[1] ;
	$result['email'] = $entry['uid'][0] . '@' . $GLOBALS['sys_default_domain'] ;
	// You may also want to customise $result['language_id']
	// You may also want to customise $result['timezone']
	// You may also want to customise $result['jabber_address']
	// You may also want to customise $result['address']
	// You may also want to customise $result['address2']
	// You may also want to customise $result['phone']
	// You may also want to customise $result['fax']
	// You may also want to customise $result['title']
	// You may also want to customise $result['ccode']
	
	return $result ;
}

function plugin_ldapextauth_getdn ($plugin, $username) {
	return "uid=$username," . $plugin->base_dn ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
