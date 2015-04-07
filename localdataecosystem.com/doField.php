<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'field');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	try {
	
		$Dicts = new clsDicts();
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$DictId = null;
		$PropId = null;
		$PartId = null;

		$ReturnURL = 'index.php';
				
		if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
			throw new exception("dictid not specified");
		}
		$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
		if (!isset($_SESSION['forms'][PAGE_NAME]['propid'])){
			throw new exception("propid not specified");
		}
		$PropId = $_SESSION['forms'][PAGE_NAME]['propid'];
	
		$objDict = $Dicts->Dictionaries[$DictId];
		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
		}	

		if (!isset($objDict->Properties[$PropId])){
			throw new exception("Unknown Property");
		}
		$objProp = $objDict->Properties[$PropId];

		$ReturnUrl = "property.php?dictid=$DictId&propid=$PropId";
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['partid'])){
			$PartId = $_SESSION['forms'][PAGE_NAME]['partid'];
			if (!isset($objProp->Parts[$PartId])){
				throw new exception("Unknown Part");
			}
			$objPart = $objProp->Parts[$PartId];
			$ReturnUrl = "part.php?dictid=$DictId&propid=$PropId&partid=$PartId";
		}			
				
		
		$DataType = '';
		$Length = '';
				
		if (isset($_SESSION['forms'][PAGE_NAME]['datatype'])){
			$DataType = $_SESSION['forms'][PAGE_NAME]['datatype'];		
		}		
		if (isset($_SESSION['forms'][PAGE_NAME]['length'])){
			$Length = $_SESSION['forms'][PAGE_NAME]['length'];		
		}		
		
		dataFieldUpdate($DictId, $PropId, $PartId, $DataType, $Length);

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>