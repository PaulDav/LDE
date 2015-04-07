<?php
	require_once('function/utils.inc');
	require_once('data/dataData.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');

	require_once('class/clsShape.php');
	require_once('class/clsData.php');
	
	
	define('PAGE_NAME', 'setshape');
	
	session_start();
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$GroupId = null;
		$SetId = null;
		$SetShapeId = null;
		$ShapeId = null;
	
		if (isset($_SESSION['forms'][PAGE_NAME]['setshapeid'])){
			$SetShapeId = $_SESSION['forms'][PAGE_NAME]['setshapeid'];			
		}
			
		if (isset($_SESSION['forms'][PAGE_NAME]['setid'])){
			$SetId = $_SESSION['forms'][PAGE_NAME]['setid'];
		}
		if (is_null($SetId)){
			throw new exception("SetId not specified");
		}
		$objSet = new clsSet($SetId);
		if (!($objSet->canEdit)){
			throw new exception("You cannot update this Set");
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
			$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];			
		}
	
		$Shapes = new clsShapes();
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		
		switch ($Mode) {
			case 'edit':
			case 'delete':
				if (is_null($SetShapeId)){
					throw new exception("SetShapeId not specified");
				}				
				break;
			case 'new':
				break;
			default:
				throw new exception("Invalid Mode");
		}

		if (!is_null($ShapeId)){
			if (!isset($Shapes->Items[$ShapeId])){
				throw new Exception("Invalid Shape");
			}
			$objShape = $Shapes->Items[$ShapeId];
		}

		switch ( $Mode ){
			case "new":
			case "edit":				
				$SetShapeId = dataSetShapeUpdate($Mode, $SetShapeId , $SetId, $ShapeId);
				break;
			case 'delete':
				dataSetShapeDelete($SetId, $SetShapeId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "set.php?setid=$SetId";				
				break;
			default:
				$ReturnUrl = "set.php?setid=$SetId#shapes";
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