<?php
	require_once('function/utils.inc');
	require_once('data/dataShape.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsShape.php');
	
	define('PAGE_NAME', 'shape');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$GroupId = null;
		$ShapeId = null;

		$Shapes = new clsShapes;
		
		if (isset($_SESSION['forms'][PAGE_NAME]['groupid'])){
			$GroupId = $_SESSION['forms'][PAGE_NAME]['groupid'];
			$objGroup = new clsGroup($GroupId);
			if (!($objGroup->canEdit)){
				throw new exception("You cannot update this Group");
			}		
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
			$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];
			
			if (!isset($Shapes->Items[$ShapeId])){
				throw new exception("Unknown Shape");
			}
			$objShape = $Shapes->Items[$ShapeId];
			if (!($objShape->canEdit)){
				throw new exception("You cannot update this Shape");
			}
		}
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				if ( $GroupId == ''){
					throw new exception("Group not specified");
				}
				
				break;
			case 'edit':
			case 'delete':
				
				if ( $ShapeId == ''){
					throw new exception("Shape not specified");
				}

				if (!isset($Shapes->Items[$ShapeId])){
					throw new exception("Unknown Shape");
				}
				$objShape = $Shapes->Items[$ShapeId];
				$GroupId = $objShape->GroupId;
				
				break;
			default:
				throw new exception("Invalid Mode");
		}


		switch ($Mode) {
			case 'new':
			case 'edit':
		
				$Name = "";
				if (isset($_SESSION['forms'][PAGE_NAME]['name'])){
					$Name = $_SESSION['forms'][PAGE_NAME]['name'];			
				}
				if ( $Name==''){
					throw new exception("Name not specified");
				}
				
				$Description = "";
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		
				
				$Publish = false;
				if (isset($_SESSION['forms'][PAGE_NAME]['publish'])){
					if ($_SESSION['forms'][PAGE_NAME]['publish'] == "Yes"){
						$Publish = true;
					}
				}		
				break;
		}

		$ReturnUrl = '';
		
		switch ( $Mode ){
			case 'new':
			case 'edit':
				$ShapeId = dataShapeUpdate($Mode, $ShapeId , $GroupId, $Name, $Description, $Publish);
				$ReturnUrl = "shape.php?shapeid=$ShapeId";				
				break;
			case "delete":
				dataShapeDelete($ShapeId);
				$ReturnUrl = "group.php?groupid=$GroupId";
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