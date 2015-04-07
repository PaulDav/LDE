<?php
	require_once('function/utils.inc');
	require_once('class/clsSystem.php');
	require_once('class/clsUser.php');
	
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput('account');
	
	$CurrentPassword = null;

	$Email = null;
	
	$Mode = 'edit';
	if (isset($_REQUEST['Mode'])){
		$Mode = $_REQUEST['Mode'];			
	}
	
	if (!($System->LoggedOn === true)){
		throw new Exception("Invalid Mode");
	}
	$UserId = $System->User->Id;
	

	try {
					
		switch ($Mode) {
			case 'new':
				break;
			default:
				throw new exception("Invalid Mode");
		}

		if (isset($_SESSION['forms']['account']['Email'])){
			$Email = strtolower($_SESSION['forms']['account']['Email']);
		}
		if (isset($_SESSION['forms']['account']['CurrentPassword'])){
			$CurrentPassword = $_SESSION['forms']['account']['CurrentPassword'];
		}
				
		if (!$System->User->CheckPassword($CurrentPassword)){
			throw new Exception("Password incorrect");
		}
		
		switch ($Mode){
			case "new":
				if (empty($Email)){
					throw new Exception("Email not specified");
				}
				break;				
			default:
				
						
		}
		
		if (!( valid_email($Email))){
			throw new exception("Invalid email address");
		}
		CheckEmailUnique($Email);

		switch ($Mode){
			case 'new':				
				$sql = "insert into tbl_user_email ( emlUser, emlEmail) values ($UserId, '$Email')";
				$result = $System->DbExecute($sql);
				if (!$result) {
				  throw new Exception('Could not add email address.');
				}								
				break;
		}

		$ReturnUrl = ".";
		if (isset($_SESSION['forms']['account']['ReturnURL'])){
			$ReturnUrl = $_SESSION['forms']['account']['ReturnURL'];
		}
		
		unset($_SESSION['forms']['account']);
		
		$System->Session->Message = "email updated";
		
		header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->Session->ErrorMessage = $e->getMessage();
		header("Location: account.php?fail");
		exit;
	}


function CheckEmailUnique($Email){

	global $System;
	
	$sql = "SELECT * FROM tbl_user_email WHERE emlEmail='".$Email."'";
	
	if ($System->LoggedOn){
		$sql .= " AND emlUser <> ".$System->User->Id;
	}
				  
	$result = $System->DbExecute($sql);
	if (!$result) {
	  throw new Exception('Could not execute query');
	}
	
	if ($result->num_rows>0) {
	  throw new Exception('That Email address has already been used - please choose another one, or use the forgot password facility.');
	}
	
	return true;
	
}	


?>