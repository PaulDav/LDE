<?php

require_once("clsSystem.php");
require_once("clsGraph.php");
require_once(dirname(__FILE__).'/../function/utils.inc');


class clsSources {
	
	public $Items = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	private $folder = "library";
	private $filename = "sources.xml";
	private $path = null;
	
	public $LibNamespace = "http://schema.legsb.gov.uk/lde/library/";
	

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

			$DocumentElement = $this->dom->createElementNS($this->LibNamespace, 'Sources');
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}

		$this->RefreshSources();
		
		
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('lib', $this->LibNamespace);
		
	}

	public function refreshSources(){

		foreach ($this->xpath->query("/lib:Sources/lib:Source") as $xmlSource){
			
			$objSource = new clsSource;
			$objSource->xml = $xmlSource;
			$objSource->Id = $xmlSource->getAttribute("id");
			$objSource->UserId = $xmlSource->getAttribute("userid");
			
			$objSource->Name = xmlelementvalue($xmlSource, 'Name');
			$objSource->Description = xmlelementvalue($xmlSource, 'Description');
			$objSource->URL = xmlelementvalue($xmlSource, 'URL');
			
			$objSource->canView = $this->canView;
			$objSource->canEdit = $this->canEdit;
			$objSource->canControl = $this->canControl;
			
			
			$this->Items[$objSource->Id] = $objSource;
		}
	}
	
	public function Save(){
		$this->dom->save($this->path);
	}
	

}

class clsSource {
	
	public $xml = null;
	public $Id = null;
	public $Name = null;
	Public $URL = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
}




class clsDefinitions {
	
	public $Items = array();
	public $TypeItems = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	private $folder = "library";
	private $filename = "definitions.xml";
	private $path = null;
	
	public $LibNamespace = "http://schema.legsb.gov.uk/lde/library/";
	

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

			$DocumentElement = $this->dom->createElementNS($this->LibNamespace, 'Definitions');
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}

		$this->RefreshDefinitions();
		
		
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('lib', $this->LibNamespace);
		
	}

	public function refreshDefinitions(){

		foreach ($this->xpath->query("/lib:Definitions/lib:Definition") as $xmlDef){
			
			$objDef = new clsDefinition;
			$objDef->xml = $xmlDef;
			$objDef->Id = $xmlDef->getAttribute("id");
			$objDef->UserId = $xmlDef->getAttribute("userid");
			$objDef->TypeId = $xmlDef->getAttribute("typeid");
			$objDef->SourceId = $xmlDef->getAttribute("sourceid");
			
			$objDef->Name = xmlElementValue($xmlDef, 'Name');
			$objDef->Description = xmlElementValue($xmlDef, 'Description');
			$objDef->URL = xmlElementValue($xmlDef, 'URL');
			
			$objDef->canView = $this->canView;
			$objDef->canEdit = $this->canEdit;
			$objDef->canControl = $this->canControl;
			
			$this->Items[$objDef->Id] = $objDef;
			$this->TypeItems[$objDef->TypeId][$objDef->Id] = $objDef;
			
		}
	}
	
	public function getItem($Id){

		if (!isset($this->Items[$Id])){
			return false;
		}

		return $this->Items[$Id];

	}
	
	public function Save(){
		$this->dom->save($this->path);
	}
	

}

class clsDefinition {
	
	public $xml = null;
	public $Id = null;
	public $TypeId = null;
	public $SourceId = null;
	public $Name = null;
	Public $URL = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
	
	
}


?>