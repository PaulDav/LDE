<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'property');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	$DictId = '';
	$PropId = '';
	
	$Dicts = new clsDicts();

	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}

	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	if (isset($_SESSION['forms'][PAGE_NAME]['propid'])){
		$PropId = $_SESSION['forms'][PAGE_NAME]['propid'];
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];

	try {

		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
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
				if ( $PropId == ''){
					throw new exception("propid not specified");
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
				$PropType = null;
				
				if (isset($_SESSION['forms'][PAGE_NAME]['label'])){
					$Label = $_SESSION['forms'][PAGE_NAME]['label'];			
				}
				if ( $Label==''){
					throw new exception("Label not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		
				
				if (isset($_SESSION['forms'][PAGE_NAME]['proptype'])){
					$PropType = $_SESSION['forms'][PAGE_NAME]['proptype'];		
					if (!in_array($PropType,$System->Config->PropertyTypes)){
						throw new exception("Invalid Property Type");
					}
				}		
				
				
				break;
		}
		

		switch ( $Mode ){
			case "new":
				$PropId = dataPropUpdate($Mode, null , $DictId, $Label, $Description, $PropType);
				break;
			case "edit":
				dataPropUpdate($Mode, $PropId, $DictId, $Label, $Description, $PropType);
				break;
			case "delete":
				dataPropDelete($PropId,$DictId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "dict.php?dictid=$DictId";
				break;
			default:
				$ReturnUrl = "property.php?dictid=$DictId&propid=$PropId";
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