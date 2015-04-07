<?php

	require_once(dirname(__FILE__).'/../class/clsSession.php');
	require_once(dirname(__FILE__).'/../class/clsSystem.php');
	

class clsUser {

  private $Id;
  private $Name = null;
  private $Email = null;
  private $Emails = array();
  private $PictureOf = null;
  private $Password = null;
  private $Hash = null;
  private $HashDate = null;
    
  public $Invites = array();

  public function __get($name){
  	return $this->$name;
  }
  	
  function __set($name,$value){
  	$this->$name = $value;
  }
 
 
 public function __construct ($Id=null){

 	global $System;
 	
 	if (!isset($System)){
 		$System = new clsSystem();
 	}
 	
 	$db = $System->db;
 	if (isset($System)){
 		$objSession = $System->Session;
 	}
 	else
 	{
	 	$objSession = new clsSession();
 	}
 	
	if (is_null($Id)){
	 	$Id = $objSession->UserId;
	 	if ($Id == ""){
	    	throw new Exception('Not logged on');	 			
	 	}	 	
 	 }
 	
 	  $sql = "select * from tbl_user where usrRecnum=$Id";
	  $rst = $db->query($sql);
	  
	  if (!$rst) {
	    throw new Exception('Could not execute query');
	  }
	
	  if ($rst->num_rows==1) {
	  	
  	
		  $rstRow = $rst->fetch_assoc();
		  $this->Id = $rstRow['usrRecnum'];
		  if (!is_null($rstRow['usrName'])){
			  $this->Name = $rstRow['usrName'];
		  }
		  $this->PictureOf = $rstRow['usrImage'];
		  $this->Password = $rstRow['usrPassword'];
		  $this->Hash = $rstRow['usrHash'];
		  
		  if (!is_null($rstRow['usrHashDate'])){
			  $this->HashDate = new datetime($rstRow['usrHashDate']);
		  }
	
		  
		$rstEmail = $db->query("SELECT * FROM tbl_user_email WHERE emlUser=$Id");
		if (!($rstEmail===false)) {
			while($rowEmail = $rstEmail->fetch_assoc()){
				$this->Emails[] = $rowEmail['emlEmail'];
			}
		}
		
		reset($this->Emails);
		$this->Email = current($this->Emails);
	  }

  }
  
  
  public function CheckPassword($Password){
  	if (sha1($Password) == $this->Password){
  		return true;
  	}
  	
	return false;  	
  	
  }
  
  
public function SetHash(){
  	
  	global $System;
  	if (!isset($System)){
  		$System = new clsSystem();
  	}
  	
	$Salt = 'cm';
	if (isset($System->Config->Config['sessionvarprefix'])) {
		$Salt = $System->Config->Config['sessionvarprefix'];
	}
    $Hash = md5($Salt.time());

    $sql = "UPDATE tbl_user SET usrHash = '$Hash', usrHashDate = NOW() WHERE usrRecnum=$this->Id;";
	$result = dbExecute($sql);

	if ($result === false){
		return false;
	}
	
	$this->Hash = $Hash;
	$this->HashDate = new datetime();
	
	return true;    
}
  
}