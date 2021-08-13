<?php
/**
 * Copyright © 2001 Jeff Dairiki
 * Copyright © 2004 Reini Urban
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

class DbaPartition
{
    function __construct(&$dbm, $prefix)
    {
        $this->_h = &$dbm;
        $this->_p = $prefix;
    }

    function open($mode = 'w')
    {
        $this->_h->open();
    }

    function close()
    {
        $this->_h->close();
    }

    function firstkey()
    {
        $dbh = &$this->_h;
        $prefix = &$this->_p;
        $n = strlen($prefix);
        for ($key = $dbh->firstkey(); $key !== false; $key = $dbh->nextkey()) {
            if (substr($key, 0, $n) == $prefix)
                return (string)substr($key, $n);
        }
        return false;
    }

    function nextkey()
    {
        $dbh = &$this->_h;
        $prefix = &$this->_p;
        $n = strlen($prefix);
        for ($key = $dbh->nextkey(); $key !== false; $key = $dbh->nextkey()) {
            if (substr($key, 0, $n) == $prefix)
                return (string)substr($key, $n);
        }
        return false;
    }

    function exists($key)
    {
        return $this->_h->exists($this->_p . $key);
    }

    function fetch($key)
    {
        return $this->_h->fetch($this->_p . $key);
    }

    function insert($key, $val)
    {
        return $this->_h->insert($this->_p . $key, $val);
    }

    function replace($key, $val)
    {
        return $this->_h->replace($this->_p . $key, $val);
    }

    function delete($key)
    {
        return $this->_h->delete($this->_p . $key);
    }

    function get($key)
    {
        return $this->_h->get($this->_p . $key);
    }

    function set($key, $val)
    {
        return $this->_h->set($this->_p . $key, $val);
    }

    function sync()
    {
        return $this->_h->sync();
    }

    function optimize()
    {
        return $this->_h->optimize();
    }
}
