<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsRights.php');
require_once(dirname(__FILE__).'/../class/clsArchive.php');

require_once(dirname(__FILE__).'/../class/clsData.php');
require_once(dirname(__FILE__).'/../class/clsShape.php');


function dataLicenceUpdate($Mode, $Id = null, $Name = null, $Description = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a licence');
	}
	
	$nsLde = $System->Config->Namespaces['lde'];
	$nsRights = $System->Config->Namespaces['rights'];
	
	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicences;
	}
	if (!$Licences->canEdit){
		throw new exception("You cannot update Licences");
	}

	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Licences->xpath->query("/rights:Licences/rights:Licence[@id]") as $xmlExistingLicence){
					$ExistingId = $xmlExistingLicence->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlLicence = $Licences->dom->createElementNS($nsRights,"Licence");
			$Licences->dom->documentElement->appendChild($xmlLicence);
			$xmlLicence->setAttribute("id",$Id);
			
			$xmlLicence->setAttribute("ownerid",$System->User->Id);
			
			break;
		default:

			$objLicence = $Licences->getItem($Id);
			if (!is_object($objLicence)){
				throw new Exception("unknown licence");
			}
			
			if (!$objLicence->canControl){
				throw new Exception("You cannot Update this Licence");
			}

			$xmlLicence = $objLicence->xml;

			break;
	}

	xmlSetElement($xmlLicence, "Name", $Name);
	xmlSetElement($xmlLicence, "Description", $Description);

	$Archive = new clsArchive();
	$Archive->setVersion($xmlLicence);
	
	$Licences->Save();
	
	return $Id;

}  	

function dataLicenceDelete($Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a licence');
	}
	
	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicences;
	}
	if (!$Licences->canEdit){
		throw new exception("You cannot update Licences");
	}
		
	$objLicence = $Licences->getItem($Id);
	if (!is_object($objLicence)){
		throw new Exception("unknown licence");
	}
	
	if (!$objLicence->canControl){
		throw new Exception("You cannot Update this Licence");
	}

	$objLicence->xml->parentNode->removeChild($objLicence->xml);

	$Licences->Save();
	
}


function dataLicenceAddSet($LicenceId = null, $SetId = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$nsRights = $System->Config->Namespaces['rights'];
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a licence');
	}

	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicences();
	}
	
	global $Sets;
	if (!isset($Sets)){
		$Sets = new clsSets;
	}

	$objLicence = $Licences->getItem($LicenceId);
	if (!is_object($objLicence)){
		throw new Exception("unknown licence");
	}
	
	if (!$objLicence->canControl){
		throw new Exception("You cannot Update this Licence");
	}

	$xmlLicence = $objLicence->xml;
	
	$objSet = $Sets->getItem($SetId);

	if (!is_object($objSet)){
		throw new exception("Set does not exist");
	}

	if (!($objSet->canControl)){
		throw new exception("You cannot control this Set");
	}
	
	$xmlLicenceSets = $Licences->xpath->query("rights:Sets",$objLicence->xml)->Item(0);
	if (!is_object($xmlLicenceSets)){
		$xmlLicenceSets = $Licences->dom->createElementNS($nsRights,"Sets");
		$objLicence->xml->appendChild($xmlLicenceSets);
	}
	
	$xmlSet = $Licences->xpath->query("rights:Set[@id = $SetId]",$xmlLicenceSets)->Item(0);
	if (is_object($xmlSet)){
		throw new Exception("Set is already on this Licence");
	}
	
	$xmlSet = $Licences->dom->createElementNS($nsRights,"Set");
	$xmlSet->setAttribute('id', $SetId);
	$xmlLicenceSets->appendChild($xmlSet);
	
	$Archive = new clsArchive();
	$Archive->setVersion($xmlLicence);

	$Licences->Save();
	
}

function dataLicenceDeleteSet($LicenceId = null, $SetId = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$nsRights = $System->Config->Namespaces['rights'];
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a licence');
	}

	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicences;
	}
	
	global $Sets;
	if (!isset($Sets)){
		$Sets = new clsSets;
	}

	$objLicence = $Licences->getItem($LicenceId);
	if (!is_object($objLicence)){
		throw new Exception("unknown licence");
	}
	
	if (!$objLicence->canControl){
		throw new Exception("You cannot Update this Licence");
	}

	$xmlLicence = $objLicence->xml;
	
	$objSet = $Sets->getItem($SetId);

	if (!($objSet->canControl)){
		throw new exception("You cannot control this Set");
	}
		
	$xmlSet = $Licences->xpath->query("rights:Sets/rights:Set[@id = $SetId]",$objLicence->xml)->Item(0);
	if (is_object($xmlSet)){
		$xmlSet->parentNode->removeChild($xmlSet);		
	}
	
	
	$Archive = new clsArchive();
	$Archive->setVersion($xmlLicence);

	$Licences->Save();
	
}


function dataLicenceOrgAdd($LicenceId, $OrgId){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a licence');
	}
	
	$nsLde = $System->Config->Namespaces['lde'];
	$nsRights = $System->Config->Namespaces['rights'];
	
	
	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicence();
	}
	
	$objLicence = $Licences->getItem($LicenceId);
	if (!is_object($objLicence)){
		throw new exception("unknown Licence");
	}
	
	if (!$objLicence->canEdit){
		throw new exception("you cannot update this licence");
	}
	
	$xmlLicence = $objLicence->xml;
		
	$xmlLicenceOrgs = $Licences->xpath->query("rights:Organisations",$objLicence->xml)->Item(0);
	if (!is_object($xmlLicenceOrgs)){
		$xmlLicenceOrgs = $Licences->dom->createElementNS($nsRights,"Organisations");
		$objLicence->xml->appendChild($xmlLicenceOrgs);
	}
	
	$xmlOrg = $Licences->xpath->query("rights:Organisation[@id = $OrgId]",$xmlLicenceOrgs)->Item(0);
	if (is_object($xmlOrg)){
		throw new Exception("Organisation is already on this Licence");
	}
	
	$xmlOrg = $Licences->dom->createElementNS($nsRights,"Organisation");
	$xmlOrg->setAttribute('id', $OrgId);
	$xmlLicenceOrgs->appendChild($xmlOrg);
	
	$Archive = new clsArchive();
	$Archive->setVersion($xmlLicence);

	$Licences->Save();
		
}

function dataLicenceOrgDelete($LicenceId = null, $OrgId = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$nsRights = $System->Config->Namespaces['rights'];
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a licence');
	}

	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicences;
	}
	
	$objLicence = $Licences->getItem($LicenceId);
	if (!is_object($objLicence)){
		throw new Exception("unknown licence");
	}
	
	if (!$objLicence->canControl){
		throw new Exception("You cannot Update this Licence");
	}

	$xmlLicence = $objLicence->xml;
			
	$xmlOrg = $Licences->xpath->query("rights:Organisations/rights:Organisation[@id = $OrgId]",$objLicence->xml)->Item(0);
	if (is_object($xmlOrg)){
		$xmlOrg->parentNode->removeChild($xmlOrg);		
	}
	
	$Archive = new clsArchive();
	$Archive->setVersion($xmlLicence);

	$Licences->Save();
	
}



function dataLicenceDefAdd($LicenceId, $DefId){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a licence');
	}
	
	$nsLde = $System->Config->Namespaces['lde'];
	$nsRights = $System->Config->Namespaces['rights'];
	
	
	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicence();
	}
	
	$objLicence = $Licences->getItem($LicenceId);
	if (!is_object($objLicence)){
		throw new exception("unknown Licence");
	}
	
	if (!$objLicence->canEdit){
		throw new exception("you cannot update this licence");
	}
	
	$xmlLicence = $objLicence->xml;
		
	$xmlLicenceDefs = $Licences->xpath->query("rights:Definitions",$objLicence->xml)->Item(0);
	if (!is_object($xmlLicenceDefs)){
		$xmlLicenceDefs = $Licences->dom->createElementNS($nsRights,"Definitions");
		$objLicence->xml->appendChild($xmlLicenceDefs);
	}
	
	$xmlDef = $Licences->xpath->query("rights:Definitions[@id = $DefId]",$xmlLicenceDefs)->Item(0);
	if (is_object($xmlDef)){
		throw new Exception("Definition is already on this Licence");
	}
	
	$xmlDef = $Licences->dom->createElementNS($nsRights,"Definition");
	$xmlDef->setAttribute('id', $DefId);
	$xmlLicenceDefs->appendChild($xmlDef);
	
	$Archive = new clsArchive();
	$Archive->setVersion($xmlLicence);

	$Licences->Save();
		
}

function dataLicenceDefDelete($LicenceId = null, $DefId = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$nsRights = $System->Config->Namespaces['rights'];
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a licence');
	}

	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicences;
	}
	
	$objLicence = $Licences->getItem($LicenceId);
	if (!is_object($objLicence)){
		throw new Exception("unknown licence");
	}
	
	if (!$objLicence->canControl){
		throw new Exception("You cannot Update this Licence");
	}

	$xmlLicence = $objLicence->xml;
			
	$xmlDef = $Licences->xpath->query("rights:Definitions/rights:Definition[@id = $DefId]",$objLicence->xml)->Item(0);
	if (is_object($xmlDef)){
		$xmlDef->parentNode->removeChild($xmlDef);		
	}
	
	$Archive = new clsArchive();
	$Archive->setVersion($xmlLicence);

	$Licences->Save();
	
}

?>