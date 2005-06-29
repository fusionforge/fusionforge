<?php

//
//	Here is where you define different sets of default elements
//

$machines=array('All','DEC','HP','Macintosh','PC','SGI','Sun','Other');

$products=array('Software A');

$oss=array('All',
'Windows 3.1',
'Windows 95',
'Windows 98',
'Windows ME',
'Windows 2000',
'Windows NT',
'Windows XP',
'Windows Server 2003',
'Mac System 7',
'Mac System 7.5',
'Mac System 7.6.1',
'Mac System 8.0',
'Mac System 8.5',
'Mac System 8.6',
'Mac System 9.x',
'MacOS X',
'Linux',
'BSDI',
'FreeBSD',
'NetBSD',
'OpenBSD',
'AIX',
'BeOS',
'HP-UX',
'IRIX',
'Neutrino',
'OpenVMS',
'OS/2',
'OSF/1',
'Solaris',
'SunOS',
'other');

$components=array('Cog A','Cog B');

$versions=array('v1.0','v1.1');

$severities=array('blocker',
'critical',
'major',
'normal',
'minor',
'trivial',
'enhancement');

$patch_ress=array('Accepted','Rejected','Out of Date','Awaiting Response');
$bug_ress=array('Accepted As Bug','Fixed','Won\'t Fix','Invalid','Awaiting Response','Works For Me');

//
//	Here is where you combine the arrays of elements into
//	field definitions, including titles, types, and attributes
//

//NAME, TYPE, ATTR1, ATTR2, REQUIRED, SOURCE_ARRAY

$hardware=array('Hardware',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0,0, $machines);
$product=array('Product',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0,0,$products);
$os=array('Operating System',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0,0,$oss);
$component=array('Component',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0,0,$components);
$version=array('Version',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0,0,$versions);
$severity=array('Severity',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0,0,$severities);
$url=array('URL',ARTIFACT_EXTRAFIELDTYPE_TEXT,40,100,0,array());

$patchres=array('Resolution',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0,0,$patch_ress);
$bugres=array('Resolution',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0,0,$bug_ress);

$severity=array('Severity',ARTIFACT_EXTRAFIELDTYPE_SELECT,0,0,0,$severities);

//
//	Here is where you define which trackers to create
//	Note that you can define as many as you want
//

/*
NAME DESCRIPTION, $is_public,$allow_anon,$email_all,$email_address,
$due_period,$use_resolution,$submit_instructions,$browse_instructions,
$datatype=0,$fields
*/

$trackers[]=array('Bugs','Bug Tracking System',1,0,'','',30,0,'','',1, array($hardware, $product, $os, $component, $version, $severity, $bugres, $url));

$trackers[]=array('Support','Tech Support Tracking System',1,0,'','',30,0,'','',2, array($hardware, $product, $os, $component, $version, $severity, $url));

$trackers[]=array('Patches','Patch Tracking System',1,0,'','',30,0,'','',3, array($component, $version, $patchres));

$trackers[]=array('Feature Requests','Feature Request Tracking System',1,0,'','',30,0,'','',4, array($product, $os, $component));

?>
