<?php

	require_once('class/clsSystem.php');
	require_once('function/utils.inc');
	
	session_start();

	$System = new clsSystem;
	$Session = $System->Session;

	$Session->Clear('ErrorMessage');
	$Session->Clear('Message');
		
	$Key = "";
	if (isset($_REQUEST['key'])){
		$Key = $_REQUEST['key'];
	}

				
	try {
		
			if (empty($Key)){
     			throw new Exception('Could not log you in.');		
			}
		
			$sql = "select * from tbl_user where usrHash='$Key'";
			$result = $db->query($sql);
	  	    if (!$result) {
		    	throw new Exception("Failed to login");
		  	}

			if ($result->num_rows>0) {
				$rstRow = $result->fetch_assoc();
			  	$Session->UserName = $rstRow['usrName'];
			  	$Session->UserId = $rstRow['usrRecnum'];
			  	$Session->HashLogin = true;
			  	
			  	$sql = "update tbl_user SET usrHash = NULL WHERE usrRecnum = ".$Session->UserId;
				$result = $System->DbExecute($sql);
			}
			else
			{
	    			throw new Exception('Could not log you in.');
			}

			header("Location: account.php");
		  }
	  catch(Exception $e)  {
			$Session->ErrorMessage = $e->getMessage();
			header("Location: ."); 
	  }

?>