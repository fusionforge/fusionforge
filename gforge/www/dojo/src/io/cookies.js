/*
	Copyright (c) 2004-2005, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/

dj_deprecated("dojo.io.cookies has been replaced by dojo.io.cookie");
dojo.require("dojo.io.cookie");
if(!dojo.io.cookies) { dojo.io.cookies = dojo.io.cookie; }
dojo.provide("dojo.io.cookies");
