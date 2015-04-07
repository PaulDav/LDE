<?php
	require_once('function/utils.inc');
	require_once('data/dataLibrary.php');
	require_once('class/clsSystem.php');
	require_once('class/clsLibrary.php');
	
	define('PAGE_NAME', 'definition');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$DefId = null;

	if (isset($_SESSION['forms'][PAGE_NAME]['defid'])){
		$DefId = $_SESSION['forms'][PAGE_NAME]['defid'];
	}
	
	$objSources = new clsSources();
	$objDefs = new clsDefinitions();
	
	try {

		if (!$objDefs->canEdit){
			throw new exception("You cannot update definitions");
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
				if ( is_null($DefId)){
					throw new exception("defid not specified");
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

				$TypeId = null;
				$Name = null;
				$Description = '';
				$SourceId = null;
				$URL = '';

				
				if (isset($_SESSION['forms'][PAGE_NAME]['typeid'])){
					$TypeId = $_SESSION['forms'][PAGE_NAME]['typeid'];			
				}
				if ( IsEmptyString($TypeId)){
					throw new exception("TypeId not specified");
				}
				if (!isset($System->Config->DefTypes[$TypeId])){
					throw new exception("Invalid TypeId");
				}

				
				if (isset($_SESSION['forms'][PAGE_NAME]['sourceid'])){
					$SourceId = $_SESSION['forms'][PAGE_NAME]['sourceid'];			
				}
				if ( IsEmptyString($SourceId)){
//					throw new exception("SourceId not specified");
				}
				elseif (!isset($objSources->Items[$SourceId])){
					throw new exception("Unknown Source");
				}
				
				if (isset($_SESSION['forms'][PAGE_NAME]['name'])){
					$Name = $_SESSION['forms'][PAGE_NAME]['name'];			
				}
				if ( IsEmptyString($Name)){
					throw new exception("Name not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['url'])){
					$URL = $_SESSION['forms'][PAGE_NAME]['url'];
				}		

				break;
		}
		

		switch ( $Mode ){
			case "new":
			case "edit":	
				$DefId = dataDefUpdate($Mode, $DefId, $TypeId, $SourceId, $Name, $Description, $URL);
				break;
			case "delete":
				dataDefDelete($DefId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "library.php";
				break;
			default:
				$ReturnUrl = "definition.php?defid=$DefId";
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