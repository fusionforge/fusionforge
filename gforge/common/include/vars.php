<?php
/**
 * Static variable array definitions.
 * Note that array keys *cannot* be redefined as values are inserted into
 * database tables.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

// Note that $LICENSE values are identical in content and order to those 
// listed at: http://opensource.org/licenses/.  Note that the trove database
// should match this list.
//
$LICENSE = array();

// OSI licenses
//
$LICENSE['gpl']       = 'GNU General Public License (GPL)';
$LICENSE['lgpl']      = 'GNU Library Public License (LGPL)';
$LICENSE['bsd']       = 'BSD License';
$LICENSE['mit']       = 'MIT License';
$LICENSE['artistic']  = 'Artistic License';
$LICENSE['mpl']       = 'Mozilla Public License 1.0 (MPL)';
$LICENSE['qpl']       = 'Qt Public License (QPL)';
$LICENSE['ibm']       = 'IBM Public License';
$LICENSE['cvw']       = 'MITRE Collaborative Virtual Workspace License (CVW License)';
$LICENSE['rscpl']     = 'Ricoh Source Code Public License';
$LICENSE['python']    = 'Python License';
$LICENSE['zlib']      = 'zlib/libpng License';
$LICENSE['apache']    = 'Apache Software License';
$LICENSE['vovida']    = 'Vovida Software License 1.0';
$LICENSE['sissl']     = 'Sun Internet Standards Source License (SISSL)';
$LICENSE['iosl']      = 'Intel Open Source License';
$LICENSE['mpl11']     = 'Mozilla Public License 1.1 (MPL 1.1)';
$LICENSE['jabber']    = 'Jabber Open Source License';
$LICENSE['nokia']     = 'Nokia Open Source License';
$LICENSE['sleepycat'] = 'Sleepycat License';
$LICENSE['nethack']   = 'Nethack General Public License';
$LICENSE['ibmcpl']    = 'IBM Common Public License';
$LICENSE['apsl']      = 'Apple Public Source License';

// non-OSI
//
$LICENSE['public']   = 'Public Domain';
$LICENSE['website']  = 'Website Only';
$LICENSE['other']    = 'Other/Proprietary License';

// shell binary options
//
$SHELLS = array();
$SHELLS[1] = '/bin/bash';
$SHELLS[2] = '/bin/sh';
$SHELLS[3] = '/bin/ksh';
$SHELLS[4] = '/bin/tcsh';
$SHELLS[5] = '/bin/csh';

?>
