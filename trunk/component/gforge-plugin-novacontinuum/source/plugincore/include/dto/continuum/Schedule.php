<?php


class Schedule {
	var $id;
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.Schedule';
	var $description;
	var $name;
	var $active = true;
	var $delay = 0;
	var $cronExpression;
	var $maxJobExecutionTime;

	function _populate($array){
		if(isset($array)){
			$this->id = $array['id'];
			$this->description = $array['description'];
			$this->name = $array['name'];
			$this->active = $array['active'];
			$this->delay = $array['delay'];
			$this->cronExpression = $array['cronExpression'];
			$this->maxJobExecutionTime = $array['maxJobExecutionTime'];
		}
	}
	
	function _getRpcValue(){
		$ret = array(
			'id' 									=> new xmlrpcval($this->id, "int"),
			'__class' 						=> new xmlrpcval($this->__class, "string"),
			'description' 				=> new xmlrpcval($this->description, "string"),
			'name' 								=> new xmlrpcval($this->name, "string"),
			'active' 							=> new xmlrpcval($this->active, "boolean"),
			'delay' 							=> new xmlrpcval($this->delay, "int"),
			'cronExpression' 			=> new xmlrpcval($this->cronExpression, "string"),
			'maxJobExecutionTime' => new xmlrpcval($this->maxJobExecutionTime, "int")
		);
		
		return new xmlrpcval($ret,"struct");
	}
	
	function getExplodedCronExp(){
		return explode(' ',$this->cronExpression);
	
	}
	function getSeconde(){
		$exploded = $this->getExplodedCronExp();
		return $exploded[0];
	}
	
	function getMinute(){
		$exploded = $this->getExplodedCronExp();
		return $exploded[1];
	}
	
	function getHour(){
		$exploded = $this->getExplodedCronExp();
		return $exploded[2];
	}
	
	function getDayOfMonth(){
		$exploded = $this->getExplodedCronExp();
		return $exploded[3];
	}
	
	function getMonth(){
		$exploded = $this->getExplodedCronExp();
		return $exploded[4];
	}
	
	function getDayOfWeek(){
		$exploded = $this->getExplodedCronExp(); 
		return $exploded[5];
	}
	
	function getYear(){
		$exploded = $this->getExplodedCronExp(); 
		if(count($exploded)>6){
			return $exploded[6];
		}else{
			return '';
		}
	}
	
	function setCronExp($array){
		if(count($array) == 7){
			$this->cronExpression =	$array[0].' '.$array[1].' '.$array[2].' '.$array[3].' '.$array[4].' '.$array[5].' '.$array[6].' '.$array[7];
		}else if(count($array) == 6){
			$this->cronExpression =	$array[0].' '.$array[1].' '.$array[2].' '.$array[3].' '.$array[4].' '.$array[5].' '.$array[6];
		}
	}
}

?>