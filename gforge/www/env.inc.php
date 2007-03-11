<?php
/*
 * Created on 24 sept. 2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

error_reporting( E_ALL );
////header( "Content-type: text/html; charset=utf-8" );
//@ini_set( "display_errors", true );
//@ini_set( "display_errors", false );

# In case of errors, let output be clean.
$gfRequestTime = microtime( true );

# Attempt to set up the include path, to fix problems with relative includes
////$IP = dirname( __FILE__ ) ;
$IP = dirname(dirname( __FILE__ )) ;
//define( 'GF_INSTALL_PATH', $IP );
$sep = PATH_SEPARATOR;
if( !ini_set( "include_path", "/etc/gforge/custom$sep/etc/gforge$sep$IP$sep$IP/www/include$sep$IP/plugins/scmccase/etc$sep$IP/plugins/scmsvn/etc$sep$IP/plugins/scmcvs/etc$sep$IP/plugins" ) ) {
        set_include_path( "/etc/gforge/custom$sep/etc/gforge$sep$IP$sep$IP/www/include$sep$IP/plugins/scmccase/etc$sep$IP/plugins/scmsvn/etc$sep$IP/plugins/scmcvs/etc$sep$IP/plugins" );
}
ini_set( 'memory_limit', '20M' );
#echo '['.$IP.']';

?>
