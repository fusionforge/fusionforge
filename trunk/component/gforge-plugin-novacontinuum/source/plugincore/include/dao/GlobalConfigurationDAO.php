<?php

require_once(dirname(__FILE__).'/../dto/GlobalConfiguration.php');

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

class GlobalConfigurationDAO {
	
	function GlobalConfigurationDAO() {
     
  }

  function &getInstance() {
      static $instance = null;
      if (null === $instance) {
          $instance = new GlobalConfigurationDAO();
      }
      return $instance;
  }

	function getConfiguration() {
		$configuration=new GlobalConfiguration();
		
		$query = "SELECT keyName, configValue FROM plugin_novacontinuum_configuration";
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			for ($i = 0; $i < $numrows; $i++)
			{
				$key = db_result ($result, $i, "keyName");
				$value = db_result ($result, $i, "configValue");
				$configuration->values[$key]=$value;
			}
		}
		
		return $configuration;
	}
	
	function updateConfiguration($configuration){
		foreach ($configuration->values as $key=>$value) {
  		$this->__updateValue($key,$value);
  	}
	}
	
	
	function __updateValue($key,$value){
		$ok = false;
		
		$query = "UPDATE plugin_novacontinuum_configuration SET configValue='" . $value ."' WHERE keyName='". $key ."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			if(db_affected_rows($result)==0){
		
				$query = "INSERT INTO plugin_novacontinuum_configuration (keyName, configValue) VALUES ('". $key ."','" .$value ."')";
		
				$result = db_query ($query);
				if ($result === false)
				{
					log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
				}
				else
				{
					$id = db_insertid ($result, "plugin_novacontinuum_http_proxy", "id");
					if ($id == 0)
					{
						log_error ("Function db_insertid() failed after query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
					}
					else
					{
						$ok = true;
					}
				}
			}else{
				$ok = true;
			}
			
		}
		return $ok;
	}
}
?>