<?php //-*-php-*-
rcs_id('$Id: Forbidden.php 6184 2008-08-22 10:33:41Z vargenau $');
/* Copyright (C) 2004 ReiniUrban
 * This file is part of PhpWiki. Terms and Conditions see LICENSE. (GPL2)
 */

/** 
 * The PassUser name gets created automatically. 
 * That's why this class is empty, but must exist.
 */
class _ForbiddenPassUser
extends _ForbiddenUser
{
    function dummy() {
        return;
    }
}

// $Log: not supported by cvs2svn $
// Revision 1.1  2004/11/05 18:11:38  rurban
// required dummy file
//

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>