<?php
	require_once('function/utils.inc');
	require_once('data/dataProfile.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsProfile.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'spec');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	$SpecId = '';
	$ProfileId = '';

	try {
		
		$Profiles = new clsProfiles();
		$Specs = new clsSpecs();
	
		if (isset($_SESSION['forms'][PAGE_NAME]['groupid'])){
			$GroupId = $_SESSION['forms'][PAGE_NAME]['groupid'];
			$objGroup = new clsGroup($GroupId);
			if (!($objGroup->canEdit)){
				throw new exception("You cannot update this Group");
			}		
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['specid'])){
			$SpecId = $_SESSION['forms'][PAGE_NAME]['specid'];			
			$objSpec = $Specs->Items[$SpecId];
			if (!($objSpec->canEdit)){
				throw new exception("You cannot update this Specification");
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
				
				if ( $SpecId == ''){
					throw new exception("SpecId not specified");
				}
				
					break;
			default:
				throw new exception("Invalid Mode");
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['profileid'])){
			$ProfileId = $_SESSION['forms'][PAGE_NAME]['profileid'];
			$objProfile = $Profiles->Items[$ProfileId];
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

		$FileType = "";
		if (isset($_SESSION['forms'][PAGE_NAME]['filetype'])){
			$FileType = $_SESSION['forms'][PAGE_NAME]['filetype'];		
		}
		if (IsEmptyString($FileType)){
			throw new exception("File Type not specified");
		}
		if (!in_array($FileType, $System->Config->ImportFileTypes)){
			throw new exception("Invalid File Type");
		}
		
		
		$Publish = false;
		if (isset($_SESSION['forms'][PAGE_NAME]['publish'])){
			if ($_SESSION['forms'][PAGE_NAME]['publish'] == "Yes"){
				$Publish = true;
			}
		}		

		if ($Mode == 'new'){
			if ( $SpecId == ''){
				$SpecId = clean(strtolower($Name));
			}			
		}
		
		switch ( $Mode ){
			case "new":
			case "edit":
				$SpecId = dataSpecUpdate($Mode, $SpecId , $GroupId, $ProfileId, $Name, $Description, $FileType, $Publish);
				break;
		}

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: spec.php?specid=$SpecId");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>