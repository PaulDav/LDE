<?php
	require_once('function/utils.inc');
	require_once('data/dataProfile.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	require_once('class/clsProfile.php');
	
	define('PAGE_NAME', 'translist');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
		
	try {
	
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}

		$GroupId = '';
		
		$SpecId = null;
		$TransId = null;
		
		$ListDictId = null;
		$ListId = null;
		
		$Dicts = new clsDicts();
		$Specs = new clsSpecs();
	
		if (!isset($_SESSION['forms'][PAGE_NAME]['specid'])){
			throw new exception("specid not specified");
		}
		$SpecId = $_SESSION['forms'][PAGE_NAME]['specid'];
		if (!isset($Specs->Items[$SpecId])){
			throw new exception('Unknown Specification');
		}
		$objSpec = $Specs->Items[$SpecId];
		
		if (!isset($_SESSION['forms'][PAGE_NAME]['transid'])){
			throw new exception("transid not specified");
		}
		$TransId = $_SESSION['forms'][PAGE_NAME]['transid'];
		if (!isset($objSpec->Translations[$TransId])){
			throw new exception('Unknown Translation');
		}
		$objTrans = $objSpec->Translations[$TransId];
		
		if (!($objSpec->canEdit)){
			throw new exception("You cannot update this Translation");
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
				
				if (isset($_SESSION['forms'][PAGE_NAME]['listid'])){
					$ListId = $_SESSION['forms'][PAGE_NAME]['listid'];
				}
				if (is_null($ListId)){
					throw new exception("listid not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['listdictid'])){
					$ListDictId = $_SESSION['forms'][PAGE_NAME]['listdictid'];
				}
				if (is_null($ListDictId)){
					throw new exception("listdictid not specified");
				}

				if (!isset($Dicts->Dictionaries[$ListDictId])){
					throw new Exception("Unknown List Dictionary");
				}
								
				$objListDict = $Dicts->Dictionaries[$ListDictId];
				
				if (!isset($objListDict->Lists[$ListId])){
					throw new exception("Unknown List");
				}
				$objList = $objListDict->Lists[$ListId];
				if (!$objListDict->canView){
					throw new exception("You can't use this list");
				}
				
				break;
		}
		

		switch ( $Mode ){
			case "edit":
				dataTransListUpdate($SpecId, $TransId, $ListDictId, $ListId);
				break;
			case "delete":
				dataTransListRemove($SpecId, $TransId);
				break;
		}

		$ReturnUrl = "translation.php?specid=$SpecId&transid=$TransId";
		
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>