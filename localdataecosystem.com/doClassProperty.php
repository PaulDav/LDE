<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'classproperty');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	$DictId = '';
	$ClassId = '';
	$PropId = '';
	$PropDictId = '';
	$ClassPropId = null;
	
	$Sequence = null;
	$Cardinality = null;
	$UseAsName = false;
	$UseAsIdentifier = false;
	$UseInLists = true;
	
	$Dicts = new clsDicts();
		
	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}
	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['classid'])){
		throw new exception("classid not specified");
	}
		
	$ClassId = $_SESSION['forms'][PAGE_NAME]['classid'];
	$objClass = $objDict->Classes[$ClassId];

	if (isset($_SESSION['forms'][PAGE_NAME]['propid'])){
		$PropId = $_SESSION['forms'][PAGE_NAME]['propid'];
		$PropDictId = $DictId;
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['propdictid'])){
		$PropDictId = $_SESSION['forms'][PAGE_NAME]['propdictid'];
	}
	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['classpropid'])){
		$ClassPropId = $_SESSION['forms'][PAGE_NAME]['classpropid'];
		$objClassProp = $objClass->Properties[$ClassPropId];
		if ($PropId == ""){
			$PropId = $objClassProp->PropId;
			$PropDictId = $objClassProp->PropDictId;
		}
	}

	if (isset($_SESSION['forms'][PAGE_NAME]['sequence'])){
		$Sequence = $_SESSION['forms'][PAGE_NAME]['sequence'];
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['cardinality'])){
		$Cardinality = $_SESSION['forms'][PAGE_NAME]['cardinality'];
	}

	
	if (isset($_SESSION['forms'][PAGE_NAME]['useasname'])){
		switch ($_SESSION['forms'][PAGE_NAME]['useasname']){
			case 'true':
				$UseAsName = true;
				break;
			default:
				$UseAsName = false;
				break;				
		}
	}

	if (isset($_SESSION['forms'][PAGE_NAME]['useasidentifier'])){
		switch ($_SESSION['forms'][PAGE_NAME]['useasidentifier']){
			case 'true':
				$UseAsIdentifier = true;
				break;
			default:
				$UseAsIdentifier = false;
				break;				
		}
	}
	

	if (isset($_SESSION['forms'][PAGE_NAME]['useinlists'])){
		switch ($_SESSION['forms'][PAGE_NAME]['useinlists']){
			case 'true':
				$UseInLists = true;
				break;
			default:
				$UseInLists = false;
				break;				
		}
	}
	
	
	try {

		if ($PropId == ""){
			throw new exception("propid not specified");
		}
		
		
		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
		}
		
		if (!is_null($Sequence)){
			if (!(is_numeric($Sequence))){
				throw new exception('Invalid Sequence');
			}
		}
		
		
		if (!is_null($Cardinality)){
			if (!in_array($Cardinality, $System->Config->Cardinalities)){
				throw new exception("Invalid Cardinality");
			}
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
				if (is_null($ClassPropId)){
					throw new exception("classpropid not specified");
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
				$ClassPropId = dataClassPropUpdate($Mode, $ClassPropId, $DictId, $ClassId, $PropDictId, $PropId, $Cardinality, $UseAsName, $UseAsIdentifier, $UseInLists, $Sequence);
				break;
			case "delete":
				dataClassPropDelete($DictId, $ClassId, $ClassPropId);
				break;
		}

		$ReturnUrl = "class.php?dictid=$DictId&classid=$ClassId#properties";
						
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>