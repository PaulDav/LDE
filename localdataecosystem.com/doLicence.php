<?php
	require_once('function/utils.inc');
	require_once('class/clsSystem.php');
	require_once('class/clsRights.php');
	require_once('class/clsData.php');

	require_once('data/dataLicence.php');
	
	
	define('PAGE_NAME', 'licence');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$LicenceId = null;
		$SetId = null;

		$Licences = new clsLicences();
		$Sets = new clsSets;
		
		if (isset($_SESSION['forms'][PAGE_NAME]['licenceid'])){
			$LicenceId = $_SESSION['forms'][PAGE_NAME]['licenceid'];
			$objLicence = $Licences->getItem($LicenceId);

			if (!($objLicence->canEdit)){
				throw new exception("You cannot update this Licence");
			}
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['setid'])){
			$SetId = $_SESSION['forms'][PAGE_NAME]['setid'];
			$objSet = $Sets->getItem($SetId);

			if (!is_object($objSet)){
				throw new exception("Set does not exist");
			}

			if (!($objSet->canControl)){
				throw new exception("You cannot control this Set");
			}
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
				
				if ( is_null($LicenceId)){
					throw new exception("Licence Id not specified");
				}

				$objLicence = $Licences->getItem($LicenceId);
				if (!is_object($objLicence)){
					throw new exception("Unknown Licence");
				}
				
				break;
			default:
				throw new exception("Invalid Mode");
		}


		switch ($Mode) {
			case 'new':
				
				if (is_null($SetId)){
					throw new exception("Set not specified");
				}
				break;
		}
		
		switch ($Mode){
		
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
				
		}

		$ReturnUrl = '';
		
		switch ( $Mode ){
			case 'new':
			case 'edit':
				$LicenceId = dataLicenceUpdate($Mode, $LicenceId , $Name, $Description);
				$Licences->refreshLicences();

				if ($Mode == 'new'){				
					dataLicenceAddSet($LicenceId, $SetId);
				}
				
				$ReturnUrl = "licence.php?licenceid=$LicenceId";
				
				break;
			case "delete":
				dataLicenceDelete($LicenceId);
				$ReturnUrl = ".";
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