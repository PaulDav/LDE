<?php
	require_once('function/utils.inc');
	require_once('data/dataData.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');

	require_once('class/clsProfile.php');
	require_once('class/clsData.php');
	
	
	define('PAGE_NAME', 'setprofile');
	
	session_start();
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$GroupId = null;
		$SetId = null;
		$SetProfileId = null;
		$ProfileId = null;
	
		if (isset($_SESSION['forms'][PAGE_NAME]['setprofileid'])){
			$SetProfileId = $_SESSION['forms'][PAGE_NAME]['setprofileid'];			
		}
			
		if (isset($_SESSION['forms'][PAGE_NAME]['setid'])){
			$SetId = $_SESSION['forms'][PAGE_NAME]['setid'];
		}
		if (is_null($SetId)){
			throw new exception("SetId not specified");
		}
		$objSet = new clsSet($SetId);
		if (!($objSet->canEdit)){
			throw new exception("You cannot update this Set");
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['profileid'])){
			$ProfileId = $_SESSION['forms'][PAGE_NAME]['profileid'];			
		}
	
		$Profiles = new clsProfiles();
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		
		switch ($Mode) {
			case 'edit':
			case 'delete':
				if (is_null($SetProfileId)){
					throw new exception("SetProfileId not specified");
				}				
				break;
			case 'new':
				break;
			default:
				throw new exception("Invalid Mode");
		}

		if (!is_null($ProfileId)){
			if (!isset($Profiles->Items[$ProfileId])){
				throw new Exception("Invalid Profile");
			}
			$objProfile = $Profiles->Items[$ProfileId];
		}

		switch ( $Mode ){
			case "new":
			case "edit":				
				$SetProfileId = dataSetProfileUpdate($Mode, $SetProfileId , $SetId, $ProfileId);
				break;
			case 'delete':
				dataSetProfileDelete($SetId, $SetProfileId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "set.php?setid=$SetId";				
				break;
			default:
				$ReturnUrl = "set.php?setid=$SetId#profiles";
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