<?php

require_once(dirname(__FILE__).'/../dto/HttpProxy.php');

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

class HttpProxyDAO {
	
	function HttpProxyDAO() {
     
  }

  function &getInstance() {
      static $instance = null;
      if (null === $instance) {
          $instance = new HttpProxyDAO();
      }
      return $instance;
  }


	function addHttpProxy($instance){
		$ok = false;
		
		$query = "INSERT INTO plugin_novacontinuum_http_proxy (name, host, port, userName, pwd) VALUES ('". $instance->name ."','" .$instance->host ."','" .$instance->port ."','" .$instance->userName ."','" .$instance->password . "')";
		
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
		return $ok;
	}
	
	function updateHttpProxy($instance){
		$ok = false;
		
		$query = "UPDATE plugin_novacontinuum_http_proxy SET name='". $instance->name ."' , host='". $instance->host ."' , port='". $instance->port ."' , userName='". $instance->userName ."' , pwd='". $instance->password ."' WHERE id='".$instance->id."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = true;
		}
		return $ok;
	}
	
	function deleteHttpProxy($instanceid){
		$ok = false;
		
		$query = "DELETE FROM plugin_novacontinuum_http_proxy WHERE id='".$instanceid."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = true;
		}
		return $ok;
	}
	
	function getAllHttpProxies(){
	
		$array_instances = array ();
		$query = "SELECT id,name, host, port, userName, pwd FROM plugin_novacontinuum_http_proxy ORDER BY name";
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
				$instance = $this->__mapinstance($result, $i);
				$array_instances [] =$instance;
			}
		}
		return $array_instances;
	}
	
	function getHttpProxy($proxyId){
		$instance = null;
		$query = "SELECT id,name, host, port, userName, pwd FROM plugin_novacontinuum_http_proxy WHERE id=" . $proxyId;
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if ($numrows == 1)
			{
				$instance = $this->__mapinstance($result, 0);
			}
			else
			{
				log_error ("Function db_query() returned " . $numrows . " results with query '" . $query . "'", __FILE__, __FUNCTION__);
			}
		}
		return $instance;
	}
	
	function __mapinstance($result,$index){
		$id = db_result ($result, $index, "id");
		$name = db_result ($result, $index, "name");
		$host = db_result ($result, $index, "host");
		$port = db_result ($result, $index, "port");
		$userName = db_result ($result, $index, "userName");
		$password = db_result ($result, $index, "pwd");
				
		$instance =  new HttpProxy($name,$host,$port,$userName,$password);
		$instance->id=$id;
		
		return $instance;	
	}
}
?>