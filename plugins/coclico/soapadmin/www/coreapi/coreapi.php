<?php
/*-
 * SOAP Core Api for FusionForge
 *
 * Copyright © 2010
 *      Laurent Huet <laurent.huet@gmail.com>
 * All rights reserved.
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
 *-
 * FusionForge Core Api (full description in FusionforgeCoreApi.wsdl)
 * Rewrite of the older Api based on NuSOAP
 *
 * Main technical features :
 * - Based on the PHP SOAP extension (PHP > 5.x)
 * - SOAP Document/Litteral wrapped mode
 * - WS-I Basic Profile Compliant
 *
 */

require_once './coreapiservice.php';

# uncomment this for development purpose only
# ini_set('soap.wsdl_cache_enabled', '0');

use_soap_error_handler(false);

$server = new CoreApiServer();
$server->setClass("CoreApiService");
$server->handle();

?>
