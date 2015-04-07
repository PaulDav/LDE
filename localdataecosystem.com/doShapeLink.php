<?php
	require_once('function/utils.inc');
	require_once('data/dataShape.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsShape.php');
	require_once('class/clsDict.php');
	
	require_once('form/doFrmShapeProperties.php');
	
	define('PAGE_NAME', 'shapelink');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = null;
	$ShapeId = null;
	$ShapeLinkId = null;

	$objShape = null;
	$objGroup = null;
	
	$RelId = null;
	$RelDictId = null;
	$Inverse = null;
	
	$FromShapeClassId = null;
	$ToShapeClassId = null;
	
	try {

		global $Dicts;
		$Dicts = new clsDicts();
		
		global $Shapes;
		$Shapes = new clsShapes();

		if (!isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
			throw new exception("Shape not specified");
		}
		$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];
		if (!isset($Shapes->Items[$ShapeId])){
			throw new exception("Unknown Shape");
		}
		$objShape = $Shapes->Items[$ShapeId];
		if (!($objShape->canEdit)){
			throw new exception("You cannot update this Shape");
		}
		$objGroup = $objShape->GroupId;
				
		$objShapeLink = null;		

		if (isset($_SESSION['forms'][PAGE_NAME]['shapelinkid'])){
			$ShapeLinkId = $_SESSION['forms'][PAGE_NAME]['shapelinkid'];		
			if (!isset($objShape->ShapeLinks[$ShapeLinkId])){
				throw new exception("Unknown Shape Link");
			}
			$objShapeLink = $objShape->ShapeLinks[$ShapeLinkId];
			
			$FromShapeClassId = $objShapeLink->FromShapeClassId;			
			$ToShapeClassId = $objShapeLink->ToShapeClassId;
		}
		
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
				
		$Fields = $_SESSION['forms'][PAGE_NAME];
		
		
		switch ( $Mode ){
			case "new":
				break;
			case "edit":
			case 'delete':
				if (is_null($ShapeLinkId)){
					throw new exception("Shape Link Id not specified");
				}
				break;
		}
		
				
		
		$ReturnUrl = "shape.php?shapeid=$ShapeId#links";
		
		switch ( $Mode ){
			case "new":
			case "edit":

				if (isset($_SESSION['forms'][PAGE_NAME]['fromshapeclassid'])){
					$FromShapeClassId = $_SESSION['forms'][PAGE_NAME]['fromshapeclassid'];		
				}		
				if (is_null($FromShapeClassId)){
					throw new exception('From Shape Class not specified');
				}		
				if (!isset($objShape->ShapeClasses[$FromShapeClassId])){
					throw new exception("Unknown From Shape Class");
				}
				$objFromShapeClass = $objShape->ShapeClasses[$FromShapeClassId];
								
				if (isset($_SESSION['forms'][PAGE_NAME]['toshapeclassid'])){
					$ToShapeClassId = $_SESSION['forms'][PAGE_NAME]['toshapeclassid'];		
				}		
				if (is_null($ToShapeClassId)){
					throw new exception('To Shape Class not specified');
				}		
				if (!isset($objShape->ShapeClasses[$ToShapeClassId])){
					throw new exception("Unknown To Shape Class");
				}
				$objToShapeClass = $objShape->ShapeClasses[$ToShapeClassId];
				
				
				
				if (!isset($_SESSION['forms'][PAGE_NAME]['reldictid'])){
					throw new exception("Relationship Dictionary not specified");
				}
				$RelDictId = $_SESSION['forms'][PAGE_NAME]['reldictid'];
				
				if (!isset($_SESSION['forms'][PAGE_NAME]['relid'])){
					throw new exception("Relationship  not specified");
				}
				$RelId = $_SESSION['forms'][PAGE_NAME]['relid'];
				
				if (!isset($Dicts->Dictionaries[$RelDictId]->Relationships[$RelId])){
					throw new exception("Unknown Relationship");
				}
				$objRel = $Dicts->Dictionaries[$RelDictId]->Relationships[$RelId];
				
				if (isset($_SESSION['forms'][PAGE_NAME]['inverse'])){
					if ($_SESSION['forms'][PAGE_NAME]['inverse'] == 'true'){
						$Inverse = true;
					}
				}


				$LinkEffDates = false;
				if (isset($_SESSION['forms'][PAGE_NAME]['linkeffdates'])){
					if ($_SESSION['forms'][PAGE_NAME]['linkeffdates'] == 'true'){
						$LinkEffDates = true;	
					}
				}

				$Cardinality = $objRel->Cardinality;
				if (isset($_SESSION['forms'][PAGE_NAME]['cardinality'])){
					$Cardinality = $_SESSION['forms'][PAGE_NAME]['cardinality'];
				}
				if (!in_array($Cardinality, $System->Config->RelCardinalities)){
					throw new Exception("Invalid Cardinality");
				}
				
				if (isset($System->Config->RelSubCardinalities[$objRel->Cardinality])){
					if (!in_array($Cardinality,$System->Config->RelSubCardinalities[$objRel->Cardinality])){
						throw new Exception("Invalid Cardinality");
					}
				}

				$ShapeLinkId = dataShapeLink( $ShapeLinkId, $objShape, $objFromShapeClass, $objToShapeClass, $objRel, $Inverse, $Cardinality, $LinkEffDates);
				
				$objShape->Selection->refresh();
				$Shapes->refreshXpath();
		
				$objShapeLink = $objShape->ShapeLinks[$ShapeLinkId];
				
				$HasProperties = $Dicts->RelProperties($objRel->DictId, $objRel->Id);
				doFrmShapeProperties($objShapeLink, $HasProperties, $Fields);
				
				$ReturnUrl = "shapelink.php?shapeid=$ShapeId&shapelinkid=$ShapeLinkId";				
				
				break;
				
			case 'delete':
				
				dataShapeLinkDelete($objShape, $ShapeLinkId);
								
				break;
								
		}

		
		$Shapes->Save();
		

		unset($_SESSION['forms'][PAGE_NAME]);
		
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}
	
	
?>