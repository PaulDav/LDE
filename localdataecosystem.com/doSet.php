<?php
	require_once('function/utils.inc');
	require_once('data/dataData.php');
	require_once('class/clsSystem.php');
	require_once('class/clsRights.php');

	require_once('class/clsData.php');
	
	define('PAGE_NAME', 'set');
	
	session_start();
	$System = new clsSystem();
	$Orgs = new clsOrganisations;
	
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$OrgId = '';
	$SetId = '';
//	$EffAt = '';
//	$EffFrom = '';
//	$EffTo = '';

	try {
	
	
		if (isset($_SESSION['forms'][PAGE_NAME]['setid'])){
			$SetId = $_SESSION['forms'][PAGE_NAME]['setid'];			
			$objSet = new clsSet($SetId);
			if (!($objSet->canEdit)){
				throw new exception("You cannot update this Set");
			}
			$OrgId = $objSet->OrgId;
	//		$EffAt = $objSet->Effective->At;
	//		$EffFrom = $objSet->Effective->From;
	//		$EffTo = $objSet->Effective->To;
					
		}
	
		if (isset($_SESSION['forms'][PAGE_NAME]['orgid'])){
			$OrgId = $_SESSION['forms'][PAGE_NAME]['orgid'];
			if (!isset($Orgs->Items[$OrgId])){
				throw new exception("Unknown Organisation");
			}
			$objOrg = $Orgs->Items[$OrgId];
			if (!($objOrg->canEdit)){
				throw new exception("You cannot update this Organisation");
			}		
		}
		
	
			
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				if ( $OrgId == ''){
					throw new exception("Organisation not specified");
				}
				
				break;
			case 'edit':
			case 'clear':				
			case 'delete':
				
				if ( $SetId == ''){
					throw new exception("Set not specified");
				}
				
					break;
			default:
				throw new exception("Invalid Mode");
		}

		switch ($Mode){
			case 'delete':
			case 'clear':
				break;
			default:
				$Name = null;
				if (isset($_SESSION['forms'][PAGE_NAME]['name'])){
					$Name = $_SESSION['forms'][PAGE_NAME]['name'];			
				}
				if ( empty($Name)){
					throw new exception("Name not specified");
				}
				
				$Source = null;
				if (isset($_SESSION['forms'][PAGE_NAME]['source'])){
					$Source = $_SESSION['forms'][PAGE_NAME]['source'];		
				}		
				
				$Status = 1;
				if (isset($_SESSION['forms'][PAGE_NAME]['status'])){
					$Status = $_SESSION['forms'][PAGE_NAME]['status'];
				}				
				if (!isset($System->Config->SetStatusTypes[$Status])){
					throw new exception("Invalid Status");
				}

				
				$Context = 10;
				if (isset($_SESSION['forms'][PAGE_NAME]['context'])){
					$Context = $_SESSION['forms'][PAGE_NAME]['context'];
				}				
				if (!isset($System->Config->SetContextTypes[$Context])){
					throw new exception("Invalid Context");
				}
				
				$LicenceType = 10;
				if (isset($_SESSION['forms'][PAGE_NAME]['licencetype'])){
					$LicenceType = $_SESSION['forms'][PAGE_NAME]['licencetype'];
				}				
				if (!isset($System->Config->SetLicenceTypeTypes[$LicenceType])){
					throw new exception("Invalid Licence Type");
				}
				
				
				break;
				
		}
		

		switch ( $Mode ){
			case "new":
			case "edit":				
				$SetId = dataSetUpdate($Mode, $SetId , $OrgId, $Name, $Source, $Status, $Context, $LicenceType);
				break;
			case 'delete':
				dataSetDelete($SetId);
				break;				
			case 'clear':
				dataSetClear($SetId);
				break;				
				
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "organisation.php?orgid=$OrgId";
				break;
			default:
				$ReturnUrl = "set.php?setid=$SetId";		
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