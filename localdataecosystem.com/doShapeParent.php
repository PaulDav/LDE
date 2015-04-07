<?php
	require_once('function/utils.inc');
	require_once('data/dataShape.php');
	require_once('class/clsShape.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	
	define('PAGE_NAME', 'shapeparent');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	
	$ShapeId = null;
	$ParentId = null;

	try {
		
		$Shapes = new clsShapes();
	
		if (!isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
			throw new exception("shapeid not specified");
		}
		$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];
		
		if (!isset($Shapes->Items[$ShapeId])){
			throw new exception("Unknown Shape");
		}
		$objShape = $Shapes->Items[$ShapeId];

		if (!($objShape->canEdit)){
			throw new exception("You cannot update this Shape");
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
				
				if (isset($_SESSION['forms'][PAGE_NAME]['parentid'])){
					$ParentId = $_SESSION['forms'][PAGE_NAME]['parentid'];
				}
				if ( is_null($ParentId)){
					throw new exception("parentid not specified");
				}
				if (!isset($Shapes->Items[$ParentId])){
					throw new exception("Unknown Parent Shape");
				}
				$objParentShape = $Shapes->Items[$ParentId];
				if (!$objParentShape->canView){
					throw new exception("You can't use this shape");
				}
				
				break;
		}

		switch ( $Mode ){
			case "edit":
				dataShapeSetParent($ShapeId, $ParentId);
				break;
			case "delete":
				dataShapeRemoveParent($ShapeId);
				break;
		}

		$ReturnUrl = "shape.php?shapeid=$ShapeId";
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>