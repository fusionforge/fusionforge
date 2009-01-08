<?php
/**
 * External search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id: externalsearch-init.php 6516 2008-05-28 20:32:48Z cbayle $
 */

global $gfplugins;
require_once $gfplugins.'externalsearch/include/ExternalSearchPlugin.class.php' ;

define('SEARCH__TYPE_IS_EXTERNAL', 'external');

$externalSearchPluginObject = new ExternalSearchPlugin();

register_plugin($externalSearchPluginObject) ;

// End:

?>
