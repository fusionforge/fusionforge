<?php
/**
 * FusionForge FRS : docman include view
 *
 * Copyright 2014 Franck Villaume - TrivialDev
 * http://fusionforge.org
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $g;
global $content;

$content .= html_e('p', array(), _('Alternatively, you can pick a file available in the Documents Management tool.'), false);
$dm = new DocumentManager($g);
$dgf = new DocumentGroupFactory($g);
$content .= $dm->showSelectNestedGroups($dgf->getNested(), 'docman_fileid', true, 0, array(), true);
