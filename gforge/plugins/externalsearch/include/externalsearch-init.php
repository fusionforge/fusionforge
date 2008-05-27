<?php
/**
 * External search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once $gfplugins.'externalsearch/include/ExternalSearchPlugin.class.php' ;

define('SEARCH__TYPE_IS_EXTERNAL', 'external');

$externalSearchPluginObject = new ExternalSearchPlugin();

register_plugin($externalSearchPluginObject) ;

// End:

?>
