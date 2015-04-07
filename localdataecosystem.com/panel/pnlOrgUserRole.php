<?php

require_once(dirname(__FILE__).'/../class/clsRights.php');
require_once(dirname(__FILE__).'/../function/utils.inc');
require_once('pnlRole.php');
require_once('pnlUser.php');

Function pnlOrgUserRole( $OrgId, $UserRoleId){
	
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrgs();
	}
	
	if (!isset($Orgs->Items[$OrgId])){
		throw new Exception("Unknown Organisation");
	}
	$objOrg = $Orgs->Items[$OrgId];

	if (!isset($objOrg->UserRoles[$UserRoleId])){
		throw new Exception("Unknown User Role");
	}
	$objUserRole = $objOrg->UserRoles[$UserRoleId];
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='orguserrole.php?orgid=$OrgId&orguserroleid=$UserRoleId'>".$UserRoleId."</a></td></tr>";
	$Content .= "<tr><th>User</th><td>".pnlUser($objUserRole->UserId)."</td></tr>";
	$Content .= "<tr><th>Role</th><td>".pnlRole($OrgId, $objUserRole->RoleId)."</td></tr>";
	if (!is_null($objUserRole->StartDate)){
		$Content .= "<tr><th>Start Date</th><td>".$objUserRole->StartDate->format('d/m/Y')."</td></tr>";
	}
	if (!is_null($objUserRole->EndDate)){
		$Content .= "<tr><th>End Date</th><td>".$objUserRole->EndDate->format('d/m/Y')."</td></tr>";
	}
	
    $Content .= '</table>';
	    
    return $Content;
}


?>