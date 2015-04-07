<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsRights.php');
require_once(dirname(__FILE__).'/../class/clsLibrary.php');


function dataOrgUpdate($Mode, $Id = null, $Name = null, $Description = null, $URI = null, $WebSite = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a library');
	}

	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrganisations;
	}
	if (!$Orgs->canEdit){
		throw new exception("You cannot update an organisation");
	}
			
	
	$nsRights = $System->Config->Namespaces['rights'];
	
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Orgs->xpath->query("/rights:Organisations/rights:Organisation[@id]") as $xmlExistingOrg){
					$ExistingId = $xmlExistingOrg->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlOrg = $Orgs->dom->createElementNS($nsRights,"Organisation");
			$Orgs->dom->documentElement->appendChild($xmlOrg);
			$xmlOrg->setAttribute("id",$Id);
			
			$xmlOrg->setAttribute("userid",$System->User->Id);
			
			break;
		default:

			$xmlOrg = $Orgs->xpath->query("/rights:Organisations/rights:Organisation[@id='$Id']")->item(0);
			if (!is_object($xmlOrg)){
				throw new exception("Organisation does not exist");
			}
			
			break;
	}
						
	xmlSetElement($xmlOrg, "Name", $Name);
	xmlSetElement($xmlOrg, "Description", $Description);
	xmlSetElement($xmlOrg, "URI", $URI);
	xmlSetElement($xmlOrg, "WebSite", $WebSite);
	
	$Orgs->Save();

	return $Id;

}  	

function dataOrgDelete($Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update an Organisation');
	}
	
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrgs;
	}
	if (!$Orgs->canEdit){
		throw new exception("You cannot update this organisation");
	}
	
	$xmlOrg = $Orgs->xpath->query("/rights:Organisations/rights:Organisation[@id='$Id']")->item(0);
	if (!is_object($xmlOrg)){
		throw new Exception("Organisation does not exist");
	}

	$xmlOrg->parentNode->removeChild($xmlOrg);

	$Orgs->Save();
	
}


function dataRoleUpdate($Mode, $OrgId, $Id = null, $Name = null, $Description = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a role');
	}

	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrganisations;
	}
	if (!$Orgs->canEdit){
		throw new exception("You cannot update an organisation");
	}
			
	
	$nsRights = $System->Config->Namespaces['rights'];
	
	$objOrg = $Orgs->getItem($OrgId);
	if (!is_object($objOrg)){
		throw new exception("Organisation does not exist");
	}	

	$xmlRoles = $Orgs->xpath->query("./rights:Roles", $objOrg->xml)->item(0);
	if (!is_object($xmlRoles)){
		$xmlRoles = $Orgs->dom->createElementNS($nsRights,"Roles");
		$objOrg->xml->appendChild($xmlRoles);		
	}
	
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Orgs->xpath->query("rights:Role", $xmlRoles) as $xmlExistingRole){
					$ExistingId = $xmlExistingRole->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlRole = $Orgs->dom->createElementNS($nsRights,"Role");
			$xmlRoles->appendChild($xmlRole);
			$xmlRole->setAttribute("id",$Id);

			break;
		default:

			$xmlRole = $Orgs->xpath->query("./rights:Roles/rights:Role[@id='$Id']",$objOrg->xml)->item(0);
			if (!is_object($xmlRole)){
				throw new exception("Role does not exist");
			}
			
			break;
	}
						
	xmlSetElement($xmlRole, "Name", $Name);
	xmlSetElement($xmlRole, "Description", $Description);
	
	$Archive = new clsArchive();
	$Archive->setVersion($objOrg->xml);
	
	$Orgs->Save();

	return $Id;

}  	

function dataRoleDelete($OrgId, $Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update an Organisation');
	}
	
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrgs;
	}
	if (!$Orgs->canEdit){
		throw new exception("You cannot update this organisation");
	}
	
	$xmlRole = $Orgs->xpath->query("/rights:Organisations/rights:Organisation[@id='$OrgId']/rights:Roles/rights:Role[@id='$Id']")->item(0);
	if (!is_object($xmlRole)){
		throw new Exception("Role does not exist");
	}

	$xmlRole->parentNode->removeChild($xmlRole);

	$Orgs->Save();
	
}




function dataOrgUserRoleUpdate($Mode, $OrgId, $Id = null, $UserId = null, $RoleId = null, $StartDate = null, $EndDate= null){
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update an organisation');
	}

	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrganisations;
	}
	if (!$Orgs->canEdit){
		throw new exception("You cannot update an organisation");
	}
			
	
	$nsRights = $System->Config->Namespaces['rights'];
	
	$objOrg = $Orgs->getItem($OrgId);
	if (!is_object($objOrg)){
		throw new exception("Organisation does not exist");
	}
	
	If (!isset($objOrg->Roles[$RoleId])){
		throw new exception("Unknown Role");
	}
	$objRole = $objOrg->Roles[$RoleId];
	
	if (empty($UserId)){
		throw new exception("User Not Specified");
	}

	$xmlOrgUsers = $Orgs->xpath->query("./rights:Users", $objOrg->xml)->item(0);
	if (!is_object($xmlOrgUsers)){
		$xmlOrgUsers = $Orgs->dom->createElementNS($nsRights,"Users");
		$objOrg->xml->appendChild($xmlOrgUsers);		
	}
	
	$xmlOrgUser = $Orgs->xpath->query("./rights:User[@userid = $UserId]", $xmlOrgUsers)->item(0);
	if (!is_object($xmlOrgUser)){
		$xmlOrgUser = $Orgs->dom->createElementNS($nsRights,"User");
		$xmlOrgUser->setAttribute("userid",$UserId);
		$xmlOrgUsers->appendChild($xmlOrgUser);
	}
	
	$xmlUserRoles = $Orgs->xpath->query("./rights:UserRoles", $xmlOrgUser)->item(0);
	if (!is_object($xmlUserRoles)){
		$xmlUserRoles = $Orgs->dom->createElementNS($nsRights,"UserRoles");
		$xmlOrgUser->appendChild($xmlUserRoles);
	}
	
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Orgs->xpath->query(".//rights:UserRole", $xmlOrgUsers) as $xmlExistingUserRole){
					$ExistingId = $xmlExistingUserRole->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlUserRole = $Orgs->dom->createElementNS($nsRights,"UserRole");
			$xmlUserRoles->appendChild($xmlUserRole);
			$xmlUserRole->setAttribute("id",$Id);

			break;
		default:

			$xmlUserRole = $Orgs->xpath->query("./rights:UserRole[@id='$Id']",$xmlUserRoles)->item(0);
			if (!is_object($xmlUserRole)){
				throw new exception("User Role does not exist");
			}
			
			break;
	}
						
	$xmlUserRole->setAttribute("roleid",$RoleId);
	$xmlUserRole->removeAttribute("startdate");
	if (!is_null($StartDate)){
		$xmlUserRole->setAttribute("startdate",$StartDate->format('Y-m-d'));
	}
	$xmlUserRole->removeAttribute("enddate");
	if (!is_null($EndDate)){
		$xmlUserRole->setAttribute("enddate",$EndDate->format('Y-m-d'));
	}
	
	$Archive = new clsArchive();
	$Archive->setVersion($objOrg->xml);
	
	$Orgs->Save();

	return $Id;

}  	

function dataOrgUserRoleDelete($OrgId, $Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update an Organisation');
	}
	
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrgs;
	}
	if (!$Orgs->canEdit){
		throw new exception("You cannot update this organisation");
	}
	
	
	
	$xmlUserRole = $Orgs->xpath->query("/rights:Organisations/rights:Organisation[@id='$OrgId']/rights:Users//rights:User/rights:UserRoles/rights:UserRole[@id='$Id']")->item(0);
	if (!is_object($xmlUserRole)){
		throw new Exception("User Role does not exist");
	}

	$xmlUserRoles = $xmlUserRole->parentNode;
	
	$xmlUserRole->parentNode->removeChild($xmlUserRole);

	if ($xmlUserRoles->childNodes->length < 1){
		$xmlUser = $xmlUserRoles->parentNode;
		$xmlUser->parentNode->removeChild($xmlUser);
	}
	
	$Orgs->Save();
	
}

function dataRolePurposeUpdate($Mode, $OrgId, $RoleId, $Id = null, $PurposeId = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a role');
	}

	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrganisations;
	}
	if (!$Orgs->canEdit){
		throw new exception("You cannot update an organisation");
	}
			
	
	$nsRights = $System->Config->Namespaces['rights'];
	
	$objOrg = $Orgs->getItem($OrgId);
	if (!is_object($objOrg)){
		throw new exception("Organisation does not exist");
	}	

	if (!isset($objOrg->Roles[$RoleId])){
		throw new exception("Role does not exist");
	}
	$objRole = $objOrg->Roles[$RoleId];
	
	
	$xmlRolePurposes = $Orgs->xpath->query("./rights:RolePurposes", $objRole->xml)->item(0);
	if (!is_object($xmlRolePurposes)){
		$xmlRolePurposes = $Orgs->dom->createElementNS($nsRights,"RolePurposes");
		$objRole->xml->appendChild($xmlRolePurposes);		
	}
	
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Orgs->xpath->query("rights:RolePurpose", $xmlRolePurposes) as $xmlExistingRolePurpose){
					$ExistingId = $xmlExistingRolePurpose->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlRolePurpose = $Orgs->dom->createElementNS($nsRights,"RolePurpose");
			$xmlRolePurposes->appendChild($xmlRolePurpose);
			$xmlRolePurpose->setAttribute("id",$Id);

			break;
		default:

			$xmlRolePurpose = $Orgs->xpath->query("./rights:RolePurposes/rights:RolePurpose[@id='$Id']",$objRole->xml)->item(0);
			if (!is_object($xmlRolePurpose)){
				throw new exception("Role Purpose does not exist");
			}
			
			break;
	}

	$xmlRolePurpose->setAttribute('purposeid', $PurposeId);

	$Orgs->Save();

	return $Id;

}  	

function dataRolePurposeDelete($OrgId, $RoleId,  $Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update an Organisation');
	}
	
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrgs;
	}
	if (!$Orgs->canEdit){
		throw new exception("You cannot update this organisation");
	}
	
	$objOrg = $Orgs->getItem($OrgId);
	if (!is_object($objOrg)){
		throw new exception("Organisation does not exist");
	}	
	
	
	if (!isset($objOrg->Roles[$RoleId])){
		throw new exception("Role does not exist");
	}
	$objRole = $objOrg->Roles[$RoleId];
	
	
	$xmlRolePurpose = $Orgs->xpath->query("./rights:RolePurposes/rights:RolePurpose[@id='$Id']", $objRole->xml)->item(0);
	if (!is_object($xmlRolePurpose)){
		throw new Exception("Role purpose does not exist");
	}

	$xmlRolePurpose->parentNode->removeChild($xmlRolePurpose);

	$Orgs->Save();
	
}

function dataOrgDefUpdate($Mode, $Id = null, $OrgId = nul, $DefId = null, $DateFrom = null, $DateTo = null, $URL = null, $Reference = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update an organisation');
	}

	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrganisations;
	}

	
	global $Defs;
	if (!isset($Defs)){
		$Defs = new clsDefinitions();
	}
	
	$nsRights = $System->Config->Namespaces['rights'];
	
	
	if (is_null($OrgId)){
		throw new Exception("Org Id not specified");
	}
	if (!isset($Orgs->Items[$OrgId])){
		throw new Exception("Unknown Organisation");
	}
	$objOrg = $Orgs->Items[$OrgId];
	if (!$objOrg->canEdit){
		throw new exception("You cannot update this organisation");
	}

	
	
	if (is_null($DefId)){
		throw new Exception("Def Id not specified");
	}
	if (!isset($Defs->Items[$DefId])){
		throw new Exception("Unknown Definition");
	}
	$objDef = $Defs->Items[$DefId];
	
	
	$xmlHasDefs = $Orgs->xpath->query("rights:HasDefs",$objOrg->xml)->item(0);
	if (!is_object($xmlHasDefs)){
		$xmlHasDefs = $Orgs->dom->createElementNS($nsRights,"HasDefs");
		$objOrg->xml->appendChild($xmlHasDefs);		
	}
	
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Orgs->xpath->query("rights:HasDef[@id]",$xmlHasDefs) as $xmlExistingDef){
					$ExistingId = $xmlExistingDef->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlHasDef = $Orgs->dom->createElementNS($nsRights,"HasDef");
			$xmlHasDefs->appendChild($xmlHasDef);
			$xmlHasDef->setAttribute("id",$Id);
			
			$xmlHasDef->setAttribute("userid",$System->User->Id);
			
			break;
		default:

			$xmlHasDef = $Orgs->xpath->query("rights:HasDef[@id='$Id']",$xmlHasDefs)->item(0);
			if (!is_object($xmlHasDef)){
				throw new exception("Has Def does not exist");
			}
			
			break;
	}

	$xmlHasDef->setAttribute("defid",$DefId);
	$xmlHasDef->setAttribute("deftypeid",$objDef->TypeId);
	
	$xmlHasDef->removeAttribute("dateFrom");
	if (!is_null($DateFrom)){
		if (!($inDate = DateTime::createFromFormat('d/m/Y', $DateFrom))){
			throw "Invalid From Date";
		}
		$xmlHasDef->setAttribute('dateFrom',$inDate->format('Y-m-d'));
	}

	$xmlHasDef->removeAttribute("dateTo");
	if (!is_null($DateTo)){
		if (!($inDate = DateTime::createFromFormat('d/m/Y', $DateTo))){
			throw "Invalid To Date";
		}
		$xmlHasDef->setAttribute('dateTo',$inDate->format('Y-m-d'));
	}
	
	
	xmlSetElement($xmlHasDef, "URL", $URL);
	xmlSetElement($xmlHasDef, "Reference", $Reference);
	
	$Orgs->Save();

	return $Id;

}  	

function dataOrgDefDelete($OrgId = null,$Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update an Organisation');
	}
	
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrgs;
	}
	
	if (!isset($Orgs->Items[$OrgId])){
		throw new exception("Unknown Organisation");
	}
	$objOrg = $Orgs->Items[$OrgId];
	
	
	if (!$objOrg->canEdit){
		throw new exception("You cannot update this organisation");
	}
	
	$query = "/rights:Organisations/rights:Organisation[@id='$OrgId']/rights:HasDefs/rights:HasDef[@id=$Id]";
	$xmlOrgDef = $Orgs->xpath->query($query)->item(0);
	if (!is_object($xmlOrgDef)){
		throw new Exception("Organisation Definition does not exist");
	}

	$xmlOrgDef->parentNode->removeChild($xmlOrgDef);

	$Orgs->Save();
	
}



?>