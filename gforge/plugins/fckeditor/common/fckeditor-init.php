<?php
/**
 * GForge Plugin FCKeditor plugin Factory
 *
 * Portions Copyright 2004 (c) Roland Mas <99.roland.mas @nospam@ aist.enst.fr>
 * Portions Copyright 2004 (c) Francisco Gimeno <kikov @nospam@ kikov.org>
 * The rest Copyright 2005 (c) Daniel Perez <danielperez.arg@gmail.com>
 *
 * @version $Id$
 *
 * This file is part of GForge-plugin-fckeditor
 *
 * GForge-plugin-fckeditor is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge-plugin-fckeditor is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge-plugin-fckeditor; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
require_once ('fckeditorPlugin.class') ;

$fckeditorPluginObject = new fckeditorPlugin ;

register_plugin ($fckeditorPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
