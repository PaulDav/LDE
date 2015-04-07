<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'part');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	try {
	
		$Dicts = new clsDicts();
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$GroupId = '';
		$DictId = '';
		$PropId = '';
		$PartId = '';
	
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
		
		if (isset($_SESSION['forms'][PAGE_NAME]['partid'])){
			$PartId = $_SESSION['forms'][PAGE_NAME]['partid'];
			if (!isset($objProp->Parts[$PartId])){
				throw new exception("Unknown Part");
			}
			$objPart = $objProp->Parts[$PartId];
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
				if ( $PartId == ''){
					throw new exception("partid not specified");
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
				
				$Label = "";
				$Description = '';
				$DataType = '';
				$Cardinality = '';
				
				if (isset($_SESSION['forms'][PAGE_NAME]['label'])){
					$Label = $_SESSION['forms'][PAGE_NAME]['label'];			
				}
				if ( $Label==''){
					throw new exception("Label not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['datatype'])){
					$DataType = $_SESSION['forms'][PAGE_NAME]['datatype'];		
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['cardinality'])){
					$Cardinality = $_SESSION['forms'][PAGE_NAME]['cardinality'];		
				}		
				
				break;
		}
		

		switch ( $Mode ){
			case "new":
				$PartId = dataPartUpdate($Mode, null , $DictId, $PropId, $Label, $Description, $DataType, $Cardinality);
				break;
			case "edit":
				dataPartUpdate($Mode, $PartId, $DictId, $PropId, $Label, $Description, $DataType, $Cardinality);
				break;
			case "delete":
				dataPartDelete($PartId,$PropId,$DictId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "property.php?dictid=$DictId&propid=$PropId";
				break;
			default:
				$ReturnUrl = "part.php?dictid=$DictId&propid=$PropId&partid=$PartId";
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