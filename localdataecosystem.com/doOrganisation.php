<?php
	require_once('function/utils.inc');
	require_once('data/dataRights.php');
	require_once('class/clsSystem.php');
	require_once('class/clsRights.php');
	require_once('class/clsLibrary.php');
	
	define('PAGE_NAME', 'organisation');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$OrgId = null;

	if (isset($_SESSION['forms'][PAGE_NAME]['orgid'])){
		$OrgId = $_SESSION['forms'][PAGE_NAME]['orgid'];
	}
	
	$Orgs = new clsOrganisations();

	try {

		if (!$Orgs->canEdit){
			throw new exception("You cannot update organisations");
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
				if ( is_null($OrgId)){
					throw new exception("orgid not specified");
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
				$Description = null;
				$WebSite = null;
				$URI = null;

				if (isset($_SESSION['forms'][PAGE_NAME]['name'])){
					$Name = $_SESSION['forms'][PAGE_NAME]['name'];			
				}
				if ( IsEmptyString($Name)){
					throw new exception("Name not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['uri'])){
					$URI = $_SESSION['forms'][PAGE_NAME]['uri'];
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['website'])){
					$WebSite = $_SESSION['forms'][PAGE_NAME]['website'];
				}		

				break;
		}
		

		switch ( $Mode ){
			case "new":
			case "edit":	
				$OrgId = dataOrgUpdate($Mode, $OrgId, $Name, $Description, $URI, $WebSite);
				break;
			case "delete":
				dataOrgDelete($OrgId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "organisations.php";
				break;
			default:
				$ReturnUrl = "organisation.php?orgid=$OrgId";
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