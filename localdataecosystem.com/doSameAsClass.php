<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	require_once('class/clsModel.php');
	
	define('PAGE_NAME', 'sameasclass');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = null;
	$DictId = null;
	$ClassId = null;
	$SameAsDictId = null;
	$SameAsClassId = null;
		
	$Dicts = new clsDicts();
	$Model = new clsModel();
		
	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}
	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['classid'])){
		throw new exception("classid not specified");
	}
		
	$ClassId = $_SESSION['forms'][PAGE_NAME]['classid'];
	$objClass = $Dicts->getClass($DictId, $ClassId);

	if (isset($_SESSION['forms'][PAGE_NAME]['sameasclassid'])){
		$SameAsClassId = $_SESSION['forms'][PAGE_NAME]['sameasclassid'];
		$SameAsDictId = $DictId;
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['sameasdictid'])){
		$SameAsDictId = $_SESSION['forms'][PAGE_NAME]['sameasdictid'];
	}
	
	$objSameAsClass = $Dicts->getClass($SameAsDictId, $SameAsClassId);
	
	try {

		if (!is_object($objClass)){
			throw new exception("Unknown Class");
		}
		
		if (!is_object($objSameAsClass)){
			throw new exception("Unknown Same As Class");
		}
		
		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
		}
		
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':	
// check same as class is the same concept

				$objConcept = null;
				foreach ($Model->Concepts as $optConcept){		
					if ($optConcept->Name == $objClass->Concept){
						$objConcept = $optConcept;
						break;
					}
				}
				if (!is_null($objConcept)){
					$Concepts = array();
					$Concepts[$objConcept->Name] = $objConcept;
					foreach ($objConcept->SubConceptIds as $SubConceptId){
						if (isset($Model->Concepts[$SubConceptId])){
							$objSubConcept = $Model->Concepts[$SubConceptId];
							$Concepts[$objSubConcept->Name] = $objSubConcept;				
						}
					}
				}
				
				if (!isset($Concepts[$objSameAsClass->Concept])){
					throw new exception("Wrong Concept for Same As Class");
				}				
				
				break;
			case 'delete':
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}
		
		switch ( $Mode ){
			case "new":
				dataSameAsClassUpdate($Mode, $DictId, $ClassId, $SameAsDictId, $SameAsClassId);
				break;
			case "delete":
				dataSameAsClassDelete($DictId, $ClassId, $SameAsDictId, $SameAsClassId);				
				break;
		}

		$ReturnUrl = "class.php?dictid=$DictId&classid=$ClassId#sameas";
						
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>