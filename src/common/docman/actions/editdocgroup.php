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

$dirid = getIntFromRequest('dirid');

$groupname = getStringFromRequest('groupname');
$parent_dirid = getIntFromRequest('parent_dirid');

$dg = new DocumentGroup($g,$dirid);
if ($dg->isError())
	exit_error('Error',$dg->getErrorMessage());

if (!$dg->update($groupname,$parent_dirid))
	exit_error('Error',$dg->getErrorMessage());

$feedback = _('Document Group Updated successfully');
Header('Location: '.util_make_url('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&feedback='.urlencode($feedback)));
exit;

?>

