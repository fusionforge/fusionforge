<?php

require_once(dirname(__FILE__).'/../connection/TransactionManager.php');

/**
 * Facade pour les transactions.
 */
class TransactionFacade {

    /**
     * Commence une transaction.
     */
    function begin(){
        return TransactionManager::begin();         
    }
    
    /**
     * Commit une transaction.
     */
    function commit(){
        return TransactionManager::commit();
    }

    /**
     * Rollback une transaction.
     */
    function rollback(){
        return TransactionManager::rollback();
    }

}

?>