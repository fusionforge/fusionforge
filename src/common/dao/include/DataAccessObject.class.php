<?php
/**
 *  Base class for data access objects
 */
class DataAccessObject {
	 var $da;

    //! A constructor
    /**
    * Constructs the Dao
    * @param DataAccess $da Instance of the DataAccess class
    */
    function DataAccessObject( & $da ) {
        $this->table_name = 'CLASSNAME_MUST_BE_DEFINE_FOR_EACH_CLASS';

        $this->da=$da;
    }

    //! An accessor
	/**
	 * For SELECT queries
	 *
	 * @param string $sql    The query string
	 * @param array  $params The arguments
	 * @return mixed Either false if error or object DataAccessResult
	 */
    function &retrieve($sql,$params) {
        $result = new DataAccessResult(db_query_params($sql,$params));

        return $result;
    }

    //! An accessor
    /**
     * For INSERT, UPDATE and DELETE queries
     * @param string $sql the query string
	 * @param array  $params The arguments
	 * @return boolean true if success
     */
    function update($sql,$params) {
        $result = db_query_params($sql,$params);
        return $result;
    }
}
