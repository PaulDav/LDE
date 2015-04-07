<?php
	require_once('function/utils.inc');
	require_once('data/dataProfile.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	require_once('class/clsProfile.php');
	
	define('PAGE_NAME', 'profileclass');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = null;
	$ProfileId = null;
	$ProfileClassId = null;
	$ProfileRelId = null;
	$DictId = null;
	$ClassId = null;

	$Create = false;
	$Select = false;
	
	$Parent = null;
	
	
	$Dicts = new clsDicts();
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['profileid'])){
		throw new exception("profileid not specified");
	}
	$ProfileId = $_SESSION['forms'][PAGE_NAME]['profileid'];			
	$Profile = new clsProfile($ProfileId);
	
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}
	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	$Dict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['classid'])){
		throw new exception("classid not specified");
	}	
	$ClassId = $_SESSION['forms'][PAGE_NAME]['classid'];
	$Class = $Dict->Classes[$ClassId];

	
	if (isset($_SESSION['forms'][PAGE_NAME]['profileclassid'])){
		$ProfileClassId = $_SESSION['forms'][PAGE_NAME]['profileclassid'];
		$ProfileClass = $Profile->Classes[$ProfileClassId];
		if ($ClassId == ""){
			$ClassId = $ProfileClass->ClassId;
			$DictId = $ProfileClass->DictId;
		}
		$ProfileRelId = $ProfileClass->RelId;
	}


	if (isset($_SESSION['forms'][PAGE_NAME]['create'])){
		switch ($_SESSION['forms'][PAGE_NAME]['create']){
			case 'true':
				$Create = true;
				break;
			default:
				$Create = false;
				break;				
		}
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['select'])){
		switch ($_SESSION['forms'][PAGE_NAME]['select']){
			case 'true':
				$Select = true;
				break;
			default:
				$Select = false;
				break;				
		}
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['profilerelid'])){
		$ProfileRelId = $_SESSION['forms'][PAGE_NAME]['profilerelid'];
		$ProfileRel = $Profile->Relationships[$ProfileRelId];
	}
	
		
	try {

		if ($ClassId == ""){
			throw new exception("classid not specified");
		}
		
		
		if (!($Profile->canEdit)){
			throw new exception("You cannot update this Profile");
		}
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				break;
			case 'edit':
			case 'delete':
				if ( $ProfileClassId == ''){
					throw new exception("profileclassid not specified");
				}
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}

		
		switch ($Mode){
			case "delete":
				break;
			default:				
				break;
		}
		

		switch ( $Mode ){
			case "new":
			case "edit":
				$ProfileClassId = dataProfileClassUpdate($Mode, $ProfileClassId , $ProfileId, $DictId, $ClassId, $ProfileRelId, $Create, $Select);
				break;
			case "delete":
				dataProfileClassDelete($ProfileId, $ProfileClassId);
				break;
		}

		$ReturnUrl = "profileclass.php?profileid=$ProfileId&profileclassid=$ProfileClassId";
		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "profile.php?profileid=$ProfileId#classes";
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