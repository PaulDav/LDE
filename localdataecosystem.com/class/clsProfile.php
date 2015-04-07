<?php

require_once("clsSystem.php");
require_once("clsGroup.php");
require_once("clsDict.php");
require_once("clsGraph.php");
require_once(dirname(__FILE__).'/../function/utils.inc');


class clsProfiles{
	
	public $Items = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	private $folder = "profiles";
    private $filename = "profiles.xml";
    private $path = null;
	
	public $ProfileNamespace = "http://schema.legsb.gov.uk/lde/profile/";
	public $ShapeNamespace = "http://schema.legsb.gov.uk/lde/shape/";
	
	public $MetaNamespace = "http://schema.legsb.gov.uk/lde/metadata/";
		
	public function __construct(){
	
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
				
		$this->dom = new DOMDocument('1.0', 'utf-8');
		
		$this->path = $System->path."/".$this->folder."//".$this->filename;
		
		
		if (@$this->dom->load($this->path) === false){

			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;

			$DocumentElement = $this->dom->createElementNS($this->ProfileNamespace, 'Profiles');
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}
		
		$this->RefreshProfiles();
		
	}
	
	public function refreshXpath(){

		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('profile', $this->ProfileNamespace);
		$this->xpath->registerNamespace('shape', $this->ShapeNamespace);
		
	}

	public function refreshProfiles(){

		foreach ($this->xpath->query("/profile:Profiles/profile:Profile") as $xmlProfile){
			
			$objProfile = new clsProfile;
			$objProfile->xml = $xmlProfile;
			$objProfile->Profiles = $this;
			$objProfile->refresh();
			
			$this->Items[$objProfile->Id] = $objProfile;
			
		}
	}
	
	public function Save(){
		$this->dom->save($this->path);
	}
}



class clsProfile {
	
	public $Profiles = null;
		
	public $xml = null;

	public $Id = null;
	public $GroupId = null;
	public $OwnerId = null;
	public $Name = null;
	public $Description = null;
	public $Publish = false;

	public $ShapeId = null;
	
	public $SelectionId = null;
	public $Partitions = array();
		
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;

	
	public function refresh(){
		
		$this->Id = $this->xml->getAttribute("id");
		$this->OwnerId = $this->xml->getAttribute("ownerid");
			
		if (!IsEmptyString($this->xml->getAttribute("groupid"))){
			$this->GroupId = $this->xml->getAttribute("groupid");
		}			

		if (!IsEmptyString($this->xml->getAttribute("shapeid"))){
			$this->ShapeId = $this->xml->getAttribute("shapeid");
		}			

		if (!IsEmptyString($this->xml->getAttribute("selid"))){
			$this->SelectionId = $this->xml->getAttribute("selid");
		}			
		
		
		$this->Name = xmlElementValue($this->xml, 'Name');
		$this->Description = xmlElementValue($this->xml, 'Description');
			
		$this->canView = $this->Profiles->canView;
		$this->canEdit = $this->Profiles->canEdit;
		$this->canControl = $this->Profiles->canControl;
		
	}
	
}






class clsProfileClass{
	
	public $xml = null;
	
	public $Id = null;
	public $DictId = null;
	public $ClassId = null;
	
	public $Select = true;
	public $Create = false;
	
	public $RelId = null;
	
	public $ProfileRelationshipIds = array();

}

class clsProfileRelationship{
	
	public $xml = null;
	
	public $Id = null;
	public $DictId = null;
	public $RelId = null;
	public $Inverse = false;
	
	public $SubjectProfileClassId = null;
	public $ObjectProfileClassId = null;
	
}

class clsSpecs{
	
	public $Items = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	private $folder = "specs";
    private $filename = "specifications.xml";
    private $path = null;
	
	public $SpecNamespace = "http://schema.legsb.gov.uk/lde/spec/";
	public $ProfileNamespace = "http://schema.legsb.gov.uk/lde/profile/";
	public $ShapeNamespace = "http://schema.legsb.gov.uk/lde/shape/";
	
	public $MetaNamespace = "http://schema.legsb.gov.uk/lde/metadata/";

	public function __construct(){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		$this->dom = new DOMDocument('1.0', 'utf-8');

		$this->path = $System->path."/".$this->folder."//".$this->filename;
		
		if (@$this->dom->load($this->path) === false){

			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;

			$DocumentElement = $this->dom->createElementNS($this->SpecNamespace, 'Specs');
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}
		$this->RefreshSpecs();		
		
	}
	
	public function refreshXpath(){

		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('spec', $this->SpecNamespace);
		$this->xpath->registerNamespace('profile', $this->ProfileNamespace);
		$this->xpath->registerNamespace('shape', $this->ShapeNamespace);
		
	}

	public function refreshSpecs(){
		
		foreach ($this->xpath->query("/spec:Specs/spec:Spec") as $xmlSpec){
			$objSpec = new clsSpec;
			$objSpec->xml = $xmlSpec;
			$objSpec->Specs = $this;
			$objSpec->refresh();
			$this->Items[$objSpec->Id] = $objSpec;
			
		}
	}
	
	public function Save(){
		$this->dom->save($this->path);
	}
}



class clsSpec {
	
	public $Specs = null;
		
	public $xml = null;

	public $Id = null;
	public $GroupId = null;
	public $OwnerId = null;
	public $Name = null;
	public $Description = null;
	public $FileType = null;
	
	public $Publish = false;

	public $ProfileId = null;	
	public $Fields = array();
	public $Translations = array();
		
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;

	
	public function refresh(){

		$this->Id = $this->xml->getAttribute("id");
		$this->OwnerId = $this->xml->getAttribute("ownerid");
			
		if (!IsEmptyString($this->xml->getAttribute("groupid"))){
			$this->GroupId = $this->xml->getAttribute("groupid");
		}			

		if (!IsEmptyString($this->xml->getAttribute("profileid"))){
			$this->ProfileId = $this->xml->getAttribute("profileid");
		}			

		$this->Name = xmlElementValue($this->xml, 'Name');
		$this->Description = xmlElementValue($this->xml, 'Description');
		$this->FileType = $this->xml->getAttribute('filetype');
		
			
		$this->canView = $this->Specs->canView;
		$this->canEdit = $this->Specs->canEdit;
		$this->canControl = $this->Specs->canControl;

		$this->RefreshFields();
		$this->RefreshTranslations();
		
	}
	
	public function refreshFields(){
		$this->Fields = array();
		foreach ($this->Specs->xpath->query("spec:Fields/spec:Field",$this->xml) as $xmlField){
			$Field = new clsSpecField;
			if (!IsEmptyString($xmlField->getAttribute('col'))){
				$Field->Col = $xmlField->getAttribute('col');
			}
			if (!IsEmptyString($xmlField->getAttribute('default'))){
				$Field->Default = $xmlField->getAttribute('default');
			}
			if (!IsEmptyString($xmlField->getAttribute('translation'))){
				$Field->TransId = $xmlField->getAttribute('translation');
			}
			
			$Field->Name = $xmlField->getAttribute('name');
			$this->Fields[$Field->Name] = $Field;
		}		
	}

	
	public function refreshTranslations(){
		
		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}
		
		$this->Translations = array();
		foreach ($this->Specs->xpath->query("spec:Translations/spec:Translation",$this->xml) as $xmlTranslation){
			$Trans = new clsTranslation();
			$Trans->xml = $xmlTranslation;
			$Trans->Id = $xmlTranslation->getAttribute('id');
			$Trans->Name = xmlElementValue($xmlTranslation,'Name');
			$Trans->Description = xmlElementValue($xmlTranslation,'Description');

			$ListDictId = $xmlTranslation->getAttribute('listdictid');
			$ListId = $xmlTranslation->getAttribute('listid');
			
			if (!IsEmptyString($ListDictId)){
				if (!IsEmptyString($ListId)){
					if (isset($Dicts->Dictionaries[$ListDictId])){
						if (isset($Dicts->Dictionaries[$ListDictId]->Lists[$ListId])){
							$Trans->List = $Dicts->Dictionaries[$ListDictId]->Lists[$ListId];
						}
					}
				}
			}
			
			$this->Translations[$Trans->Id] = $Trans;
			
			foreach ($this->Specs->xpath->query("spec:Items/spec:Item",$xmlTranslation) as $xmlItem){
				$objItem = new clsTranslationItem();

				$objItem->xml = $xmlItem;
				$objItem->Id = $xmlItem->getAttribute("id");
				$objItem->FromValue = xmlElementValue($xmlItem, "FromValue");
				$objItem->ToValue = xmlElementValue($xmlItem, "ToValue");

				$Trans->Items[$objItem->Id] = $objItem;
			}
			
			
		}
		
	}
	
}



class clsSpecField{
	public $Col = null;
	public $Default = null;
	public $TransId = null;
	public $Name = null;	
}


class clsTranslation{
	public $xml = null;
	
	public $Id = null;
	public $Name = null;
	public $Description = null;
	public $List = null;
	public $Items = array();
}

class clsTranslationItem{
	public $xml = null;
	
	public $Id = null;
	public $FromValue = null;
	public $ToValue = null;
}



?>
