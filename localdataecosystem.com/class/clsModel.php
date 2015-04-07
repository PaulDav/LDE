<?php

class clsModel {

	private $dom = null;
	private $xmlModel = null;
	private $xpath = null;
	private $DefaultNS = null;
	
  private $Id;
  private $Name;
  private $Version;
  
  private $Concepts = array();
  private $Relationships = array();
  private $Views = array();
  
  public function __get($name){
  	
  	switch ($name){
			case "Name":
				return xmlElementValue($this->xmlModel,"Name");
				break;
			case "Version":
				return xmlElementValue($this->xmlModel,"Version");
				break;
		}
  	
  	return $this->$name;
  }
  	

	public function __construct($Id = null){

		
		global $System;
	 	if (!isset($System)){
	 		$System = new clssystem();
	 	}
		
		if (is_null($Id)){
			$Id = $System->Config->Vars['instance']['conceptmodel'];
		}
		
		
		$this->Id = $Id;		
		
		$this->dom = new DOMDocument;
		$this->dom->formatOutput = true;
		
		$this->dom->load("conceptmodels/conceptmodels.xml");
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		$this->xmlModel = $this->xpath->query("/cm:ConceptModelling/cm:Models/cm:Model[@id=$Id]")->item(0);
		
		foreach ($this->xpath->query("cm:Concepts/cm:Concept",$this->xmlModel) as $xmlConcept){
			$objConcept = new clsConcept();
			$objConcept->Id = $xmlConcept->getAttribute("id");			
			$objConcept->Name = xmlElementValue($xmlConcept,"Name");
			$objConcept->Definition = xmlElementValue($xmlConcept,"Definition");
			$objConcept->Type = xmlElementValue($xmlConcept,"Type");
			if ($objConcept->Type == ""){
				$objConcept->Type = "Prime";
			}
			
			foreach ($this->xpath->query("cm:SuperConcepts/cm:Concept",$xmlConcept) as $xmlSuperConcept){
				$SuperConceptId = $xmlSuperConcept->getAttribute("id");
				if (!in_array($SuperConceptId,$objConcept->SuperConceptIds)){
					$objConcept->SuperConceptIds[] = $SuperConceptId;
				}
				
			}
			
			
			foreach ($this->xpath->query("cm:ProxyConcepts/cm:Concept",$xmlConcept) as $xmlProxyConcept){
				$ProxyConceptId = $xmlProxyConcept->getAttribute("id");
				if (!in_array($ProxyConceptId,$objConcept->ProxyConceptIds)){
					$objConcept->ProxyConceptIds[] = $ProxyConceptId;
				}
			}
			
			
			$this->Concepts[$objConcept->Id] = $objConcept;
		}

// set sub concepts

		foreach ($this->Concepts as $objConcept){
			foreach ($objConcept->SuperConceptIds as $SuperConceptId){
				$objSuperConcept = $this->Concepts[$SuperConceptId];
				if (!in_array($objConcept->Id,$objSuperConcept->SubConceptIds)){
					$objSuperConcept->SubConceptIds[] = $objConcept->Id;
				}
			}
		}
		
		
		foreach ($this->xpath->query("cm:Relationships/cm:Relationship",$this->xmlModel) as $xmlRelationship){
			$objRelationship = new clsConceptRelationship();
			$objRelationship->Id = $xmlRelationship->getAttribute("id");
			if ($xmlRelationship->getAttribute("definiing") == 'true'){			
				$objRelationship->Defining = true;
			}
			if ($xmlRelationship->getAttribute("inverseDefiniing") == 'true'){			
				$objRelationship->InverseDefining = true;
			}

			$xmlSubjectConcept = $this->xpath->query("cm:Subject/cm:Concept",$xmlRelationship)->item(0);			
			$objRelationship->SubjectConceptId = $xmlSubjectConcept->getAttribute("id");
			
			$xmlObjectConcept = $this->xpath->query("cm:Object/cm:Concept",$xmlRelationship)->item(0);			
			$objRelationship->ObjectConceptId = $xmlObjectConcept->getAttribute("id");
						
			$objRelationship->Property = xmlElementValue($xmlRelationship,"Property");
			$objRelationship->Label = xmlElementValue($xmlRelationship,"Label");
			$objRelationship->InverseProperty = xmlElementValue($xmlRelationship,"Inverse");
			$objRelationship->InverseLabel = xmlElementValue($xmlRelationship,"InverseLabel");
			
			$objRelationship->Definition = xmlElementValue($xmlRelationship,"Definition");
			
			
			if ($objRelationship->InverseProperty == ""){
				$objRelationship->InverseProperty = $objRelationship->InverseLabel;
			}
				
			$this->Relationships[$objRelationship->Id] = $objRelationship;
		}
		
		
		foreach ($this->xpath->query("cm:Views/cm:View",$this->xmlModel) as $xmlView){
			$objView = new clsConceptView();
			$objView->Id = $xmlView->getAttribute("id");			
			$objView->Name = xmlElementValue($xmlView,"Name");
			$this->Views[$objView->Id] = $objView;
		}
		
		
	}
	
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('cm', $this->DefaultNS);
				
	}
	
	
	public function getRelationships($SubjectConceptId = null, $ObjectConceptId = null){
		
		$Relationships['normal'] = array();
		$Relationships['inverse'] = array();
		
		foreach ($this->Relationships as $objRelationship){
			
			$Match = true;
			
			if (!is_null($SubjectConceptId)){				
				if (!($this->isSubConceptOf($SubjectConceptId, $objRelationship->SubjectConceptId))){
					$Match = false;
				}
			}

			if (!is_null($ObjectConceptId)){
				if (!($this->isSubConceptOf($ObjectConceptId, $objRelationship->ObjectConceptId))){
					$Match = false;
				}				
			}
			
			if ($Match === true){
				$Relationships['normal'][] = $objRelationship->Id;
			}

// repeat for inverse relationships
			
			$Match = true;
			
			if (!is_null($SubjectConceptId)){
				if (!($this->isSubConceptOf($SubjectConceptId, $objRelationship->ObjectConceptId))){
					$Match = false;
				}				
			}
			
			if (!is_null($ObjectConceptId)){
				if (!($this->isSubConceptOf($ObjectConceptId, $objRelationship->SubjectConceptId))){
					$Match = false;
				}
			}
			
			if ($Match === true){
				$Relationships['inverse'][] = $objRelationship->Id;
			}
						
		}
		
		return $Relationships;		
		
	}
	

	public function isSubConceptOf($CheckConceptId, $SuperConceptId){
		
		if ($CheckConceptId == $SuperConceptId ){
			return true;
		}
		
		$objSuperConcept = $this->Concepts[$SuperConceptId];
		foreach ($objSuperConcept->SubConceptIds as $SubConceptId){
		    if ($this->isSubConceptOf($CheckConceptId,$SubConceptId) === true){
		    	return true;
		    }
		}

		foreach ($objSuperConcept->ProxyConceptIds as $ProxyConceptId){
		    if ($this->isSubConceptOf($CheckConceptId,$ProxyConceptId) === true){
		    	return true;
		    }
		}
		
		return false;
		
	}
	
	public function getConceptByName($ConceptName){
				
		foreach ($this->Concepts as $objConcept){
			if ($objConcept->Name == $ConceptName){
				return $objConcept;
			}
		}
		
		return false;
		
	}
	
	
}



class clsConcept {

	public $Id;
	public $Name;
	public $Definition;
	public $Type;
	
	public $SuperConceptIds = array();
	public $SubConceptIds = array();
	public $ProxyConceptIds = array();
	
}


class clsConceptRelationship {

	public $Id;
	public $Defining = false;
	public $InverseDefining = false;
	public $Name;
	public $Definition;
	public $SubjectConceptId;
	public $ObjectConceptId;
	public $Property;
	public $Label;
	public $InverseProperty;
	public $InverseLabel;
	
}

class clsConceptView {

	public $Id;
	public $Name;
	
}

