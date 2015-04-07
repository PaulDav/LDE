<?php
	require_once('function/utils.inc');
	require_once('data/dataData.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');

	require_once('class/clsLibrary.php');
	require_once('class/clsData.php');
	
	
	define('PAGE_NAME', 'setpurpose');
	
	session_start();
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$SetId = null;
		$SetPurposeId = null;
		$PurposeId = null;
	
		if (isset($_SESSION['forms'][PAGE_NAME]['setpurposeid'])){
			$SetPurposeId = $_SESSION['forms'][PAGE_NAME]['setpurposeid'];			
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
		
		if (isset($_SESSION['forms'][PAGE_NAME]['purposeid'])){
			$PurposeId = $_SESSION['forms'][PAGE_NAME]['purposeid'];			
		}
	
		$Defs = new clsDefinitions();
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}

		switch ($Mode) {
			case 'edit':
			case 'delete':
				if (is_null($SetPurposeId)){
					throw new exception("SetPurposeId not specified");
				}				
				break;
			case 'new':
				break;
			default:
				throw new exception("Invalid Mode");
		}

		if (!is_null($PurposeId)){
			if (!isset($Defs->Items[$PurposeId])){
				throw new Exception("Invalid Purpose");
			}
			$objDef = $Defs->Items[$PurposeId];
		}

		switch ( $Mode ){
			case "new":
			case "edit":
				$SetPurposeId = dataSetPurposeUpdate($Mode, $SetPurposeId , $SetId, $PurposeId);
				break;
			case 'delete':
				dataSetPurposeDelete($SetId, $SetPurposeId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "set.php?setid=$SetId";				
				break;
			default:
				$ReturnUrl = "set.php?setid=$SetId#purposes";
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