<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	require_once('class/clsModel.php');
	
	define('PAGE_NAME', 'class');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$Dicts = new clsDicts();
	
	$GroupId = '';
	$DictId = '';
	$ClassId = '';

	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}

	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	if (isset($_SESSION['forms'][PAGE_NAME]['classid'])){
		$ClassId = $_SESSION['forms'][PAGE_NAME]['classid'];
	}

	
	try {

		if (!isset($Dicts->Dictionaries[$DictId])){
			throw "unknown Dictionary";
		}
		
		$objDict = $Dicts->Dictionaries[$DictId];
		
		$objModel = new clsModel();
		
		
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
				if ( $ClassId == ''){
					throw new exception("classid not specified");
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
				$Concept = '';
				$Heading = '';
				$Description = '';
				$Source = '';
				$Concept = '';
				
				if (isset($_SESSION['forms'][PAGE_NAME]['label'])){
					$Label = $_SESSION['forms'][PAGE_NAME]['label'];			
				}
				if ( $Label==''){
					throw new exception("Label not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['heading'])){
					$Heading = $_SESSION['forms'][PAGE_NAME]['heading'];			
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['concept'])){
					$Concept = $_SESSION['forms'][PAGE_NAME]['concept'];
					$found = false;
					foreach ($objModel->Concepts as $optConcept){
						if ($optConcept->Name == $Concept){
							$found = true;
						}
					}
					if (!$found){
						throw new exception("Invalid Concept");
					}
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['source'])){
					$Source = $_SESSION['forms'][PAGE_NAME]['source'];
				}		
				
				break;
		}
		

		switch ( $Mode ){
			case "new":
				$ClassId = dataClassUpdate($Mode, null , $DictId, $Concept, $Label, $Description, $Heading, $Source);
				break;
			case "edit":
				dataClassUpdate($Mode, $ClassId, $DictId, $Concept, $Label, $Description, $Heading, $Source);
				break;
			case "delete":
				dataClassDelete($ClassId, $DictId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "dict.php?dictid=$DictId";
				break;
			default:
				$ReturnUrl = "class.php?dictid=$DictId&classid=$ClassId";
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