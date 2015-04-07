<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'propertylist');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$Dicts = new clsDicts();
	
	$GroupId = '';
	$DictId = '';
	$PropId = '';
	$ListId = '';
	
	$Dicts = new clsDicts();
			
	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}
	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['propid'])){
		throw new exception("propid not specified");
	}
		
	$PropId = $_SESSION['forms'][PAGE_NAME]['propid'];
	$objProp = $objDict->Properties[$PropId];
	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['listid'])){
		$ListId = $_SESSION['forms'][PAGE_NAME]['listid'];
		$ListDictId = $DictId;
		if (isset($_SESSION['forms'][PAGE_NAME]['listdictid'])){
			$ListDictId = $_SESSION['forms'][PAGE_NAME]['listdictid'];
		}
		$objListDict = $objDict;
		if (!($ListDictId == $objDict->Id)){
			$objListDict = $Dicts->Dictionaries[$ListDictId];
		}
	}
		
	
	
	
	try {

		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
		}
		
		$Mode = 'new';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				break;
			case 'delete':
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}

		
		dataPropListUpdate($Mode, $DictId, $PropId, $ListDictId, $ListId);

		$ReturnUrl = "property.php?dictid=$DictId&propid=$PropId";
				
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>