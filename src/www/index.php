<?php
/**
 * FusionForge Front Page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * Copyright 2008-2010 (c) FusionForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'news/news_utils.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfwww.'include/features_boxes.php';

$HTML->header(array('title'=>_('Welcome'), 'h1' => ''));

// Main page content is now themeable;
// Default is index_std.php;
include ( $HTML->getRootIndex() );

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
