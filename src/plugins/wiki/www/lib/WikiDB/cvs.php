<?php

// rcs_id( '$Id: cvs.php 7417 2010-05-19 12:57:42Z vargenau $' );

require_once( 'lib/WikiDB.php' );
require_once( 'lib/WikiDB/backend/cvs.php' );

/**
 * Wrapper class for the cvs backend.
 *
 * @Author: Gerrit Riessen, gerrit.riessen@open-source-consultants.de
 *
 * Use the new cvsclient PECL extension, if available
 * http://pecl.php.net/package/cvsclient
 *
 */
class WikiDB_cvs
extends WikiDB
{  
    var $_backend;

    /**
     * Constructor requires the DB parameters. 
     */
    function WikiDB_cvs( $dbparams ) {
        if (loadPhpExtension('cvsclient'))
            $this->_backend = new WikiDB_backend_cvsclient( $dbparams );
        else
            $this->_backend = new WikiDB_backend_cvs( $dbparams );
    }
}
?>