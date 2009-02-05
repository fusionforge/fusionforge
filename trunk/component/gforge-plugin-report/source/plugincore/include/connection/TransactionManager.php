<?php

/**
 * Cette classe g�re les transactions.
 */
class TransactionManager {

    /**
     * Commence une transaction.
     */
    function begin(){
    	TransactionManager::setEncoding();
    	$ret = false;
		$query = "BEGIN";
		$result = db_query ($query);
		if ($result !== false)
		{
				$ret = true;
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $ret;
    }
    
    function setEncoding(){
    	$ret = false;
		// permet d'avoir des caract�res accentu�s dans les requ�tes SQL.
        $query = "SET client_encoding = 'LATIN9'";
		$result = db_query ($query);
		if ($result !== false)
		{
				$ret = true;
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $ret;
    }
    /**
     * Commit une transaction.
     */
    function commit(){
        $ret = false;
		$query = "COMMIT";
		$result = db_query ($query);
		if ($result !== false)
		{
				$ret = true;
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $ret;
    }

    /**
     * Rollback une transaction.
     */
    function rollback(){
    	$ret = false;
		$query = "ROLLBACK";
		$result = db_query ($query);
		if ($result !== false)
		{
				$ret = true;
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $ret;   
    }
}

?>