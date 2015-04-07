<?php
	require_once('function/utils.inc');
	require_once('data/dataRights.php');
	require_once('class/clsSystem.php');

	require_once('class/clsLibrary.php');
	require_once('class/clsRights.php');
	
	
	define('PAGE_NAME', 'rolepurpose');
	
	session_start();
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$OrgId = null;
		$RoleId = null;
		$RolePurposeId = null;
		$PurposeId = null;
		
		$Orgs = new clsOrganisations();
		$Defs = new clsDefinitions();
			
		if (isset($_SESSION['forms'][PAGE_NAME]['rolepurposeid'])){
			$RolePurposeId = $_SESSION['forms'][PAGE_NAME]['rolepurposeid'];			
		}
			
		if (isset($_SESSION['forms'][PAGE_NAME]['orgid'])){
			$OrgId = $_SESSION['forms'][PAGE_NAME]['orgid'];
		}
		if (is_null($OrgId)){
			throw new exception("OrgId not specified");
		}
		$objOrg = $Orgs->getItem($OrgId);
		if (!is_object($objOrg)){
			throw new exception("Unknown Organisation");
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['roleid'])){
			$RoleId = $_SESSION['forms'][PAGE_NAME]['roleid'];
		}
		if (is_null($RoleId)){
			throw new exception("RoleId not specified");
		}
		if (!isset($objOrg->Roles[$RoleId])){
			throw new exception("Unknown Role");			
		}
		$objRole = $objOrg->Roles[$RoleId];

		if (isset($_SESSION['forms'][PAGE_NAME]['purposeid'])){
			$PurposeId = $_SESSION['forms'][PAGE_NAME]['purposeid'];			
		}
	

		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}

		switch ($Mode) {
			case 'edit':
			case 'delete':
				if (is_null($RolePurposeId)){
					throw new exception("RolePurposeId not specified");
				}				
				break;
			case 'new':
				break;
			default:
				throw new exception("Invalid Mode");
		}

		switch ($Mode) {
			case 'edit':
			case 'new':
				
				if (!is_null($PurposeId)){
					if (!isset($Defs->Items[$PurposeId])){
						throw new Exception("Invalid Purpose");
					}
					$objDef = $Defs->Items[$PurposeId];
				}				
				
				break;
		}
		
		

		switch ( $Mode ){
			case "new":
			case "edit":
				$RolePurposeId = dataRolePurposeUpdate($Mode, $OrgId, $RoleId, $RolePurposeId, $PurposeId);
				break;
			case 'delete':
				dataRolePurposeDelete($OrgId, $RoleId, $RolePurposeId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "role.php?orgid=$OrgId&roleid=$RoleId";
				break;
			default:
				$ReturnUrl = "rolepurpose.php?orgid=$OrgId&roleid=$RoleId&rolepurposeid=$RolePurposeId";				
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