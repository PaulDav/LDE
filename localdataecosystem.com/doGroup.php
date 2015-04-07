<?php
	require_once('function/utils.inc');
	require_once('data/dataGroup.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsUser.php');
	
	define('PAGE_NAME', 'group');
	
	session_start();
	$System = new clssystem();
	
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	
	try {
			
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				break;
			case 'edit':
				
				$GroupId = '';
				if (isset($_SESSION['forms'][PAGE_NAME]['groupid'])){
					$GroupId = $_SESSION['forms'][PAGE_NAME]['groupid'];			
				}
				if ( $GroupId == ''){
					throw new exception("Group not specified");
				}
				
				$objGroup = new clsGroup($GroupId);
				if (!($System->User->Id == $objGroup->OwnerId)){
					throw new exception("You cannot update this Group");
				}

				break;
			default:
				throw new exception("Invalid Mode");
		}

		
		$Name = "";
		if (isset($_SESSION['forms'][PAGE_NAME]['name'])){
			$Name = $_SESSION['forms'][PAGE_NAME]['name'];			
		}
		if ( $Name==''){
			throw new exception("Name not specified");
		}
		
		$Description = "";
		if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
			$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
		}		
		
		$Publish = false;
		if (isset($_SESSION['forms'][PAGE_NAME]['publish'])){
			if ($_SESSION['forms'][PAGE_NAME]['publish'] == "Yes"){
				$Publish = true;
			}
		}		

		
		
		
		switch ( $Mode ){
			case "new":
				$GroupId = dataGroupUpdate($Mode, NULL , $Name, $Description, $Publish);
				break;
			case "edit":
				dataGroupUpdate($Mode, $GroupId, $Name, $Description, $Publish);
				break;
		}

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: group.php?groupid=$GroupId");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>