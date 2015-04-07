<?php
	require_once('function/utils.inc');
	require_once('data/dataProfile.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsProfile.php');
	require_once('class/clsShape.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'profile');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);
		
	try {		
	
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$GroupId = null;
		$ProfileId = null;
		
		$Profiles = new clsProfiles;
		$Shapes = new clsShapes;
		
	
		if (isset($_SESSION['forms'][PAGE_NAME]['groupid'])){
			$GroupId = $_SESSION['forms'][PAGE_NAME]['groupid'];
			$objGroup = new clsGroup($GroupId);
			if (!($objGroup->canEdit)){
				throw new exception("You cannot update this Group");
			}		
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['profileid'])){
			$ProfileId = $_SESSION['forms'][PAGE_NAME]['profileid'];			
			$Profile = $Profiles->Items[$ProfileId];
			if (!($Profile->canEdit)){
				throw new exception("You cannot update this Profile");
			}
		}
	
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				if ( $GroupId == ''){
					throw new exception("Group not specified");
				}
				
				break;
			case 'edit':
			case 'delete':
				
				if ( $ProfileId == ''){
					throw new exception("Profile not specified");
				}
				
				if (!isset($Profiles->Items[$ProfileId])){
					throw new exception("Unknown Profile");
				}
				$objProfile = $Profiles->Items[$ProfileId];
				$GroupId = $objProfile->GroupId;
				
				break;
			default:
				throw new exception("Invalid Mode");
		}

		switch ($Mode) {
			case 'new':
			case 'edit':
		
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
				break;
		}

		$ReturnUrl = '';
		
		switch ( $Mode ){
			case 'new':
			case 'edit':
				$ProfileId = dataProfileUpdate($Mode, $ProfileId , $GroupId, $Name, $Description, $Publish);
				$ReturnUrl = "profile.php?profileid=$ProfileId";				
				break;
			case "delete":
				dataProfileDelete($ProfileId);
				$ReturnUrl = "group.php?groupid=$GroupId";
				break;
		}

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
		
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>