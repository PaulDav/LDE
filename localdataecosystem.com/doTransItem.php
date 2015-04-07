<?php
	require_once('function/utils.inc');
	require_once('data/dataProfile.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	require_once('class/clsProfile.php');
	
	define('PAGE_NAME', 'transitem');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	try {
	
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$objSpec = null;
		$objTrans = null;
		$objItem = null;
		
		$GroupId = null;
		$SpecId = null;
		$TransId = null;
		$ItemId = null;
		
		$FromValue = null;
		$ToValue = null;
		
		$Specs = new clsSpecs;
		
			
		if (!isset($_SESSION['forms'][PAGE_NAME]['specid'])){
			throw new exception("specid not specified");
		}
		$SpecId = $_SESSION['forms'][PAGE_NAME]['specid'];			
		if (!isset($Specs->Items[$SpecId])){
			throw new Exception("Unknown Spec Id");
		}
		$objSpec = $Specs->Items[$SpecId];
		
		if (!isset($_SESSION['forms'][PAGE_NAME]['transid'])){
			throw new exception("transid not specified");
		}
		$TransId = $_SESSION['forms'][PAGE_NAME]['transid'];			
		if (!isset($objSpec->Translations[$TransId])){
			throw new Exception("Unknown Trans Id");
		}
		$objTrans = $objSpec->Translations[$TransId];
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['itemid'])){
			$ItemId = $_SESSION['forms'][PAGE_NAME]['itemid'];
		}
				
		if (!is_null($ItemId)){
			if (!isset($objTrans->Items[$ItemId])){
				throw new exception("Unknown Item Id");
			}
			$objItem = $objTrans->Items[$ItemId];
		}
				
		
		if (isset($_SESSION['forms'][PAGE_NAME]['fromvalue'])){
			$FromValue = $_SESSION['forms'][PAGE_NAME]['fromvalue'];
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['tovalue'])){
			$ToValue = $_SESSION['forms'][PAGE_NAME]['tovalue'];
		}
				
		
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
				if (is_null($ItemId)){
					throw new exception("item not specified");
				}
				
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}


		switch ($Mode){
			case 'edit':
				if (is_null($FromValue)){
					throw new exception("From Value not specified");
				}
				if (is_null($ToValue)){
					throw new exception("To Value not specified");
				}
				break;
			case "delete":
				break;
			default:				
				break;
		}
		

		switch ( $Mode ){
			case 'new':
			case 'edit':
				$ItemId = dataTransItemUpdate($Mode, $ItemId , $SpecId, $TransId, $FromValue, $ToValue);
				break;
			case 'delete':
				dataTransItemDelete($SpecId, $TransId, $ItemId);
				break;
		}

		$ReturnUrl = "translation.php?specid=$SpecId&transid=$TransId#items";
				
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>