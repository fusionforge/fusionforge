<?php
/**
 *  Base class for data access objects
 */
class DataAccessObject {
	 var $da;

    //! A constructor
    /**
    * Constructs the Dao
    * @param $da instance of the DataAccess class
    */
    function DataAccessObject( & $da ) {
        $this->table_name = 'CLASSNAME_MUST_BE_DEFINE_FOR_EACH_CLASS';

        $this->da=$da;
    }


    //! An accessor
    /**
    * For SELECT queries
    * @param $sql the query string
    * @return mixed either false if error or object DataAccessResult
    */
    function &retrieve($sql,$params) {
        $result = new DataAccessResult(db_query_params($sql,$params));

        return $result;
    }

    //! An accessor
    /**
    * For INSERT, UPDATE and DELETE queries
    * @param $sql the query string
    * @return boolean true if success
    */
    function update($sql,$params) {
        $result = db_query_params($sql,$params);
        return $result;
    }



}
?>
