<?php
	require_once('function/utils.inc');
	require_once('data/dataRights.php');
	require_once('class/clsSystem.php');
	require_once('class/clsLibrary.php');
	require_once('class/clsRights.php');
	
	define('PAGE_NAME', 'orgdef');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	try {
	
	
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$OrgDefId = null;
		$OrgId = null;
		$DefId = null;
		
		$objOrg = NULL;
		$objOrgDef = null;

		$Defs = new clsDefinitions();
		$Orgs = new clsOrganisations();
				
		if (!isset($_SESSION['forms'][PAGE_NAME]['orgid'])){
			throw new exception("OrgId Not Specified");
		}
		$OrgId = $_SESSION['forms'][PAGE_NAME]['orgid'];
		if (!isset($Orgs->Items[$OrgId])){
			throw new Exception("Unknown Organisation $OrgId");
		}
		$objOrg = $Orgs->Items[$OrgId];

		if (isset($_SESSION['forms'][PAGE_NAME]['orgdefid'])){
			$OrgDefId = $_SESSION['forms'][PAGE_NAME]['orgdefid'];
		}
		
			
		if (!$objOrg->canEdit){
			throw new exception("You cannot update thids organisation");
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
				if ( is_null($OrgDefId)){
					throw new exception("orgdefid not specified");
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

				$DefId = null;
				$URL = null;
				$Reference = null;
				$DateFrom = null;
				$DateTo = null;
				

				if (!isset($_SESSION['forms'][PAGE_NAME]['defid'])){
					echo 'Definition not specified';
				}
				$DefId = $_SESSION['forms'][PAGE_NAME]['defid'];
				if (!isset($Defs->Items[$DefId])){
					echo 'unknown Definition';
				}
				
				if (isset($_SESSION['forms'][PAGE_NAME]['url'])){
					$URL = $_SESSION['forms'][PAGE_NAME]['url'];
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['reference'])){
					$Reference = $_SESSION['forms'][PAGE_NAME]['reference'];
				}
				
				if (isset($_SESSION['forms'][PAGE_NAME]['datefrom'])){
					$DateFrom = $_SESSION['forms'][PAGE_NAME]['datefrom'];
					if (!(DateTime::createFromFormat('d/m/Y', $DateFrom))){
						throw new exception ("Invalid From Date");
					}
				}
				
				if (isset($_SESSION['forms'][PAGE_NAME]['dateto'])){
					$DateTo = $_SESSION['forms'][PAGE_NAME]['dateto'];
					if (!(DateTime::createFromFormat('d/m/Y', $DateTo))){
						throw new exception ("Invalid To Date");
					}
				}
				
				break;
		}
		

		switch ( $Mode ){
			case "new":
			case "edit":	
				$OrgDefId = dataOrgDefUpdate($Mode, $OrgDefId, $OrgId,  $DefId, $DateFrom, $DateTo, $URL, $Reference);
				break;
			case "delete":
				dataOrgDefDelete($OrgId, $OrgDefId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "organisation.php?orgid=$OrgId";
				break;
			default:
				$ReturnUrl = "orgdef.php?orgid=$OrgId&orgdefid=$OrgDefId";
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