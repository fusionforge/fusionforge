<?php
/**
 * Copyright © 2002 Lawrence Akka
 * Copyright © 2002 Jeff Dairiki
 * Copyright © 2005,2007 Reini Urban
 *
 * This file is part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

/*
 * The guts of this code have been moved to lib/XmlRpcServer.php.
 *
 * This file is really a vestige, as now, you can direct XML-RPC
 * request to the main wiki URL (e.g. index.php) --- it will
 * notice that you've POSTed content-type of text/xml and
 * fire up the XML-RPC server automatically.
 */

// Intercept GET requests from confused users.  Only POST is allowed here!
if (empty($GLOBALS['HTTP_SERVER_VARS']))
    $GLOBALS['HTTP_SERVER_VARS'] =& $_SERVER;
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    die('This is the address of the XML-RPC interface.' .
        '  You must use XML-RPC calls to access information here.');
}

// Constant defined to indicate to phpwiki that it is being accessed via XML-RPC
define ("WIKI_XMLRPC", true);

// Start up the main code
include_once 'index.php';
include_once 'lib/main.php';

include_once 'lib/XmlRpcServer.php';

$server = new XmlRpcServer();
$server->service();
