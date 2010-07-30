<?php

/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume
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

/* when moving a document group to trash, it's recursive and it's applied to documents that belong to these document groups */

require_once ('docman/DocumentFactory.class.php');
require_once ('docman/DocumentGroupFactory.class.php');
require_once ('docman/include/utils.php');

$dirid = getIntFromRequest('dirid');

$df = new DocumentFactory($g);
if ($df->isError())
	exit_error(_('Error'),$df->getErrorMessage());

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError())
	exit_error(_('Error'),$dgf->getErrorMessage());

$d_arr =& $df->getDocuments();
if (!$d_arr || count($d_arr) <1)
	$d_arr = &$df->getDocuments();

// Get the document groups info
$trashnested_groups =& $dgf->getNested();
$trashnested_docs=array();
//put the doc objects into an array keyed off the docgroup
foreach ($d_arr as $doc) {
	$trashnested_docs[$doc->getDocGroupID()][] = $doc;
}

docman_recursive_stateid($dirid,$trashnested_groups,$trashnested_docs);

$dg = new DocumentGroup($g,$dirid);
$dg->setStateID('2');

$feedback = _('Document Group moved to trash successfully');
Header('Location: '.util_make_url('/docman/?group_id='.$group_id.'&feedback='.urlencode($feedback)));
exit;

?>
