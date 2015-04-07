<?php

require_once(dirname(__FILE__).'/../class/clsConfig.php');


class clsSession {

  private $SessionVarPrefix = "";

  public function __get($name){
  	
  	$VarName = $this->SessionVarPrefix . $name;

  	if (isset($_SESSION[$VarName])){
  		return $_SESSION[$VarName];
  	}
  	return "";
  }
  	
  function __set($name,$value){

  	$VarName = $this->SessionVarPrefix . $name;
  	
  	$_SESSION[$VarName] = $value;
  	
  }
 
 
 public function __construct(){

 	global $System;
 	if (isset($System)){
 		if (is_object($System->Config)){
 			$objConfig = $System->Config;
 		}
 	}
 	if (!isset($objConfig)){
	 	$objConfig = new clsConfig();
 	}
 	 	
 	if (isset($objConfig->Vars['instance']['sessionvarprefix'])){
		$this->SessionVarPrefix = $objConfig->Vars['instance']['sessionvarprefix'];
 	}
 	
}

 public function Clear($name = null){
  		 	 	
	if (!is_null($name)){
		$VarName = $this->SessionVarPrefix . $name;  	
  		unset($_SESSION[$VarName]);
	}
	else
	{
		unset($_SESSION);		
	}
 
}


}