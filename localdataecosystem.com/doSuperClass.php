<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'superclass');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	
	$DictId = '';
	$ClassId = '';
	
	$SuperDictId = '';
	$SuperClassId = '';

	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}
	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	$SuperDictId = $DictId;
	
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['classid'])){
		throw new exception("classid not specified");
	}	
	$ClassId = $_SESSION['forms'][PAGE_NAME]['classid'];
	
	
	
	try {

		
		$Dicts = new clsDicts();
	
		if (!isset($Dicts->Dictionaries[$DictId])){
			throw new Exception("Unknown Dictionary");
		}
		$objDict = $Dicts->Dictionaries[$DictId];
		
		if (!isset($objDict->Classes[$ClassId])){
			throw new exception("Unknown Class");
		}
		$objClass = $objDict->Classes[$ClassId];
		
		
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
				
				if (isset($_SESSION['forms'][PAGE_NAME]['superclassid'])){
					$SuperClassId = $_SESSION['forms'][PAGE_NAME]['superclassid'];
				}
				if ( $SuperClassId==''){
					throw new exception("superclassid not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['superdictid'])){
					$SuperDictId = $_SESSION['forms'][PAGE_NAME]['superdictid'];
				}
				
				if (!isset($Dicts->Dictionaries[$SuperDictId])){
					throw new Exception("Unknown Super Dictionary");
				}
				$objSuperDict = $Dicts->Dictionaries[$SuperDictId];
				
				if (!isset($objSuperDict->Classes[$SuperClassId])){
					throw new exception("Unknown Super Class");
				}
				$objSuperClass = $objSuperDict->Classes[$SuperClassId];
				if (!$objSuperDict->canView){
					throw new exception("You can't use this class");
				}
				if (!($objClass->Concept == $objSuperClass->Concept)){
					throw new exception("Wrong Concept");
				}
				
				break;
		}
		

		switch ( $Mode ){
			case "edit":
				dataClassSetSuperClass($Mode, $DictId, $ClassId, $SuperDictId, $SuperClassId);
				break;
			case "delete":
				dataClassRemoveSuperClass($DictId, $ClassId);
				break;
		}

		$ReturnUrl = "class.php?dictid=$DictId&classid=$ClassId";
		
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>