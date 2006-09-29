<?php
/**
 * GForge Plugin SVNTracker Factory
 *
 * Portions Copyright 2004 (c) Roland Mas <99.roland.mas @nospam@ aist.enst.fr>
 * The rest Copyright 2004 (c) Francisco Gimeno <kikov @nospam@ kikov.org>
 *
 * @version $Id: svntracker-init.php,v 1.1 2005/10/11 18:46:40 danper Exp $
 *
 * This file is part of GForge-plugin-svntracker
 *
 * GForge-plugin-svntracker is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge-plugin-svntracker is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge-plugin-svntracker; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
require_once ('svntrackerPlugin.class') ;

$svntrackerPluginObject = new svntrackerPlugin ;

register_plugin ($svntrackerPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
