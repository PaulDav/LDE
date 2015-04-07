<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsSystem.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');
require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsShape.php');


class clsApi {
	
	private $URL = null;
	private $Name = null;
	
	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;
	
	private $LdeNamespace = "http://schema.legsb.gov.uk/lde/";
	private $MetaNamespace = "http://schema.legsb.gov.uk/lde/metadata/";
	
	private $System = null;
	private $Dicts = null;
	private $Shapes = null;
	private $Groups = null;
		
	
	public function __get($name){
		switch ($name){
			case 'xml':
				return $this->dom->saveXML();
				break;
		}
	}
	
	public function __construct($URL = null){

		if (is_null($this->System)){
			global $System;
			if (!isset($System)){
				$System = new clsSystem();
			}
			$this->System = $System;
		}
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
	
		$DocumentElement = $this->dom->createElementNS($this->LdeNamespace, 'LocalDataEcosystem');
		$this->dom->appendChild($DocumentElement);
		$DocumentElement->setAttribute("xmlns:meta", $this->MetaNamespace);
		
		$xmlName = $this->dom->createElementNS($this->LdeNamespace, 'Name');
		$DocumentElement->appendChild($xmlName);
		if (isset($this->System->Config->Vars['instance']['appname'])){
			$xmlName->nodeValue = $this->System->Config->Vars['instance']['appname'];
		}
		
		if (isset($this->System->Config->Vars['external']['uri'])){
			$xmlImports = $this->dom->createElementNS($this->LdeNamespace, 'Imports');
			$DocumentElement->appendChild($xmlImports);
			foreach ($this->System->Config->Vars['external']['uri'] as $EcoUri){
				$xmlImport = $this->dom->createElementNS($this->LdeNamespace, 'Import');
				$xmlImport->nodeValue = $EcoUri;
				$xmlImports->appendChild($xmlImport);				
			}
		}
		
		$this->Groups = new clsGroups();
		$this->Groups->Published = true;
		$this->RefreshGroups($this->Groups, $DocumentElement);
						
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
//		echo '<pre>'.htmlentities($this->dom->saveXML()).'</pre>';		
		
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('lde', $this->LdeNamespace);
				
	}
	
	private function refreshGroups($objGroups, $xmlParent){
			
		$objGroups->getIds();
		
		$xmlGroups = $this->dom->createElementNS($this->LdeNamespace,"Groups");
		$xmlParent->appendChild($xmlGroups);
		
		foreach ($objGroups->Ids as $GroupId) {
			
			$objGroup=new clsGroup($GroupId);
			if ($objGroup->canView){
				
				$xmlGroup = $this->dom->createElementNS($this->LdeNamespace,"Group");
				$xmlGroups->appendChild($xmlGroup);
				$xmlGroup->setAttribute('id',$objGroup->Id);
				
				$xmlName = $this->dom->createElementNS($this->LdeNamespace,"Name");
				$xmlName->nodeValue = $objGroup->Name;
				$xmlGroup->appendChild($xmlName);
	
				if (!is_null($objGroup->Picture)) {
					$xmlGroup->setAttribute('image',"image.php?Id=".$objGroup->Picture);
				}
				
				if (!IsEmptyString($objGroup->Description)){
					$xmlDescription = $this->dom->createElementNS($this->LdeNamespace,"Description");
					$xmlDescription->nodeValue = $objGroup->Description;
					$xmlGroup->appendChild($xmlDescription);
				}
				
				$this->refreshDicts($objGroup, $xmlGroup);
				$this->refreshShapes($objGroup, $xmlGroup);
				
			}
		}
	}
		
	
	private function refreshDicts($objGroup, $xmlParent){

		if (is_null($this->Dicts)){
			$this->Dicts = new clsDicts();
		}
		
		$xmlDictionaries = $this->dom->createElementNS($this->Dicts->DictNamespace,"Dictionaries");
		$xmlParent->appendChild($xmlDictionaries);
		
		
		foreach ($objGroup->DictionaryIds as $DictId){
			if (isset($this->Dicts->Dictionaries[$DictId])){
				$objDict = $this->Dicts->Dictionaries[$DictId];
				
				$xmlDictionaries->appendChild($this->dom->importNode($objDict->dom->documentElement,true));
				
			}
		}
		
	}
	
	private function refreshShapes($objGroup, $xmlParent){

		if (is_null($this->Shapes)){
			$this->Shapes = new clsShapes();
		}
		
		$xmlShapes = $this->dom->createElementNS($this->Shapes->ShapeNamespace,"Shapes");
		$xmlParent->appendChild($xmlShapes);
		
		
		foreach ($objGroup->ShapeIds as $ShapeId){
			$objShape = $this->Shapes->Items[$ShapeId];
			if ($objShape->Publish === true){
				if (is_object($objShape)){
					$xmlShapes->appendChild($this->dom->importNode($objShape->xml,true));				
				}
			}
		}
		
	}
	
	
}



?>