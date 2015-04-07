<?php
	require_once('function/utils.inc');
	require_once('class/clsSystem.php');
	
	session_start();

	$System = new clsSystem();
	$Session = $System->Session;
	
	$Session->Clear('ErrorMessage');
	$Session->Clear('Message');

	try {
	
		if (!(isset($_POST['handle']) && isset($_POST['passwd']))) {
			throw new exception("please give login information");
		}
		
		$handle = $_POST['handle'];
		$passwd = $_POST['passwd'];
			
		$sql = "SELECT DISTINCT usrRecnum, usrName FROM tbl_user LEFT JOIN tbl_user_email ON emlUser=usrRecnum WHERE emlEmail='".$handle."' and usrPassword = sha1('".$passwd."')";
		$result = $System->db->query($sql);
  	    if (!$result) {
	   		throw new Exception("Failed to login");
		}

		if ($result->num_rows>0) {
			$rstRow = $result->fetch_assoc();
		  	$Session->UserName = $rstRow['usrName'];
		  	$Session->UserId = $rstRow['usrRecnum'];	
		}
		else{
     		throw new Exception('Could not log you in.');
  		}
						
  		$Session->Clear('HashLogin');
  			
		$Session->Message = 'Welcome.';
		header("Location: ."); 
	}
	catch(Exception $e)  {
		    // unsuccessful login	
		$Session->ErrorMessage = $e->getMessage();
		header("Location: login.php"); 
	}
?>