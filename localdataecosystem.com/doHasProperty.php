<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'hasproperty');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
		
	
	$GroupId = null;
	$DictId = null;
	$ClassId = null;
	$Relid = null;
	$PropId = null;
	$PropDictId = null;
	$HasPropId = null;
	
	$Cardinality = null;
	$UseAsName = false;
	$UseAsIdentifier = false;
	
	$objParent = null;
	$ParentType = null;

	$Dicts = new clsDicts();
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}
	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (isset($_SESSION['forms'][PAGE_NAME]['classid'])){
		$ClassId = $_SESSION['forms'][PAGE_NAME]['classid'];
		$objParent = $objDict->Classes[$ClassId];
		$ParentType = 'class';
	}
		

	if (isset($_SESSION['forms'][PAGE_NAME]['relid'])){
		$RelId = $_SESSION['forms'][PAGE_NAME]['relid'];
		$objParent = $objDict->Relationships[$RelId];
		$ParentType = 'relationship';
	}
		
	

	if (isset($_SESSION['forms'][PAGE_NAME]['propid'])){
		$PropId = $_SESSION['forms'][PAGE_NAME]['propid'];
		$PropDictId = $DictId;
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['propdictid'])){
		$PropDictId = $_SESSION['forms'][PAGE_NAME]['propdictid'];
	}
	
	
	if (isset($_SESSION['forms'][PAGE_NAME]['haspropid'])){
		$HasPropId = $_SESSION['forms'][PAGE_NAME]['haspropid'];
		$objHasProp = $objParent->Properties[$HasPropId];
		if ($PropId == ""){
			$PropId = $objHasProp->PropId;
			$PropDictId = $objHasProp->PropDictId;
		}
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
	
	
	try {

		if (is_null($objParent)){
			throw new exception("Parent Not Specified");
		}
		
		if ($PropId == ""){
			throw new exception("propid not specified");
		}
		
		
		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
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
				if ( $HasPropId == ''){
					throw new exception("haspropid not specified");
				}
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}

		
		
		switch ( $Mode ){
			case "new":
			case "edit":				
				$HasPropId = dataHasPropUpdate($Mode, $HasPropId , $ParentType, $DictId, $objParent->Id, $PropDictId, $PropId, $Cardinality, $UseAsName, $UseAsIdentifier);
				break;
			case "delete":
				dataHasPropDelete($ParentType, $DictId, $objParent->Id, $HasPropId);
				break;
		}


		$ReturnUrl = "";
		switch ($ParentType){
			case "class":
				$ReturnUrl = "hasproperty.php?dictid=$DictId&classid=$ClassId&haspropid=$HasPropId";
				switch ( $Mode ){
					case "delete":
						$ReturnUrl = "class.php?dictid=$DictId&classid=$ClassId#properties";
						break;
				}
				break;
			case "relationship":
				$ReturnUrl = "hasproperty.php?dictid=$DictId&relid=$RelId&haspropid=$HasPropId";
				switch ( $Mode ){
					case "delete":
						$ReturnUrl = "relationship.php?dictid=$DictId&relid=$RelId#properties";
						break;
				}
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