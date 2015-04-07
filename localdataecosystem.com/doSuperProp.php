<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'superprop');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	
	try {
	
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$Dicts = new clsDicts();
		
		$GroupId = '';
		
		$DictId = '';
		$PropId = '';
		
		$SuperDictId = '';
		$SuperClassId = '';
	
		if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
			throw new exception("dictid not specified");
		}
		$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
		$SuperDictId = $DictId;
		
		
		if (!isset($_SESSION['forms'][PAGE_NAME]['propid'])){
			throw new exception("propid not specified");
		}	
		$PropId = $_SESSION['forms'][PAGE_NAME]['propid'];
		
		$objDict = $Dicts->Dictionaries[$DictId];
		if (!isset($objDict->Properties[$PropId])){
			throw new exception("Unknown Property");
		}
		$objProp = $objDict->Properties[$PropId];
		

		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
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
				
				if (isset($_SESSION['forms'][PAGE_NAME]['superpropid'])){
					$SuperPropId = $_SESSION['forms'][PAGE_NAME]['superpropid'];
				}
				if ( $SuperPropId==''){
					throw new exception("superpropid not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['superdictid'])){
					$SuperDictId = $_SESSION['forms'][PAGE_NAME]['superdictid'];
				}
				$objSuperDict = $Dicts->Dictionaries[$SuperDictId];
				if (!isset($objSuperDict->Properties[$SuperPropId])){
					throw new exception("Unknown Super Property");
				}
				$objSuperProp = $objSuperDict->Properties[$SuperPropId];
				if (!$objSuperDict->canView){
					throw new exception("You can't use this property");
				}
				
				break;
		}
		

		switch ( $Mode ){
			case "edit":				
				dataPropSetSuperProperty($Mode, $DictId, $PropId, $SuperDictId, $SuperPropId);
				break;
			case "delete":
				dataPropRemoveSuperProperty($DictId, $PropId);
				break;
		}

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