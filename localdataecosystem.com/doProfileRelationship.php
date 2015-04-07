<?php
	require_once('function/utils.inc');
	require_once('data/dataProfile.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	require_once('class/clsProfile.php');
	
	define('PAGE_NAME', 'profilerelationship');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	try {
	
	
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$GroupId = null;
		$ProfileId = null;
		$ProfileClassId = null;
		$ProfileRelId = null;
		
		$DictId = null;
		$RelId = null;
		$Inverse = false;
		
		$Dicts = new clsDicts();
		
		if (!isset($_SESSION['forms'][PAGE_NAME]['profileid'])){
			throw new exception("profileid not specified");
		}
		$ProfileId = $_SESSION['forms'][PAGE_NAME]['profileid'];			
		$objProfile = new clsProfile($ProfileId);
	
		if (isset($_SESSION['forms'][PAGE_NAME]['profilerelid'])){
			$ProfileRelId = $_SESSION['forms'][PAGE_NAME]['profilerelid'];
			$objProfileRel = $objProfile->Relationships[$ProfileRelId];
			$ProfileClassId = $objProfileRel->SubjectProfileClassId;
			$DictId = $objProfileRel->DictId;
			$RelId = $objProfileRel->RelId;
			$Inverse = $objProfileRel->Inverse;
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['profileclassid'])){
			$ProfileClassId = $_SESSION['forms'][PAGE_NAME]['profileclassid'];
		}

		
		if (isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
			$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];
		}
		
		if (is_null($DictId)){
			throw new excpetion("DictId not specified");
		}		
		$Dict = $Dicts->Dictionaries[$DictId];
			
		if (isset($_SESSION['forms'][PAGE_NAME]['relid'])){
			$RelId = $_SESSION['forms'][PAGE_NAME]['relid'];
		}
		if (is_null($RelId)){
			throw new exception("relid not specified");
		}	
		$objRel = $Dict->Relationships[$RelId];

		if (isset($_SESSION['forms'][PAGE_NAME]['inverse'])){
			$Inverse = $_SESSION['forms'][PAGE_NAME]['inverse'];
			switch ($Inverse){
				case 'true':
					$Inverse = true;
					break;
				case 'false':
					$Inverse = false;
					break;
				default:
					throw new exception("Invalid Inverse");
					break;					
			}
			
		}


		if (!($objProfile->canEdit)){
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
				if ( $ProfileRelId == ''){
					throw new exception("profilerelid not specified");
				}
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}


		
		
		switch ( $Mode ){
			case "new":
			case "edit":
				$ProfileRelId = dataProfileRelUpdate($Mode, $ProfileRelId , $ProfileId, $ProfileClassId, $DictId, $RelId, $Inverse);
				break;
			case "delete":
				dataProfileRelDelete($ProfileId, $ProfileRelId);
				break;
		}

		$ReturnUrl = "profilerelationship.php?profileid=$ProfileId&profilerelid=$ProfileRelId";
		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "profile.php?profileid=$ProfileId";
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