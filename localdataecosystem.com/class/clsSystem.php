<?php

require_once(dirname(__FILE__).'/../class/clsUser.php');
require_once(dirname(__FILE__).'/../class/clsConfig.php');
require_once(dirname(__FILE__).'/../class/clsSession.php');


class clsSystem {
	
	public $db;
	public $database = null;
	public $path = null;
	public $LoggedOn = false;
	public $User = false;
	public $Config = false;
	public $Session = false;
	public $Fail = false;
	
	public $Domain = null;

	public function __construct(){
		
//		echo 'creating system <br/>';
//		flush();
		
		global $System;
		$System = $this;
		
		$this->path = null;
		$parse = parse_url($_SERVER["REQUEST_URI"]);
		$path = $parse['path'];
		if (!(substr($path,-1) == '/')){
			$path = dirname($path)."/";
	 	} 
		$this->path = $_SERVER['DOCUMENT_ROOT']."/".$path;
		
				
		$this->Config = new clsConfig();
		$this->Session = new clsSession();

		
		
		global $db;
		
		$this->db = $this->DbConnect();
		$db = $this->db;
		
		if ($this->Session->HashLogin === true){
			switch (basename($_SERVER['SCRIPT_NAME'])){
				case 'account.php':
				case 'doAccount.php':
					break;
				default:
					$this->Session->ErrorMessage = "You must update your account";
					return FALSE;
					break;
			}
		}
	
		
		if (!($this->Session->UserId == "")) {
			$this->LoggedOn = true;
		}
		
		if ($this->LoggedOn){
			$this->User = new clsUser();			
		}
			
		if (isset($_REQUEST['fail'])){
			$this->Fail = true;
		}
		
	}

		
	public function __get($name){
	  	switch ($name){
	  		default:
			  	return $this->$name;
	  	}
	}

	public function CreateUser($Name = null, $Password = null, $Email){
		
		if (empty($Email)){
			throw new exception("Email Address not specified");
		}
		
		if (!valid_email($Email)){
			throw new exception("Invalid Email Address");
		}
		
		if (!empty($Password)){
			if ((strlen($Password) < 6) || (strlen($Password) > 16)) {
		      throw new Exception('Password must be between 6 and 16 characters');
			}
		}
		
		
		if (!empty($Name)){
// check that the name has not already been used
			$sql = "select * from tbl_user where usrName='".$Name."'";

			$result = $this->db->query($sql);
			if (!$result) {
			  throw new Exception('Database Error');
			}
			
			if ($result->num_rows>0) {
			  throw new Exception('Name is already in use by another user');
			}
			
		}

		if (!empty($Name)){
			if (empty($Password)){
				throw new exception("Password not specified");
			}
			$Name = "'$Name'";
		}
		else
		{
			$Name = 'NULL';
		}

		if (!empty($Password)){
			$Password = "sha1('".$Password."')";
		}
		else
		{			
			$Password = 'NULL';
		}
		
		$Email = "'$Email'";

	  	$result = $this->db->query("insert into tbl_user ( usrName, usrPassword, usrEmail) values ($Name, $Password, $Email)");
		if (!$result) {
			throw new Exception('Could not register user');
		}
	  
		$Id = $this->db->insert_id;

		return $Id;
				
	}
	
	private function DbConnect(){

		$Server = $this->Config->Vars['database']['server'];
		$User = $this->Config->Vars['database']['user'];
		$PW = $this->Config->Vars['database']['password'];
		$this->database = $this->Config->Vars['database']['database'];
		
		$db = @new mysqli($Server, $User, $PW, $this->database);
		
		if (mysqli_connect_error()) {
		    die('Connect Error (' . mysqli_connect_errno() . ') '
	            . mysqli_connect_error());
		}

		if (!$db) {
 			throw new Exception('Could not connect to database');
		}
		else
		{  	
			if (!$db->set_charset("utf8")) {
				throw new exception("Error loading character set utf8:", $mysqli->error);
			}
			
     		return $db;
   		}

	}

	public function DbExecute($sql){
	
		$result = $this->db->query($sql);
	  	if (!$result) {
	    	throw new Exception("Could not execute sql $sql");
	  	}
	
		return $result;
	}
		
	public function doError($ErrorMessage, $URL = null){
		$this->Session->Error = true;
		$this->Session->ErrorMessage = $ErrorMessage;
				
		if (is_null($URL)){
			if (isset($_REQUEST['onErrorURL']))
			$URL = $_REQUEST['onErrorURL'];
		}
		if (empty($URL)){
			$URL = ".";
		}

		header("Location: $URL");
	}

}