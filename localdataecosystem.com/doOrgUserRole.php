<?php
	require_once('function/utils.inc');
	require_once('data/dataRights.php');
	require_once('class/clsSystem.php');

	require_once('class/clsRights.php');
	require_once('class/clsUser.php');
	
	
	define('PAGE_NAME', 'orguserrole');
	
	session_start();
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);

	try {
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$OrgId = null;
		$OrgUserRoleId = null;
		$UserId = null;
		$RoleId = null;
		$UserEmail = null;
		$StartDate = null;
		$EndDate = null;
		
		$Orgs = new clsOrganisations();
			
		if (isset($_SESSION['forms'][PAGE_NAME]['orguserroleid'])){
			$OrgUserRoleId = $_SESSION['forms'][PAGE_NAME]['orguserroleid'];			
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
		if (isset($_SESSION['forms'][PAGE_NAME]['userid'])){
			$UserId = $_SESSION['forms'][PAGE_NAME]['userid'];
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['useremail'])){
			$UserEmail = $_SESSION['forms'][PAGE_NAME]['useremail'];
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['startdate'])){
			$StartDate = $_SESSION['forms'][PAGE_NAME]['startdate'];
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['enddate'])){
			$EndDate = $_SESSION['forms'][PAGE_NAME]['enddate'];
		}
		

		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}

		switch ($Mode) {
			case 'edit':
			case 'delete':
				if (is_null($OrgUserRoleId)){
					throw new exception("OrgUserRoleId not specified");
				}				

				if (!isset($objOrg->UserRoles[$OrgUserRoleId])){
					throw new exception("Unknown OrgUserRoleId");
				}
				
				break;
			case 'new':
				break;
			default:
				throw new exception("Invalid Mode");
		}

		
				
		switch ($Mode) {
			case 'new':
			case 'edit':
				
				if (is_null($RoleId)){
					throw new Exception("Role not specified");
				}
				if (!isset($objOrg->Roles[$RoleId])){
					throw new Exception("Invalid Role");
				}

				if (is_null($UserId)){				
					if (is_null($UserEmail)){
						throw new Exception("User Email not specified");
					}
					$UserId = getUserIdForEmail($UserEmail);
					if (!$UserId){
						throw new Exception("Invalid User Email");
					}
				}
				
				if (!is_null($StartDate)){
					$StartDate = DateTime::createFromFormat('!d/m/Y', $StartDate);
					if (!$StartDate){
						throw new Exception("Invalid Start Date");
					}					
				}
				
				if (!is_null($EndDate)){
					$EndDate = DateTime::createFromFormat('!d/m/Y', $EndDate);
					if (!$EndDate){
						throw new Exception("Invalid End Date");
					}					
				}				
				
				break;
		}

		$ReturnUrl = "organisation.php?orgid=$OrgId";

		switch ( $Mode ){
			case "new":
			case "edit":
				$OrgUserRoleId = dataOrgUserRoleUpdate($Mode, $OrgId, $OrgUserRoleId, $UserId, $RoleId, $StartDate, $EndDate);
				$ReturnUrl = "orguserrole.php?orgid=$OrgId&orguserroleid=$OrgUserRoleId";
				
				break;
			case 'delete':
				dataOrgUserRoleDelete($OrgId, $OrgUserRoleId);
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