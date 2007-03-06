<?php
/**
 * GForge language management
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('common/include/escapingUtils.php');

$lang = getStringFromRequest('lang');

$unit        = 'item';
$table       = 'tmp_lang';
$primary_key = 'seq';
$whereclause = " lang1 ,tmp_lang lang2 WHERE lang1.language_id='Base' AND lang2.language_id='" . $lang . "' AND lang1.pagename=lang2.pagename AND lang1.category=lang2.category AND lang1.pagename!='#' AND lang2.pagename!='#' AND lang2.tmpid!='-1' ORDER BY lang1.seq";
$columns     = "lang1.seq, lang1.pagename, lang1.category, lang1.tstring, lang2.tstring";

include_once('admintabfiles.php');

?>
