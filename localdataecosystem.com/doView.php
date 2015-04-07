<?php
	require_once('function/utils.inc');
	require_once('data/dataView.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsView.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'view');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	$ViewId = '';

	if (isset($_SESSION['forms'][PAGE_NAME]['groupid'])){
		$GroupId = $_SESSION['forms'][PAGE_NAME]['groupid'];
		$objGroup = new clsGroup($GroupId);
		if (!($objGroup->canEdit)){
			throw new exception("You cannot update this Group");
		}		
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['viewid'])){
		$ViewId = $_SESSION['forms'][PAGE_NAME]['viewid'];			
		$objView = new clsView($ViewId);
		if (!($objView->canEdit)){
			throw new exception("You cannot update this View");
		}
	}
	
	try {
			
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
				
				if ( $ViewId == ''){
					throw new exception("View not specified");
				}
				
					break;
			default:
				throw new exception("Invalid Mode");
		}

		
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
		
		switch ( $Mode ){
			case "new":
			case "edit":				
				if ( $ViewId == ''){
					$ViewId = clean(strtolower($Name));
				}
				$ViewId = dataViewUpdate($Mode, $ViewId , $GroupId, $Name, $Description, $Publish);
				break;
		}

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: view.php?viewid=$ViewId");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>