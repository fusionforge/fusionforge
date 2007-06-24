<?php
/**
 * GForge Plugin CVSTracker Factory
 *
 * Portions Copyright 2004 (c) Roland Mas <99.roland.mas @nospam@ aist.enst.fr>
 * The rest Copyright 2004 (c) Francisco Gimeno <kikov @nospam@ kikov.org>
 *
 * @version $Id$
 *
 * This file is part of GForge-plugin-cvstracker
 *
 * GForge-plugin-cvstracker is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge-plugin-cvstracker is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge-plugin-cvstracker; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
require_once ('cvstrackerPlugin.class.php') ;

$cvstrackerPluginObject = new cvstrackerPlugin ;

register_plugin ($cvstrackerPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
