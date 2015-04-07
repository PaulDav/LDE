<?php
	require_once('function/utils.inc');
	require_once('class/clsSystem.php');
	require_once('class/clsUser.php');
	
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput('account');
	
	$CurrentPassword = null;

	$UserName = null;
	$Email = null;

	$NewPassword1 = null;
	$NewPassword2 = null;
	
	$Mode = 'edit';
	if (isset($_REQUEST['Mode'])){
		$Mode = $_REQUEST['Mode'];			
	}
	
	
	$UserId = null;

	try {
					
		switch ($Mode) {
			case 'new':
				if ($System->LoggedOn){
					throw new Exception("Please log off before registering a new user");
				}
				break;
			case 'hash':
				if (!$System->Session->HashLogin){
					throw new Exception("Invalid Mode");
				}				
				break;
			case 'edit':
				if (!($System->LoggedOn === true)){
					throw new Exception("Invalid Mode");
				}
				break;
			default:
				throw new exception("Invalid Mode");
		}

		if (isset($_SESSION['forms']['account']['UserName'])){
			$UserName = $_SESSION['forms']['account']['UserName'];
		}
		if (isset($_SESSION['forms']['account']['Email'])){
			$Email = strtolower($_SESSION['forms']['account']['Email']);
		}
		if (isset($_SESSION['forms']['account']['NewPassword1'])){
			$NewPassword1 = $_SESSION['forms']['account']['NewPassword1'];
		}
		if (isset($_SESSION['forms']['account']['NewPassword2'])){
			$NewPassword2 = $_SESSION['forms']['account']['NewPassword2'];
		}
		if (isset($_SESSION['forms']['account']['CurrentPassword'])){
			$CurrentPassword = $_SESSION['forms']['account']['CurrentPassword'];
		}
				

		switch ($Mode){
			case "new":
				if (empty($UserName)){
					throw new Exception("User Name not specified");
				}
				if (empty($Email)){
					throw new Exception("Email not specified");
				}
				if (empty($NewPassword1)){
					throw new Exception("New Password not specified");
				}
				break;
				
			case "hash":
				if (is_null($System->User->Name)){
					if (empty($UserName)){
						throw new Exception("User Name not specified");
					}
				}
				
				if (empty($NewPassword1)){
					throw new Exception("New Password not specified");
				}
				break;
			default:
				
				if (!$System->User->CheckPassword($CurrentPassword)){
					throw new Exception("Password incorrect");
				}
						
		}
		
		if (!empty($Email)){
			if (!( valid_email($Email))){
				throw new exception("Invalid email address");
			}
			CheckEmailUnique($Email);
		}

		if (!empty($NewPassword1)){
		    if ($NewPassword1 != $NewPassword2) {
		      throw new Exception('Passwords do not match');
		    }

		    if ((strlen($NewPassword1) < 6) || (strlen($NewPassword1) > 16)) {
		      throw new Exception('Password must be between 6 and 16 characters');
		    }
		}
		
		
		switch ($Mode){
			case 'new':
				$sql = "insert into tbl_user ( usrName, usrPassword) values ('$UserName', sha1('$NewPassword1'))";
				$result = $System->DbExecute($sql);
				if (!$result) {
				  throw new Exception('Could not register you - please try again later.');
				}
				  
				$UserId = $System->db->insert_id;				
				$System->Session->UserId = $UserId;
				
				$sql = "insert into tbl_user_email ( emlUser, emlEmail) values ($UserId, '$Email')";
				$result = $System->DbExecute($sql);
				if (!$result) {
				  throw new Exception('Could not register you - please try again later.');
				}
								
				break;
				
			default:
				
				$UserId = $System->User->Id;
				
				$sql = "update tbl_user set ";
				$sqlFields = "";
								
				if (!empty($UserName)){
					if (!empty($sqlFields)){$sqlFields .= ", ";}
					$sqlFields .= "usrName='$UserName' ";
				}

				if (!empty($NewPassword1)){
					if (!empty($sqlFields)){$sqlFields .= ", ";}
					$sqlFields .= " usrPassword=sha1('$NewPassword1'), usrHash=NULL ";
				}
								
				if (!empty($sqlFields)){
					$sql = $sql.$sqlFields." WHERE usrRecnum = ".$UserId;
					$result = $System->DbExecute($sql);
					if (!$result) {
				    	throw new Exception("Could not update account.");
				  	}
				}
				
				if (!empty($Email)){
					$sql = "insert into tbl_user_email ( emlUser, emlEmail) values ($UserId, '$Email')";
					$result = $System->DbExecute($sql);
					if (!$result) {
					  throw new Exception('Could not add Email address.');
					}					
				}
				
			  	break;
		}

		$System->User = new clsUser($UserId);
		
		$ReturnUrl = ".";
		if (isset($_SESSION['forms']['account']['ReturnURL'])){
			$ReturnUrl = $_SESSION['forms']['account']['ReturnURL'];
		}
		
		unset($_SESSION['forms']['account']);
		$System->Session->Clear('HashLogin');
		
		$System->Session->Message = "Account updated";
		
		
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