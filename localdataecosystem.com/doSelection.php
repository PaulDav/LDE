<?php
	require_once('function/utils.inc');
	require_once('data/dataShape.php');
	require_once('data/dataProfile.php');
	
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsShape.php');
	
	define('PAGE_NAME', 'selection');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}

	$SelectionOf = null;
	$Type = null;
	$TypeId = null;
	$SelId = null;

	$objShape = null;
	$objSel = null;
	$objType = null;
	
	$Mode = 'edit';
	
	try {
	
		$Shapes = new clsShapes();
		
		if (isset($_SESSION['forms'][PAGE_NAME]['selid'])){
			$SelId = $_SESSION['forms'][PAGE_NAME]['selid'];
			if (!isset($Shapes->Selections[$SelId])){
				throw new exception("Unknown Selection");
			}
			$objSel = $Shapes->Selections[$SelId];			
			if (!($objSel->canEdit)){
				throw new exception("You cannot update this Selection");
			}
		}
		
		if (is_null($SelId)){
			$Mode = 'new';
		}		

		
		if (isset($_SESSION['forms'][PAGE_NAME]['selectionof'])){
			
			$SelectionOf = $_SESSION['forms'][PAGE_NAME]['selectionof'];
			if (!isset($Shapes->Items[$SelectionOf])){
				throw new exception("Unknown Super Shape");
			}
			$objSuperShape = $Shapes->Items[$SelectionOf];			
			if (!($objSuperShape->canView)){
				throw new exception("You cannot view this Super Shape");
			}
		}
		
		if (is_null($SelectionOf)){
			throw new Exception('Selection Of not specified');
		}

		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['type'])){
			$Type = $_SESSION['forms'][PAGE_NAME]['type'];
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['typeid'])){
			$TypeId = $_SESSION['forms'][PAGE_NAME]['typeid'];

			switch ($Type){
				case 'profile':
					$Profiles = new clsProfiles;
					if (!isset($Profiles->Items[$TypeId])){
						throw new exception("Unknown Profile");
					}
					$objType = $Profiles->Items[$TypeId];
					break;
				default:
					throw new exception("Invalid Type");
					break;
			}
			
		}
		
		
		
		$Fields = $_SESSION['forms'][PAGE_NAME];
			
		$arrSelFields = funGetClassSelection($objSuperShape->Selection->ShapeClass);

		$SelId = dataShapeUpdate($Mode, $SelId , null, null, null, false, $SelectionOf);
		$Shapes->refreshShapes();

		dataShapeUpdateSelection($SelId, $arrSelFields);
		
// set the selection on the type

		$ReturnUrl = "selection.php?selid=$SelId";
		
		
		switch ($Type){
			case 'profile':
				$ProfileId = $TypeId;
				dataProfileSetSelection($ProfileId, $SelId);
				$ReturnUrl = "profile.php?profileid=$TypeId#selection";				
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
	
	
function funGetClassSelection($objShapeClass, $arrSelFields = array()){
	global $Fields;
	
	$PropNum = 0;
	foreach ($objShapeClass->ShapeProperties as $objShapeProperty){
		if ($objShapeProperty->Selected === true){
			$PropNum = $PropNum + 1;
			$FieldName = 'class_'.$objShapeClass->Id.'_prop_'.$PropNum;
			if (isset($Fields[$FieldName.'_sel'])){
				if ($Fields[$FieldName.'_sel'] = 'selected'){
					$arrSelFields['class'][$objShapeClass->Id]['properties'][$PropNum]['sel'] = true;					
					$arrSelFields['class'][$objShapeClass->Id]['properties'][$PropNum]['properties'] = funGetComplexFields($objShapeProperty->ShapeProperties, $FieldName);				
				}
			}
		}
		
		foreach ($objShapeClass->ShapeLinks as $objShapeLink){
			
			if (is_null($objShapeLink->Relationship)){
				continue;
			}
			if (is_null($objShapeLink->ShapeClass)){
				continue;
			}

			$arrSelFields = funGetLinkSelection($objShapeLink, $arrSelFields);
			
			$arrSelFields = funGetClassSelection($objShapeLink->ShapeClass, $arrSelFields);
			
		}
	}
	
	return $arrSelFields;
	
}

function funGetLinkSelection($objShapeLink, $arrSelFields = array()){
	global $Fields;
	
	$PropNum = 0;
	foreach ($objShapeLink->ShapeProperties as $objShapeProperty){
		if ($objShapeProperty->Selected === true){
			$PropNum = $PropNum + 1;
			$FieldName = 'link_'.$objShapeLink->Id.'_prop_'.$PropNum;
			if (isset($Fields[$FieldName.'_sel'])){
				if ($Fields[$FieldName.'_sel'] = 'selected'){
					$arrSelFields['link'][$objShapeLink->Id]['properties'][$PropNum]['sel'] = true;
				}				
			}
		}
		
	}
	
	return $arrSelFields;
	
}


function funGetComplexFields($objShapeProperties, $ParentFieldName){
	
	global $Fields;
	
	$arrComplexFields = array();
	
	$PropNum = 0;
	foreach ($objShapeProperties as $objShapeProperty){
		if ($objShapeProperty->Selected === true){
			$PropNum = $PropNum + 1;
			$FieldName = $ParentFieldName.'_prop_'.$PropNum;
			if (isset($Fields[$FieldName.'_sel'])){
				if ($Fields[$FieldName.'_sel'] = 'selected'){
					$arrComplexFields['properties'][$PropNum]['sel'] = true;					
					$arrComplexFields['properties'][$PropNum]['properties'] = funGetComplexFields($objShapeProperty->ShapeProperties, $FieldName);										
				}				
			}
		}
	}

	return $arrComplexFields;
	
}



?>