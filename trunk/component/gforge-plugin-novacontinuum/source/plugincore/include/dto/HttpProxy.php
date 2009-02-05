<?php

class HttpProxy {

	var $name;
	var $host;
	var $port;
	var $userName;
	var $password;
	var $id = -1;
	
	function HttpProxy($name, $host, $port, $userName, $password)
	{
			$this->name=$name;
			$this->host=$host;
			$this->port=$port;
			$this->userName=$userName;
			$this->password=$password;
	}
}
?>