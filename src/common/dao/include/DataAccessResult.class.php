<?php
/**
 *  Fetches MySQL database rows as objects
 */
class DataAccessResult  implements Iterator {
    /**
    * @access protected
    * $da stores data access object
    */
    var $da;
    /**
    * @access protected
    * $query stores a query resource
    */
    var $query;

    var $_current;
    var $_row;
    var $nb_rows;

    function DataAccessResult($result) {
        $this->result       = $result;

            $this->_current = -1;
            $this->_row     = false;
            $this->rewind();

    }

    /**
    * Returns an array from query row or false if no more rows
    * @return mixed
    */
    function &getRow() {
        $row = $this->current();
        $this->next();
        return $row;
    }

    /**
    * Returns the number of rows affected
    * @return int
    */
    function rowCount() {
        if (!isset($this->nb_rows)) {
            $this->nb_rows = db_numrows($this->result);
        }
        return $this->nb_rows;
    }

    /**
    * Returns false if no errors or returns a MySQL error message
    * @return mixed
    */
    function isError() {
        $error=db_error();
        if (!empty($error))
            return $error;
        else
            return false;
    }


    // {{{ Iterator
    function &current() {
        return $this->_row;
    }

    function next() {
        $this->_current++;
        if ($this->_current < $this->rowCount()) {
        $this->_row = db_fetch_array_by_row($this->result, $this->_current);
        } else {
            $this->_row = false;
        }
    }

    function valid() {
        return $this->_row !== false;
    }

    function rewind() {
        if ($this->rowCount() > 0) {
            $this->_current = -1;
            $this->next();
        }
    }

    function key() {
        return $this->_current;
    }
    // }}}
}
?>
