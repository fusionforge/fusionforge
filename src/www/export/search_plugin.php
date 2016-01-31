<?php
/**
 * Project List plugin for FusionForge
 * Copyright 2008, Nicolas Quienot - Linagora
 * Copyright 2015, Franck Villaume - TrivialDev
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';

$sysdebug_enable = false;

print '<?xml version="1.0" encoding="UTF-8"?>';
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName><?php echo forge_get_config ('forge_name'); ?></ShortName>
<Description><?php echo _("Search in project"); ?></Description>
<InputEncoding>UTF-8</InputEncoding>
<Url type="text/html" template="<?php print util_make_url('/search/?type_of_search=soft&amp;words={searchTerms}'); ?>" />
</OpenSearchDescription>
