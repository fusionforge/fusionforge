<?php
/**
 * Handles persistence of data for plugins
 * @author Olivier Berger
 *
 */

/**
 * Handles persistence of data for a plugin as a whole
 * @author Olivier Berger
 *
 */

// May create an interface with 2 implementations : JSON or php serialize...

class PluginPersistentStore extends Error {

	var $plugin_id;
	
	/* 
	 
	CREATE TABLE plugins_persistence (
	plugin_id integer,
	persisted_key character varying(100),
	persisted_data bytea,
	-- CONSTRAINT plugins_persistence_pkey PRIMARY KEY (plugin_id, persisted_key) not necessary to have unique keys
	-- May need an index on (plugin_id, persisted_key)
	)
*/
	
	const persistence_table = 'plugins_persistence';
	
	/**
	 * Constructor
	 * @param integer $plugin_id
	 */
	public function __construct($plugin_id) {
		$this->plugin_id = $plugin_id;
	}
	
	protected function additionalAndClause() {
		return '';
	}
	/**
	 * Loads persisted objects (saved in serialized form) 
	 * @param key $key
	 * @return array
	 */
	public function readObject($key) {
		$result = array();
		$res = db_query_params('SELECT persisted_data
					FROM '.self::persistence_table.' 
					WHERE plugin_id = $1
					AND persisted_key = $2 '.$self->additionalAndClause(),
					array($this->plugin_id,
							$key));
		$rows = db_numrows($res);
		
		/*
		if ($rows > 1) {
			$this->setError(_('More than one value for the plugin + key'))
		}*/
		for ($i=0; $i<$rows; $i++) {
			$data = db_result($res,$i,'persisted_data');
			$object = unserialize($data);
			$result[] = $object;
		}
		return $result;
	}
	
	public function saveObject($object, $key) {
		$data = serialize($object);
		$res = db_query_params('INSERT INTO '. self::persistence_table .' (plugin_id, persisted_key, persisted_data) VALUES ($1, $2, $3)',
				array($this->plugin_id, $key, $data));
	}
	
}

/**
 * Handles persistence of data for a plugin, by group
 * @author olivier
 *
 */
class PluginGroupPersistentStore {
	var $group_plugin_id;

	/*	
	CREATE TABLE group_plugin_persistence (
	group_plugin_id integer,
	persisted_key character varying(100),
	persisted_data bytea,
	CONSTRAINT plugins_persistence_pkey PRIMARY KEY (group_plugin_id, persisted_key)
	
		*/
	public function __construct($plugin_id, $group_id) {
		parent::__construct($plugin_id);
		
		$res = db_query_params('SELECT group_plugin_id
						FROM group_plugin
						WHERE plugin_id=$1
						AND group_id=$2', array($plugin_id, $group_id));
		$rows = db_numrows($res);
		
		if ($rows > 1) {
			$this->setError(_('More than one value for the plugin + key'));
		}
		$this->group_plugin_id = db_result($res,0,'persisted_data');
	}
	
	protected function additionalAndClause() {
		$clause = parent::additionalAndClause();
		$clause .= 'AND group_plugin_id='.$this->group_plugin_id;
		return $clause;
	}
	
}

/**
 * Handles persistence of data for a plugin, by user
 * @author olivier
 *
 */
class PluginUserPersistentStore {
	var $user_id;
	
		/*	
	
	CREATE TABLE user_plugin_persistence (
	user_plugin_id integer,
	persisted_key character varying(100),
	persisted_data bytea,
	CONSTRAINT plugins_persistence_pkey PRIMARY KEY (user_plugin_id, persisted_key)
	
		*/
	
}