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
        return db_numrows($this->result);
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
        $this->_row = db_fetch_array($this->result);
    }
    
    function valid() {
        return $this->_row !== false;
    }
    
    function rewind() {
        if ($this->rowCount() > 0) {
            db_reset_result($this->result, 0);
            $this->next();
            $this->_current = 0;
        }
    }
    
    function key() {
        return $this->_current;
    }
    // }}}
}
?>
