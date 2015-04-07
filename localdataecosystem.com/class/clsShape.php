<?php

require_once("clsDict.php");


class clsShapes{
	
	public $Items = array();
	public $Selections = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
		
	private $folder = "shapes";
    private $filename = "shapes.xml";
    private $FilePath = null;
    
    public $LdeNamespace = "http://schema.legsb.gov.uk/lde/";
	public $ShapeNamespace = "http://schema.legsb.gov.uk/lde/shape/";
	public $MetaNamespace = "http://schema.legsb.gov.uk/lde/metadata/";
		
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

			$DocumentElement = $this->dom->createElementNS($this->ShapeNamespace, 'Shapes');
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}
		
		$this->RefreshShapes();
		
		
		if (isset($System->Config->Vars['external']['uri'])){
			foreach ($System->Config->Vars['external']['uri'] as $EcoUri){
				$EcoApi = $EcoUri.'/api.php';
				$domEco = new DOMDocument();
				if (@$domEco->load($EcoApi) === false){
					echo "cant load $EcoApi <br/>";
					continue;
				}
				$xpathEco = new domxpath($domEco);
				$xpathEco->registerNamespace('lde', $this->LdeNamespace);
				$xpathEco->registerNamespace('shape', $this->ShapeNamespace);
				
				foreach ($xpathEco->query("/lde:LocalDataEcosystem/lde:Groups/lde:Group/shape:Shapes") as $xmlShapes){					
					$this->RefreshShapes($xmlShapes, $EcoUri);					
				}				
			}
		}
		
	}
	
	public function refreshXpath(){

		$dom = $this->dom;
		
		$this->xpath = new domxpath($dom);
		$this->xpath->registerNamespace('shape', $this->ShapeNamespace);
		
	}

	public function refreshShapes($xmlShapes = null, $EcoSystem = null){

		if (is_null($xmlShapes)){
			$xpath = $this->xpath;
			$xmlShapes = $this->dom->documentElement;
		}
		else
		{
			$dom = $xmlShapes->ownerDocument;		
			$xpath = new domxpath($dom);
			$xpath->registerNamespace('shape', $this->ShapeNamespace);
			
		}
			
		
		foreach ($xpath->query("shape:Shape", $xmlShapes) as $xmlShape){
			
			$objShape = new clsShape;
			$objShape->xml = $xmlShape;
			$objShape->Shapes = $this;
			$objShape->EcoSystem = $EcoSystem;
			
			$objShape->refresh();
			
			
			
			
			if (!IsEmptyString($xmlShape->getAttribute('selectionOf'))){
				$this->Selections[$objShape->Id] = $objShape;
			}
			else
			{		
				$this->Items[$objShape->Id] = $objShape;
			}
			
		}
	}
	
	
	public function getItem($Id){

		if (!isset($this->Items[$Id])){
			return false;
		}

		return $this->Items[$Id];

	}
	
	
	
	public function Save(){
		
		$this->dom->save($this->FilePath);
		
	}
	
	
	
	public function SuperShapeClass($objShapeClass){

		$objShape = $objShapeClass->Shape;
		if (is_null($objShape->SelectionOf)){
			return false;
		}
		if (!isset($this->Items[$objShape->SelectionOf])){
			return false;
		}
		$objSuperShape = $this->Items[$objShape->SelectionOf];

		foreach ($objSuperShape->ShapeClasses as $objSuperShapeClass){
			if (!($this->matchSuperShapeClass($objShapeClass, $objSuperShapeClass) === false)){
				return $objSuperShapeClass;
			}
		}
		
		return false;		
		
	}
	
	
	
	
	private function matchSuperShapeClass($objShapeClass, $objSuperShapeClass){

// call this many times for each supershapeclass until it is true	
		

// check if ShapeClasses are for the same Class

		if (!($objShapeClass->Class == $objSuperShapeClass->Class)){
			return false;
			// does not match so try class in lower links 
		}

		
// if it does match, check that other links also match		
		foreach ($objShapeClass->ShapeLinks as $objShapeLink){
			
			$Matched = false;
			foreach ($objSuperShapeClass->ShapeLinks as $objSuperShapeLink){
				if ($objShapeLink->Relationship == $objSuperShapeLink->Relationship){
					if ($objShapeLink->Inverse == $objSuperShapeLink->Inverse ){
						if (!($this->matchSuperShapeClass($objShapeLink->ShapeClass, $objSuperShapeLink->ShapeClass)) === false){
							$Matched = true;
						}
					} 
				}
			}
			if (!$Matched){
				return false;
			}
		}
		
		return $objSuperShapeClass;
		
	}

}


class clsShape {
	public $xml;
	public $Shapes = null;
	
	private $dom = null;
	public $xpath = null;
	
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
	
	public $Publish = false;
	
	public $EcoSystem = null;
	public $Id = null;
	public $GroupId = null;
	public $ParentId = null;
	public $OwnerId = null;
	
	public $Name = null;
	public $Description = null;
	
	public $Selection = null;
	
	public $ShapeClasses = array();
	public $ShapeLinks = array();
	

	public $SelectionOf = null;
	
	
//	private $dotConcepts = array();
//	private $dotClasses = array();

	private $GraphConceptClusters = array();
	
	public function __construct(){
		
		$this->Selection = new clsShapeSelection;
		$this->Selection->Shape = $this;
		
	}

	
	public function refresh(){
		
		$this->dom = $this->xml->ownerDocument;
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('shape', $this->Shapes->ShapeNamespace);

		$this->Id = '';
		if (!is_null($this->EcoSystem)){
			$this->Id = $this->EcoSystem.'/shape/';
		}		
		$this->Id .= $this->xml->getAttribute("id");
		
		$this->OwnerId = $this->xml->getAttribute("ownerid");

		if (!IsEmptyString($this->xml->getAttribute("selectionOf"))){
			$this->SelectionOf = $this->xml->getAttribute("selectionOf");
		}			
		
		if (!IsEmptyString($this->xml->getAttribute("groupid"))){
			$this->GroupId = $this->xml->getAttribute("groupid");
		}			
			
		if (!IsEmptyString($this->xml->getAttribute("parentid"))){
			$this->ParentId = $this->xml->getAttribute("parentid");
		}

		
		if ($this->xml->getAttribute("publish") == 'yes'){
			$this->Publish = true;
		}
		
		
		$this->Name = xmlElementValue($this->xml, 'Name');
		$this->Description = xmlElementValue($this->xml, 'Description');
			

		$xmlSelection = $this->xpath->query("shape:Selection",$this->xml)->item(0);
		if (!is_object($xmlSelection)){
			$xmlSelection = $this->dom->createElementNS($this->Shapes->ShapeNamespace,"Selection");
			$this->xml->appendChild($xmlSelection);
		}
			
		$this->Selection->Shape = $this;
		$this->Selection->xml = $xmlSelection;
		$this->Selection->refresh();
			
		$this->canView = $this->Shapes->canView;
		
		if (is_null($this->EcoSystem)){
			$this->canEdit = $this->Shapes->canEdit;
			$this->canControl = $this->Shapes->canControl;
		}
		
	}
	
	
	
	public function getDot($Style){

		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}
		
		$Script = "";

		$Dict = $this;
		$objGraph = new clsGraph();
			
		$this->GraphConceptClusters = array();

		$ComplexGroups = array();
		foreach ($this->ShapeClasses as $optShapeClass){
			$this->getDotShapeClass($Style, $objGraph, $optShapeClass, $ComplexGroups);
		}

		foreach ($this->ShapeLinks as $optShapeLink){
			$this->getDotShapeLink($Style, $objGraph, $optShapeLink, $ComplexGroups);
		}		

		foreach ($this->ShapeClasses as $optShapeClass){
			$this->getDotSuperClass($objGraph, $optShapeClass);
		}
		
		
		$Script = $objGraph->script;
		
		return $Script;
	}
	
	private function getDotShapeClass($Style,$objGraph, $objShapeClass = null, &$ComplexGroups = array()){

		global $Dicts;
		
		if (!is_null($objShapeClass->Class)){
			
			$NodeId = 'shapeclass_'.$objShapeClass->Id;
			$objClass = $objShapeClass->Class;
			
			switch ($Style){
				case 1:
					
					
					$ThisGraph = $objGraph;

					$Concept = $objClass->Concept;
					if (!IsEmptyString($Concept)){
						if (!isset($this->GraphConceptClusters[$Concept])){					
							$Label = $Concept;
							$this->GraphConceptClusters[$Concept] = $objGraph->addSubGraph("cluster",$Label);
						}
						$ThisGraph = $this->GraphConceptClusters[$Concept];
					}
					
					$Label = $objGraph->FormatDotLabel($objClass->Label,12);
					$NodeUrl = "class.php?dictid=".$objClass->DictId."&classid=".$objClass->Id;					
					$ThisGraph->addNode($NodeId,$Label,"circle","bisque",0.7, 0.7, $NodeUrl);
					break;
				case 2:
					$HeaderLabel = $objGraph->FormatDotCell($objClass->Label,50)."<br/>(".strtoupper($objClass->Concept).")";
					
					$Label = "<<table id='$NodeId' border='0' cellborder='1' cellspacing='0'>";
			
					$Label .= "<tr><td colspan='4' bgcolor='lightblue'>$HeaderLabel</td></tr>";

					$PropSeq = 0;
					foreach ($objShapeClass->ShapeProperties as $ShapeProperty){
						if ($ShapeProperty->Selected === true){
							if (!is_null($ShapeProperty->Property)){

								$PropSeq = $PropSeq + 1;

								$objProp = $ShapeProperty->Property;
								
								$DataType = '';
								if (count($ShapeProperty->ShapeProperties) == 0){						
									if (is_object($objProp->Field)){
										$DataType = $objProp->Field->DataType;
									}
								}
								
								
								
								$Cardinality = $ShapeProperty->Cardinality;
								if ($Cardinality == 'one'){
									$Cardinality = '';
								}								
								
								$PropLabel = "<tr><td PORT='$PropSeq' align='left' balign='left' valign='top'>".$objGraph->FormatDotCell($objProp->Label,20)."</td>  <td  align='left' balign='left' valign='top'>".$Cardinality."</td>   <td  align='left' balign='left' valign='top'>".$objGraph->FormatDotCell($DataType,30)."</td>";
								$PropLabel .= "<td>";
								
								foreach ($ShapeProperty->Filters as $objFilter){
									$PropLabel .= $objFilter->Type.' '.$objFilter->Value.'<br/>';
								}
									
								$PropLabel .= "</td>";
								
								$this->getComplexPropertyDot($NodeId.':'.$PropSeq,$ShapeProperty,$objGraph,$ComplexGroups);
								
								$PropLabel .= "</tr>";
								$Label .= $PropLabel;
							}
						}
					}
					
					$Label .= "</table>>";
					$NodeUrl = "class.php?dictid=".$objClass->DictId."&classid=".$objClass->Id;					
					$objGraph->addNode($NodeId,$Label,"plaintext", null, null, null, $NodeUrl);
					
			}
			

		}
	}
	
	
	private function getDotSuperClass($objGraph, $objShapeClass){

		global $Dicts;
		
		if (!is_null($objShapeClass->Class)){
			
			$NodeId = 'shapeclass_'.$objShapeClass->Id;
			$objClass = $objShapeClass->Class;

			foreach ($Dicts->SuperClasses($objClass->DictId, $objClass->Id) as $optSuperClass){
				foreach ($this->ShapeClasses as $optShapeClass){
					if ($optShapeClass->Class == $optSuperClass){						
						$SuperNodeId = 'shapeclass_'.$optShapeClass->Id;
						$objGraph->addEdge($NodeId, $SuperNodeId, 'sub class of',null,'dotted');
					}
				}
			}
		}
			
	}
	
	
	
	private function getDotShapeLink($Style, $objGraph, $objShapeLink, &$ComplexGroups = array()){
		
		if (!is_null($objShapeLink->Relationship)){
			$objRel = $objShapeLink->Relationship;

			$RelLabel = '';
			switch ($objShapeLink->Inverse){
				case true:
					$RelLabel = $objRel->InverseLabel;
					break;
				default:
					$RelLabel = $objRel->Label;
					break;
			}

			$NodeId = 'shapelink_'.$objShapeLink->Id;
			
			
			switch ($Style){
				case 1:
					$Label = $objGraph->FormatDotLabel($RelLabel,12);
					break;
				case 2:
					$HeaderLabel = $objGraph->FormatDotCell($RelLabel,50);									
					
					$Label = "<<table id='$NodeId' border='0' cellborder='1' cellspacing='0'>";
			
					$Label .= "<tr><td colspan='2' bgcolor='palegreen'>$HeaderLabel</td></tr>";
					
					foreach ($objShapeLink->ShapeProperties as $ShapeProperty){
						if ($ShapeProperty->Selected === true){
							if (!is_null($ShapeProperty->Property)){
								$objProp = $ShapeProperty->Property;
								$PropLabel = "<tr><td align='left' balign='left' valign='top'>".$objGraph->FormatDotCell($objProp->Label,20)."</td></tr>";
								$Label .= $PropLabel;
							}
						}
					}
					
					$Label .= "</table>>";
					break;
					
			}
					
			$objGraph->addEdge('shapeclass_'.$objShapeLink->FromShapeClassId,'shapeclass_'.$objShapeLink->ToShapeClassId,$Label);
		}
		
	}
	
	
	private function getComplexPropertyDot($Port, $objParentShapeProperty, $objGraph, &$ComplexGroups){

		if (count($objParentShapeProperty->ShapeProperties) > 0){
		
			$NodeId = 'complex_'.(count($ComplexGroups) + 1);
			$ComplexGroups[] = $NodeId;
				
			$Shape="plaintext";
			$Label = "<<table id='$NodeId' border='0' cellborder='1' cellspacing='0'>";
				
			$PropSeq = 0;
			foreach ($objParentShapeProperty->ShapeProperties as $objShapeProperty){
					
				$PropSeq = $PropSeq + 1;
		
				if (is_object($objShapeProperty->Property)){
					$objProperty = 	$objShapeProperty->Property;
				
					$DataType = '';
					if (count($objShapeProperty->ShapeProperties) == 0){						
						if (is_object($objProperty->Field)){
							$DataType = $objProperty->Field->DataType;
						}
					}
					else
					{
						$this->getComplexPropertyDot($NodeId.':'.$PropSeq, $objShapeProperty, $objGraph, $ComplexGroups);
					}
					$Cardinality = $objShapeProperty->Cardinality;
					if ($Cardinality == 'one'){
						$Cardinality = '';
					}
					
					$Label .= "<tr><td PORT='$PropSeq' align='left' balign='left' valign='top'>".$objGraph->FormatDotCell($objProperty->Label,20)."</td>  <td  align='left' balign='left' valign='top'>".$Cardinality."</td>  <td  align='left' balign='left' valign='top'>".$objGraph->FormatDotCell($DataType,30)."</td></tr>";
				}
			}

			$Label .= "</table>>";

			$NodeUrl = "property.php?dictid=".$objProperty->DictId."&propid=".$objProperty->Id.'#elements';
			$objGraph->addNode($NodeId,$Label,$Shape,null,null,null,$NodeUrl);
				
			$objGraph->addEdge($Port, $NodeId);	
			
		}
	}
	
}
	

class clsShapeSelection {
	public $xml = null;
	public $Shape = null;
	
	public $ShapeClass = null;

	
	public function refresh(){
	
		$this->Shape->ShapeClasses = array();
		$this->Shape->ShapeLinks = array();

		foreach ($this->Shape->xpath->query("shape:ShapeClass",$this->xml) as $xmlShapeClass){
			$objShapeClass = new clsShapeClass();
			$objShapeClass->Shape = $this->Shape; 
			$objShapeClass->xml = $xmlShapeClass;
			$objShapeClass->refresh();
			$this->Shape->ShapeClasses[$objShapeClass->Id] = $objShapeClass;			
		}

		foreach ($this->Shape->xpath->query("shape:ShapeLink",$this->xml) as $xmlShapeLink){
			$objShapeLink = new clsShapeLink();
			$objShapeLink->Shape = $this->Shape; 
			$objShapeLink->xml = $xmlShapeLink;
			$objShapeLink->refresh();
			$this->Shape->ShapeLinks[$objShapeLink->Id] = $objShapeLink;			
		}
		
	}
	
}


class clsShapeClass {
	
	public $xml = null;
	public $Shape = null;
	
	public $Parent = null;
	
	public $Id = null;
		
	public $Class = null;
	public $Create = true;
	public $Select = true;
	public $Match = true;
	
	public $ShapeProperties = array();
	
	public function refresh(){

		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}
		
		$this->Id = $this->xml->getAttribute("id");

		$ClassDictId = $this->xml->getAttribute('classdictid');
		$ClassId = $this->xml->getAttribute('classid');
		
		$objClass = null;

		if (!isemptystring($ClassDictId)){
			if (!isemptystring($ClassId)){
				$objClass = $Dicts->getClass($ClassDictId, $ClassId);
			}
			
		}
		
		if (is_object($objClass)){
			$this->Class = $objClass;
		}

		
		if (!($this->xml->getAttribute("create") == 'true')){
			$this->Create = false;
		}
		if (!($this->xml->getAttribute("select") == 'true')){
			$this->Select = false;
		}
		if (!($this->xml->getAttribute("match") == 'true')){
			$this->Match = false;
		}
		
		
		
		foreach ($this->Shape->xpath->query("shape:ShapeProperties/shape:ShapeProperty",$this->xml) as $xmlShapeProperty){
			$objShapeProperty = new clsShapeProperty();
			$objShapeProperty->Shape = $this->Shape;
			
			$objShapeProperty->xml = $xmlShapeProperty;
			
			$objShapeProperty->refresh();
			if (is_null($objShapeProperty->Property)){
				continue;
			}
			$this->ShapeProperties[] = $objShapeProperty;
			
		}

		$this->Shape->ShapeClasses[$this->Id] = $this;
		
	}
	
}




class clsShapeProperty {
	
	public $xml = null;
	public $Shape = null;
		
	public $Property = null;
	public $Selected = false;
	public $Cardinality = 'one';
	public $Filters = array();
	
	public $ShapeProperties = array();
		
	public function refresh(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}
		
		
		$PropDictId = $this->xml->getAttribute('propdictid');
		$PropId = $this->xml->getAttribute('propid');
		
		$objProp = null;

		if (!isemptystring($PropDictId)){
			if (!isemptystring($PropId)){
				$objProp = $Dicts->getProperty($PropDictId, $PropId);
			}			
		}
				
		if (is_object($objProp)){
			$this->Property = $objProp;
		}
		
		if ($this->xml->getAttribute('selected') == 'true'){
			$this->Selected = true;
		}
		
		if (!IsEmptyString($this->xml->getAttribute('cardinality'))){
			if (in_array($this->xml->getAttribute('cardinality'),$System->Config->Cardinalities)){
				$this->Cardinality = $this->xml->getAttribute('cardinality');
			}
		}
		
		
		foreach ($this->Shape->xpath->query("shape:Filters/shape:Filter",$this->xml) as $xmlFilter){
			$objFilter = new clsShapeFilter();
			
			$objFilter->Shape = $this->Shape;			
			$objFilter->xml = $xmlFilter;
			$objFilter->ShapeProperty = $this;
			
			$objFilter->refresh();
			$this->Filters[] = $objFilter;
			
		}
		
// complex properties
		foreach ($this->Shape->xpath->query("shape:ShapeProperties/shape:ShapeProperty",$this->xml) as $xmlShapeProperty){
			$objShapeProperty = new clsShapeProperty();
			$objShapeProperty->Shape = $this->Shape;
			
			$objShapeProperty->xml = $xmlShapeProperty;
			
			$objShapeProperty->refresh();
			$this->ShapeProperties[] = $objShapeProperty;			
		}
				
		
	}
	
}



class clsShapeLink {
	
	public $xml = null;
	public $Shape = null;
	
	public $Id = null;
	
	public $Relationship = null;
	public $Inverse = false;
	public $Cardinality = null;
	public $EffDates = false;
	
	public $FromShapeClassId = null;
	public $ToShapeClassId = null;
	
	public $ShapeProperties = array();
	
	
	public function refresh(){

		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}
				
		$RelDictId = $this->xml->getAttribute('reldictid');
		$RelId = $this->xml->getAttribute('relid');
		
		$this->Id = $this->xml->getAttribute("id");

		$this->FromShapeClassId = $this->xml->getAttribute('fromshapeclassid');
		$this->ToShapeClassId = $this->xml->getAttribute('toshapeclassid');
		
		if (!isemptystring($RelDictId)){
			if (isset($Dicts->Dictionaries[$RelDictId])){
				if (!isemptystring($RelId)){
					if (isset($Dicts->Dictionaries[$RelDictId]->Relationships[$RelId])){
						$this->Relationship = $Dicts->Dictionaries[$RelDictId]->Relationships[$RelId];
						$this->Cardinality = $this->Relationship->Cardinality;
					}
				}
			}			
		}
		
		
		
		
		if ($this->xml->getAttribute('inverse') == 'true'){
			$this->Inverse = true;
		}		


		if (!($this->xml->getAttribute('cardinality') == '')){
			$this->Cardinality = $this->xml->getAttribute('cardinality');
		}		
		
		
		if ($this->xml->getAttribute('effdates') == 'true'){
			$this->EffDates = true;
		}		
		
		
		foreach ($this->Shape->xpath->query("shape:ShapeProperties/shape:ShapeProperty",$this->xml) as $xmlShapeProperty){
			$objShapeProperty = new clsShapeProperty();
			$objShapeProperty->Shape = $this->Shape;
			
			$objShapeProperty->xml = $xmlShapeProperty;
			
			$objShapeProperty->refresh();
			$this->ShapeProperties[] = $objShapeProperty;
		}
		
				
	}
	
}


class clsShapeFilter{
	
	public $Type = null;
	public $Value = null;
	
	public $xml;
	public $Shape;
	public $ShapeProperty;

	public function refresh(){
				
		if (!IsEmptyString($this->xml->getAttribute("type"))){
			$this->Type = $this->xml->getAttribute("type");
		}
		
		if (!IsEmptyString($this->xml->getAttribute("value"))){		
			$this->Value = $this->xml->getAttribute("value");
		}
		
	}
	
}
