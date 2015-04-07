<?php
	require_once('function/utils.inc');
	require_once('data/dataLicence.php');
	require_once('class/clsSystem.php');

	require_once('class/clsRights.php');
	
	
	define('PAGE_NAME', 'licenceorg');
	
	session_start();
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$Licences = new clsLicences();
		$Orgs = new clsOrganisations();
		
		$LicenceId = null;
		$OrgId = null;
	
		
		if (isset($_SESSION['forms'][PAGE_NAME]['licenceid'])){
			$LicenceId = $_SESSION['forms'][PAGE_NAME]['licenceid'];
		}
		if (is_null($LicenceId)){
			throw new exception("LicenceId not specified");
		}
		$objLicence = $Licences->getItem($LicenceId);
		if (!is_object($objLicence)){
			throw new exception("Unknown Licence");
		}
		
		if (!$objLicence->canEdit){
			throw new exception("You cannot update this Licence");
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['orgid'])){
			$OrgId = $_SESSION['forms'][PAGE_NAME]['orgid'];			
		}
		if (is_null($OrgId)){
			throw new exception("OrgId not specified");
		}
				
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}

		switch ($Mode) {
			case 'delete':
				break;
			case 'new':
				if (!isset($Orgs->Items[$OrgId])){
					throw new exception("Unknown Organisation");
				}
				
				break;
			default:
				throw new exception("Invalid Mode");
		}

		switch ( $Mode ){
			case "new":
				dataLicenceOrgAdd($LicenceId, $OrgId);
				break;
			case 'delete':
				dataLicenceOrgDelete($LicenceId, $OrgId);
				break;
		}

		$ReturnUrl = "licence.php?licenceid=$LicenceId#orgs";				
		
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
		
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>