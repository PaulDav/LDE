<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsProfile.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

function dataProfileUpdate($Mode, $Id = null, $GroupId = null, $Name = null, $Description = null, $Publish = false) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a profile');
	}

	global $Profiles;
	if (!isset($Profiles)){
		$Profiles = new clsProfiles;
	}
	if (!$Profiles->canEdit){
		throw new exception("You cannot update profiles");
	}
			
	switch ($Mode) {
		case 'new':			
			$MaxId = 0;
			foreach ($Profiles->xpath->query("/profile:Profiles/profile:Profile[@id]") as $xmlExistingProfile){
				$ExistingId = $xmlExistingProfile->getAttribute("id");
				if ($ExistingId > $MaxId){
					$MaxId = $ExistingId;
				}
			}
			$Id = $MaxId + 1;				

			$xmlProfile = $Profiles->dom->createElementNS($Profiles->ProfileNamespace,"Profile");
			$Profiles->dom->documentElement->appendChild($xmlProfile);
			$xmlProfile->setAttribute("id",$Id);
			
			$xmlProfile->setAttribute("ownerid",$System->User->Id);
			
			break;
		default:
			
			$xmlProfile = $Profiles->xpath->query("/profile:Profiles/profile:Profile[@id='$Id']")->item(0);
			if (!is_object($xmlProfile)){
				throw new exception("Profile does not exist");
			}
			
			break;
	}

	$xmlProfile->setAttribute("groupid",$GroupId);
	
	xmlSetElement($xmlProfile, "Name", $Name);
	xmlSetElement($xmlProfile, "Description", $Description);
	
	
	switch ($Publish){
		case true;
			$xmlProfile->setAttribute("published", 'true');
			break;
		default;
			$xmlProfile->setAttribute("published", 'false');
			break;			
	}
	
	
	$Profiles->Save();

	return $Id;

}  	

function dataProfileDelete($Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a profile');
	}
	
	global $Profiles;
	if (!isset($Profiles)){
		$Profiles = new clsProfiles;
	}
	if (!$Profiles->canEdit){
		throw new exception("You cannot update profiles");
	}
	
	$xmlProfile = $Profiles->xpath->query("/profile:Profiles/profile:Profile[@id='$Id']")->item(0);
	if (!is_object($xmlProfile)){
		throw new Exception("Profile does not exist");
	}

	$xmlProfile->parentNode->removeChild($xmlProfile);

	$Profiles->Save();
	
}


function dataProfileSetShape($ProfileId = null, $ShapeId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	global $Profiles;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a profile');
	}
	
	if (!isset($Profiles->Items[$ProfileId])){
		throw new Exception("Unknown Profile");
	}
	$objProfile = $Profiles->Items[$ProfileId];
		
	$objProfile->xml->setAttribute("shapeid",$ShapeId);
		
	$Profiles->Save();
	
}


function dataProfileRemoveShape($ProfileId=null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}

	global $Profiles;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a profile');
	}
	
	if (!isset($Profiles->Items[$ProfileId])){
		throw new Exception("Unknown Profile");
	}
	$objProfile = $Profiles->Items[$ProfileId];
	
	$objProfile->xml->removeAttribute("shapeid");
	
	$Profiles->Save();
		
}


function dataProfileSetSelection($ProfileId = null, $SelId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	global $Profiles;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a profile');
	}
	
	if (!isset($Profiles->Items[$ProfileId])){
		throw new Exception("Unknown Profile");
	}
	$objProfile = $Profiles->Items[$ProfileId];
		
	$objProfile->xml->setAttribute("selid",$SelId);
		
	$Profiles->Save();
	
}


function dataProfileRemoveSelection($ProfileId=null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}

	global $Profiles;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a profile');
	}
	
	if (!isset($Profiles->Items[$ProfileId])){
		throw new Exception("Unknown Profile");
	}
	$objProfile = $Profiles->Items[$ProfileId];
	
	$objProfile->xml->removeAttribute("selid");
	
	$Profiles->Save();
		
}




function xdataProfileClassUpdate($Mode, $Id=null, $ProfileId, $DictId=null, $ClassId=null, $ProfileRelId = null, $Create = false, $Select = true){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a profile');
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	if (is_null($ProfileId)){
		throw new Exception("Profile Id not specified");
	}
	
	$Profile = new clsProfile($ProfileId);
	if (!($Profile->canEdit)){
		throw new exception("You cannot update this Profile");
	}

	if (is_null($DictId)){
		throw new Exception("Dict Id not specified");
	}
	
	if (is_null($ClassId)){
		throw new Exception("Class Id not specified");
	}
	
	if (!isset($Dicts->Dictionaries[$DictId])){
		throw new Exception("Unknown Dictionary");
	}
	$Dict = $Dicts->Dictionaries[$DictId];

	if (!isset($Dict->Classes[$ClassId])){
		throw new Exception("Unknown Class");
	}
	$Class = $Dict->Classes[$ClassId];
			
	$xmlClasses = $Profile->xpath->query("/profile:Profile/profile:Classes")->item(0);
	if (!is_object($xmlClasses)){
		$xmlClasses = $Profile->dom->createElementNS($Profile->ProfileNamespace,"Classes");
		$Profile->dom->documentElement->appendChild($xmlClasses);
	}
	
	$objProfileRel = null;
	if (!is_null($ProfileRelId)){
		$objProfileRel = $Profile->Relationships[$ProfileRelId];
	}
	
	switch ($Create){
		case true:
			$Create = 'true';
			break;
		default:
			$Create = 'false';
			break;			
	}

	switch ($Select){
		case true:
			$Select = 'true';
			break;
		default:
			$Select = 'false';
			break;			
	}
	
	
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Profile->xpath->query("profile:Class[@id]",$xmlClasses) as $xmlExistingClass){
					$ExistingId = $xmlExistingClass->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlClass = $Profile->dom->createElementNS($Profile->ProfileNamespace,"Class");
			$xmlClasses->appendChild($xmlClass);
			$xmlClass->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlClass = $Profile->xpath->query("profile:Class[@id='$Id']",$xmlClasses)->item(0);
			if (!is_object($xmlClass)){
				throw new exception("Profile Class does not exist");
			}			
			break;
	}
	
	$xmlClass->setAttribute("dictid",$DictId);					
	$xmlClass->setAttribute("classid",$ClassId);

	$xmlClass->setAttribute("create",$Create);
	$xmlClass->setAttribute("select",$Select);
	
	if (is_null($ProfileRelId)){
		$Profile->dom->documentElement->setAttribute("class",$Id);
	}
	else
	{
		$objProfileRel->xml->setAttribute("class",$Id);
	}
	
	$Profile->Save();
	
	return $Id;
	
}  	



function xdataProfileRelUpdate($Mode, $Id=null, $ProfileId, $ProfileClassId = nbull, $DictId=null, $RelId=null, $Inverse=false){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a profile');
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	if (is_null($ProfileId)){
		throw new Exception("Profile Id not specified");
	}
	
	$objProfile = new clsProfile($ProfileId);
	if (!($objProfile->canEdit)){
		throw new exception("You cannot update this Profile");
	}
	
	$xmlParent = $objProfile->dom->documentElement;
	
	if (!is_null($ProfileClassId)){
		$objProfileClass = $objProfile->Classes[$ProfileClassId];
		$xmlParent = $objProfileClass->xml;
	}
	

	if (is_null($DictId)){
		throw new Exception("Dict Id not specified");
	}
	
	if (is_null($RelId)){
		throw new Exception("Rel Id not specified");
	}
	
	if (!isset($Dicts->Dictionaries[$DictId])){
		throw new Exception("Unknown Dictionary");
	}
	$Dict = $Dicts->Dictionaries[$DictId];

	if (!isset($Dict->Relationships[$RelId])){
		throw new Exception("Unknown Relationship");
	}
	$objRel = $Dict->Relationships[$RelId];
			
	$xmlRelationships = $objProfile->xpath->query("profile:Relationships", $xmlParent)->item(0);
	if (!is_object($xmlRelationships)){
		$xmlRelationships = $objProfile->dom->createElementNS($objProfile->ProfileNamespace,"Relationships");
		$xmlParent->appendChild($xmlRelationships);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objProfile->xpath->query("//profile:Relationship[@id]") as $xmlExistingRel){
					$ExistingId = $xmlExistingRel->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlRel = $objProfile->dom->createElementNS($objProfile->ProfileNamespace,"Relationship");
			$xmlRelationships->appendChild($xmlRel);
			$xmlRel->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlRel = $objProfile->xpath->query("//profile:Relationship[@id='$Id']")->item(0);
			if (!is_object($xmlRel)){
				throw new exception("Profile Relationship does not exist");
			}			
			break;
	}

	$xmlRel->setAttribute("dictid",$DictId);					
	$xmlRel->setAttribute("relid",$RelId);
	$xmlRel->removeAttribute("inverse");
	if ($Inverse === true){
		$xmlRel->setAttribute("inverse",'true');
	}
	
	
	$objProfile->Save();
	
	return $Id;
	
}  	


function xdataProfileRelDelete($ProfileId = null,  $Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a profile');
	}
		
	if (is_null($ProfileId)){
		throw new Exception("Profile Id not specified");
	}
	
	$objProfile = new clsProfile($ProfileId);
	if (!($objProfile->canEdit)){
		throw new exception("You cannot update this Profile");
	}
	
	
	$objProfileRel = $objProfile->Relationships[$Id];
	
	$objProfileRel->xml->parentNode->removeChild($objProfileRel->xml);
	
	$objProfile->Save();
	
}


function dataSpecUpdate($Mode, $Id = null,  $GroupId = null, $ProfileId, $Name = null, $Description = null, $FileType = null, $Publish = false) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a specification');
	}

	global $Specs;
	if (!isset($Specs)){
		$Specs = new clsSpecs();
	}
	if (!$Specs->canEdit){
		throw new exception("You cannot update specifications");
	}
			
	switch ($Mode) {
		case 'new':			
			$MaxId = 0;
			foreach ($Specs->xpath->query("/spec:Specs/spec:Spec[@id]") as $xmlExistingSpec){
				$ExistingId = $xmlExistingSpec->getAttribute("id");
				if ($ExistingId > $MaxId){
					$MaxId = $ExistingId;
				}
			}
			$Id = $MaxId + 1;				

			$xmlSpec = $Specs->dom->createElementNS($Specs->SpecNamespace,"Spec");
			$Specs->dom->documentElement->appendChild($xmlSpec);
			$xmlSpec->setAttribute("id",$Id);
			
			$xmlSpec->setAttribute("ownerid",$System->User->Id);
			
			break;
		default:
			
			$xmlSpec = $Specs->xpath->query("/spec:Specs/spec:Spec[@id='$Id']")->item(0);
			if (!is_object($xmlSpec)){
				throw new exception("Spec does not exist");
			}
			
			break;
	}

	$xmlSpec->setAttribute("groupid",$GroupId);
	
	xmlSetElement($xmlSpec, "Name", $Name);
	xmlSetElement($xmlSpec, "Description", $Description);
	
	
	switch ($Publish){
		case true;
			$xmlSpec->setAttribute("published", 'true');
			break;
		default;
			$xmlSpec->setAttribute("published", 'false');
			break;			
	}

	$xmlSpec->setAttribute("profileid", $ProfileId);
	$xmlSpec->setAttribute("filetype", $FileType);
	
	
	$Specs->Save();

	return $Id;
	
}  	


function dataSpecMapRemoveField($objSpec, $objFormField){
	
	$FieldName = $objFormField->FieldName;
	
	$xmlField = $objSpec->Specs->xpath->query("spec:Fields/spec:Field[@name='$FieldName']",$objSpec->xml)->item(0);
	if (is_object($xmlField)){
		$xmlField->parentNode->removeChild($xmlField);
	}
	$objSpec->Specs->refreshXpath();		
	
}


function dataSpecMapSetField($objSpec, $objFormField ,$ColNum, $Default, $TransId){
	
	$Update = false;
	if (!IsEmptyString($ColNum)){			
		if (!is_numeric($ColNum)){
			throw new exception("Invalid Column Number");
		}			
		$Update = true;
	}

	if (!IsEmptyString($Default)){
		$Update = true;
	}
	
	if (!IsEmptyString($TransId)){
		if (!isset($objSpec->Translations[$TransId])){
			throw new exception("Unknown Translation");
		}
		$Update = true;
	}
	
	if (!$Update){
		return;
	}
	
	
	$xmlFields = $objSpec->Specs->xpath->query("spec:Fields", $objSpec->xml)->item(0);
	if (!is_object($xmlFields)){
		$xmlFields = $objSpec->Specs->dom->createElementNS($objSpec->Specs->SpecNamespace, 'Fields');
		$objSpec->xml->appendChild($xmlFields);
	}
	
	$FieldName = $objFormField->FieldName;
	
	$xmlField = $objSpec->Specs->xpath->query("spec:Field[@name='$FieldName']",$xmlFields)->item(0);
	if (!is_object($xmlField)){
		$xmlField = $objSpec->Specs->dom->createElementNS($objSpec->Specs->SpecNamespace, 'Field');
		$xmlFields->appendChild($xmlField);
	}
	$xmlField->setAttribute("name",$FieldName);
	if (!IsEmptyString($ColNum)){
		$xmlField->setAttribute("col",$ColNum);
	}
	if (!IsEmptyString($Default)){
		$xmlField->setAttribute("default",$Default);
	}
	if (!IsEmptyString($TransId)){
		$xmlField->setAttribute("translation",$TransId);
	}
	
	$objSpec->Specs->refreshXpath();		
			
}



function dataTransUpdate($Mode, $Id = null,  $SpecId = null, $Name = null, $Description = null) {

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}

	global $Specs;
	if (!isset($Specs)){
		$Specs = new clsSpecs();
	}
	
	if (is_null($SpecId)){
		throw new Exception("Specification Id not specified");
	}

	if (!isset($Specs->Items[$SpecId])){
		throw new exception("Unknown Specification");
	}
	$objSpec = $Specs->Items[$SpecId];
	if (!($objSpec->canEdit)){
		throw new exception("You cannot update this Translation");
	}
	
	$xmlTranslations = $Specs->xpath->query("spec:Translations",$objSpec->xml)->item(0);
	if (!is_object($xmlTranslations)){
		$xmlTranslations = $Specs->dom->createElementNS($Specs->SpecNamespace,"Translations");
		$objSpec->xml->appendChild($xmlTranslations);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Specs->xpath->query("//spec:Translations/spec:Translation[@id]",$objSpec->xml) as $xmlExistingTranslation){
					$ExistingId = $xmlExistingTranslation->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlTranslation = $Specs->dom->createElementNS($Specs->SpecNamespace,"Translation");
			$xmlTranslations->appendChild($xmlTranslation);
			$xmlTranslation->setAttribute("id",$Id);
			
			break;
		default:
			$xmlTranslation = $Specs->xpath->query("spec:Translation[@id='$Id']",$xmlTranslations)->item(0);
			if (!is_object($xmlTranslation)){
				throw new exception("Translation does not exist");
			}
			
			break;
	}
						
	xmlSetElement($xmlTranslation, "Name", $Name);
	xmlSetElement($xmlTranslation, "Description", $Description);			

	$Specs->Save();
	
	return $Id;
	
}  	

function dataTransDelete($Id = null,  $SpecId = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a translation');
	}
	
	global $Specs;
	if (!isset($Specs)){
		$Specs = new clsSpecs();
	}
	
	
	if (is_null($SpecId)){
		throw new Exception("Spec Id not specified");
	}
	
	if (!isset($Specs->Items[$SpecId])){
		throw new exception("Unknown Specification");
	}
	$objSpec = $Specs->Items[$SpecId];
	if (!($objSpec->canEdit)){
		throw new exception("You cannot update this Translation");
	}
		
	$xmlTranslation = $Specs->xpath->query("spec:Translations/spec:Translation[@id='$Id']",$objSpec->xml)->item(0);
	if (!is_object($xmlTranslation)){
		throw new Exception("Translation does not exist");
	}

	$xmlTranslation->parentNode->removeChild($xmlTranslation);

	$Specs->Save();
	
}



function dataTransListUpdate($SpecId=null, $TransId=null, $ListDictId=null, $ListId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	global $Specs;
	if (!isset($Specs)){
		$Specs = new clsSpecs();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a translation');
	}
	
	if (is_null($SpecId)){
		throw new Exception("Specification Id not specified");
	}
	if (is_null($TransId)){
		throw new Exception("Translation Id not specified");
	}
	if (is_null($ListDictId)){
		throw new Exception("List Dictionary Id not specified");
	}
	if (is_null($ListId)){
		throw new Exception("List Id not specified");
	}
	
	if (!isset($Specs->Items[$SpecId])){
		throw new exception("Unknown Specification");
	}
	$objSpec = $Specs->Items[$SpecId];
	if (!($objSpec->canEdit)){
		throw new exception("You cannot update this Specification");
	}

	if (!isset($objSpec->Translations[$TransId])){
		throw new exception("Unknown Translation");
	}	
	$objTrans = $objSpec->Translations[$TransId];
	
	$objTrans->xml->setAttribute("listdictid",$ListDictId);
	$objTrans->xml->setAttribute("listid",$ListId);
			
	$Specs->Save();
	
}


function dataTransListRemove($SpecId=null, $TransId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a translation');
	}
	
	global $Specs;
	if (!isset($Specs)){
		$Specs = new clsSpecs();
	}
	
	
	if (is_null($SpecId)){
		throw new Exception("Specification Id not specified");
	}
	if (is_null($TransId)){
		throw new Exception("Translation Id not specified");
	}
	
	if (!isset($Specs->Items[$SpecId])){
		throw new exception("Unknown Specification");
	}
	$objSpec = $Specs->Items[$SpecId];
	if (!($objSpec->canEdit)){
		throw new exception("You cannot update this Specification");
	}
	$objTrans = $objSpec->Translations[$TransId];
	
	$objTrans->xml->removeAttribute("listdictid");
	$objTrans->xml->removeAttribute("listid");
	
	$Specs->Save();
	
}


function dataTransItemUpdate($Mode, $Id = null,  $SpecId = null, $TransId, $FromValue, $ToValue = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a specification');
	}
	
	global $Specs;
	if (!isset($Specs)){
		$Specs = new clsSpecs();
	}
	
	if (is_null($SpecId)){
		throw new Exception("Specification Id not specified");
	}
	
	
	
	if (!isset($Specs->Items[$SpecId])){
		throw new exception("Unknown Specification");
	}
	$objSpec = $Specs->Items[$SpecId];
	if (!($objSpec->canEdit)){
		throw new exception("You cannot update this Specification");
	}
		
	if (!isset($objSpec->Translations[$TransId])){
		throw new Exception("Invalid Translation");
	}
	$objTrans = $objSpec->Translations[$TransId];	
	
	$xmlItems = $Specs->xpath->query("spec:Items",$objTrans->xml)->item(0);
	if (!is_object($xmlItems)){
		$xmlItems = $Specs->dom->createElementNS($Specs->SpecNamespace,"Items");
		$objTrans->xml->appendChild($xmlItems);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Specs->xpath->query("//spec:Item[@id]",$xmlItems) as $xmlExistingItem){
					$ExistingId = $xmlExistingItem->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlItem = $Specs->dom->createElementNS($Specs->SpecNamespace,"Item");
			$xmlItems->appendChild($xmlItem);
			$xmlItem->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlItem = $Specs->xpath->query("spec:Item[@id='$Id']",$xmlItems)->item(0);
			if (!is_object($xmlItem)){
				throw new exception("Item does not exist");
			}
			
			break;
	}
						
	xmlSetElement($xmlItem, "FromValue", $FromValue);
	xmlSetElement($xmlItem, "ToValue", $ToValue);
	
	$Specs->Save();
	
	return $Id;
	
}  	

function dataTransItemDelete($SpecId, $TransId, $Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	global $Specs;
	if (!isset($Specs)){
		$Specs = new clsSpecs();
	}
	
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a translation');
	}
	
	if (is_null($SpecId)){
		throw new Exception("Spec Id not specified");
	}
	
	if (!isset($Specs->Items[$SpecId])){
		throw new exception("Unknown Specification");
	}
	$objSpec = $Specs->Items[$SpecId];
	if (!($objSpec->canEdit)){
		throw new exception("You cannot update this Specification");
	}
		
	$xmlItem = $Specs->xpath->query("spec:Translations/spec:Translation[@id='$TransId']/spec:Items/spec:Item[@id='$Id']", $objSpec->xml)->item(0);
	if (!is_object($xmlItem)){
		throw new Exception("Item does not exist");
	}

	$xmlItem->parentNode->removeChild($xmlItem);

	$Specs->Save();

}


?>