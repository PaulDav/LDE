<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');
require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsRecordset.php');

require_once('clsDict.php');
require_once('clsShape.php');
require_once('clsData.php');


class clsDocument {

  public $Id;
  public $SetId = null;
      
  private $System;

  private $Statements = null;
  
  private $SubjectForms = null;
  private $BlankSubjectForms = null;
  
  private $LinkForms = null;
  private $BlankLinkForms = null;
  
  private $ShapeId = null;
  private $objShape = null;
  
  private $Dicts = null;
  
 	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;
	
    
  public function __get($name){
  	switch ($name){
  		case 'Statements':
  			if (is_null($this->Statements)){
  				$this->getStatements();
  			}
  			break;
  		
  		case 'SubjectForms':
  		case 'BlankSubjectForms':
    	case 'LinkForms':	  			
  		case 'BlankLinkForms':

  			
  			$doSetExtending = false;
  			if (is_null($this->SubjectForms)){
  				$this->getSubjectForms();
  				$doSetExtending = true;

  			}
  			if (is_null($this->BlankSubjectForms)){
  				$this->getBlankSubjectForms();
  				$doSetExtending = true;
  			}
  			if (is_null($this->LinkForms)){
  				$this->getLinkForms();
  				$doSetExtending = true;
  			}
  			if (is_null($this->BlankLinkForms)){
  				$this->getBlankLinkForms();
  				$doSetExtending = true;
  			}
  			if ($doSetExtending){
	  			$this->setExtending();
  			}
  			break;
  			
 		case 'xml':
			return $this->getXML();
			break;
  	}
  	
  	return $this->$name;
  }

  
    public function __set($name, $val){
  	switch ($name){
  		case 'ShapeId':
  			$this->setShape($val);
  			break;
  		default:
  			$this->$name = $val;
  			break;  			
  	}
  	
  	return $this->$name;
  }

  
	public function __construct ($Id = null){
	 			
	 	global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}
	 	$this->System = $System;
	 	
	 	global $Dicts;
	 	if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}
		$this->Dicts = $Dicts;
	 		
	 		 	 	
	 	if (!is_null($Id)){
	 	
		 	$sql = "SELECT * FROM tbl_document WHERE docRecnum = $Id";
		 	
		 	$rst = $System->DbExecute($sql);
			if (!$rst->num_rows > 0){
				return false;
			}
			
			$rstRow = $rst->fetch_assoc();	
	
			$this->Id = $rstRow['docRecnum'];			
			$this->setShape($rstRow['docShape']);
			$this->SetId = $rstRow['docSet'];
	 	}
		
/*		
		if ($System->LoggedOn){
			if ($System->User->Id == $this->OwnerId){
				$this->canView = true;
				$this->canEdit = true;
				$this->canControl = true;
			}
		}
*/

	}
	
	private function setShape($ShapeId){

		global $Shapes;
		if (!isset($Shapes)){
			$Shapes = new clsShapes();
		}
		
	  	$this->BlankSubjectForms = null;
	  	$this->BlankLinkForms = null;

	  	$this->ShapeId = $ShapeId;
	  	if (isset($Shapes->Items[$ShapeId])){ 	
		  	$this->objShape = $Shapes->Items[$ShapeId];
	  	}
	  	
	  	return;
	  		  	
	}
	
	public function getStatements(){
		
		$Statements = array();
		
		if (!is_null($this->Id)){
			
			$Id = $this->Id;
				
//		 	$sql = "SELECT stmRecnum FROM tbl_statement WHERE stmDocument = ".$this->Id;
		 	$sql = "SELECT * FROM tbl_statement LEFT JOIN tbl_value ON valStatement = stmRecnum WHERE stmDocument = $Id";
		 	
		 	$rst = $this->System->DbExecute($sql);
		 	
			while ($row = $rst->fetch_assoc()) {
				$StatId = $row['stmRecnum'];
				$Statements[] = new clsStatement($StatId, $row);
			}
		}
		
		$this->Statements = $Statements;
		
		return $Statements;
				
	}
	
	private function getSubjectForm($ShapeClass, $SubjectId = null, $objSubject = null){

		$Statements = null;
		if (!is_null($SubjectId)){
			if (is_null($this->Statements)){
				$this->getStatements();
			}
			$Statements = $this->Statements;
		}

		$Form = new clsForm($ShapeClass, $SubjectId, $this,  $Statements, $objSubject);
		
		return $Form;
	
	}
	
	
	public function getSubjectLinkForms($SubjectId, $ShapeLink = null){
		
		$LinkForms = array();
		foreach ($this->LinkForms as $objLinkForm){
			if (!is_null($ShapeLink)){
				if (!($objLinkForm->ShapeLink == $ShapeLink)){
					continue;
				}
			}
				
			$useLinkForm = false;
			if (is_null($SubjectId)){
				$useLinkForm = true;
			}
			else
			{
				if ($objLinkForm->FromId == $SubjectId){
					$useLinkForm = true;
				}
				if ($objLinkForm->ToId == $SubjectId){
					$useLinkForm = true;
				}
				
			}
			if ($useLinkForm){
				$LinkForms[$objLinkForm->LinkId] = $objLinkForm;
			}
		}	
		
		return $LinkForms;

	}

	public function getLinkForm($ShapeLink, $LinkId = null){
		
		$Statements = null;
		if (!is_null($LinkId)){		
			$Statements = $this->getStatements();
		}
		
		$Form = new clsLinkForm($ShapeLink, $LinkId, $this,  $Statements);
		
		return $Form;
	
	}
	
	private function getBlankSubjectForms(){

		if (!is_null($this->BlankSubjectForms)){
			return;
		}
		if (is_null($this->objShape)){
			throw new exception("Shape not set");
		}
		
		$this->BlankSubjectForms = array();
		
		if (is_object($this->objShape)){
			foreach ($this->objShape->ShapeClasses as $objShapeClass){
				$this->BlankSubjectForms[$objShapeClass->Id] = $this->getSubjectForm($objShapeClass);
			}
		}		
	}
	
	private function getSubjectForms(){
		
		if (!is_null($this->SubjectForms)){
			return;
		}
		
		$this->SubjectForms = array();

		if (is_null($this->Statements)){			
			$this->getStatements();
		}
		
		$SubjectIds = array();
		foreach ($this->Statements as $objStatement){

// ignore 'matched to' statements
			if ($objStatement->TypeId == 110){
				continue;
			}
			
			if (!is_null($objStatement->SubjectId)){
				$SubjectIds[$objStatement->SubjectId] = $objStatement->SubjectId;
			}
			if (!is_null($objStatement->ObjectId)){
				$SubjectIds[$objStatement->ObjectId] = $objStatement->ObjectId;
			}
		}
		
		foreach ($SubjectIds as $SubjectId){

			$objSubject = new clsSubject($SubjectId);
			$objSubject->AsAtDocumentId = $this->Id;
			
			$objShapeClass = null;
			if (!is_null($this->objShape)){
				foreach ($this->objShape->ShapeClasses as $optShapeClass){
					if ($optShapeClass->Class->DictId == $objSubject->ClassDictId){
						if ($optShapeClass->Class->Id == $objSubject->ClassId){
							$objShapeClass = $optShapeClass;
						}
						
					}
				}
			}
			
			if (!is_null($objShapeClass)){				
				$this->SubjectForms[$SubjectId] = $this->getSubjectForm($objShapeClass, $SubjectId, $objSubject);
			}
			
		}
		
	}
	

	
	private function getBlankLinkForms(){

		if (!is_null($this->BlankLinkForms)){
			return;
		}
		if (is_null($this->objShape)){
			throw new exception("Shape not set");
		}
		
		$this->BlankLinkForms = array();
		
		if (is_object($this->objShape)){
			foreach ($this->objShape->ShapeLinks as $objShapeLink){
				$this->BlankLinkForms[$objShapeLink->Id] = $this->getLinkForm($objShapeLink);
			}
		}		
	}
	
	private function getLinkForms(){

		if (!is_null($this->LinkForms)){
			return;
		}
		
		$this->LinkForms = array();
		
		if (is_null($this->Statements)){
			$this->getStatements();
		}
		
		$LinkIds = array();
		foreach ($this->Statements as $objStatement){
			if ($objStatement->TypeId == 300){
				$LinkIds[$objStatement->Id] = $objStatement->Id;
			}
		}
		
		foreach ($LinkIds as $LinkId){
			$objLink = new clsLink($LinkId);
			$objLink->AsAtDocumentId = $this->Id;
			
			$objShapeLink = null;
			if (!is_null($this->objShape)){
				foreach ($this->objShape->ShapeLinks as $optShapeLink){
					if ($optShapeLink->Relationship->DictId == $objLink->RelDictId){
						if ($optShapeLink->Relationship->Id == $objLink->RelId){
							$objShapeLink = $optShapeLink;
						}					
					}
				}
			}
			if (!is_null($objShapeLink)){
				$this->LinkForms[$LinkId] = $this->getLinkForm($objShapeLink, $LinkId);
			}
		}
		
	}
	
	
	private function setExtending(){
// set the Extending property on forms and links 				
				
		foreach ($this->BlankLinkForms as $objLinkForm){
			if ($objLinkForm->ShapeLink->Relationship->Extending === true){
				if (isset($this->BlankSubjectForms[$objLinkForm->ShapeLink->ToShapeClassId])){
					$objObjectForm = $this->BlankSubjectForms[$objLinkForm->ShapeLink->ToShapeClassId];
					if ($objObjectForm->ShapeClass->Create === true){
						if (!($objObjectForm->Select === true)){
							$objObjectForm->CreateExtended = true;
							$objLinkForm->CreateExtended = true;
						}
					}
				}
			}
		}

		foreach ($this->LinkForms as $objLinkForm){
			if ($objLinkForm->ShapeLink->Relationship->Extending === true){
				if (isset($this->SubjectForms[$objLinkForm->ToId])){				
					$objObjectForm = $this->SubjectForms[$objLinkForm->ToId];
					if ($objObjectForm->ShapeClass->Create === true){
						if (!($objObjectForm->Select === true)){
							$objObjectForm->CreateExtended = true;
							$objLinkForm->CreateExtended = true;
						}
					}
				}
			}
		}
	}
	
	
	public function getDot($Style){

		$Script = "";
		
		$objGraph = new clsGraph();
		
		$Nodes = array();
		$Links = array();
		$ConceptClusters = array();
				
		if (is_null($this->SubjectForms)){
  			$this->getSubjectForms();
  		}
		if (is_null($this->LinkForms)){
  			$this->getLinkForms();
  		}
  				
		foreach ($this->SubjectForms as $objSubjectForm){			
			$objSubjectForm->getFormDot($Style, $objGraph, $Nodes, $Links, $ConceptClusters, 0);			
		}
		
		foreach ($this->LinkForms as $objLinkForm){			
			$objLinkForm->getLinkFormDot($Style, $objGraph,$Nodes, $Links, $ConceptClusters, 0);			
		}
		
		
		$Script = $objGraph->script;
		
		return $Script;
	}
	
	private function getXML(){

		$System = $this->System;
		
		if (is_null($this->Statements)){
			$this->getStatements();
		}
						
		global $Shapes;
		if (!isset($Shapes)){
			$Shapes = new clsShapes();
		}
		
		if (is_null($this->SubjectForms)){
  			$this->getSubjectForms();
  		}

		$objDoc = $this;
		
		$nsLde = $System->Config->Namespaces['lde'];
		$nsMeta = $System->Config->Namespaces['meta'];
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
	
		$xmlDoc = $this->dom->createElementNS($nsLde, 'Document');
		$this->dom->appendChild($xmlDoc);
		$xmlDoc->setAttribute("xmlns:meta", $nsMeta);
		
		$xmlDoc->setAttribute('id',$objDoc->Id);
		$xmlDoc->setAttribute('setid',$objDoc->SetId);

		if (is_object($this->objShape)){
			$objShape = $this->objShape;
			$xmlShape = $this->dom->createElementNS($nsLde, 'Shape');
			$xmlDoc->appendChild($xmlShape);
			$xmlShape->setAttribute('id',$objShape->Id);
			$xmlName = $this->dom->createElementNS($nsLde, 'Name');
			$xmlShape->appendChild($xmlName);
			$xmlName->nodeValue = $objShape->Name;
		}

		$xmlStatements = $this->dom->createElementNS($nsLde, 'Statements');
		$xmlDoc->appendChild($xmlStatements);
		
		foreach ($this->Statements as $objStatement){
			$xmlStatements->appendChild($this->dom->importNode($objStatement->xml->documentElement,true));
		}
		
		
		$xmlSubjects = $this->dom->createElementNS($nsLde, 'Subjects');
		$xmlDoc->appendChild($xmlSubjects);
				
		foreach ($objDoc->SubjectForms as $objSubjectForm){

			$objSubject = $objSubjectForm->Subject;
			
			if (is_object($objSubject)){
				$objSubject->AsAtDocumentId = $this->Id;
				$xmlSubject = $this->dom->createElementNS($nsLde, 'Subject');
				$xmlSubjects->appendChild($xmlSubject);
				$xmlSubject->setAttribute('id',$objSubject->Id);
				
				if (!is_null($objSubject->Identifier)){
					$xmlSubject->setAttribute('identifier',$objSubject->Identifier);
				}
				
				if (!is_null($objSubject->Name)){
					$xmlSubject->setAttribute('name',$objSubject->Name);
				}
			}
		}

		return $this->dom;
		
	}	
	
	
	
}


class clsForm{
	
	public $ShapeClass = null;	
	public $Document = null;
	public $DocumentId = null;

	public $Select = true;
	public $Create = false;
	public $Update = false;
	public $CreateExtended = false; // set to true if the ShapeClass is Create only and is the Object of an Extending Relationship

	public $SubjectId = null;
	public $Subject = null;
	public $Created = false;

	public $Statements = null;

	public $FormFields = array();
	public $SameAsStatements = array();
		
	private $xml = null;
	private $dom = null;
	private $FormNamespace = "http://schema.legsb.gov.uk/lde/form/";  
	private $DefaultNS = null;
	private $xpath = null;
	
	private $Dicts = null;
	
			
	 public function __get($name){
	  	switch ($name){	
		  	case 'xml':
	  			if (is_null($this->xml)){
	  				$this->getXml();
	  			}
	  			break;
	  	}
	  	
	  	if (isset($this->$name)){
		  	return $this->$name;
	  	}
	 }  			
	
	
	public function __construct($ShapeClass = null, $SubjectId = null, $Document = null, $Statements=null, $objSubject = null ){

		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}
		$this->Dicts = $Dicts;		
		
		$this->ShapeClass = $ShapeClass;		
		$this->SubjectId = $SubjectId;
		$this->Document = $Document;
		if (!is_null($Document)){
			$this->DocumentId = $Document->Id;
		}
		
				
		$this->Statements = array();

		if (is_array($Statements)){
			foreach ($Statements as $objStatement){
				switch ($objStatement->TypeId){
					case 100:
					case 110:
					case 200:
						if ($objStatement->SubjectId == $this->SubjectId){
							$this->Statements[$objStatement->Id] = $objStatement;
						}
						break;
				}
			}
		}
		
		if (is_null($objSubject)){
			if (!is_null($this->SubjectId)){
				$objSubject = new clsSubject($this->SubjectId);
				if (!is_null($this->DocumentId)){
					$objSubject->AsAtDocumentId = $this->DocumentId;
				}
	// override statements for this subject, so that it also brings forward complex attributes from the previous version of the subject.
	// note that $this->statements is different to $statements
				$this->Statements = $objSubject->Statements;
				
			}
		}

		$this->Subject = $objSubject;
		
		
		$this->Create = true;
		$this->Select = true;
// ---------------------------------------------------		
		
		if (is_array($Statements)){							
			foreach ($Statements as $objStatement){
				
				if ($objStatement->DocId == $this->DocumentId){
				
					if ($objStatement->TypeId == 100){
						if ($objStatement->SubjectId == $this->SubjectId){
							if ($objStatement->LinkDictId == $this->ShapeClass->Class->DictId){
								if ($objStatement->LinkId == $this->ShapeClass->Class->Id){
									$this->Created = true;
								}
							}
						}							
					}											
				}
			}
		}
		
		if (is_object($this->ShapeClass)){
			setFormProperties($this->ShapeClass->ShapeProperties, $this, $this->Statements);			
		}
		
		foreach ($this->Statements as $objStatement){
			if ($objStatement->TypeId == 110){
				$this->SameAsStatements[$objStatement->Id] = $objStatement;				
			}
		}
		
	}
	

	private function getXml(){
				
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
				
		$this->dom = new DOMDocument('1.0', 'utf-8');		
		$this->dom->formatOutput = true;
					
		$this->getXmlForm($this);

		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);

		$this->xpath = $this->refreshXpath();

		return $this->xml = $this->dom->saveXML();
		
		
	}

	private function refreshXpath($dom = null){
		
		if (is_null($dom)){
			$dom = $this->dom;
		}
		
		$xpath = new domxpath($dom);
		$xpath->registerNamespace('form', $this->FormNamespace);	
		
		return $xpath;
		
	}
		
	
	private function getXmlForm($objForm = null, $xmlParent = null){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		if (is_null($objForm)){
			$objForm = $this;
		}
		
		
		if (is_null($xmlParent)){
			$xmlParent = $this->dom;
		}
		
		$xmlForm = $this->dom->createElementNS($this->FormNamespace, 'Form');
		$xmlParent->appendChild($xmlForm);
		
		$xmlProfile = $this->dom->createElementNS($this->FormNamespace, 'Profile');
		$xmlForm->appendChild($xmlProfile);		

		$ProfileSeq = 0;		
		$this->getXmlProfile($objForm, $xmlProfile, $ProfileSeq);
		
		$this->refreshXpath();
		
		$SubjectSeq = 0;		
		$this->getXmlSubject($objForm, $xmlForm, $xmlProfile, $SubjectSeq);

		return $this->xml = $this->dom->saveXML();
		
	}
	
	
	public function getXmlProfile($objParent, $xmlParent, &$ProfileSeq, $dom = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		if (is_null($dom)){
			$dom = $this->dom;
		}

		$xmlClass = $dom->createElementNS($this->FormNamespace, 'Class');
		$xmlParent->appendChild($xmlClass);

		$ProfileSeq = $ProfileSeq + 1;
		$xmlClass->setAttribute('seq',$ProfileSeq);
		
		$xmlClass->setAttribute('label',$objParent->ShapeClass->Class->Label);
		$xmlClass->setAttribute('dictid',$objParent->ShapeClass->Class->DictId);
		$xmlClass->setAttribute('id',$objParent->ShapeClass->Class->Id);
		
		getFormXmlProperties($objParent, $xmlClass, $dom, $this->FormNamespace, $ProfileSeq);		

		$this->getXmlExtendingProfile($objParent, $xmlClass, $ProfileSeq, $dom);
		
	}
	
	private function getXmlExtendingProfile($objParent, $xmlParent, &$ProfileSeq, $dom = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		if (is_null($dom)){
			$dom = $this->dom;
		}

		$xmlRelationships = $dom->createElementNS($this->FormNamespace, 'Relationships');
		$xmlParent->appendChild($xmlRelationships);

		
		foreach ($objParent->Document->BlankLinkForms as $objLinkForm){
			if ($objLinkForm->ShapeLink->FromShapeClassId == $objParent->ShapeClass->Id){
				if ($objLinkForm->CreateExtended === true){
					$objLinkForm->getXmlProfile($objLinkForm, $xmlRelationships, $ProfileSeq, $this->dom);					
				}
			}
		}		
		
	}
	
	
	public function getXmlSubject($objParent, $xmlParent, $xmlProfileParent, &$SubjectSeq, $dom = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		if (is_null($dom)){
			$dom = $this->dom;
		}
		
		$xpath = $this->refreshXpath($dom);
		
		$xmlClass = $xpath->query("form:Class",$xmlProfileParent)->item(0);
		
		if (is_object($xmlClass)){
			
			$xmlSubject = $dom->createElementNS($this->FormNamespace, 'Subject');
			$xmlParent->appendChild($xmlSubject);
			
			$SubjectSeq = $SubjectSeq + 1;
			$xmlSubject->setAttribute('seq',$SubjectSeq);
	
			$xmlSubject->setAttribute('profileseq',$xmlClass->getAttribute('seq'));
			
			
			if (!is_null($objParent->SubjectId)){
				$xmlSubject->setAttribute('id',$objParent->SubjectId);
			}
			
			$xmlProperties = $xpath->query("form:Properties",$xmlClass)->item(0);
			if (is_object($xmlProperties)){
				getFormXmlAttributes($objParent, $xmlSubject, $xmlProperties, $dom, $this->FormNamespace, $xpath, $SubjectSeq);
			}
			
			$xmlRels = $xpath->query("form:Class/form:Relationships",$xmlProfileParent)->item(0);
			if (is_object($xmlRels)){		
				$this->getXmlLinks($objParent, $xmlSubject, $xmlRels, $SubjectSeq, $dom);
			}
		}
		
		return $xmlClass;

	}


	
	private function getXmlLinks($objParent, $xmlParent, $xmlRels, &$SubjectSeq, $dom=null){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		if (is_null($dom)){
			$dom = $this->dom;
		}
		
		$xpath = $this->refreshXpath($dom);
		
		$xmlLinks = $dom->createElementNS($this->FormNamespace, 'Links');
		$SubjectSeq = $SubjectSeq + 1;
		$xmlLinks->setAttribute('seq',$SubjectSeq);
		
		$xmlParent->appendChild($xmlLinks);
		
		foreach ($xpath->query("form:Relationship",$xmlRels) as $xmlRel){
			
			if (is_object($objParent->Document)){
				foreach ($objParent->Document->LinkForms as $optLinkForm){
					
					if ($optLinkForm->ShapeLink->FromShapeClassId == $objParent->ShapeClass->Id){
						if ($optLinkForm->ShapeLink->Relationship->DictId == $xmlRel->getAttribute('dictid')){
							if ($optLinkForm->ShapeLink->Relationship->Id == $xmlRel->getAttribute('id')){
								$objLinkForm = $optLinkForm;							
								$objLinkForm->getXmlLink($objLinkForm, $xmlLinks, $xmlRel, $SubjectSeq, $dom);
							}						
						}
					}
				}
			}
		}
		
	}

	public function isEmpty(){
		
		$result = true;
		if (!is_null($this->SubjectId)){
			$result = false;
		}
		
		return $result;
		
	}
	
	
	public function getDot($Style){
		
		$objForm = $this;

		$Script = "";
		
		$objGraph = new clsGraph();

		$Nodes = array();
		$Links = array();
		$ConceptClusters = array();
		

		$this->getFormDot($Style, $objGraph, $Nodes, $Links, $ConceptClusters);
		
		$Script = $objGraph->script;
		
		return $Script;
	}

		
	public function getFormDot($Style = 1, $objGraph, &$Nodes = array(), &$Links = array(), &$Clusters = array(), $Level = 1){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		
		$objForm = $this;
		
		$NodeId = 'subject_'.$objForm->SubjectId;
		if (isset($Nodes[$NodeId])){
			return $NodeId;	
		}		

		$objSubject = new clsSubject($objForm->SubjectId);
		$objSubject->AsAtDocumentId = $this->DocumentId;
		
		$objClass = $this->Dicts->getClass($objSubject->ClassDictId, $objSubject->ClassId);

		$Color = 'white';

		if (!is_null($objSubject->Context)){
			$Color = $objSubject->Context->Color;
		}
			
		
		
		$Shape = null;
		$NodeHeight = null;
		$NodeWidth = null;
		
		$ThisGraph = $objGraph;
		
		switch ( $Style ){
			case 1:
				$Shape = null;
				$Label = $objGraph->FormatDotLabel($objSubject->Label,20);

				$NodeHeight = 0.7;
				$NodeWidth = 0.7;
				
				if ($Level == 1){
					$NodeHeight = 1;
					$NodeWidth = 1;
				}
				
				$Concept = null;				
				if (is_object($objClass)){
					$Concept = $objClass->Concept;
				}
				if (!IsEmptyString($Concept)){
					if (!isset($Clusters[$Concept])){					
						$Clusters[$Concept] = $objGraph->addSubGraph("cluster",$Concept);
					}
					$ThisGraph = $Clusters[$Concept];
				}
				
				break;
			case 2 or 4:
		
				$Shape="plaintext";
				$SubjectHeaderLabel = '<b>'.$objGraph->FormatDotCell($objForm->ShapeClass->Class->Label,50)."</b><br/>(".strtoupper($objForm->ShapeClass->Class->Concept).")";
				
				$Label = "<<table id='$NodeId' border='0' cellborder='1' cellspacing='0'>";
				$Label .= "<tr><td colspan='2' bgcolor='$Color'>$SubjectHeaderLabel</td></tr>";
		
				$PortNo = 0;
				foreach ($objSubject->Attributes as $DictId=>$arrDictAtts){
					foreach ($arrDictAtts as $PropId=>$arrAtts){
					
						foreach ($arrAtts as $objAtt){
							
							$PortNo = $PortNo + 1;
							
							$Color = 'white';
							if (!($objAtt->Statement->DocId == $this->DocumentId)){
								$Color = 'lightgrey';
							}
							$Label .= "<tr><td align='left' balign='left' valign='top'><b>".$objGraph->FormatDotCell($objAtt->Label,20)."</b></td><td PORT='$PortNo' bgcolor='$Color' align='left' balign='left' valign='top'>";
							
							switch ($objAtt->Property->Type){
								case 'simple':
									$Label .= $objGraph->FormatDotCell(truncate(   str_replace(chr(10), ',',  $objAtt->Value)),30);
									break;
								case 'complex':
									$Label .= $this->getFormComplexAttributeDot($objGraph, $objAtt);
									break;
							}
							
							$Label .= "</td></tr>";
		
							
						}
					}
				}
				
				
				$Label .= "</table>>";
				
				
				if ($Style == 4){
					if (is_object($objSubject->CreatedSet)){
						$objSet = $objSubject->CreatedSet;
						if (!isset($Clusters[$objSet->Id])){					
							$Clusters[$objSet->Id] = $objGraph->addSubGraph("cluster",$objSet->Name);
						}
						$ThisGraph = $Clusters[$objSet->Id];
					}
				}
				
				break;
				
			case 3:
				$Shape = null;
				$Label = $objGraph->FormatDotLabel($objSubject->Label,20);

				$NodeHeight = 0.7;
				$NodeWidth = 0.7;
				
				if ($Level == 1){
					$NodeHeight = 1;
					$NodeWidth = 1;
				}
				
				$Concept = null;				
				if (is_object($objClass)){
					$Concept = $objClass->Concept;
				}
				if (!IsEmptyString($Concept)){
					$Label .= "\n(".strtoupper($Concept);
				}
				
				break;
		}
		
		$Nodes[$NodeId] = $NodeId;
		$NodeUrl = null;
		if (!is_null($this->DocumentId)){
			$NodeUrl = 'documentsubject.php?docid='.$this->DocumentId;
			if (!is_null($objForm->SubjectId)){
				$NodeUrl .= "&subjectid=$objForm->SubjectId";
			}
		}
		
		$ThisGraph->addNode($NodeId,$Label,$Shape,$Color,$NodeHeight,$NodeWidth,$NodeUrl);			
		
// Matches
		foreach ($objSubject->Matches as $objMatch){			
			$objSameAsSubject = new clsSubject($objMatch->SameAsSubjectId);

			$MatchColor = "white";
			if (!is_null($objSameAsSubject->Context)){
				$MatchColor = $objSameAsSubject->Context->Color;
			}
			
			$SameAsNodeId = $objSameAsSubject->getSubjectDot($Style, $objGraph, $Nodes, $Links, $Clusters, $Level, $MatchColor);
			$objGraph->addEdge($NodeId, $SameAsNodeId, 'matched to', $MatchColor, 'dashed');
		}
		
		
		
		if ($Level == 1){
			foreach ($objForm->Document->LinkForms as $LinkForm){
				$useForm = false;
				if ($LinkForm->FromId == $objForm->SubjectId){
					$useForm = true;
				}						
				if ($LinkForm->ToId == $objForm->SubjectId){
					$useForm = true;
				}						
				if ($useForm){
					$LinkNodeId = $LinkForm->getLinkFormDot($Style, $objGraph, $Nodes, $Links, $Clusters, $Level + 1);
				}
			}
		}
		
		return $NodeId;	
	}
	
	private function getFormComplexAttributeDot($objGraph, $objAtt){
			
		$found = false;
		
		$Label = "<table border='0' cellborder='1' cellspacing='0'>";

		foreach ($objAtt->ComplexAttributes as $objAtt){

			$found = true;
			
			$Color = 'white';
			if (!($objAtt->Statement->DocId == $this->DocumentId)){
				$Color = 'lightgrey';
			}
			$Label .= "<tr><td align='left' balign='left' valign='top'><b>".$objGraph->FormatDotCell($objAtt->Label,20)."</b></td><td bgcolor='$Color' align='left' balign='left' valign='top'>";
						
			switch ($objAtt->Property->Type){
				case 'simple':
					$Label .= $objGraph->FormatDotCell(truncate(   str_replace(chr(10), ',',  $objAtt->Value)),30);
					break;					
				case 'complex':
					$Label .= $this->getFormComplexAttributeDot($objGraph, $objAtt);
					break;
			}
			
			$Label .= "</td></tr>";
					
		}
		
		$Label .= "</table>";

		if ($found){
			return $Label;
		}
		return '';
		
	}
	
}



function setFormProperties($ShapeProperties, $objParent, $Statements = null){
	
	global $Dicts;	
	
	$HasProperties = null;
	$SubjectId = null;
	$AboutId = null;
		
	switch(get_class($objParent)){
		case 'clsForm':
			$HasProperties = $Dicts->ClassProperties($objParent->ShapeClass->Class->DictId, $objParent->ShapeClass->Class->Id);				
			$SubjectId = $objParent->SubjectId;			
			break;
		case 'clsLinkForm':
			$HasProperties = $Dicts->RelProperties($objParent->ShapeLink->Relationship->DictId, $objParent->ShapeLink->Relationship->Id);				
			if (is_object($objParent->Statement)){
				$AboutId = $objParent->Statement->Id;
			}

			break;
			
		case 'clsFormField':
			if (is_object($objParent->Statement)){
				$AboutId = $objParent->Statement->Id;
			}
			
			break;
	}
		

	$FieldNum = 0;
	foreach ($ShapeProperties as $objShapeProperty){
		
		if (is_null($objShapeProperty->Property)){
			continue;
		}
				
		$PropDictId = $objShapeProperty->Property->DictId;
		$PropId = $objShapeProperty->Property->Id;
			
		$arrValues = array();
		$arrDocValues = array();
		// force a blank version of field onto the form before populated versions
		$arrValues[] = array('value'=>null, 'statement'=>null);


// all statements for the same property must come from the same document
		
		if (is_array($Statements)){
			foreach ($Statements as $objStatement){
				if ($objStatement->TypeId == 200){

					$useStatement = false;

					if (!is_null($AboutId)){
						if ($objStatement->AboutId == $AboutId){
							$useStatement = true;
						}
					}

					if (!is_null($SubjectId)){
						if ($objStatement->SubjectId == $SubjectId){
							$useStatement = true;
						}
					}
										
					if ($useStatement){
						if ($objStatement->LinkDictId == $PropDictId){
							if ($objStatement->LinkId == $PropId){
								$arrDocValues[$objStatement->DocId][] = array('value'=>$objStatement->Value, 'statement'=>$objStatement);
								$objParent->hasValues = true;
							}								
						}
					}
					
					
				}
									
			}
		}

		ksort($arrDocValues);		
		if (is_array(end($arrDocValues))){
			$arrValues = array_merge($arrValues, end($arrDocValues));			
// ensures that attributes for the same property come from the same document.
		}

				
		$FieldNum = $FieldNum + 1;
		
		$Occurance = -1;

		foreach ($arrValues as $arrValue){
			$Occurance = $Occurance + 1;
			
			$objFormField = new clsFormField();

			$objFormField->Cardinality = $objShapeProperty->Cardinality;
//			$objFormField->Lists = $objShapeProperty->Property->Lists;
			
			$objFormField->Property = $objShapeProperty->Property;
																					
			$objFormField->Value = $arrValue['value'];
			$objFormField->Statement = $arrValue['statement'];
							
			$objParent->FormFields[$FieldNum][$Occurance] = $objFormField;
			
			if (count($objShapeProperty->ShapeProperties) > 0){
				setFormProperties($objShapeProperty->ShapeProperties, $objFormField, $Statements);
			}
			
		}
					
	}
	
}


class clsLinkForm{
		
	public $ShapeLink = null;	
	public $Document = null;
	public $DocumentId = null;
	
	public $CreateExtended = false; // set to true if the Object ShapeClass is Create only and is an Extending Relationship	
		
	public $LinkId = null;		
	public $FromId = null;
	public $ToId = null;
	public $Statements = null;

	public $Statement = null;
	public $Inverse = false;
	public $EffectiveFrom = null;
	public $EffectiveTo = null;
	
	
	public $FormFields = array();
	
	
	private $xml = null;
	private $dom = null;
	private $FormNamespace = "http://schema.legsb.gov.uk/lde/form/";  
	private $DefaultNS = null;
	private $xpath = null;
	
	private $Dicts = null;
	
	
	 public function __get($name){
	  	switch ($name){	
		  	case 'xml':
	  			if (is_null($this->xml)){
	  				$this->getXml();
	  			}
	  			break;
	  	}
	  	
	  	if (isset($this->$name)){
		  	return $this->$name;
	  	}
	 }  			
		
	
	public function __construct($ShapeLink, $LinkId = null, $Document = null, $Statements=null ){
		
		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
			$this->Dicts = $Dicts;
		}
		
//		global $Shapes;
//		if (!isset($Shapes)){
//			$Shapes = new clsShapes();
//		}
				
		$this->ShapeLink = $ShapeLink;		
		$this->LinkId = $LinkId;
		$this->Document = $Document;
		if (!is_null($Document)){
			$this->DocumentId = $Document->Id;
		}
				
		$this->Statements = array();

		if (is_array($Statements)){
			foreach ($Statements as $objStatement){
				if ($objStatement->Id == $LinkId){
					$this->Statements[$objStatement->Id] = $objStatement;
					$this->Statement = $objStatement;
				}
				if ($objStatement->AboutId == $LinkId){
					$this->Statements[$objStatement->Id] = $objStatement;
				}
			}
		}
		

		$objLink = null;
		if (!is_null($this->LinkId)){
			$objLink = new clsLink($this->LinkId);
			if (!is_null($this->DocumentId)){
				$objLink->AsAtDocumentId = $this->DocumentId;
			}
// override statements for this subject, so that it also brings forward complex attributes from the previous version of the subject.
// note that $this->statements is different to $statements
			$this->Statements = $objLink->Statements;
			
		}

		if (!is_null($objLink)){
			switch ($this->Inverse){
				case true:
					$this->FromId = $objLink->ObjectId;
					$this->ToId = $objLink->SubjectId;
					break;
				default:
					$this->FromId = $objLink->SubjectId;
					$this->ToId = $objLink->ObjectId;
					break;
			}
			
			$this->EffectiveFrom = $objLink->EffectiveFrom;
			$this->EffectiveTo = $objLink->EffectiveTo;			
			
		}
		
		
		if (is_object($this->ShapeLink)){
			setFormProperties($this->ShapeLink->ShapeProperties, $this, $this->Statements);			
		}
		
	}
	
	
	
	private function getXml(){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
				
		$this->dom = new DOMDocument('1.0', 'utf-8');		
		$this->dom->formatOutput = true;
					
		$this->getXmlLinkForm($this);

		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);

		$this->xpath = $this->refreshXpath();
		
		return $this->xml = $this->dom->saveXML();
		
	}

	private function refreshXpath($dom = null){
		
		if (is_null($dom)){
			$dom = $this->dom;
		}
		
		$xpath = new domxpath($dom);
		$xpath->registerNamespace('form', $this->FormNamespace);	
		
		return $xpath;
		
	}

	
	private function getXmlLinkForm($objLinkForm = null, $xmlParent = null){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		if (is_null($objLinkForm)){
			$objLinkForm = $this;
		}
		
		
		if (is_null($xmlParent)){
			$xmlParent = $this->dom;
		}
		
		$xmlLinkForm = $this->dom->createElementNS($this->FormNamespace, 'LinkForm');
		$xmlParent->appendChild($xmlLinkForm);
		
		$xmlProfile = $this->dom->createElementNS($this->FormNamespace, 'Profile');
		$xmlLinkForm->appendChild($xmlProfile);		

		$ProfileSeq = 0;		
		$this->getXmlProfile($objLinkForm, $xmlProfile, $ProfileSeq);
		
		$this->refreshXpath();
		
		$LinkSeq = 0;
		
		$this->getXmlLink($objLinkForm, $xmlLinkForm, $xmlProfile, $LinkSeq);
		
		return $this->xml = $this->dom->saveXML();
		
	}
	
	
	public function getXmlProfile($objParent, $xmlParent, &$ProfileSeq, $dom = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		if (is_null($dom)){
			$dom = $this->dom;
		}
			
		
// $objBlankLinkForm->ShapeLink->Relationship->Extending		
		
		$xmlRelationship = $dom->createElementNS($this->FormNamespace, 'Relationship');
		$xmlParent->appendChild($xmlRelationship);
		
		$ProfileSeq = $ProfileSeq + 1;
		$xmlRelationship->setAttribute('seq',$ProfileSeq);
		
		$xmlRelationship->setAttribute('label',$objParent->ShapeLink->Relationship->Label);
		$xmlRelationship->setAttribute('dictid',$objParent->ShapeLink->Relationship->DictId);
		$xmlRelationship->setAttribute('id',$objParent->ShapeLink->Relationship->Id);
		
		$xmlRelationship->setAttribute('cardinality',$objParent->ShapeLink->Relationship->Cardinality);
		if ($objParent->ShapeLink->Relationship->Extending === true){		
			$xmlRelationship->setAttribute('extending','true');
		}
		
		if ($objParent->ShapeLink->EffDates === true){		
			$xmlRelationship->setAttribute('effectivedates','true');
		}
		
		getFormXmlProperties($objParent, $xmlRelationship, $dom, $this->FormNamespace, $ProfileSeq);

		
		if ($objParent->ShapeLink->Relationship->Extending === true){
			if (is_object($objParent->Document)){
				foreach ($objParent->Document->BlankSubjectForms as $objBlankSubjectForm){
					if ($objBlankSubjectForm->ShapeClass->Id == $objParent->ShapeLink->ToShapeClassId){
						$objBlankSubjectForm->getXmlProfile($objBlankSubjectForm, $xmlRelationship, $ProfileSeq, $dom);					
					}
				}
			}
		}		
		
	}
	
	public function getXmlLink($objParent, $xmlParent, $xmlProfileParent, &$Seq, $dom=null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}

		
		if (is_null($dom)){
			$dom = $this->dom;			
		}
		
		$xpath = $this->refreshXpath($dom);
		
		$xmlRelationship = $xpath->query("form:Relationship",$xmlProfileParent)->item(0);
		if (!is_object($xmlRelationship)){
			return false;
		}
		
		$xmlLink = $dom->createElementNS($this->FormNamespace, 'Link');
		$xmlParent->appendChild($xmlLink);
		
		$Seq = $Seq + 1;
		$xmlLink->setAttribute('seq',$Seq);
		
		$ProfileSeq = $xmlRelationship->getAttribute('seq');
		
		$xmlLink->setAttribute('profileseq',$ProfileSeq);
		
		if (!is_null($objParent->LinkId)){
			$xmlLink->setAttribute('statementid',$objParent->LinkId);
		}
		
		$xmlProperties = $xpath->query("form:Properties",$xmlRelationship)->item(0);
		if (is_object($xmlProperties)){
			getFormXmlAttributes($objParent, $xmlLink, $xmlProperties, $dom, $this->FormNamespace, $xpath, $Seq );
		}
		
		
		$xmlProfileClass = $xpath->query("form:Class",$xmlRelationship)->item(0);
		if (is_object($xmlProfileClass)){
			if ($objParent->ShapeLink->Relationship->Extending === true){
				if (isset($objParent->Document->SubjectForms[$objParent->ToId])){
					$objObjectForm = $objParent->Document->SubjectForms[$objParent->ToId];
					$xmlExtendedSubject = $objObjectForm->getXmlSubject($objObjectForm, $xmlLink, $xmlRelationship, $Seq, $dom);
				}			
			}
		}		
		
		
		
	}
	
	public function getLinkFormDot($Style = 1, $objGraph, &$Nodes = array(), &$Links = array(), &$ConceptClusters = array(), $Level = 1){

		$objLinkForm = $this;

		$objLink = new clsLink($objLinkForm->LinkId);
		$objLink->AsAtDocumentId = $this->DocumentId;

		$FromNodeId = 'subject_'.$objLinkForm->FromId;
		$ToNodeId = 'subject_'.$objLinkForm->ToId;
		
		$Label = '';
		
		switch ($objLinkForm->Inverse){
			case true:
				$RelLabel = $objLinkForm->ShapeLink->Relationship->InverseLabel;			
				break;
			default:
				$RelLabel = $objLinkForm->ShapeLink->Relationship->Label;
				break;								
		}

		
		switch ($Style){
			case 1:
			case 3:
				$Label = $RelLabel;			
				break;
			case 2 or 4:

				$hasValues = false;													
				$Label = "<<table border='0' cellborder='1' cellspacing='0'>";
				$Label .= "<tr><td colspan='2' bgcolor='palegreen'>$RelLabel</td></tr>";
								
				if (!is_null($objLinkForm->EffectiveFrom)){
					$hasValues = true;
					$Label .= "<tr><td align='left' balign='left' valign='top'><b>Effective From</b></td><td  align='left' balign='left' valign='top'>".convertDate($objLinkForm->EffectiveFrom)."</td></tr>";
				}
				if (!is_null($objLinkForm->EffectiveTo)){
					$hasValues = true;
					$Label .= "<tr><td align='left' balign='left' valign='top'><b>Effective To</b></td><td  align='left' balign='left' valign='top'>".convertDate($objLinkForm->EffectiveTo)."</td></tr>";
				}
								
				foreach ($objLinkForm->FormFields as $arrFormFields){
					foreach ($arrFormFields as $objFormField){
						if (!IsEmptyString($objFormField->Value)){
							$AttLabel = "<tr><td align='left' balign='left' valign='top'><b>".$objGraph->FormatDotCell($objFormField->Property->Label,20)."</b></td><td  align='left' balign='left' valign='top'>".$objGraph->FormatDotCell(truncate($objFormField->Value),30)."</td></tr>";
							$Label .= $AttLabel;
							$hasValues = true;
						}
					}
				}
							
				$Label .= "</table>>";
								
				if (!$hasValues){
					$Label = $RelLabel;
				}
				
				break;
		}
		
		

				
		$objGraph->addEdge($FromNodeId,$ToNodeId,$Label);

		if ($Level = 1){
			if (!isset($Nodes[$FromNodeId])){
				if (isset($this->Document->SubjectForms[$objLinkForm->FromId])){
					$this->Document->SubjectForms[$objLinkForm->FromId]->getFormDot($Style, $objGraph, $Nodes, $Links, $ConceptClusters, $Level + 1);
				}
				else
				{
					$objSubject = new clsSubject($objLinkForm->FromId);
					$objAsAtDocument = $this->DocumentId;
					$objSubject->getSubjectDot($Style, $objGraph, $Nodes, $Links, $ConceptClusters, 1, 'lightgoldenrod');					
				}
			}
			if (!isset($Nodes[$ToNodeId])){
				if (isset($this->Document->SubjectForms[$objLinkForm->ToId])){
					$this->Document->SubjectForms[$objLinkForm->ToId]->getFormDot($Style, $objGraph, $Nodes, $Links, $ConceptClusters, $Level + 1);
				}
				else
				{
					$objSubject = new clsSubject($objLinkForm->ToId);
					$objAsAtDocument = $this->DocumentId;					
					$objSubject->getSubjectDot($Style, $objGraph, $Nodes, $Links, $ConceptClusters, 1, 'lightgoldenrod');					
				}
			}
			
		}		
		
	}	
	

}




class clsFormField{
//	public $FieldName = null;
//	public $ClassProp = null; 
//	public $HasProp = null; 

	public $Property = null;
	public $Value = null;
	public $Statement = null;

	public $Cardinality = 'one';
	public $Lists = null;

	public $FormFields = array();

}





function getFormXmlProperties($objParent, $xmlParent, $dom, $Namespace, &$ProfileSeq = 0){
			
	global $Dicts;
	
	$xmlProperties = $dom->createElementNS($Namespace, 'Properties');
	$xmlParent->appendChild($xmlProperties);		
	
	foreach ($objParent->FormFields as $arrFormField){

		$FormField = current($arrFormField);
		$xmlProperty = $dom->createElementNS($Namespace, 'Property');

		$ProfileSeq = $ProfileSeq + 1;
		$xmlProperty->setAttribute('seq',$ProfileSeq);

		$xmlProperty->setAttribute('dictid',$FormField->Property->DictId);
		$xmlProperty->setAttribute('id',$FormField->Property->Id);
		
		
		$xmlProperty->setAttribute('label',$FormField->Property->Label);
		$xmlProperty->setAttribute('cardinality',$FormField->Cardinality);

		switch ($FormField->Property->Type){
			case 'simple':
				
				$xmlProperty->setAttribute('datatype',$FormField->Property->Field->DataType);
				
				if (!IsEmptyString($FormField->Property->Field->Length)){
					$xmlProperty->setAttribute('length',$FormField->Property->Field->Length);
				}

				if (is_array($FormField->Property->Lists)){
					
					$xmlOptions = $dom->createElementNS($Namespace, 'Options');
					$xmlProperty->appendChild($xmlOptions);
					
					foreach ($FormField->Property->Lists as $objPropList){
						$objList = $Dicts->Dictionaries[$objPropList->ListDictId]->Lists[$objPropList->ListId];
						foreach ($objList->Values as $objListValue){
							$optValue = $Dicts->Dictionaries[$objListValue->ValueDictId]->Values[$objListValue->ValueId];
							$xmlOption = $dom->createElementNS($Namespace, 'Option');
							
							xmlSetElementText($xmlOption, $optValue->Label);
							
//							$xmlOption->nodeValue = $optValue->Label;
							$xmlOptions->appendChild($xmlOption);
						}
					}
				}
				break;
			case 'complex':
				
				$xmlProperty->setAttribute('complex','true');										
				getFormXmlProperties($FormField, $xmlProperty, $dom, $Namespace, $ProfileSeq);
													
				break;
			case 'default':
				throw new Exception('unknown property type');
				break;
		}

		$xmlProperties->appendChild($xmlProperty);
	}
	
	return $xmlProperties;
}
	


function getFormXmlAttributes($objParent, $xmlParent, $xmlProperties, $dom, $Namespace, $xpath, &$Seq){
		
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}		

	$xmlAttributes = $dom->createElementNS($Namespace, 'Attributes');
	$Seq = $Seq + 1;
	$xmlAttributes->setAttribute('seq',$Seq);
	
	
	$xmlParent->appendChild($xmlAttributes);
		
		
	foreach ($objParent->FormFields as $arrFormFields){

		$FormField = current($arrFormFields);
		$PropDictId = $FormField->Property->DictId;
		$PropId = $FormField->Property->Id;
		
		$xmlProperty = $xpath->query("form:Property[@dictid='$PropDictId' and @id='$PropId']", $xmlProperties)->item(0);
		if (is_object($xmlProperty)){
			$ProfileSeq = $xmlProperty->getAttribute('seq');
			
			reset($arrFormFields);
			
			if (count($arrFormFields) > 1){
// remove the blank field
				array_shift($arrFormFields);				
			}
			
			foreach ($arrFormFields as $FormField){

				$xmlAttribute = $dom->createElementNS($Namespace, 'Attribute');
				$xmlAttributes->appendChild($xmlAttribute);
				
				$Seq = $Seq + 1;
				$xmlAttribute->setAttribute('seq',$Seq);
				
				$xmlAttribute->setAttribute('profileseq',$ProfileSeq);
				
				if (!is_null($FormField->Statement)){
					$xmlAttribute->setAttribute('statementid',$FormField->Statement->Id);							
				}
				
				switch ($FormField->Property->Type){
					case 'simple':
						
						if (!IsEmptyString($FormField->Value)){
							$xmlValue = $dom->createElementNS($Namespace, 'Value');
							$Seq = $Seq + 1;
							$xmlValue->setAttribute('seq',$Seq);

							$xmlValue->nodeValue = $FormField->Value;
							
							$xmlAttribute->appendChild($xmlValue);
						}
						
						
						break;
					case 'complex':

						$xmlComplexProperties = $xpath->query("form:Properties",$xmlProperty)->item(0);
						if (is_object($xmlComplexProperties)){
							getFormXmlAttributes($FormField, $xmlAttribute, $xmlComplexProperties, $dom, $Namespace, $xpath, $Seq);
						}
						
						break;
				}
			}
		}
					
	}
}


function updateForm($xpathForm, $objForm, $xmlSubject = null, $xmlClass=null){
	
	global $SetId;
	global $DocId;
	global $Mode;

	$SubjectId = $objForm->SubjectId;
	
	if (is_null($xpathForm)){
		return $SubjectId;
	}
	
	$xmlProfile = $xpathForm->query("form:Profile")->item(0);
	if (!is_object($xmlProfile)){
		return $SubjectId;
	}

	$ClassDictId = $objForm->ShapeClass->Class->DictId;
	$ClassId = $objForm->ShapeClass->Class->Id;	
	
	if (is_null($xmlClass)){
		$xmlClass = $xpathForm->query(".//form:Class[@dictid='$ClassDictId' and @id='$ClassId']",$xmlProfile)->item(0);
	}
	if (!is_object($xmlClass)){
		return $SubjectId;
	}

	if (is_null($xmlSubject)){
		$xmlSubject = $xpathForm->query(".//form:Subject")->item(0);
	}
	if (!is_object($xmlSubject)){
		return $SubjectId;
	}

	$ThisMode = $Mode;

	$SubjectId = $objForm->SubjectId;
	$SameAsId = null;
	
// check if subject exists in the data set, and create a sameAs if not
	if (!is_null($SubjectId)){
		$objSubject = new clsSubject($SubjectId);
		if (!($objSubject->CreatedDocumentId == $DocId)){
			$SameAsId = $SubjectId;
			$SubjectId = null;
		}
	}

	
	if (is_null($SubjectId)){		
		$SubjectStatId = null;
		$ThisMode = 'new';
		$SubjectStatId = dataStatUpdate($ThisMode, $SubjectStatId , $SetId, $DocId, 100, $objForm->ShapeClass->Class->DictId, $objForm->ShapeClass->Class->Id);
		$objSubjectStatement = new clsStatement($SubjectStatId);
		$SubjectId = $objSubjectStatement->SubjectId;
	}

	if (!is_null($SameAsId)){
		$SameAsStatId = dataStatUpdate('new', null, $SetId, $DocId, 110, null, null, $SubjectId, $SameAsId);		
	}
	
	foreach ($objForm->FormFields as $FieldNum=>$arrFields){
		UpdateField($xpathForm, $arrFields, $SubjectId, $xmlClass, $xmlSubject );
	}

/*	
	foreach ($xpathForm->query("form:Links/form:Link[form:Subject]", $xmlSubject) as $xmlLink){
		$LinkId = $xmlLink->getAttribute("statementid");
		if (IsEmptyString($LinkId)){
			$LinkId = null;
		}

				
		$ProfileLinkSeq = $xmlLink->getAttribute('profileseq');		
		$xmlRelationship = $xpathForm->query(".//form:Relationship[@seq=$ProfileLinkSeq]", $xmlProfile)->item(0);
		
		if (is_object($xmlRelationship)){
		
			$xmlObject = $xpathForm->query("form:Subject", $xmlLink)->item(0);
			if (is_object($xmlObject)){
			
				$objLinkForm = null;
				if (is_null($LinkId)){
					
					foreach ($objForm->ShapeClass->Shape->ShapeLinks as $optShapeLink){
						if ($optShapeLink->FromShapeClassId == $objForm->ShapeClass->Id){
							if ($optShapeLink->Relationship->DictId == $xmlRelationship->getAttribute("dictid")){
								if ($optShapeLink->Relationship->Id == $xmlRelationship->getAttribute("id")){
									if (isset($objForm->Document->BlankLinkForms[$optShapeLink->Id])){
										$objLinkForm = $objForm->Document->BlankLinkForms[$optShapeLink->Id];
										break;										
									}
									
								}
							}
								
						}
					}
					
				}
				else
				{
					if (isset($objForm->Document->LinkForms[$LinkId])){
						$objLinkForm = $objForm->Document->LinkForms[$LinkId];
					}
				}
				
		
				if (!is_null($objLinkForm)){
					
					$ToShapeClassId = $objLinkForm->ShapeLink->ToShapeClassId;
									
					$LinkMode = 'new';
					if (!is_null($LinkId)){
						$LinkMode = 'edit';
					}
					
					$FromId = $SubjectId;
					$ToId = $xmlObject->getAttribute("id");
					if (IsEmptyString($ToId)){					
						if (isset($objLinkForm->Document->BlankSubjectForms[$ToShapeClassId])){
							$objObjectForm = $objLinkForm->Document->BlankSubjectForms[$ToShapeClassId];						
						}					
					}
					else
					{
						if (isset($objForm->Document->SubjectForms[$ToId])){
							$objObjectForm = $objForm->Document->SubjectForms[$ToId];
						}
					}
					
					$ProfileObjectSeq = $xmlObject->getAttribute('profileseq');		
					$xmlObjectClass = $xpathForm->query(".//form:Class[@seq=$ProfileObjectSeq]", $xmlProfile)->item(0);
	
					if (is_object($xmlObjectClass)){
						$ToId = updateForm($xpathForm, $objObjectForm, $xmlObject, $xmlObjectClass);
						
						switch ($objLinkForm->ShapeLink->Inverse){
							case true:
								$LinkId = dataStatUpdate($LinkMode, $LinkId , $SetId, $DocId, 300, $objLinkForm->ShapeLink->Relationship->DictId, $objLinkForm->ShapeLink->Relationship->Id, $ToId, $FromId);
								break;						
							default:
								$LinkId = dataStatUpdate($LinkMode, $LinkId , $SetId, $DocId, 300, $objLinkForm->ShapeLink->Relationship->DictId, $objLinkForm->ShapeLink->Relationship->Id, $FromId, $ToId);
								break;						
						}

						updateLinkForm($LinkId, $xpathForm, $objLinkForm, $xmlLink, $xmlRelationship);
					}
				}
			}
		}
	}
*/				
	return $SubjectId;

}
	


function UpdateField($xpathForm, $arrFields = array(), $SubjectId, $xmlProfileParent, $xmlSubjectParent, $AboutStatement=null){
		
	global $SetId;
	global $DocId;
	global $Mode;
	
/*	
	$NewAttributes = false;
// force attributes to be created if this is a SameAs form.
	if (!IsEmptyString($xmlSubjectParent->getAttribute('id'))){
		if (!($xmlSubjectParent->getAttribute('id') == $SubjectId)){
			$NewAttributes = true;
		}		
	}
*/	
	
	
	$objFormField = current($arrFields);
	$PropDictId = $objFormField->Property->DictId;
	$PropId = $objFormField->Property->Id;
	$xmlProperty = $xpathForm->query("form:Properties/form:Property[@dictid='$PropDictId' and @id='$PropId']", $xmlProfileParent)->item(0);
		
	if (!is_object($xmlProperty)){
		continue;
	}
	
	$ProfileSeq = $xmlProperty->getAttribute('seq');
	if (isemptystring($ProfileSeq)){
		continue;
	}
	
	switch ($objFormField->Property->Type){
		case 'simple':
			
			foreach ($arrFields as $occ=>$objFormField){
				if (!is_null($objFormField->Statement)){							
					$FieldStatId = $objFormField->Statement->Id;
					
					$FieldMode = 'edit';
					
					$Value = '';
					$xmlValue = $xpathForm->query("form:Attributes/form:Attribute[@profileseq='$ProfileSeq' and @statementid=$FieldStatId]/form:Value",$xmlSubjectParent)->item(0);
					if (is_object($xmlValue)){							
						$Value = $xmlValue->nodeValue;
					}

					if (empty($Value)){
						dataStatDelete($objFormField->Statement->Id);
					}
					elseif ($objFormField->Statement->SubjectId == $SubjectId){

						// create a new statement if the existing statement is NOT from the current document
						if (!($objFormField->Statement->DocId == $DocId)){
							$FieldStatId = null;
							$FieldMode = 'new';
						}
						
						$FieldStatId = dataStatUpdate($FieldMode, $FieldStatId , $SetId, $DocId, 200, $PropDictId, $PropId, $SubjectId, null, $Value, null, null, $AboutStatement);
					}
					else
					{
						// same as
						$FieldStatId = null;
						$FieldMode = 'new';						
						$FieldStatId = dataStatUpdate($FieldMode, $FieldStatId , $SetId, $DocId, 200, $PropDictId, $PropId, $SubjectId, null, $Value, null, null, $AboutStatement);
					}
					
				}
			}

			foreach ($xpathForm->query("form:Attributes/form:Attribute[not(@statementid) and @profileseq='$ProfileSeq']/form:Value",$xmlSubjectParent) as $xmlValue){
				$FieldMode = 'new';
				$FieldStatId = null;
				$Value = $xmlValue->nodeValue;
				
				if (!IsEmptyString($Value)){
					$FieldStatId = dataStatUpdate($FieldMode, $FieldStatId , $SetId, $DocId, 200, $PropDictId, $PropId, $SubjectId, null, $Value, null, null, $AboutStatement);
				}
			}
			break;
		case 'complex':
			
			foreach ($arrFields as $occ=>$objFormField){
				
				if (!is_null($objFormField->Statement)){
					
					if ($objFormField->Statement->SubjectId == $SubjectId){
						
						$FieldStatId = $objFormField->Statement->Id;
						
						$xmlComplexAttribute = $xpathForm->query("form:Attributes/form:Attribute[@profileseq='$ProfileSeq' and @statementid=$FieldStatId]",$xmlSubjectParent)->item(0);
						if (is_object($xmlComplexAttribute)){

							// create a new statement if the exisiting statement is NOT from the current document
//							if (!($objFormField->Statement->DocId == $DocId)){
//								$FieldStatId = null;
//							}
														
							foreach ($objFormField->FormFields as $arrComplexFields){
								UpdateField($xpathForm, $arrComplexFields, $SubjectId, $xmlProperty, $xmlComplexAttribute, $FieldStatId);
							}
						}
						else
						{
							dataStatDelete($objFormField->Statement->Id);						
						}
					}
				}
			}
			
			reset($arrFields);
			$objFormField = current($arrFields);
			// sets objFormField to the blank FormField
			
			
			foreach ($xpathForm->query("form:Attributes/form:Attribute[not(@statementid) and @profileseq='$ProfileSeq']",$xmlSubjectParent) as $xmlComplexAttribute){
				$FieldStatId = null;
				$FieldMode = 'new';
				$FieldStatId = dataStatUpdate($FieldMode, $FieldStatId , $SetId, $DocId, 200, $PropDictId, $PropId, $SubjectId, null, null, null, null, $AboutStatement);
				
				foreach ($objFormField->FormFields as $arrComplexFields){
					UpdateField($xpathForm, $arrComplexFields, $SubjectId, $xmlProperty, $xmlComplexAttribute, $FieldStatId);
				}
			}
				
			break;
	}
	
	
}


function updateLinkForm($LinkId, $xpathLinkForm, $objLinkForm, $xmlLink = null, $xmlRelationship=null){
	
	global $SetId;
	global $DocId;
	global $Mode;
	
	if (is_null($xpathLinkForm)){
		return $LinkId;
	}
	
	$xmlProfile = $xpathLinkForm->query("form:Profile")->item(0);
	if (!is_object($xmlProfile)){
		return $LinkId;
	}

	$RelDictId = $objLinkForm->ShapeLink->Relationship->DictId;
	$RelId = $objLinkForm->ShapeLink->Relationship->Id;	
	
	if (is_null($xmlRelationship)){
		$xmlRelationship = $xpathLinkForm->query("form:Relationship[@dictid='$RelDictId' and @id='$RelId']",$xmlProfile)->item(0);
	}
	if (!is_object($xmlRelationship)){
		return $LinkId;
	}

	if (is_null($xmlLink)){
		$xmlLink = $xpathLinkForm->query("form:Link")->item(0);
	}
	if (!is_object($xmlLink)){
		return $LinkId;
	}

	foreach ($objLinkForm->FormFields as $FieldNum=>$arrFields){		
		UpdateLinkField($xpathLinkForm, $arrFields, $xmlRelationship, $xmlLink, $LinkId);
	}

	return $LinkId;

}
	


function UpdateLinkField($xpathLinkForm, $arrFields = array(), $xmlProfileParent, $xmlLinkParent, $AboutStatement=null){
		
	global $SetId;
	global $DocId;
	global $Mode;
	
	$objFormField = current($arrFields);
	$PropDictId = $objFormField->Property->DictId;
	$PropId = $objFormField->Property->Id;
	$xmlProperty = $xpathLinkForm->query("form:Properties/form:Property[@dictid='$PropDictId' and @id='$PropId']", $xmlProfileParent)->item(0);
		
	if (!is_object($xmlProperty)){
		continue;
	}
	
	$ProfileSeq = $xmlProperty->getAttribute('seq');
	if (isemptystring($ProfileSeq)){
		continue;
	}
	
	switch ($objFormField->Property->Type){
		case 'simple':
			
			foreach ($arrFields as $occ=>$objFormField){
				
				if (!is_null($objFormField->Statement)){							
					$FieldStatId = $objFormField->Statement->Id;
					
					$FieldMode = 'edit';
					if ($objFormField->Statement->AboutId == $AboutStatement){
						
						$Value = '';
						$xmlValue = $xpathLinkForm->query("form:Attributes/form:Attribute[@profileseq='$ProfileSeq' and @statementid=$FieldStatId]/form:Value",$xmlLinkParent)->item(0);
						if (is_object($xmlValue)){							
							$Value = $xmlValue->nodeValue;
						}

						// create a new statement if the exisiting statement is NOT from the current document
						if (!($objFormField->Statement->DocId == $DocId)){
							$FieldStatId = null;
							$FieldMode = 'new';
						}
						
						$FieldStatId = dataStatUpdate($FieldMode, $FieldStatId , $SetId, $DocId, 200, $PropDictId, $PropId, null, null, $Value, null, null, $AboutStatement);
					}
				}
			}

			foreach ($xpathLinkForm->query("form:Attributes/form:Attribute[not(@statementid) and @profileseq='$ProfileSeq']/form:Value",$xmlLinkParent) as $xmlValue){
				$FieldMode = 'new';
				$FieldStatId = null;
				$Value = $xmlValue->nodeValue;
				
				if (!IsEmptyString($Value)){
					$FieldStatId = dataStatUpdate($FieldMode, $FieldStatId , $SetId, $DocId, 200, $PropDictId, $PropId, null, null, $Value, null, null, $AboutStatement);
				}
			}
			break;
		case 'complex':
			
			foreach ($arrFields as $occ=>$objFormField){
				
				if (!is_null($objFormField->Statement)){
					
					if ($objFormField->Statement->AboutId == $AboutId){
						
						$FieldStatId = $objFormField->Statement->Id;
						
						$xmlComplexAttribute = $xpathLinkForm->query("form:Attributes/form:Attribute[@profileseq='$ProfileSeq' and @statementid=$FieldStatId]",$xmlLinkParent)->item(0);
						if (is_object($xmlComplexAttribute)){

							// create a new statement if the exisiting statement is NOT from the current document
//							if (!($objFormField->Statement->DocId == $DocId)){
//								$FieldStatId = null;
//							}
														
							foreach ($objFormField->FormFields as $arrComplexFields){
								UpdateField($xpathLinkForm, $arrComplexFields, $xmlProperty, $xmlComplexAttribute, $FieldStatId);
							}
						}
					}
				}
			}
			
			reset($arrFields);
			$objFormField = current($arrFields);
			// sets objFormField to the blank FormField
			
			
			foreach ($xpathLinkForm->query("form:Attributes/form:Attribute[not(@statementid) and @profileseq='$ProfileSeq']",$xmlLinkParent) as $xmlComplexAttribute){
				$FieldStatId = null;
				$FieldMode = 'new';
				$FieldStatId = dataStatUpdate($FieldMode, $FieldStatId , $SetId, $DocId, 200, $PropDictId, $PropId, null, null, null, null, null, $AboutStatement);
				
				foreach ($objFormField->FormFields as $arrComplexFields){
					UpdateField($xpathLinkForm, $arrComplexFields, $xmlProperty, $xmlComplexAttribute, $FieldStatId);
				}
			}
				
			break;
	}
	
	
}


?>