<?php

require_once("clsSystem.php");
require_once(dirname(__FILE__).'/../function/utils.inc');


class clsArchive {
	
	public $Items = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;

	private $folder = "archive";
	private $filename = "archive.xml";
    private $FilePath = null;
	
	
	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		$this->FilePath = $System->path.$this->folder."//".$this->filename;
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		
		if (@$this->dom->load($this->FilePath) === false){
			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;

			$DocumentElement = $this->dom->createElementNS($System->Config->Namespaces['lde'], 'Archive');
			$DocumentElement->setAttribute("xmlns:meta", $System->Config->Namespaces['meta']);
			
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}

		$this->RefreshArchive();		
		
	}
	
	public function refreshXpath(){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		$this->xpath = new domxpath($this->dom);

		foreach ($System->Config->Namespaces as $Alias=>$Namespace){
			$this->xpath->registerNamespace($Alias, $Namespace);
		}
		
	}

	public function refreshArchive(){

	}
	
	public function Save(){
		$this->dom->save($this->FilePath);
	}
	
	
	public function setVersion($xmlElement){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		
		if (!$System->LoggedOn){
			return false;
		}

		$nsMeta = $System->Config->Namespaces['meta'];
		$dom = $xmlElement->ownerDocument;
		
		$xmlMeta = null;
		foreach ($xmlElement->childNodes as $xmlChild) {
			if ($xmlChild->namespaceURI == $nsMeta ){
				if ($xmlChild->nodeName == 'Meta' ){
					$xmlMeta = $xmlChild;
					continue;
				}
				
			}
		}
		
		if (!is_object($xmlMeta)){
			$xmlMeta = $dom->createElementNS($nsMeta, 'Meta');
			$xmlElement->appendChild($xmlMeta);
		}

		$xmlMeta->setAttribute("created",date('Y-m-d'));
		$xmlMeta->setAttribute("by",$System->User->Id);						
		
		$Version = $xmlMeta->getAttribute('version');
		if (IsEmptyString($Version)){
			$Version = 0;
		}

		$Version = $Version + 1;
		$xmlMeta->setAttribute("version",$Version);

		$this->dom->documentElement->appendChild($this->dom->importNode($xmlElement,true));
		
		$this->Save();
			
	}
	
	public function getItems($Type, $Id){

		$Items = array();
		
		$ElementName = null;
		switch ($Type){
			case 'licence':
				$ElementName = 'rights:Licence';
				break;
			default:
				return array();
				break;
		}

		$query = "/lde:Archive/".$ElementName."[@id=$Id]";
		foreach ($this->xpath->query($query) as $xmlItem){
			$objItem = new clsArchiveItem();
			$objItem->xml = $xmlItem;
			$objItem->Type = $Type;
			$objItem->Id = $Id;
			
			foreach ($this->xpath->query("meta:Meta",$xmlItem) as $xmlMeta){
				$objItem->DateTime = $xmlMeta->getAttribute('created');
				$objItem->Version = $xmlMeta->getAttribute('version');				
			}

			$Items[$objItem->Version] = $objItem;			
		}
		
		return $Items;
		
	}
	
	
	public function getItem($Type, $Id, $Version){

		$ElementName = null;
		switch ($Type){
			case 'licence':
				$ElementName = 'rights:Licence';
				break;
			default:
				return false;
				break;
		}

		$query = "/lde:Archive/".$ElementName."[@id=$Id][meta:Meta/@version=$Version]";
		$xmlItem = $this->xpath->query($query)->item(0);
		if (!is_object($xmlItem)){
			return false;
		}
		
		$objItem = new clsArchiveItem();
		$objItem->xml = $xmlItem;
		$objItem->Type = $Type;
		$objItem->Id = $Id;
			
		foreach ($this->xpath->query("meta:Meta",$xmlItem) as $xmlMeta){
			$objItem->DateTime = $xmlMeta->getAttribute('created');
			$objItem->Version = $xmlMeta->getAttribute('version');				
		}

		return $objItem;			
		
	}
}

class clsArchiveItem {
	
	public $xml = null;
	
	public $Type = null;
	public $Id = null;
	public $Version = null;
	public $DateTime = null;
		
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
}

?>