<?php
	require_once('function/utils.inc');
	require_once('data/dataLicence.php');
	require_once('class/clsSystem.php');

	require_once('class/clsRights.php');
	require_once('class/clsLibrary.php');
	
	
	define('PAGE_NAME', 'licencedef');
	
	session_start();
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);

	
	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$Licences = new clsLicences();
		$Defs = new clsDefinitions();
		
		$LicenceId = null;
		$DefId = null;
	
		
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
		
		if (isset($_SESSION['forms'][PAGE_NAME]['defid'])){
			$DefId = $_SESSION['forms'][PAGE_NAME]['defid'];
		}
		if (is_null($DefId)){
			throw new exception("DefId not specified");
		}
				
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}

		switch ($Mode) {
			case 'delete':
				break;
			case 'new':
				if (!isset($Defs->Items[$DefId])){
					throw new exception("Unknown Definition");
				}
				
				break;
			default:
				throw new exception("Invalid Mode");
		}

		switch ( $Mode ){
			case "new":
				dataLicenceDefAdd($LicenceId, $DefId);
				break;
			case 'delete':
				dataLicenceDefDelete($LicenceId, $DefId);
				break;
		}

		$ReturnUrl = "licence.php?licenceid=$LicenceId";
		
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
		
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>