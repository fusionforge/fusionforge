<?php
/*
 * Project List plugin for Gforge
 * by Nicolas Quienot
 * Copyright (c) 2008 Linagora
 * License : GNU General Public License
 *
 *
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';

print '<?xml version="1.0" encoding="UTF-8"?>';
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName><?php echo forge_get_config ('forge_name'); ?></ShortName>
<Description><?php echo _("Search in project"); ?></Description>
<InputEncoding>UTF-8</InputEncoding>
<Image width="16" height="16" type="image/x-icon"><?php echo "http://".forge_get_config('web_host')."/images/opensearchdescription.png"; ?></Image>
<Url type="text/html" method="GET" template="<?php print 'http' . (session_issecure()?'s':'') . '://' . forge_get_config('web_host'); ?>/search/?type_of_search=soft&amp;words={searchTerms}"/>
</OpenSearchDescription>
