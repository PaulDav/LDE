<?php
	require_once('function/utils.inc');
	require_once('data/dataProfile.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	require_once('class/clsProfile.php');
	
	define('PAGE_NAME', 'translation');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$Specs = new clsSpecs();
		$Dicts = new clsDicts();
		
		$GroupId = null;
		$SpecId = null;
		$TransId = null;

		if (!isset($_SESSION['forms'][PAGE_NAME]['specid'])){
			throw new exception("specid not specified");
		}
		$SpecId = $_SESSION['forms'][PAGE_NAME]['specid'];			
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['transid'])){
			$TransId = $_SESSION['forms'][PAGE_NAME]['transid'];
		}

		if (!isset($Specs->Items[$SpecId])){
			throw new Exception('Unknown Spec Id');
		}
		$objSpec = $Specs->Items[$SpecId];
			

		if (!($objSpec->canEdit)){
			throw new exception("You cannot update this Translation");
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
				if ( is_null($TransId)){
					throw new exception("Trans Id not specified");
				}
				
				if (!isset($objSpec->Translations[$TransId])){
					throw new exception("Unknown Translation");
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
				
				$Name = "";
				$Description = '';
				
				if (isset($_SESSION['forms'][PAGE_NAME]['name'])){
					$Name = $_SESSION['forms'][PAGE_NAME]['name'];
				}
				if ( $Name==''){
					throw new exception("Name not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		
				
				break;
		}
		

		switch ( $Mode ){
			case 'new':
			case 'edit':
				$TransId = dataTransUpdate($Mode, $TransId , $SpecId, $Name, $Description);
				break;
			case "delete":
				dataTransDelete($TransId, $SpecId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "spec.php?specid=$SpecId";
				break;
			default:
				$ReturnUrl = "translation.php?specid=$SpecId&transid=$TransId";
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