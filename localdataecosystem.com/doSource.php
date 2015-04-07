<?php
	require_once('function/utils.inc');
	require_once('data/dataLibrary.php');
	require_once('class/clsSystem.php');
	require_once('class/clsLibrary.php');
	
	define('PAGE_NAME', 'source');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$SourceId = null;

	if (isset($_SESSION['forms'][PAGE_NAME]['sourceid'])){
		$SourceId = $_SESSION['forms'][PAGE_NAME]['sourceid'];
	}
	
	$Sources = new clsSources();

	try {

		if (!$Sources->canEdit){
			throw new exception("You cannot update sources");
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
				if ( is_null($SourceId)){
					throw new exception("sourceid not specified");
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

				$Name = null;
				$Description = '';
				$URL = '';

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
				$SourceId = dataSourceUpdate($Mode, $SourceId, $Name, $Description, $URL);
				break;
			case "delete":
				dataSourceDelete($SourceId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "library.php";
				break;
			default:
				$ReturnUrl = "source.php?sourceid=$SourceId";
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