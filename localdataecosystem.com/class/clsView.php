<?php

require_once("clsSystem.php");
require_once("clsGroup.php");
require_once("clsDict.php");
require_once("clsGraph.php");
require_once(dirname(__FILE__).'/../function/utils.inc');


class clsViews {
	
	private $folder = "views";
	
	private $Items = array();
	
	public function __get($name){
		switch ($name){
			default:
				return $this->$name;
				break;
		}
		
	}
	
	
	public function __construct(){
		if ($handle = opendir('./'.$this->folder)) {
		    while (false !== ($entry = readdir($handle))) {
		        if ($entry != '.' && $entry != '..' && substr($entry, 0,1) != '.') {
		    			        	
		        	$temp = explode( '.', $entry );
					$ext = array_pop( $temp );
					$Id = implode( '.', $temp );
					
		        	$objView = new clsView($Id);
		        	$this->Items[$Id] = $objView;
		        }
		    }
		    closedir($handle);
		}
		
	}
}


class clsView {
	
	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;
	
	private $xml = null;

	private $Id = null;
	private $GroupId = null;
	private $OwnerId = null;
	private $Name = null;
	private $Description = null;
	private $Publish = false;
	
	private $Exists = false;
	
	
	private $Selections = array();
		
	
	private $canView = false;
	private $canEdit = false;
	private $canControl = false;
	
	
	private $folder = "views";
	private $filename = "";
	
	private $ViewNamespace = "http://schema.legsb.gov.uk/lde/view/";
	private $MetaNamespace = "http://schema.legsb.gov.uk/lde/metadata/";
	
	public function __get($name){
		switch ($name){
			default:
				return $this->$name;
				break;
		}
		
	}

	public function __set($name,$value){
		
		$xmlMeta = $this->xpath->query("/view:View/meta:Meta")->item(0);
		switch ($name){
			case "GroupId":
				$this->dom->documentElement->setAttribute("groupid",$value);
				break;
			case "Name":				
				$xmlMeta->setAttribute("name",$value);
				break;
			case "Description";
				$xmlDesc = $this->xpath->query("meta:Description",$xmlMeta)->item(0);
				if (!is_object($xmlDesc)){
					$xmlDesc = $this->dom->createElementNS($this->MetaNamespace, 'Description');
				}
				$xmlDesc->nodeValue = $value;
				$xmlMeta->appendChild($xmlDesc);
				break;
			case "Publish":
				
				switch ($value){
					case true:
						$this->dom->documentElement->setAttribute("publish","yes");
						break;
					default:
						$this->dom->documentElement->setAttribute("publish","no");
						break;
				}
				
				break;
		}
	}

	public function __construct($Id=null){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		$this->Id = $Id;
		
		$this->filename = $this->Id.".xml";
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		
		if (@$this->dom->load($this->folder."//".$this->filename) === false){

			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;

			$DocumentElement = $this->dom->createElementNS($this->ViewNamespace, 'View');
			$this->dom->appendChild($DocumentElement);
			$DocumentElement->setAttribute("xmlns:meta", $this->MetaNamespace);
			
			
			$DocumentElement->setAttribute("id",$this->Id);
			
			$xmlMeta = $this->dom->createElementNS($this->MetaNamespace, 'Meta');
			$DocumentElement->appendChild($xmlMeta);			
			
			$xmlMeta->setAttribute("created",date('Y-m-d'));
			$xmlMeta->setAttribute("by",$System->User->Id);						
		}
		else
		{
			$this->Exists = true;
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		$this->RefreshMeta();
		$this->RefreshSelections();
		
		$this->xml = $this->dom->documentElement;
		
		if ($this->Publish == true){
			$this->canView = true;
		}
		if ($System->LoggedOn){			
			if ($this->OwnerId == $System->User->Id){
				$this->canView = true;
				$this->canEdit = true;
				$this->canControl = true;				
			}
		}

		
		if ($this->Exists){
			$objGroup = new clsGroup($this->GroupId);
			if ($objGroup->canView == true){
				$this->canView = true;
			}
			if ($objGroup->canEdit == true){
				$this->canEdit = true;
			}
		}
		
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('view', $this->ViewNamespace);
		$this->xpath->registerNamespace('meta', $this->MetaNamespace);
		
	}

	public function refreshMeta(){

		$xmlMeta = $this->xpath->query("/view:View/meta:Meta")->item(0);		

		$this->GroupId = $this->dom->documentElement->getAttribute("groupid");
		$this->OwnerId = $xmlMeta->getAttribute("by");
		
		$this->Name = $xmlMeta->getAttribute("name");
		$this->Description = xmlElementValue($xmlMeta, "Description");
		
		$this->Publish = false;
		if ($this->dom->documentElement->getAttribute("publish") == "yes"){
			$this->Publish = true;
		}
		
	}

	public function refreshSelections(){
		
		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}
		
		$this->Selections = array();
		$Seq = 0;
		foreach ($this->xpath->query("/view:View/view:Selections/view:Selection") as $xmlSelection){
			$Seq = $Seq + 1;
			$objSel = new clsViewSelection();
			$objSel->View = $this;
			
			$objSel->xml = $xmlSelection;
			
			$objSel->Seq = $Seq;
			$objSel->refresh();
			
						
//			$objSel->refreshProperties();
			
			$this->Selections[$Seq] = $objSel;
		}
		
	}
		
	public function Save(){
		$this->dom->save($this->folder."//".$this->filename);
	}
}



class clsViewSelection{

	public $View;
	
	public $Seq = 0;
	
	public $ViewClass = null;
	
	public $xml;
	
	
	public function refresh(){

		$this->ViewClass = null;
		
		$xmlViewClass = $this->View->xpath->query("view:Class",$this->xml)->item(0);
		if (is_object($xmlViewClass)){
			$this->ViewClass = new clsViewClass();
			$this->ViewClass->xml = $xmlViewClass;
			$this->ViewClass->View = $this->View;
			$this->ViewClass->ViewSelection = $this;
			
			$this->ViewClass->refresh();
			
		}
	}
		
}

class clsViewClass{

	public $xml = null;
	public $View = null;
	public $ViewSelection = null;
	
	
	public $Class = null;
		
	public $ViewProperties = array();
	public $ViewLinks = array();

	public function refresh(){
		
		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}		

		if (!$Dicts->getClass($this->xml->getAttribute("classdictid"),$this->xml->getAttribute("classid"))){
			return;
		}
		
		$this->Class = $Dicts->getClass($this->xml->getAttribute("classdictid"),$this->xml->getAttribute("classid"));
		
		$this->ViewProperties = array();
		foreach ($this->View->xpath->query("view:Properties/view:Property",$this->xml) as $xmlProperty){
			$objViewProp = new clsViewProperty();
			
			$objViewProp->xml = $xmlProperty;
			$objViewProp->View = $this->View;
			$objViewProp->ViewClass = $this;

			$objViewProp->refresh();
			
			if (!is_null($objViewProp->Property)){			
				$this->ViewProperties[] = $objViewProp;
			}
		}
		
		$this->ViewLinks = array();
		foreach ($this->View->xpath->query("view:Links/view:Link",$this->xml) as $xmlLink){
// check relationship and objectclass exist

			if (!$Dicts->getRelationship($xmlLink->getAttribute('reldictid'),$xmlLink->getAttribute('relid') )){
				continue;				
			}
			$xmlLinkClass = $this->View->xpath->query("view:Class",$xmlLink)->Item(0);
			if (!is_object($xmlLinkClass)){
				continue;
			}
			if (!$Dicts->getClass($xmlLinkClass->getAttribute('classdictid'),$xmlLinkClass->getAttribute('classid') )){
				continue;				
			}
			
			
			
			$objViewLink = new clsViewLink();
			
			$objViewLink->xml = $xmlLink;
			$objViewLink->View = $this->View;
			$objViewLink->ViewSubject = $this;
			
			$objViewLink->refresh();
			
			$this->ViewLinks[] = $objViewLink;
		
		}
		
	}	
	
}

class clsViewProperty{
	
	public $xml;
	public $View = null;
	public $ViewClass = null;
	
	public $Property = null;
	
	public $Selected = false;
	
	public $Filters = array();
	
	public function refresh(){	

		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}		
		
		
		$PropDictId = $this->xml->getAttribute("propdictid");
		$PropId = $this->xml->getAttribute("propid");
		if ($Dicts->getProperty($PropDictId, $PropId)){
			$this->Property = $Dicts->getProperty($PropDictId, $PropId);
		}
		
		if ($this->xml->getAttribute("selected") == 'true'){
			$this->Selected = true;
		}		

		$this->Filters = array();
		foreach ($this->View->xpath->query("view:Filters/view:Filter",$this->xml) as $xmlFilter){
			$objFilter = new clsViewFilter();
			
			$objFilter->xml = $xmlFilter;
			$objFilter->View = $this->View;
			
			$objFilter->ViewProperty = $this;
			$objFilter->refresh();
			$this->Filters[] = $objFilter;
		}
		
		
	}
		
}

class clsViewFilter{
	
	public $Type;
	public $Value;
	
	public $xml;
	public $ViewProperty;
	public $View;

	public function __set($name,$value){
				
		switch ($name){
			case "Type":
				$this->xml->setAttribute("type",$value);
				$this->Type = $value;
				break;
			case "Value":
				$this->xml->setAttribute("value",$value);
				$this->Value = $value;
				break;
		}
		
		$this->Property->Selection->View->refreshXpath();
		
	}

	public function refresh(){
		
		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}		
		
		
		$this->Type = $this->xml->getAttribute("type");
		$this->Value = $this->xml->getAttribute("value");
		
	}
	
}


class clsViewLink{
	
	public $xml = null;
	public $View = null;
	public $ViewSubject = null;
	
	public $Relationship = null;
	public $Inverse = false;
	public $ViewObject = null;

	
	public function refresh(){	

		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}		
		
		$RelDictId = $this->xml->getAttribute("reldictid");
		$RelId = $this->xml->getAttribute("relid");
		
		if (isset($Dicts->Dictionaries[$RelDictId])){
			$objRelDict = $Dicts->Dictionaries[$RelDictId];
			if (isset($objRelDict->Relationships[$RelId])){
				$this->Relationship = $objRelDict->Relationships[$RelId];
				
				$xmlViewClass = $this->View->xpath->query("view:Class",$this->xml)->item(0);
				if (is_object($xmlViewClass)){
					$this->ViewObject = new clsViewClass();
					$this->ViewObject->xml = $xmlViewClass;
					$this->ViewObject->View = $this->View;
					$this->ViewObject->refresh();
				}
				
			}
		}
		
	}
	
}


?>