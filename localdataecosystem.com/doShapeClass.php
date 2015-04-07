<?php
	require_once('function/utils.inc');
	require_once('data/dataShape.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsShape.php');
	require_once('class/clsDict.php');
	
	require_once('form/doFrmShapeProperties.php');
	
	define('PAGE_NAME', 'shapeclass');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = null;
	$ShapeId = null;
	$ShapeClassId = null;
	$ShapeLinkId = null;

	$objShape = null;
	$objGroup = null;
	$objShapeClass = null;
	$objShapeLink = null;
	
	$Create = false;
	$Select = false;
	$Match = false;
	
	$Mode = 'edit';
	if (isset($_REQUEST['mode'])){
		$Mode = $_REQUEST['mode'];			
	}
	
	
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

		if (isset($_SESSION['forms'][PAGE_NAME]['shapeclassid'])){
			$ShapeClassId = $_SESSION['forms'][PAGE_NAME]['shapeclassid'];
			if (!isset($objShape->ShapeClasses[$ShapeClassId])){
				throw new exception("Unknown Shape Class $ShapeClassId");
			}
			$objShapeClass = $objShape->ShapeClasses[$ShapeClassId];
		}
		
		
		$Fields = $_SESSION['forms'][PAGE_NAME];

		

		switch ( $Mode ){
			case "new":
				break;
			case "edit":
			case 'delete':
				if (is_null($ShapeClassId)){
					throw new exception("Shape Class Id not specified");
				}
				break;
		}

		switch ( $Mode ){
			case "new":
			case "edit":
		
		
				$ClassDictId = null;
				$ClassId = null;
				
				if (isset($Fields['classdictid'])){
					$ClassDictId = $Fields['classdictid'];
				}
				if (isset($Fields['classid'])){
					$ClassId = $Fields['classid'];
				}
				if (is_null($ClassDictId)){
					return;
				}
				if (is_null($ClassDictId)){
					return;
				}
				$objClass = $Dicts->getClass($ClassDictId, $ClassId);
				if (!is_object($objClass)){
					throw new exception("Unknown Class");
				}
		
				
				if (isset($Fields['create'])){
					switch ($Fields['create']){
						case 'selected':
							$Create = true;
							break;
					}
				}
				if (isset($Fields['select'])){
					switch ($Fields['select']){
						case 'selected':
							$Select = true;
							break;
					}
				}
				if (isset($Fields['match'])){
					switch ($Fields['match']){
						case 'selected':
							$Match = true;
							break;
					}
				}
				
				
				$ShapeClassId = dataShapeClass($ShapeClassId, $objShape, $objClass, $Create, $Select, $Match);
				$objShape->Selection->refresh();
				$Shapes->refreshXpath();
				
				$objShapeClass = $objShape->ShapeClasses[$ShapeClassId];
			
				
				$HasProperties = $Dicts->ClassProperties($objClass->DictId, $objClass->Id);
				doFrmShapeProperties($objShapeClass, $HasProperties, $Fields);
				
				break;
				
			case 'delete':
				
				dataShapeClassDelete($objShape, $ShapeClassId);								
				break;
				
				
		}
				
		$Shapes->Save();
		

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: shape.php?shapeid=$ShapeId");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}
	
	
	

?>