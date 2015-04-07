<?php
	require_once('function/utils.inc');
	require_once('class/clsSystem.php');
	require_once('class/clsRights.php');

	require_once('data/dataRights.php');	
	
	define('PAGE_NAME', 'role');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$RoleId = null;
		$OrgId = null;
		
		$objOrg = null;
		$objRole = null;

		$Orgs = new clsOrganisations();
		
		if (isset($_SESSION['forms'][PAGE_NAME]['orgid'])){
			$OrgId = $_SESSION['forms'][PAGE_NAME]['orgid'];
			$objOrg = $Orgs->getItem($OrgId);
			if (!is_object($objOrg)){
				throw new exception("Organisation does not exist");
			}

			if (!($objOrg->canControl)){
				throw new exception("You cannot update this Organisation");
			}
		}
		if (is_null($OrgId)){
			throw new exception("OrgId not specified");
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['roleid'])){
			$RoleId = $_SESSION['forms'][PAGE_NAME]['roleid'];
			if (!isset($objOrg->Roles[$RoleId])){
				throw new exception("Role does not exist");
			}
			$objRole = $objOrg->Roles[$RoleId];
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
				
				if ( is_null($RoleId)){
					throw new exception("Role Id not specified");
				}
				
				break;
			default:
				throw new exception("Invalid Mode");
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
				$RoleId = dataRoleUpdate($Mode, $OrgId, $RoleId, $Name, $Description);
				$ReturnUrl = "role.php?orgid=$OrgId&roleid=$RoleId";
				
				break;
			case "delete":
				dataRoleDelete($OrgId, $RoleId);
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