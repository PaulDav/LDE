<?php
	require_once('function/utils.inc');
	require_once('data/dataProfile.php');
	require_once('class/clsProfile.php');
	require_once('class/clsShape.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	
	define('PAGE_NAME', 'profileshape');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	
	$ProfileId = null;
	$ShapeId = null;

	try {

		$Profiles = new clsProfiles();
		$Shapes = new clsShapes();

		if (!isset($_SESSION['forms'][PAGE_NAME]['profileid'])){
			throw new exception("profileid not specified");
		}
		$ProfileId = $_SESSION['forms'][PAGE_NAME]['profileid'];
				
		if (!isset($Profiles->Items[$ProfileId])){
			throw new exception("Unknown Profile");
		}
		$objProfile = $Profiles->Items[$ProfileId];
		
		if (!$objProfile->canEdit){
			throw new exception("You cannot update this Profile");
		}	

		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'edit':
			case 'delete':
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}
		
		switch ($Mode){
			case "delete":
				break;
			default:

				
				if (!isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
					throw new exception("shapeid not specified");
				}
				$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];
				
				if (!isset($Shapes->Items[$ShapeId])){
					throw new exception("Unknown Shape");
				}
				$objShape = $Shapes->Items[$ShapeId];
				
				if (!$objShape->canView){
					throw new exception("You can't use this shape");
				}
				
				break;
		}

		switch ( $Mode ){
			case "edit":
				dataProfileSetShape($ProfileId, $ShapeId);
				break;
			case "delete":
				dataProfileRemoveShape($ProfileId);
				break;
		}

		$ReturnUrl = "profile.php?profileid=$ProfileId";
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>