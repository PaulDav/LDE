<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');
require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsRecordset.php');

require_once('clsDict.php');
require_once('clsDocument.php');
require_once('clsRights.php');
require_once('clsShape.php');

class clsLog {
	
	public $Id = null;
	public $DateTime = null;
	public $UserId = null;
	public $Status = null;
	public $StatusText = null;
	
}

class clsSets{

	private $PurposeId = null;
	private $OrgId = null;
	private $ContextId = null;
	private $LicenceTypeId = null;
	
	private $ShapeId = null;
	
	private $System = null;

	private $Items = array();
	
	private $SetsFound = false;
	private $WhereSet = false;
	
	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;
	
	private $LdeNamespace = "http://schema.legsb.gov.uk/lde/";
	private $MetaNamespace = "http://schema.legsb.gov.uk/lde/metadata/";
	
	

	public function __construct(){
		
		global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}
	 	$this->System = $System;
	}
	
	public function __set($name, $value){
		switch ($name){
			case 'PurposeId':
				$this->PurposeId = $value;
				$this->SetsFound = false;
				break;
			case 'OrgId':
				$this->OrgId = $value;
				$this->SetsFound = false;
				break;
			case 'ContextId':
				$this->ContextId = $value;
				$this->SetsFound = false;
				break;				
			case 'LicenceTypeId':
				$this->LicenceTypeId = $value;
				$this->SetsFound = false;
				break;				
				
			case 'ShapeId':
				$this->ShapeId = $value;
				$this->SetsFound = false;
				break;				
				
		}
	}

	
	public function __get($name){
		switch ($name){
			case 'Items':
				if (!$this->SetsFound){
					$this->getSets();					
				}
				break;

			case 'xml':
				return $this->getXML();
				break;

		}
		return $this->$name;
	}
	
	private function getSets(){
	
		$this->Items = array();
		$this->SetsFound = true;
		$this->WhereSet = false;
		
		$sql = "SELECT * FROM tbl_set ";
		
		if (!is_null($this->PurposeId)){
			$sql .= " LEFT JOIN tbl_set_purpose ON setprpSet = setRecnum ";
		}

		if (!is_null($this->ShapeId)){
			$sql .= " LEFT JOIN tbl_set_shape ON setshpSet = setRecnum ";
		}		
		
		if (!is_null($this->OrgId)){
			$sql .= $this->setWhereAnd();
			$sql .= " setOrg = ".$this->OrgId;
		}

		if (!is_null($this->PurposeId)){
			$sql .= $this->setWhereAnd();
			$sql .= " setprpPurpose = ".$this->PurposeId;
		}

		
		if (!is_null($this->ContextId)){
			$sql .= $this->setWhereAnd();
			$sql .= " setContext = ".$this->ContextId;
		}

		if (!is_null($this->LicenceTypeId)){
			$sql .= $this->setWhereAnd();
			$sql .= " setLicenceType = ".$this->LicenceTypeId;
		}
		
		
		if (!is_null($this->ShapeId)){
			$sql .= $this->setWhereAnd();
			$sql .= " setshpShape = '".PrepUserInput($this->ShapeId)."'";
		}
		
		$sql .= ";";

		$rst = $this->System->DbExecute($sql);

		while ($row = mysqli_fetch_array($rst, MYSQLI_ASSOC)) {
			$SetId = $row['setRecnum'];
			
			$objSet = new clsSet($SetId);
			if (is_object($objSet)){
			 	$this->Items[$SetId] = $objSet;
			}
		}		
	}
	
			
			
	private function setWhereAnd(){
	
		if ($this->WhereSet === false){
			$this->WhereSet = true;
			return " WHERE ";
		}
		
		return " AND ";
	
	}
	
	public function getItem($SetId){

		if (!$this->SetsFound){
			$this->getSets();
		}

		if (!isset($this->Items[$SetId])){
			return false;
		}

		return $this->Items[$SetId];

	}
	
	private function getXML(){
	
		if (!$this->SetsFound){
			$this->getSets();
		}

		global $Orgs;
		if (!isset($Orgs)){
			$Orgs = new clsOrganisations();
		}
		
		global $Shapes;
		if (!isset($Shapes)){
			$Shapes = new clsShapes();
		}
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
	
		$DocumentElement = $this->dom->createElementNS($this->LdeNamespace, 'Sets');
		$this->dom->appendChild($DocumentElement);
		$DocumentElement->setAttribute("xmlns:meta", $this->MetaNamespace);
		
		
		foreach ($this->Items as $objSet){

			$xmlSet = $this->dom->createElementNS($this->LdeNamespace, 'Set');
			$DocumentElement->appendChild($xmlSet);
			
			$xmlSet->setAttribute('id',$objSet->Id);
			
			if (!is_null($objSet->Context)){
				$xmlSet->setAttribute('context',$objSet->Context->Name);
			}
			
			if (!is_null($objSet->Status)){
				$xmlSet->setAttribute('status',$objSet->StatusText);
			}

			if (!is_null($objSet->LicenceType)){
				$xmlSet->setAttribute('licenceType',$objSet->LicenceTypeText);
			}
			
									
			$xmlName = $this->dom->createElementNS($this->LdeNamespace, 'Name');
			$xmlSet->appendChild($xmlName);
			$xmlName->nodeValue = $objSet->Name;
			
			
			$xmlOrg = $this->dom->createElementNS($this->LdeNamespace, 'Organisation');
			$xmlSet->appendChild($xmlOrg);
			$xmlOrg->setAttribute('id',$objSet->OrgId);
			if (isset($Orgs->Items[$objSet->OrgId])){
				$objOrg = $Orgs->Items[$objSet->OrgId];
				$xmlName = $this->dom->createElementNS($this->LdeNamespace, 'Name');
				$xmlOrg->appendChild($xmlName);
				$xmlName->nodeValue = $objOrg->Name;				
			}
			
			$xmlShapes = $this->dom->createElementNS($this->LdeNamespace, 'Shapes');
			$xmlSet->appendChild($xmlShapes);
			foreach ($objSet->SetShapes as $objSetShape){
				if (isset($Shapes->Items[$objSetShape->ShapeId])){
					$objShape = $Shapes->Items[$objSetShape->ShapeId];
					$xmlShape = $this->dom->createElementNS($this->LdeNamespace, 'Shape');
					$xmlShapes->appendChild($xmlShape);
					$xmlShape->setAttribute('id',$objShape->Id);
					$xmlName = $this->dom->createElementNS($this->LdeNamespace, 'Name');
					$xmlShape->appendChild($xmlName);
					$xmlName->nodeValue = $objShape->Name;
				}
			}
			
			$xmlPurposes = $this->dom->createElementNS($this->LdeNamespace, 'Purposes');
			$xmlSet->appendChild($xmlPurposes);
			foreach ($objSet->SetPurposes as $objSetPurpose){
				if (isset($Defs->Items[$objSetPurpose->PurposeId])){
					$objPurpose = $Defs->Items[$objSetPurpose->PurposeId];
					$xmlPurpose = $this->dom->createElementNS($this->LdeNamespace, 'Purpose');
					$xmlPurposes->appendChild($xmlPurpose);
					$xmlPurpose->setAttribute('id',$objPurpose->Id);
					$xmlName = $this->dom->createElementNS($this->LdeNamespace, 'Name');
					$xmlPurpose->appendChild($xmlName);
					$xmlName->nodeValue = $objPurpose->Name;
				}
			}
			
			
		}
		
		return $this->dom->saveXML();
		
	}	
	
	
}

class clsSet {

  private $Id;
  private $OrgId = null;
  
  private $Name;
  private $Source;
  
  private $Status = 1;
  private $StatusText = null;
  
  private $ContextId = null;
  private $Context = null;

  private $LicenceType = null;
  private $LicenceTypeText = null;
  
  private $SetShapes = array();
  private $SetProfiles = array();
  
  private $SetPurposes = array();
  
  private $DocumentIds = null;
  private $StatementIds = null;
  private $Logs = array();
  
  private $CurrentLog = null;
  
  private $canView = false;
  private $canEdit = false;
  private $canControl = false;
  
	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;
	
	private $System = null;
  
  
	public function __get($name){
		
	  	switch ($name){
			case 'StatementIds':
				if (is_null($this->StatementIds)){
					$this->getStatementIds();
				}
				break;
			case 'DocumentIds':
				if (is_null($this->DocumentIds)){
					$this->getDocumentIds();
				}
				break;				
			case 'xml':
				return $this->getXML();
				break;
				
	  	}
	  	
	  	return $this->$name;
	  }
  	
	 public function __construct ($Id){
		 	
	 	global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}
	 	$this->System = $System;
	 		 	 	
	 	$sql = "SELECT * FROM tbl_set WHERE setRecnum = $Id";
	 	
	 	$rst = $System->DbExecute($sql);
		if (!$rst->num_rows > 0){
			return false;
		}
		$rstRow = $rst->fetch_assoc();	

		$this->Id = $rstRow['setRecnum'];
		$this->OrgId = $rstRow['setOrg'];
		
		$this->Name = stripcslashes(Encode($rstRow['setName']));
		$this->Source = stripcslashes(Encode($rstRow['setSource']));
		$this->ContextId = $rstRow['setContext'];
		
		if (isset($System->Config->SetContextTypes[$this->ContextId])){
			$this->Context = $System->Config->SetContextTypes[$this->ContextId];
		}
		
		
		$this->LicenceType = $rstRow['setLicenceType'];
		
		$sql = "SELECT * FROM tbl_log WHERE logSet = $Id ORDER BY logTime";
	 	$rstLog = $System->DbExecute($sql);

		while ($rowLog = $rstLog->fetch_assoc()) {
			$objLog = new clsLog;
			$objLog->DateTime = $rowLog['logTime'];
			$objLog->UserId = $rowLog['logBy'];
			$objLog->Status = $rowLog['logStatus'];
						
			if (isset($System->Config->SetStatusTypes[$rowLog['logStatus']])){
				$objLog->StatusText = $System->Config->SetStatusTypes[$rowLog['logStatus']];
			}
			
			$this->Status = $objLog->Status;
			
			$this->Logs[] = $objLog;
			$this->CurrentLog = $objLog;
		}
		
		if (isset($System->Config->SetStatusTypes[$this->Status])){		
		 	$this->StatusText = $System->Config->SetStatusTypes[$this->Status];
		}
		if (isset($System->Config->SetLicenceTypeTypes[$this->LicenceType])){		
		 	$this->LicenceTypeText = $System->Config->SetLicenceTypeTypes[$this->LicenceType];
		}
		
/*		
		$sql = "SELECT * FROM tbl_set_profile WHERE setprfSet = $Id";
	 	$rstProfile = $System->DbExecute($sql);
		
		while ($rowProfile = $rstProfile->fetch_assoc()) {
			$objSetProfile = new clsSetProfile;
			$objSetProfile->Id = $rowProfile['setprfRecnum'];
			$objSetProfile->ProfileId = $rowProfile['setprfProfile'];
			$this->SetProfiles[$objSetProfile->Id] = $objSetProfile;
		}		
*/
		$sql = "SELECT * FROM tbl_set_shape WHERE setshpSet = $Id";
	 	$rstShape = $System->DbExecute($sql);
		
		while ($rowShape = $rstShape->fetch_assoc()) {
			$objSetShape = new clsSetShape;
			$objSetShape->Id = $rowShape['setshpRecnum'];
			$objSetShape->ShapeId = $rowShape['setshpShape'];
			$this->SetShapes[$objSetShape->Id] = $objSetShape;
		}		
		
		
		$sql = "SELECT * FROM tbl_set_purpose WHERE setprpSet = $Id";
	 	$rstPurpose = $System->DbExecute($sql);
		
		while ($rowPurpose = $rstPurpose->fetch_assoc()) {
			$objSetPurpose = new clsSetPurpose;
			$objSetPurpose->Id = $rowPurpose['setprpRecnum'];
			$objSetPurpose->PurposeId = $rowPurpose['setprpPurpose'];
			$this->SetPurposes[$objSetPurpose->Id] = $objSetPurpose;
		}		
		
		
		$this->canView = true;
/*		
		if ($System->LoggedOn){
			if ($System->User->Id == $this->OwnerId){
				$this->canView = true;
				$this->canEdit = true;
				$this->canControl = true;
			}
		}
*/
//		if ($this->Group->canControl) {
			$this->canView = true;
			$this->canEdit = true;
			$this->canControl = true;
//		}
		
//		if ($this->Group->MyRights->Rights > 0){
			$this->canView = true;
//		}
		
			
	}
	
	private function getStatementIds(){

		$System = $this->System;

		$this->StatementIds = null;
		
		$sql = "SELECT * FROM tbl_statement WHERE stmSet = $this->Id";
	 	$rstStatement = $System->DbExecute($sql);
		
		while ($rowStatement = $rstStatement->fetch_assoc()) {
			$this->StatementIds[] = $rowStatement['stmRecnum'];
		}
	}
	
	
	private function getDocumentIds(){

		$System = $this->System;
		
		$this->DocumentIds = null;
		
		$sql = "SELECT * FROM tbl_document WHERE docSet = $this->Id";
	 	$rstDocument = $System->DbExecute($sql);
		
		while ($rowDocument = $rstDocument->fetch_assoc()) {
			$this->DocumentIds[] = $rowDocument['docRecnum'];
		}
		
	}
	
	private function getXML(){
		
		$System = $this->System;
		
		if (is_null($this->StatementIds)){
			$this->getStatementIds();
		}		
		
		if (is_null($this->DocumentIds)){
			$this->getDocumentIds();
		}

		global $Orgs;
		if (!isset($Orgs)){
			$Orgs = new clsOrganisations();
		}
		
		global $Shapes;
		if (!isset($Shapes)){
			$Shapes = new clsShapes();
		}

		$objSet = $this;
		
		$nsLde = $System->Config->Namespaces['lde'];
		$nsMeta = $System->Config->Namespaces['meta'];
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
	
		$xmlSet = $this->dom->createElementNS($nsLde, 'Set');
		$this->dom->appendChild($xmlSet);
		$xmlSet->setAttribute("xmlns:meta", $nsMeta);
		
		$xmlSet->setAttribute('id',$objSet->Id);
			
		if (!is_null($objSet->Context)){
			$xmlSet->setAttribute('context',$objSet->Context->Name);
		}
		
		if (!is_null($objSet->Status)){
			$xmlSet->setAttribute('status',$objSet->StatusText);
		}

		if (!is_null($objSet->LicenceType)){
			$xmlSet->setAttribute('licenceType',$objSet->LicenceTypeText);
		}
		
								
		$xmlName = $this->dom->createElementNS($nsLde, 'Name');
		$xmlSet->appendChild($xmlName);
		$xmlName->nodeValue = $objSet->Name;
		
		
		$xmlOrg = $this->dom->createElementNS($nsLde, 'Organisation');
		$xmlSet->appendChild($xmlOrg);
		$xmlOrg->setAttribute('id',$objSet->OrgId);
		if (isset($Orgs->Items[$objSet->OrgId])){
			$objOrg = $Orgs->Items[$objSet->OrgId];
			$xmlName = $this->dom->createElementNS($nsLde, 'Name');
			$xmlOrg->appendChild($xmlName);
			$xmlName->nodeValue = $objOrg->Name;				
		}
		
		$xmlShapes = $this->dom->createElementNS($nsLde, 'Shapes');
		$xmlSet->appendChild($xmlShapes);
		foreach ($objSet->SetShapes as $objSetShape){
			if (isset($Shapes->Items[$objSetShape->ShapeId])){
				$objShape = $Shapes->Items[$objSetShape->ShapeId];
				$xmlShape = $this->dom->createElementNS($nsLde, 'Shape');
				$xmlShapes->appendChild($xmlShape);
				$xmlShape->setAttribute('id',$objShape->Id);
				$xmlName = $this->dom->createElementNS($nsLde, 'Name');
				$xmlShape->appendChild($xmlName);
				$xmlName->nodeValue = $objShape->Name;
			}
		}
		
		$xmlPurposes = $this->dom->createElementNS($nsLde, 'Purposes');
		$xmlSet->appendChild($xmlPurposes);
		foreach ($objSet->SetPurposes as $objSetPurpose){
			if (isset($Defs->Items[$objSetPurpose->PurposeId])){
				$objPurpose = $Defs->Items[$objSetPurpose->PurposeId];
				$xmlPurpose = $this->dom->createElementNS($this->$nsLde, 'Purpose');
				$xmlPurposes->appendChild($xmlPurpose);
				$xmlPurpose->setAttribute('id',$objPurpose->Id);
				$xmlName = $this->dom->createElementNS($nsLde, 'Name');
				$xmlPurpose->appendChild($xmlName);
				$xmlName->nodeValue = $objPurpose->Name;
			}
		}		
		
		$xmlDocuments = $this->dom->createElementNS($nsLde, 'Documents');
		$xmlSet->appendChild($xmlDocuments);
		
		if (!is_null($this->DocumentIds)){
			foreach ($this->DocumentIds as $DocId){
				$objDoc = new clsDocument($DocId);			
				$xmlDoc = $this->dom->importNode($objDoc->xml->documentElement,true);
				if (is_object($xmlDoc)){
					$xmlDocuments->appendChild($xmlDoc);
				}
			}
		}
		
		return $this->dom;
		
	}	
	
	
	
}

class clsSetShape {

  public $Id;  
  public $ShapeId = null;
    
}  


class clsSetProfile {

  public $Id;  
  public $ProfileId = null;
    
}  

class clsSetPurpose {

  public $Id;  
  public $PurposeId = null;
    
}  


class clsStatement {

  private $Id;
  private $SetId = null;
  private $DocId = null;
  
  public  $TypeId = null;
  private $TypeLabel = null;
  private $LinkDictId = null;
  private $LinkId = null;
  private $AboutId = null;
  private $SubjectId = null;
  private $ObjectId = null;
  private $Value = null;
  
  private $EffectiveFrom = null;
  Private $EffectiveTo = null;

   	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;
  
  	private $System = null;
	
	public function __get($name){
	  	switch ($name){
	  		case 'xml':
				return $this->getXML();
				break;
	  	}
	  	return $this->$name;
	  }
	  
	 public function __construct ($Id, $rstRow = null){
		 	
	 	global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}
	 	$this->System = $System;

	 	if (is_null($rstRow)){	 		
		 	$sql = "SELECT * FROM tbl_statement WHERE stmRecnum = $Id";		 			 	
		 	$rst = $System->DbExecute($sql);
			if (!$rst->num_rows > 0){
				return false;
			}
			$rstRow = $rst->fetch_assoc();	
	 	}
			
		$this->Id = $rstRow['stmRecnum'];

		$this->TypeId = $rstRow['stmType'];
		if (isset($System->Config->StatementTypes[$this->TypeId])){
			$this->TypeLabel = $System->Config->StatementTypes[$this->TypeId];
		}
		
		$this->AboutId = $rstRow['stmAboutStatement'];
		$this->SubjectId = $rstRow['stmSubject'];
		$this->ObjectId = $rstRow['stmObject'];
		
		$this->LinkDictId = stripcslashes(Encode($rstRow['stmLinkDict']));
		$this->LinkId = $rstRow['stmLink'];
		
		if ($this->TypeId == 200){
			
			$sql = "SELECT valRecnum, valDataType FROM tbl_statement INNER JOIN tbl_value ON valStatement = stmRecnum WHERE stmRecnum = $Id";
		 	$rstValId = $System->DbExecute($sql);
		 	
			if ($rstValId->num_rows > 0){
				$rowValId = $rstValId->fetch_assoc();	
	
				if (isset($rowValId['valRecnum'])){
					
		// get the value
					$sql = null;
					$ValueId = $rowValId['valRecnum'];
					
					switch ($rowValId['valDataType']){
						case 100:
						case 800:
						case 900:
						case 950:
							$sql = "SELECT valValue FROM tbl_value_string WHERE valRecnum = $ValueId;";
							break;
						case 200:
							$sql = "SELECT valValue FROM tbl_value_memo WHERE valRecnum = $ValueId;";
							break;
						case 300:
						case 400:	
							$sql = "SELECT valValue FROM tbl_value_datetime WHERE valRecnum  = $ValueId;";
							break;
						case 500:
						case 600:	
							$sql = "SELECT valValue FROM tbl_value_integer WHERE valRecnum  = $ValueId;";
							break;
						case 700:	
							$sql = "SELECT valValue FROM tbl_value_number WHERE valRecnum  = $ValueId;";
							break;					
					}
					if (!is_null($sql)){				
						$rstValue = $System->DbExecute($sql);				
						if ($rstValue->num_rows > 0){
							$rowValue = $rstValue->fetch_assoc();
							$this->Value = $rowValue['valValue'];
						}
					}
				}
			}
			
			
			
			
			if (!is_null($this->Value)){
				$objDictItem = new clsDictItem($this->LinkDictId,$this->LinkId, $this->TypeId);
				if (is_object($objDictItem->Object)){
					switch ($objDictItem->Object->Field->DataType){
						case 'date':
							$date = DateTime::createFromFormat('Y-m-d h:i:s', $this->Value);
							if (is_object($date)){
								$this->Value = $date->format('d/m/Y');
							}
							break;						
					}
				}
			
			}		
		}

		if (!is_null($rstRow['stmEffFrom'])){
			$date = DateTime::createFromFormat('!Y-m-d h:i:s', $rstRow['stmEffFrom']);
			if (is_object($date)){
				$this->EffectiveFrom = $date->format('d/m/Y');
			}			
		}
		if (!is_null($rstRow['stmEffTo'])){
			$date = DateTime::createFromFormat('!Y-m-d h:i:s', $rstRow['stmEffTo']);
			if (is_object($date)){
				$this->EffectiveTo = $date->format('d/m/Y');
			}			
		}		

		$this->SetId = $rstRow['stmSet'];		
		
		
		$this->DocId = $rstRow['stmDocument'];

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
	
	private function getXML(){

		$System = $this->System;
		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDictionaries();
		}
				
		$objStatement = $this;
		
		$nsLde = $System->Config->Namespaces['lde'];
		$nsMeta = $System->Config->Namespaces['meta'];
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
	
		$xmlStatement = $this->dom->createElementNS($nsLde, 'Statement');
		$this->dom->appendChild($xmlStatement);
		$xmlStatement->setAttribute("xmlns:meta", $nsMeta);
		
		$xmlStatement->setAttribute('id',$objStatement->Id);
		$xmlStatement->setAttribute('setid',$objStatement->SetId);
		$xmlStatement->setAttribute('docid',$objStatement->DocId);

		$xmlStatement->setAttribute('typeid',$objStatement->TypeId);
		if (!is_null($objStatement->TypeLabel)){
			$xmlStatement->setAttribute('type',$objStatement->TypeLabel);
		}

		if (!is_null($objStatement->LinkDictId)){
			$xmlStatement->setAttribute('linkdictid',$objStatement->LinkDictId);
		}

		if (!is_null($objStatement->LinkId)){
			$xmlStatement->setAttribute('linkid',$objStatement->LinkId);
			
			$objDictItem = $Dicts->getItem($objStatement->LinkDictId, $objStatement->LinkId, $objStatement->TypeId);
			if (is_object($objDictItem)){
				$xmlStatement->setAttribute('linklabel',$objDictItem->Label);				
			}
		}

		if (!is_null($objStatement->AboutId)){
			$xmlStatement->setAttribute('aboutid',$objStatement->AboutId);
		}

		if (!is_null($objStatement->SubjectId)){
			$xmlStatement->setAttribute('subjectid',$objStatement->SubjectId);
		}

		if (!is_null($objStatement->ObjectId)){
			$xmlStatement->setAttribute('objectid',$objStatement->ObjectId);
		}
		
		if (!is_null($objStatement->Value)){
			$xmlStatement->setAttribute('value',$objStatement->Value);
		}
		
		if (!is_null($objStatement->EffectiveFrom)){
			$xmlStatement->setAttribute('effectivefrom',$objStatement->EffectiveFrom);
		}

		if (!is_null($objStatement->EffectiveTo)){
			$xmlStatement->setAttribute('effectiveto',$objStatement->EffectiveTo);
		}		
		
		return $this->dom;
		
	}	
}

class clsDictItem {

  private $Id;
  private $DictId;
  private $Type;
  
  public $Label = null;
  
  public $Object = null;

  public function __construct($DictId, $Id, $Type=100){
  	
  	$this->Id = $Id;
  	$this->DictId = $DictId;
  	
  	global $Dicts;
  	if (!isset($Dicts)){
  		$Dicts = new clsDicts();
  	}
  	
  	if (!isset($Dicts->Dictionaries[$DictId])){
  		throw new exception("Unknown Dictionary");
  	}
  	$objDict = $Dicts->Dictionaries[$DictId];
  	
  	switch ($Type){
  		case 100:
  			if (isset($objDict->Classes[$Id])){
	  			$this->Object = $objDict->Classes[$Id];
				$this->Label = $this->Object->Label;
  			}
  			break;
  			
   		case 200:
  			if (isset($objDict->Properties[$Id])){
	  			$this->Object = $objDict->Properties[$Id];
				$this->Label = $this->Object->Label;
  			}
  			break;
  			
   		case 300:
  			if (isset($objDict->Relationships[$Id])){
	  			$this->Object = $objDict->Relationships[$Id];
				$this->Label = $this->Object->Label;
  			}
  			break;
  			
  		default:
  			break;
  	}
  	
  	
  }

}


class clsSubjects{
	
	private $ClassDictId;
	private $ClassId;
	private $Class = null;
	
	private $SubjectIds = array();
	private $Subjects = null;
	
	private $System;
	private $Dicts;
	
	public $ShapeId = null; // only include a subject if is has been referenced in a document for this shape
	public $FilterClass = null;
	
	public $SetId = null;
	public $ContextId = null;
	public $AsAtDocumentId = null;
	
	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;

	private $nsLde = null;
	private $nsMeta = null;	
	
	private $rstStatements = null;
	
	
	public function __get($name){
		switch ($name){
			case 'xml':
				return $this->getXML();
				break;

		}
		return $this->$name;
	}
	
	
	public function __construct(){
		
		global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}
	 	$this->System = $System;
	}
	
		
	public function getClass($DictId, $ClassId, $SubjectIds = null){

		if (!isset($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
		
		$this->Subjects = null;
		
		$this->ClassDictId = $DictId;
		$this->ClassId = $ClassId;

		$Class = $Dicts->Dictionaries[$DictId]->Classes[$ClassId];
		$this->Class = $Class;
		
		
		if (is_array($SubjectIds)){	
			$this->SubjectIds = $SubjectIds;
		}
		else
		{
					 			
			$this->Subjects = array();
			
			$SubClasses = $Dicts->SubClasses($DictId, $ClassId);
			
			$sql = "";
			
			$sql .= " SELECT stmSubject ";
			$sql .= " FROM qry_subject ";
			
			
			$reqDoc = false;
			$reqSet = false;
			
			if (!is_null($this->SetId)){
				$reqDoc = true;
			}
			if (!is_null($this->ContextId)){
				$reqSet = true;
				$reqDoc = true;
			}
			
			if ($reqDoc) {
				$sql .= " LEFT JOIN tbl_document ON stmDocument = docRecnum ";
			}		
			if ($reqSet) {
				$sql .= " LEFT JOIN tbl_set ON docSet = setRecnum ";
			}		
			
			
			$sqlWhere = "";
			
			$sqlWhere .= " WHERE stmType = 100 AND ( ( stmLinkDict = '$DictId' AND stmLink = '$ClassId' ) ";
	
			foreach ($SubClasses as $SubClass){
				$sqlWhere .= " OR ( stmLinkDict = '".$SubClass->DictId."' AND stmLink = '".$SubClass->Id."' ) ";
			}
			$sqlWhere .= " ) ";
			
			if (!is_null($this->SetId)){
				$sqlWhere .= " AND docSet = ".$this->SetId." ";
			}
	
			if (!is_null($this->ContextId)){
				$sqlWhere .= " AND setContext = '".$this->ContextId."' ";
			}
			
			$sql = $sql .$sqlWhere;
			
			$sql .= ";";
			

			$this->System->DbExecute($sql);
			$rst = $this->System->DbExecute($sql);

			while ($row = $rst->fetch_assoc()) {
				
				$SubjectId = $row['stmSubject'];
				
				$objSubject = new clsSubject($SubjectId);
				
				if (!$this->PassesShape($objSubject)){
					continue;
				}
				
				if (!$this->PassesFilter($objSubject)){
					continue;
				}
				
				$this->SubjectIds[] = $SubjectId;
				$this->Subjects[$SubjectId] = $objSubject;
			}
		}		
		return $this->SubjectIds;
		
	}

	
	private function PassesShape($objSubject){

		if (is_null($this->ShapeId)){
			return true;
		}
		
		static $Docs = array();
		
		foreach ($objSubject->DocumentIds as $DocId){
			if (isset($Docs[$DocId])){
				$objDoc = $Docs[$DocId];
			}
			else
			{
				$objDoc = new clsDocument($DocId);
				$Docs[$DocId] = $objDoc;
			}
			if (is_object($objDoc)){
  				if ($objDoc->ShapeId == $this->ShapeId){
  					return true;
  				}				
			}
		}

		return false;
	}
	
	private function PassesFilter($objSubject, $objFilterClass = null){

		if (is_null($objFilterClass)){
			$objFilterClass = $this->FilterClass;
		}
		
		if (is_null($objFilterClass)){
			return true;
		}
	
		foreach ($objFilterClass->FilterProperties as $objFilterProperty){
			
// for each filtered property			
			$found = false;
			
			foreach ($objFilterProperty->FilterValues as $objFilterValue){
// true if the filter value is empty				
				if (IsEmptyString($objFilterValue->Value)){
					$found = true;
				}
			}
			
			if (!$found){
				
// find attributes, for the property, or subProperty

				$arrProperties = array($objFilterProperty->Property);
				$arrProperties = array_merge($arrProperties,$this->Dicts->SubProperties($objFilterProperty->Property->DictId, $objFilterProperty->Property->Id ));
				$Atts = array();
				foreach ($arrProperties as $objProperty ){
					if (isset($objSubject->Attributes[$objProperty->DictId][$objProperty->Id])){
						$Atts = array_merge($Atts,$objSubject->Attributes[$objProperty->DictId][$objProperty->Id]);
					}
				}
				
// if the subject does not have ANY attributes for the property - false
//				if (!isset($objSubject->Attributes[$objFilterProperty->Property->DictId][$objFilterProperty->Property->Id])){
				if (count($Atts) == 0){
					return false;
				}

				
// all of the attributes on the subject for the property - may be more than one
//				$Atts = $objSubject->Attributes[$objFilterProperty->Property->DictId][$objFilterProperty->Property->Id];

				
// if the property is a complex property - check that all of the components of a filter are true for each parent attribute				
				$found = $this->PassesFilterComplex($objFilterProperty, $Atts);

// check that all of the values/operators for the filter are true				
				foreach ($objFilterProperty->FilterValues as $objFilterValue){
					foreach ($Atts as $objAttribute){
						$found = $this->PassesFilterCheckValue($objAttribute, $objFilterProperty->Property, $objFilterValue );
						if ($found === true){
							break;
						}
					}
				}
			}
			if (!$found){
				return false;
			}
		}	
		
		foreach ($objFilterClass->FilterLinks as $objFilterLink){
			
// for each filtered link
			$found = false;
			
			$arrLinks = array();
			foreach ($objSubject->Links as $objLink){
				if ($objLink->Relationship = $objFilterLink->Relationship){
					$arrLinks[] = $objLink;
				}
			}				
// if the subject does not have ANY links for the relationship - false
			if (count($arrLinks) == 0){
				return false;
			}

			$found = false;
			foreach ( $arrLinks as $objLink){
				$objObject = new clsSubject($objLink->ObjectId);
				if ($this->PassesFilter($objObject, $objFilterLink->FilterClass)){
					$found = true;
				}
			}
				
			if (!$found){
				return false;
			}
		}	
		
		return true;
		
	}
	
	private function PassesFilterCheckValue($objAttribute, $objProperty, $objFilterValue ){
	
		$found = false;
		
		$AttValue = $objAttribute->Value;
		$FilterValue = $objFilterValue->Value;
		
		switch ($objProperty->Field->DataType){
			case 'date':
				$AttValue = DateTime::createFromFormat('d/m/Y|', $objAttribute->Value);
				$FilterValue = DateTime::createFromFormat('d/m/Y|', $objFilter->Value);
				break;
		}
		
		switch ($objProperty->Field->DataType){
			case 'date':
				if ($AttValue == $FilterValue){
					$found = true;
				}
				break;
			case 'value':
			case 'number':
				if ($AttValue == $FilterValue){
					$found = true;
				}
				break;
				
			default:
				if (stripos($AttValue, $FilterValue) !== FALSE){
					$found = true;
				}
		}
		
		return $found;
	}
	
	
	
	private function PassesFilterComplex($objParentFilterProperty, $ParentAtts){
// if the property is a complex property - check that all of the components of a filter are true for each parent attribute				
		
		foreach ($ParentAtts as $objParentAttribute){
// this is a parent group - all parts of at least one group must be true to pass

			$found = false;

			foreach ($objParentFilterProperty->FilterProperties as $objFilterProperty){
			
				foreach ($objFilterProperty->FilterValues as $objFilterValue){
						
					foreach ($objParentAttribute->ComplexAttributes as $objAttribute){
						
						if ($objAttribute->Property === $objFilterProperty->Property){
						
							$found = $this->PassesFilterCheckValue($objAttribute, $objFilterProperty->Property, $objFilterValue );	
							if (!$found){
								continue 4;
							}
						}
					}
				}
			}
			if ($found){
				return true;
			}
		}
		return false;
		
	}
	


	private function getXML(){

		$System = $this->System;
		$this->nsLde = $System->Config->Namespaces['lde'];
		$this->nsMeta = $System->Config->Namespaces['meta'];
		
		if (!isset($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
	
		$xmlSubjects = $this->dom->createElementNS($this->nsLde, 'Subjects');
		$this->dom->appendChild($xmlSubjects);
		$xmlSubjects->setAttribute("xmlns:meta", $this->nsMeta);
		
		
		$xmlSubjects->appendChild($this->dom->importNode($this->Dicts->getXmlClass($this->Class->DictId, $this->Class->Id)->documentElement,true));
		
		
		if (is_null($this->Subjects)){
			foreach ($this->SubjectIds as $SubjectId){
				
				$objSubject = new clsSubject($SubjectId);
				$objSubject->AsAtDocumentId = $this->AsAtDocumentId;
	
				$xmlSubjects->appendChild($this->dom->importNode($objSubject->getXML($this->Class)->documentElement,true));
				
			}
		}
		else
		{
			
			$now = microtime(true);
//			echo "XML for subjects start at $now <br/>";				
			
			foreach ($this->Subjects as $objSubject){				
				$now = microtime(true);
								
//				echo "getting xml for subject ".$objSubject->Id." at $now<br/>";
//				$xmlSubject = $objSubject->getXML($this->Class);	
				
				$xmlSubjects->appendChild($this->dom->importNode($objSubject->getXML($this->Class)->documentElement,true));	

				$elapse = microtime(true) - $now; 
//				if ( $elapse > 0.2){
//				$now = microtime(true);
//					echo "XML appended elapse $elapse <br/>";
//				}		
				
			}
			
//			echo count($this->Subjects)." number of subjects <br/>";
			
		}

		$now = microtime(true);
//		echo "XML complete at $now <br/>";		
		
		return $this->dom->saveXML();
		
	}	
	
	
	public function getSubjectsListDot($Style, $objGraph, &$Nodes = null, &$Clusters = array(), $Level = 1, $Color = null){

		if (is_null($Nodes)){
			$Nodes = array();
		}
		$ListNum = 0;
		
		do {
		    $ListNum = $ListNum + 1;
		    $NodeId = 'subjectlist_'.$ListNum;		    
			}
			while (isset($Nodes[$NodeId]));
		
		$Nodes[$NodeId] = $NodeId;
			
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}

		$objClass = $this->Class;

		if (is_null($Color)){
			$Color = 'white';
		}
		
		$Shape = null;
		$NodeHeight = null;
		$NodeWidth = null;
				
		$ThisGraph = $objGraph;
		
		$Shape="plaintext";
		$SubjectHeaderLabel = '<b>'.$objGraph->FormatDotCell($objClass->Heading,50)."</b><br/>(".strtoupper($objClass->Concept).")";
		
		$xsl = new DOMDocument;
		$xsl->load('xslt/classsubjects.xslt');

		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl);				
		$proc->setParameter('', 'mode', 'dot');
		$proc->setParameter('', 'color', $Color);
		
		$this->getXML();
		
		$Label = "<".$proc->transformToXML($this->dom).">";
		$ThisGraph->addNode($NodeId,$Label,$Shape,$Color,$NodeHeight,$NodeWidth);

		return $NodeId;
		
	}
	
	
}
	
class clsSubject{
	
	private $Id;
	
	private $ClassDictId;
	private $ClassId;

	private $System;
	
	public $EffectiveDate = null;
	public $AsAtDate = null;
	public $AsAtDocumentId = null;
	
	private $Context = null;
	
	private $Statements = null;
	private $Attributes = null;
	private $Links = null;
	
	private $Matches = null;
	
	private $Name = null;
	private $Identifier = null;
	private $Label = null;

	private $DocumentIds = null;
	private $CreatedStatementId = null;
	private $CreatedDocumentId = null;

	private $CreatedSet = null;
	
	private $Dicts = null;
	
	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;
	private $nsLde = null;
	private $nsMeta = null;
	
	public $Collection = null;
		
	public function __get($name){
		switch ($name){
						
			case 'Statements':
				if (is_null($this->Statements)){
					$this->getStatements();
				}
				break;
			case 'Matches':
				if (is_null($this->Matches)){
					$this->getMatches();
				}
				break;
				
			case 'Attributes':
				if (is_null($this->Attributes)){
					$this->getAttributes();
				}
				break;
			case 'Links':
				if (is_null($this->Links)){
					$this->getLinks();
				}
				break;
				
			case 'Name':
				if (is_null($this->Name)){
					$this->getName();
				}
				break;
			case 'Identifier':
				if (is_null($this->Identifier)){
					$this->getIdentifier();
				}
				break;
			case 'Label':
				if (is_null($this->Label)){
					$this->getLabel();
				}
				break;
				
			case 'DocumentIds':
				if (is_null($this->DocumentIds)){
					$this->getDocumentIds();
				}
				break;

			case 'Context':
				$this->getContext();
				break;

			case 'CreatedSet':
				$this->getCreatedSet();
				break;
				
			case 'xml':
				return $this->getXML();
				break;
				
				
		}
		return $this->$name;
	}
	
	
	public function __construct($Id){
		
//	    $now = microtime(true);	    
//	    echo "create subject $Id<br/>";

		global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}
	 	$this->System = $System;
	 	
	 	if (IsEmptyString($Id)){
	 		throw new exception("Subject Id not specified");
	 	}
	 	
	 	$this->Id = $Id;
	 	
	 	$sql = "SELECT * FROM tbl_statement WHERE stmSubject = $Id AND stmType = 100";

	 	$rst = $System->DbExecute($sql);
		if (!$rst->num_rows > 0){
			return false;
		}
		$rstRow = $rst->fetch_assoc();

		$this->CreatedStatementId = $rstRow['stmRecnum'];
		$this->CreatedDocumentId = $rstRow['stmDocument'];
		
		$this->ClassDictId = $rstRow['stmLinkDict'];
		$this->ClassId = $rstRow['stmLink'];
				
	}

	private function getCreatedSet(){
		
		if (is_null($this->CreatedSet)){
			if (!(is_null($this->CreatedDocumentId))){
				$objDoc = new clsDocument($this->CreatedDocumentId);
				$this->CreatedSet = new clsSet($objDoc->SetId);
			}
		}
		return $this->CreatedSet;
		
	}
	
	private function getContext(){
	
		if (is_null($this->Context)){
			$objSet = $this->getCreatedSet();
			$this->Context = $objSet->Context;
		}
		
		return $this->Context;
	}
				
	
	
	public function getStatements(){
		
		if (is_null($this->Statements)){
			$this->Statements = array();
			
			$Sets = array();
			
			$SubjectId = $this->Id;
			
		 	$sql = "SELECT * FROM tbl_statement WHERE (( stmSubject = $SubjectId ) OR (stmObject = $SubjectId))";
		 	$rst = $this->System->DbExecute($sql);
		 	
			while ($row = $rst->fetch_assoc()) {
				
				$StatId = $row['stmRecnum'];
				
				$SetId = $row['stmSet'];
				if (!isset($Sets[$SetId])){
					$Sets[$SetId] = new clsSet($SetId);
				}				

				$DocId = $row['stmDocument'];
				
				$useStatement = true;
				
				if (!(is_null($this->AsAtDocumentId))){
					if ($DocId > $this->AsAtDocumentId){
						if ($DocId > $this->CreatedDocumentId){
							$useStatement = false;
						}
					}
				}
				
				if ($useStatement){
					$this->Statements[$StatId] = new clsStatement($StatId, $row);
				}
			}
			
		}
				
		return $this->Statements;
				
	}

	
		
	public function getAttributes(){
		
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
		
		$this->Attributes = array();
		
		if (is_null($this->Statements)){
			$this->getStatements();
		}
		$Statements = $this->Statements;
		
		
		foreach ($this->Dicts->ClassProperties($this->ClassDictId, $this->ClassId) as $ClassProp){
						
					$AttDocId = 0;

					foreach ($Statements as $Statement){
						if ($Statement->TypeId == 200){
							if ($Statement->LinkDictId == $ClassProp->PropDictId){
								if ($Statement->LinkId == $ClassProp->PropId){
										
									if ($Statement->DocId > $AttDocId){
										$this->Attributes[$ClassProp->PropDictId][$ClassProp->PropId] = array();
										$AttDocId = $Statement->DocId;
										// can have more than one attribute for a single property, but they must come from a single document
									}

									$objAtt = new clsAttribute($Statement, $Statements);

									if ($ClassProp->UseAsName === true){
										$objAtt->UseAsName = true;
									}
									if ($ClassProp->UseAsIdentifier === true){
										$objAtt->UseAsIdentifier = true;
									}

									$this->Attributes[$ClassProp->PropDictId][$ClassProp->PropId][]=$objAtt;
										
										
								}
							}
						}
					}
		}
		
		return $this->Attributes;
		
	}

	public function getMatches(){
		
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
				
		$this->Matches = array();
		
		if (is_null($this->Statements)){
			$this->getStatements();
		}
		$Statements = $this->Statements;
		
		foreach ($Statements as $Statement){
			if ($Statement->TypeId == 110){
				$objMatch = new clsMatch($Statement);
				$this->Matches[] = $objMatch;
			}
		}
							
		return $this->Matches;
		
	}

	public function getLinks(){
		
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
				
		$this->Links = array();
		
		if (is_null($this->Statements)){
			$this->getStatements();
		}
		$Statements = $this->Statements;
									
		foreach ($Statements as $Statement){
			if ($Statement->TypeId == 300){
				$objLink = new clsLink($Statement->Id);
				$objLink->AsAtDocumentId = $this->AsAtDocumentId;
				$this->Links[] = $objLink;
			}
		}
							
		return $this->Links;
		
	}

	
	public function getViewClassAttributes($objViewClass, $EffectiveDate = null){
		
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
				
		$this->Attribtues = array();
		$Statements = $this->getStatements($EffectiveDate);

		$PropNum = 0;
		foreach ($objViewClass->ViewProperties as $objViewProp){

			$PropNum = $PropNum + 1;
			$this->Attributes[$PropNum] = array();
			
			foreach ($Statements as $Statement){
				if ($Statement->TypeId == 200){
					if ($Statement->LinkDictId == $objViewProp->Property->DictId){
						if ($Statement->LinkId == $objViewProp->Property->Id){
//							if (!is_null($Statement->Value)){
								$objAtt = new clsAttribute($Statement, $Statements);
//								$objAtt->DictId = $Statement->LinkDictId;
//								$objAtt->PropId = $Statement->LinkId;
//								$objAtt->Label = $objViewProp->Property->Label;
//								$objAtt->Statement = $Statement;
//								$objAtt->Value = $Statement->Value;
								
								$this->Attributes[$PropNum][]=$objAtt;
//							}
						}
					}
				}
			}
		}
		
		return $this->Attributes;
		
	}
	
	
	private function getName(){
		
		$Result = "";
		
		if (is_null($this->Attributes)){
			$this->getAttributes();
		}
		foreach ($this->Attributes as $PropDictId=>$PropDictAtts){
			
			
			foreach ($PropDictAtts as $PropId=>$arrAtts){
				foreach ($arrAtts as $objAtt){
					if ($objAtt->UseAsName === true){
						if (!IsEmptyString($Result)){
							$Result = ", ";
						}
						$Result .= $objAtt->Statement->Value;
					}
				}
			}
		}
		$this->Name = $Result;
		
		if (isemptystring($Result)){
			$this->Name = $this->Id;
		}
		
		return $Result;
		
	}
		

	private function getIdentifier(){
		$Result = "";
		
		if (is_null($this->Attributes)){
			$this->getAttributes();
		}
		foreach ($this->Attributes as $PropDictId=>$PropDictAtts){
			foreach ($PropDictAtts as $PropId=>$arrAtts){
				foreach ($arrAtts as $objAtt){
					if ($objAtt->UseAsIdentifier === true){
						if (!IsEmptyString($Result)){
							$Result = ", ";
						}
						$Result .= $objAtt->Statement->Value;
					}
				}
			}
		}
		$this->Identifier = $Result;
		
		return $Result;
		
	}

	
	private function getLabel(){
		
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
		
		
		$Result = trim($this->getIdentifier().' '.$this->getName());
		
		if (isemptystring($Result)){
			$objClass = $this->Dicts->getClass($this->ClassDictId, $this->ClassId);
			$Result = $objClass->Label.' '.$this->Id;
		}
		
		$this->Label = $Result;

		return $Result;
				
	}
	
	
	private function getDocumentIds(){
		
		$this->DocumentsIds = array();
		
		if (is_null($this->Statements)){
			$this->getStatements();
		}
		foreach ($this->Statements as $objStatement){
			$this->DocumentIds[$objStatement->DocId] = $objStatement->DocId;
		}
	}
	
	public function getDot($Style){
		
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
				
		$objGraph = new clsGraph();

		$Nodes = array();
		$Links = array();
		$Clusters = array();
		
		$NodeId = $this->getSubjectDot($Style,$objGraph, $Nodes, $Links, $Clusters);

		// Matches
		if (is_null($this->Matches)){
			$this->getMatches();
		}
		foreach ($this->Matches as $objMatch){
			
			$SameAsSubjectId = $objMatch->SameAsSubjectId;
			
			$Inverse = false;
			if ($SameAsSubjectId == $this->Id){
				$SameAsSubjectId = $objMatch->SubjectId;
				$Inverse = true;
			}
			$objSameAsSubject = new clsSubject($SameAsSubjectId);

			$SameAsNodeId = $objSameAsSubject->getSubjectDot($Style, $objGraph, $Nodes, $Links, $Clusters, 2);
			
			$MatchColor = 'black';
			if (!is_null($objSameAsSubject->Context)){
				$MatchColor = $objSameAsSubject->Context->Color;
			}
			
			switch ($Inverse){
				case false:
					$objGraph->addEdge($NodeId, $SameAsNodeId, 'matched to', $MatchColor, 'dashed');
					break;
				default:
					$objGraph->addEdge($SameAsNodeId, $NodeId, 'matched to', $MatchColor, 'dashed');
					break;
			}
		}
		
// find links to subjects for which there are no other links, so that they can be listed in a single table		

		$arrEdgeList = array();
		
		foreach ($this->Links as $objLink){
			
			if (isset($Links[$objLink->Id])){
				continue;
			}

			
			$objRel = $this->Dicts->getRelationship($objLink->RelDictId, $objLink->RelId);

			$Object = null;
			$Inverse = false;
			$RelLabel = $objRel->Label;
			
			if ($objLink->SubjectId == $this->Id){
				$Object = new clsSubject($objLink->ObjectId);
			}
			elseif ($objLink->ObjectId == $this->Id){
				$Object = new clsSubject($objLink->SubjectId);
				$RelLabel = $objRel->InverseLabel;
				$Inverse = true;
			}
						
			if (!is_null($Object)){
				
				$Object->AsAtDocumentId = $this->AsAtDocumentId;
// check for other links
				$boolMoreLinks = false;
				$Object->getLinks();
//				foreach ($Object->Links as $objObjectLink){
//					if (!($objObjectLink == $objLink)){
//						$boolMoreLinks = true;
//					}
//				}
//				if (!$boolMoreLinks){
					$objObjectSet = $Object->getCreatedSet();
					$arrEdgeList[$objLink->RelDictId][$objLink->RelId][$Object->ClassDictId][$Object->ClassId][$objObjectSet->Id][$objLink->Id] = $Object;					
//				}
			}
			
		}
		foreach ($arrEdgeList as $EdgeRelDictId=>$arrEdgeRelIds){
			foreach ($arrEdgeRelIds as $EdgeRelId=>$arrEdgeClassDictIds){
				foreach ($arrEdgeClassDictIds as $EdgeClassDictId=>$arrEdgeClassIds){
					foreach ($arrEdgeClassIds as $EdgeClassId=>$arrEdgeSetIds){						
						foreach ($arrEdgeSetIds as $EdgeSetId=>$arrEdgeObjects){

							
							if (count($arrEdgeObjects) > 1) {

								$objListSet = new clsSet($EdgeSetId);
								$Color = 'white';
								if (isset($this->System->Config->SetContextTypes[$objListSet->ContextId])){
									$Color = $this->System->Config->SetContextTypes[$objListSet->ContextId]->Color;
								}

								$ThisGraph = $objGraph;
								if ($Style == 4){
									if (!isset($Clusters[$objListSet->Id])){					
										$Clusters[$objListSet->Id] = $objGraph->addSubGraph("cluster",$objListSet->Name);
									}
									$ThisGraph = $Clusters[$objListSet->Id];
								}
								
								$arrEdgeListIds = array();
								foreach ($arrEdgeObjects as $LinkId=>$arrEdgeObject){
									$arrEdgeListIds[] = $arrEdgeObject->Id;									
									$Links[$LinkId] = $LinkId;
								}
								$objObjectsList = new clsSubjects();
								$objObjectsList->AsAtDocumentId = $this->AsAtDocumentId;
								$objObjectsList->getClass($EdgeClassDictId, $EdgeClassId, $arrEdgeListIds);
								
								$ObjectListNodeId = $objObjectsList->getSubjectsListDot($Style,$ThisGraph, $Nodes, $Clusters, 1, $Color);
								
// add edge
								$objGraph->addEdge($NodeId,$ObjectListNodeId,$RelLabel);
								
							}
						}
					}
				}
			}
		}
		
		
		foreach ($this->Links as $objLink){

			if (isset($Links[$objLink->Id])){
				continue;
			}
			
			$objRel = $this->Dicts->getRelationship($objLink->RelDictId, $objLink->RelId);

			$Object = null;
			$Inverse = false;
			$RelLabel = $objRel->Label;
			
			if ($objLink->SubjectId == $this->Id){
				$Object = new clsSubject($objLink->ObjectId);
			}
			elseif ($objLink->ObjectId == $this->Id){
				$Object = new clsSubject($objLink->SubjectId);
				$RelLabel = $objRel->InverseLabel;
				$Inverse = true;
			}
						
			if (!is_null($Object)){
				
				$Object->AsAtDocumentId = $this->AsAtDocumentId;
									
				$LinkNodeId = $Object->getSubjectDot($Style, $objGraph, $Nodes, $Links, $Clusters,2);
				
				switch ( $Style ){
				case 1:
				case 3:
					$Label = $RelLabel;
					break;
				case 2:
				case 4:
								
					$Label = '';
					if (!is_null($objLink->EffectiveFrom)){
						$Label .= "<tr><td align='left' balign='left' valign='top'><b>Effective From</b></td><td  align='left' balign='left' valign='top'>".$objLink->EffectiveFrom."</td></tr>";
					}
					if (!is_null($objLink->EffectiveTo)){
						$Label .= "<tr><td align='left' balign='left' valign='top'><b>Effective To</b></td><td  align='left' balign='left' valign='top'>".$objLink->EffectiveTo."</td></tr>";
					}
	
					foreach ($objLink->Attributes as $DictAtts){
						foreach ($DictAtts as $PropAtts){
							foreach ($PropAtts as $objAtt){
								$AttLabel = "<tr><td align='left' balign='left' valign='top'><b>".$objGraph->FormatDotCell($objAtt->Label,20)."</b></td><td  align='left' balign='left' valign='top'>".$objGraph->FormatDotCell(truncate($objAtt->Value),30)."</td></tr>";
								$Label .= $AttLabel;
							}
						}							
					}
					
					if (empty($Label)){
						$Label = $RelLabel;
					}
					else
					{					
						$Label = "<<table border='0' cellborder='1' cellspacing='0'><tr><td colspan='2' bgcolor='white'><b>$RelLabel</b></td></tr>".$Label."</table>>";
					}
					break;
				}
					
				$objGraph->addEdge($NodeId,$LinkNodeId,$Label);
				$Links[$objLink->Id] = $objLink;
				
			}
			
		}

		$Script = $objGraph->script;
		
		return $Script;
	}
	
	public function getSubjectDot($Style, $objGraph, &$Nodes = array(), &$Links = array(), &$Clusters = array(), $Level = 1, $Color = null){
			
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}

		$NodeId = 'subject_'.$this->Id;
		if (isset($Nodes[$NodeId])){
			return $NodeId;
		}
		
		if (!isset($this->Dicts->Dictionaries[$this->ClassDictId])){
			throw new exception("Unknown Dictionary");
		}
		$objClassDict = $this->Dicts->Dictionaries[$this->ClassDictId];
		if (!isset($objClassDict->Classes[$this->ClassId])){
			throw new exception("Unknown Class");
		}
		$objClass = $objClassDict->Classes[$this->ClassId];
		
		if (is_null($this->Attributes)){
			$this->getAttributes();
		}
		if (is_null($this->Links)){
			$this->getLinks();
		}

		if (is_null($Color)){
			$Color = 'white';
			if (!is_null($this->getContext())){
				$Color = $this->Context->Color;
			}
		}
				
		$Shape = null;
		$NodeHeight = null;
		$NodeWidth = null;
				
		$ThisGraph = $objGraph;
		
		switch ( $Style ){
			case 1:
				$Shape = null;
				$Label = $objGraph->FormatDotLabel($this->getLabel(),20);

				$NodeHeight = 0.7;
				$NodeWidth = 0.7;
				
				if ($Level == 1){
					$NodeHeight = 1;
					$NodeWidth = 1;
				}
				
				
				$Concept = $objClass->Concept;
				if (!IsEmptyString($Concept)){
					if (!isset($Clusters[$Concept])){					
						$Clusters[$Concept] = $objGraph->addSubGraph("cluster",$Concept);
					}
					$ThisGraph = $Clusters[$Concept];
				}
				
				break;
			case 2:
			case 4:
		
				$Shape="plaintext";
				$SubjectHeaderLabel = '<b>'.$objGraph->FormatDotCell($objClass->Label,50)."</b><br/>(".strtoupper($objClass->Concept).")";
				
				$Label = "<<table id='$NodeId' border='0' cellborder='1' cellspacing='0'>";
		
				$Label .= "<tr><td colspan='2' bgcolor='$Color'>$SubjectHeaderLabel</td></tr>";
				
				foreach ($this->Attributes as $DictAtts){
					foreach ($DictAtts as $PropAtts){
						foreach ($PropAtts as $objAtt){
							
							$Label .= "<tr><td align='left' balign='left' valign='top'><b>".$objGraph->FormatDotCell($objAtt->Label,20)."</b></td><td align='left' balign='left' valign='top'>";
							
							switch ($objAtt->Property->Type){
								case 'simple':
									$Label .= $objGraph->FormatDotCell(truncate(   str_replace(chr(10), ',',  $objAtt->Value)),30);
									break;
								case 'complex':
									$Label .= $this->getComplexAttributeDot($objGraph, $objAtt);
									break;
							}
							
							$Label .= "</td></tr>";
							
						}
					}
				}
				
				$Label .= "</table>>";
				
				if ($Style == 4){
					if (is_object($this->CreatedSet)){
						$objSet = $this->CreatedSet;
						if (!isset($Clusters[$objSet->Id])){					
							$Clusters[$objSet->Id] = $objGraph->addSubGraph("cluster",$objSet->Name);
						}
						$ThisGraph = $Clusters[$objSet->Id];
					}
				}
				
				
				break;
			case 3:
				
				$Shape = null;
				$Label = $objGraph->FormatDotLabel($this->getLabel(),20);
				
				$Concept = $objClass->Concept;				
				if (!IsEmptyString($Concept)){
					$Label .= "\n(".strtoupper($Concept).')';
				}
				
				$NodeHeight = 0.7;
				$NodeWidth = 0.7;
				
				if ($Level == 1){
					$NodeHeight = 1;
					$NodeWidth = 1;
				}
				break;				
		}

		if (!isset($Nodes[$NodeId])){
			$Nodes[$NodeId] = $NodeId;		
			$NodeUrl = "subject.php?subjectid=".$this->Id;
			$ThisGraph->addNode('subject_'.$this->Id,$Label,$Shape,$Color,$NodeHeight,$NodeWidth,$NodeUrl);			
		}
		
// get extended relationships and subjects
/*
		foreach ($this->Links as $objLink){
			
			if (isset($Links[$objLink->Id])){
				continue;
			}
			
			
			$objRel = $this->Dicts->getRelationship($objLink->RelDictId, $objLink->RelId);
			if (!$objRel->Extending){
				continue;
			}

			if (!($objLink->ObjectId == $this->Id)){
				continue;
			}
			
			$Object = new clsSubject($objLink->SubjectId);
			$Inverse = true;
						
			if (!is_null($Object)){
				
				$Object->AsAtDocumentId = $this->AsAtDocumentId;
									
				$LinkNodeId = $Object->getSubjectDot($Style,$objGraph, $Nodes, $Links, $Clusters, $Level + 1);
								
				
				switch ( $Style ){
				case 1:
				case 3:
					$Label = $objRel->Label;
					break;
				case 2:
				case 4:
					
					$hasValues = false;
								
					$Label = "<<table border='0' cellborder='1' cellspacing='0'>";
					$Label .= "<tr><td colspan='2' bgcolor='white'><b>$objRel->Label</b></td></tr>";
	
					if (!is_null($objLink->EffectiveFrom)){
						$Label .= "<tr><td align='left' balign='left' valign='top'><b>Effective From</b></td><td  align='left' balign='left' valign='top'>".$objLink->EffectiveFrom."</td></tr>";
						$hasValues = true;
					}
					if (!is_null($objLink->EffectiveTo)){
						$Label .= "<tr><td align='left' balign='left' valign='top'><b>Effective To</b></td><td  align='left' balign='left' valign='top'>".$objLink->EffectiveTo."</td></tr>";
						$hasValues = true;						
					}
	
					foreach ($objLink->Attributes as $DictAtts){
						foreach ($DictAtts as $PropAtts){
							foreach ($PropAtts as $objAtt){
								$AttLabel = "<tr><td align='left' balign='left' valign='top'><b>".$objGraph->FormatDotCell($objAtt->Label,20)."</b></td><td  align='left' balign='left' valign='top'>".$objGraph->FormatDotCell(truncate($objAtt->Value),30)."</td></tr>";
								$Label .= $AttLabel;
								$hasValues = true;
							}
						}							
					}
					$Label .= "</table>>";
					
					if (!$hasValues){
						$Label = $objRel->Label;
					}
					
					break;
				}
					
				switch ($Inverse){
					case true:
						$objGraph->addEdge($LinkNodeId,$NodeId,$Label);			
						break;
					default:
						$objGraph->addEdge($NodeId,$LinkNodeId,$Label);			
						break;								
				}
				$Links[$objLink->Id] = $objLink;
				
			}
			
		}
*/		
		
		return $NodeId;
		
	}
	
	private function getComplexAttributeDot($objGraph, $objAtt){
			
		$found = false;
		
		$Label = "<table border='0' cellborder='1' cellspacing='0'>";

		foreach ($objAtt->ComplexAttributes as $objAtt){

			$found = true;
			
			$Color = 'white';
			$Label .= "<tr><td align='left' balign='left' valign='top'><b>".$objGraph->FormatDotCell($objAtt->Label,20)."</b></td><td bgcolor='$Color' align='left' balign='left' valign='top'>";
			
			switch ($objAtt->Property->Type){
				case 'simple':
					$Label .= $objGraph->FormatDotCell(truncate(   str_replace(chr(10), ',',  $objAtt->Value).' '),30);
					break;					
				case 'complex':
					$Label .= $this->getComplexAttributeDot($objGraph, $objAtt);
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
	
//	

//	
	
	
	public function getXML($objParentClass = null){
			
//		$now = microtime(true);
//		echo "get XML start at $now <br/>";
		
		$System = $this->System;
		
		global $Dicts;
		if (!isset($Dicts)){			
			$Dicts = $this->Dicts;
			if (is_null($Dicts)){
				$Dicts = new clsDicts();
			}
		}
		$this->Dicts = $Dicts;
		
		$this->nsLde = $System->Config->Namespaces['lde'];
		$this->nsMeta = $System->Config->Namespaces['meta'];
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;
			
		$xmlSubject = $this->dom->createElementNS($this->nsLde, 'Subject');
		$this->dom->appendChild($xmlSubject);
		$xmlSubject->setAttribute("xmlns:meta", $this->nsMeta);

		
		
		
		$xmlSubject->setAttribute('id',$this->Id);
		
		$xmlSubject->setAttribute('dictid',$this->ClassDictId);
		$xmlSubject->setAttribute('classid',$this->ClassId);

		$objClass = $Dicts->getClass($this->ClassDictId, $this->ClassId);
		$xmlSubject->setAttribute('label',$objClass->Label);

		
		if (is_null($objParentClass)){
			$objParentClass = $objClass;
		}
		
		$objSet = $this->getCreatedSet();
		$xmlSubject->setAttribute('setid',$objSet->Id);
		$xmlSubject->setAttribute('setname',$objSet->Name);
		
		
		$xmlAttributes = $this->dom->createElementNS($this->nsLde, 'Attributes');
		$xmlSubject->appendChild($xmlAttributes);

		if (is_null($this->Attributes)){
			$this->getAttributes();
		}
				
		foreach ($Dicts->ClassProperties($objParentClass->DictId, $objParentClass->Id) as $objClassProperty){
			$objProperty = $Dicts->getProperty($objClassProperty->PropDictId, $objClassProperty->PropId );
						
			foreach ($this->Attributes as $PropDictId=>$PropAtts){
				foreach ($PropAtts as $PropId=>$Atts){
			
					if (isset($Atts[0])){
						
						$useAtt = false;
						
						if ($Atts[0]->DictId == $objProperty->DictId){
							if ($Atts[0]->PropId == $objProperty->Id){
								$useAtt = true;
							}
						}
						if (!$useAtt){
							foreach ( $Dicts->SubProperties($objProperty->DictId, $objProperty->Id) as $SubProp){
								if ($Atts[0]->DictId == $SubProp->DictId){
									if ($Atts[0]->PropId == $SubProp->Id){
										$useAtt = true;
									}
								}
							}
						}
						
						if ($useAtt){
							foreach ($Atts as $objAtt){	

								$xmlAttribute = $this->dom->createElementNS($this->nsLde, 'Attribute');
								$xmlAttributes->appendChild($xmlAttribute);
						
								$xmlAttribute->setAttribute('dictid',$objProperty->DictId);
								$xmlAttribute->setAttribute('propid',$objProperty->Id);							
								$xmlAttribute->setAttribute('label',$objProperty->Label);
								
								switch ($objProperty->Type){							
									case 'simple':

										if (!is_null($objAtt->Value)){
											$xmlAttribute->setAttribute('value',$objAtt->Value);
										}

										break;
									case 'complex':
										$this->getXmlComplexAttribute($xmlAttribute, $objAtt);
										break;
								}
									
							}
						}
						
					}
				}
			}
						
		}
		
// extending links		
		
		$xmlLinks = $this->dom->createElementNS($this->nsLde, 'Links');
		$xmlSubject->appendChild($xmlLinks);
		
		
		foreach ($Dicts->RelationshipsFor($objParentClass->DictId, $objParentClass->Id) as $objRel){
			if ($objRel->Extending == true){
				
				if (is_null($this->Links)){
					$this->getLinks();
				}				
				
				foreach ($this->Links as $objLink){
					$useLink = false;
						
					if ($objLink->RelDictId == $objRel->DictId){
						if ($objLink->RelId == $objRel->Id){
							if (!($objLink->ObjectId == $this->Id)){
								$useLink = true;
							}
						}
					}
					if ($useLink){
						$xmlLink = $this->dom->createElementNS($this->nsLde, 'Link');
						$xmlLinks->appendChild($xmlLink);
						
						$xmlLink->setAttribute('dictid',$objRel->DictId);
						$xmlLink->setAttribute('relid',$objRel->Id);
						$xmlLink->setAttribute('label',$objRel->Label);
						
						$xmlLink->setAttribute('statementid', $objLink->CreatedStatementId);
						
						$objLinkObject = new clsSubject($objLink->ObjectId);
						$objLinkObject->AsAtDocumentId = $this->AsAtDocumentId;
						
						$xmlLink->appendChild($this->dom->importNode($objLinkObject->xml->documentElement,true));
						
					}
					
				}
				
			}
			
		}
		
		
		
		
		
//		$now = microtime(true);
//		echo "get XML end at at $now <br/>";		
		
		return $this->dom;
		
	}	
	
	
	private function getXmlComplexAttribute($xmlParent, $objParentAtt){
		
		$xmlComplexAttributes = $this->dom->createElementNS($this->nsLde, 'ComplexAttributes');
		$xmlParent->appendChild($xmlComplexAttributes);							
		
		foreach ($objParentAtt->ComplexAttributes as $objAtt){

			$xmlAttribute = $this->dom->createElementNS($this->nsLde, 'Attribute');
			$xmlComplexAttributes->appendChild($xmlAttribute);
			
			$objProperty = $objAtt->Property;
			
			$xmlAttribute->setAttribute('dictid',$objProperty->DictId);
			$xmlAttribute->setAttribute('propid',$objProperty->Id);							
			$xmlAttribute->setAttribute('label',$objProperty->Label);
						
			switch ($objProperty->Type){
				case 'simple':
					if (!is_null($objAtt->Value)){
						$xmlAttribute->setAttribute('value',$objAtt->Value);
					}					
					break;
				case 'complex':
					$this->getXmlComplexAttribute($xmlAttribute, $objAtt);
					break;
			}
			
		}
	}
	
}



class clsLink{
	
	private $Id;
	
	private $RelDictId;
	private $RelId;

	private $System;
	
	public $EffectiveFrom = null;
	public $EffectiveTo = null;
	
	public $AsAtDate = null;
	public $AsAtDocumentId = null;
	
	private $Statements = null;
	private $Attributes = null;
	
	private $DocumentIds = null;
	
	private $CreatedStatementId = null;
	private $CreatedDocumentId = null;
	
	private $SubjectId = null;
	private $ObjectId = null;
	
	private $Dicts = null;
	
	public function __get($name){
		switch ($name){
			case 'Attributes':
				if (is_null($this->Attributes)){
					$this->getAttributes();
				}
				break;
			case 'DocumentIds':
				if (is_null($this->DocumentIds)){
					$this->getDocumentIds();
				}
				break;
			case 'Statements':
				if (is_null($this->Statements)){
					$this->getStatements();
				}
				break;
				
		}
		return $this->$name;
	}
	
	
	public function __construct($Id){
		
		
		global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}
	 	$this->System = $System;

	 	$this->Id = $Id;
	 	
	 	$sql = "SELECT * FROM tbl_statement WHERE stmRecnum = $Id AND stmType = 300";

	 	$rst = $System->DbExecute($sql);
		if (!$rst->num_rows > 0){
			return false;
		}
		$rstRow = $rst->fetch_assoc();

		$this->CreatedStatementId = $rstRow['stmRecnum'];
		$this->CreatedDocumentId = $rstRow['stmDocument'];
		
		$this->RelDictId = $rstRow['stmLinkDict'];
		$this->RelId = $rstRow['stmLink'];
		
		$this->SubjectId = $rstRow['stmSubject'];
		$this->ObjectId = $rstRow['stmObject'];
		
		$this->EffectiveFrom = $rstRow['stmEffFrom'];
		$this->EffectiveTo = $rstRow['stmEffTo'];
		
	}
	
	public function getStatements(){
		
		if (is_null($this->Statements)){
			$this->Statements = array();
			
			$Sets = array();
			
			$LinkId = $this->Id;
			
		 	$sql = "SELECT stmRecnum, stmSet, stmDocument FROM tbl_statement WHERE (( stmAboutStatement = $LinkId )) ";
	
		 	$rst = $this->System->DbExecute($sql);
		 	
			while ($row = $rst->fetch_assoc()) {
				$StatId = $row['stmRecnum'];
				
				$SetId = $row['stmSet'];
				if (!isset($Sets[$SetId])){
					$Sets[$SetId] = new clsSet($SetId);
				}				

				$DocId = $row['stmDocument'];
				
				$useStatement = true;
				
				if (!(is_null($this->AsAtDocumentId))){
					if ($DocId > $this->AsAtDocumentId){
						$useStatement = false;
					}
				}
				
				if ($useStatement){
					$this->Statements[] = new clsStatement($StatId);
				}
			}
			
		}
				
		return $this->Statements;
				
	}

	public function getAttributes(){
		
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
				
		$this->Attributes = array();
		if (is_null($this->Statements)){
			$this->getStatements();
		}
		$Statements = $this->Statements;
		
		foreach ($this->Dicts->RelProperties($this->RelDictId, $this->RelId) as $HasProp){
					
					$AttDocId = 0;
					
					foreach ($Statements as $Statement){
						if ($Statement->TypeId == 200){
							if ($Statement->LinkDictId == $HasProp->PropDictId){
								if ($Statement->LinkId == $HasProp->PropId){
										
										if ($Statement->DocId > $AttDocId){
											$this->Attributes[$HasProp->Id] = array();
											$AttDocId = $Statement->DocId;
											// can have more than one attribute for a single property, but they must come from a single document
										}
																				
										$objAtt = new clsAttribute($Statement, $Statements);
									
										$this->Attributes[$HasProp->PropDictId][$HasProp->PropId][]=$objAtt;
										
										
								}
							}
						}
					}
		}
		
		return $this->Attributes;
		
	}
}


class clsMatch {
	
	public $Statement = null;
	public $SubjectId = null;
	public $SameAsSubjectId = null;
	
	
	public function __construct($Statement){				
		
		$this->Statement = $Statement;

		$this->SubjectId = $this->Statement->SubjectId;
		$this->SameAsSubjectId = $this->Statement->ObjectId;
				
	}	
}

class clsAttribute {
	
	public $Property = null;
//	public $DictId = null;
//	public $PropId = null;
	public $Statement = null;
	public $Value = null;
	public $Label = null;
	
	public $UseAsName = false;
	public $UseAsIdentifier = false;
	
	private $Dicts = null;
	
	public $ComplexAttributes = array();
		
	public function __construct($Statement, $SubjectStatements = null){

		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
				
		$this->Statement = $Statement;
				
		$this->DictId = $Statement->LinkDictId;
		$this->PropId = $Statement->LinkId;
		
		$this->Property = $this->Dicts->getProperty($this->DictId, $this->PropId);
		if (is_object($this->Property)){
			$this->Label = $this->Property->Label;
		}

		switch ($this->Property->Type){
			case 'simple':
				$this->Value = $Statement->Value;
				break;
			case 'complex':
				if (is_array($SubjectStatements)){
					$ComplexStatements = array();
					foreach ($SubjectStatements as $SubjectStatement){
						
						if ($SubjectStatement->AboutId == $this->Statement->Id){
// must all be from the latest document							
							$ComplexStatements[$SubjectStatement->DocId][] = $SubjectStatement;
						}
						
					}
					
					ksort($ComplexStatements);					
					$DocComplexStatements = end($ComplexStatements);
					
					if (is_array($DocComplexStatements)){
						foreach ($DocComplexStatements as $SubjectStatement){
							$objComplexAttribute = new clsAttribute($SubjectStatement,$SubjectStatements);
							$this->ComplexAttributes[] = $objComplexAttribute;

							if (is_null($this->Value)){
								$this->Value = "";
							}
							
							if (!(empty($objComplexAttribute->Value))){
								$this->Value .= ' '.$objComplexAttribute->Value;
							}							
							
						}
					}
					
					if (!is_null($this->Value)){
						$this->Value = trim($this->Value);
					}
					
				}
				
				break;
		}
	}
	
}






class clsCollection{
	
// collection of statements for a
// * shape
// * class
	
// !!! should be from a single SET and a licences is associated with it.	
	
public $Statements = array();	
public $Subjects = array();
public $Links = array();

public $ShapeClassSubjects = array();

public $Shape =  null;
public $Class = null;

Public $SubjectId = null;
public $AsAtDocumentId = null;

private $found = false;
private $startShapeClass = null;


private $Dicts = null;
	
	public function __construct(){
		
		global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}
	 	$this->System = $System;
	}
	
	public function __set($name, $value){
		switch ($name){
			case 'Shape':
				$this->Shape = $value;
				$this->found = false;
				break;
			case 'SubjectId':
				$this->SubjectId = $value;
				$this->found = false;
				break;
		}
	}


	public function __get($name){
		switch ($name){
			case 'Statements':
			case 'Subjects':
			case 'Links':
				if (!$this->found){
					$this->getCollection();
				}
				break;
		}
		return $this->$name;
	}
	
	private function getCollection(){
		
		$this->Statements = array();
		$this->Subjects = array();
		$this->Links = array();
		
		if (is_null($this->Shape)){
			throw new exception("Shape not set for Collection");
		}
		
// get all subjects and links for the shape, starting from the SubjectId, and bring all of the statements together into the array

		$this->getSubject($this->SubjectId);
				
	}
	
	private function getSubject($SubjectId, $objShapeClass = null){
		
		if (is_null($SubjectId)){
			return;
		}
		
		if (isset($this->Subjects[$SubjectId])){
			return;
		}
		
		$objSubject = new clsSubject($SubjectId);
		if (!is_object($objSubject)){
			return;
		}
		
		$objSubject->AsAtDocumentId = $this->AsAtDocumentId;

// check that the class is in the shape
		if (is_null($objShapeClass)){
			foreach ($this->Shape->ShapeClasses as $optShapeClass){
				if ($optShapeClass->Class->DictId == $objSubject->ClassDictId){
					if ($optShapeClass->Class->Id == $objSubject->ClassId){
						$objShapeClass = $optShapeClass;
						break;
					}				
				}
			}
		}

		if (!is_null($objShapeClass)){		
			
			if (is_null($this->startShapeClass)){
				$this->startShapeClass = $objShapeClass;
			}
			else
			{
				if ($objShapeClass === $this->startShapeClass){
					// to only allow a single subject for the selected class
					return;
				}
			}
			
			
			$this->Subjects[$SubjectId] = $objSubject;			
			$this->ShapeClassSubjects[$objShapeClass->Id][$SubjectId] = $objSubject;
			
			$this->Statements = array_merge($this->Statements,$objSubject->Statements);
			
			foreach ($objSubject->Links as $objLink){
				$this->getLink($objLink->Id);
			}
			
		}
		
	}
	
	private function getLink($LinkId){
		
		if (is_null($LinkId)){
			return;
		}
		
		if (isset($this->Links[$LinkId])){
			return;
		}
		
		$objLink = new clsLink($LinkId);
		
		if (!is_object($objLink)){
			return;
		}
		
		$objLink->AsAtDocumentId = $this->AsAtDocumentId;
	
// check that the link is in the shape

		$objShapeLink = null;
		foreach ($this->Shape->ShapeLinks as $optShapeLink){
			if ($optShapeLink->Relationship->DictId == $objLink->RelDictId){
				if ($optShapeLink->Relationship->Id == $objLink->RelId){
					$objShapeLink = $optShapeLink;
					break;
				}				
			}
		}
		
		if (!is_null($objShapeLink)){		
			$this->Links[$LinkId] = $objLink;
			
			$this->Statements = array_merge($this->Statements,$objLink->Statements);
			
			$FromShapeClass = $objShapeLink->Shape->ShapeClasses[$objShapeLink->FromShapeClassId];
			$ToShapeClass = $objShapeLink->Shape->ShapeClasses[$objShapeLink->ToShapeClassId];
			
			switch ($objShapeLink->Inverse){
				case true:
					$this->getSubject($objLink->SubjectId, $ToShapeClass);
					$this->getSubject($objLink->ObjectId, $FromShapeClass);
					break;
				default:
					$this->getSubject($objLink->SubjectId, $FromShapeClass);
					$this->getSubject($objLink->ObjectId, $ToShapeClass);
					break;
			}
						
		}
				
	}
	
	
	

	
	
	private function getComplexAttributeDot($objAtt){
			
		$found = false;
		
		$Label = "<table border='0' cellborder='1' cellspacing='0'>";

		foreach ($objAtt->ComplexAttributes as $objAtt){

			$found = true;
			
			$Color = 'white';
			$Label .= "<tr><td align='left' balign='left' valign='top'><b>".$this->objGraph->FormatDotCell($objAtt->Label,20)."</b></td><td bgcolor='$Color' align='left' balign='left' valign='top'>";
			
			switch ($objAtt->Property->Type){
				case 'simple':
					$Label .= $this->objGraph->FormatDotCell(truncate(   str_replace(chr(10), ',',  $objAtt->Value).' '),30);
					break;					
				case 'complex':
					$Label .= $this->getComplexAttributeDot($objAtt);
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
	
	
	private function getComplexAttributeLineDot($objAtt){
			
		$Label = '';
		foreach ($objAtt->ComplexAttributes as $objAtt){

			switch ($objAtt->Property->Type){
				case 'simple':
					$Label .= $objAtt->Value.' ';
					break;					
				case 'complex':
					$Label .= $this->getComplexAttributeLineDot($objAtt);
					break;
			}
					
		}
		
		return $Label;
		
	}
	
	
	
	public function getDot($Style=1){

		if (!$this->found){
			$this->getCollection();
		}
		
		if (is_null($this->Dicts)){
			global $Dicts;
			if (!isset($Dicts)){
				$Dicts = new clsDicts();
			}
			$this->Dicts = $Dicts;
		}
		
		
		$objGraph = new clsGraph();		
		$Nodes = array();
		$Links = array();
		$Clusters = array();
		
		foreach ($this->ShapeClassSubjects as $ShapeClassId=>$arrSubjects){
			foreach ($arrSubjects as $objSubject){
				$NodeId = $objSubject->getSubjectDot($Style,$objGraph, $Nodes, $Links, $Clusters);
			}
		}
		
		foreach ($this->Links as $objLink){
			if (isset($Links[$objLink->Id])){
				continue;
			}
			
			$objRel = $this->Dicts->getRelationship($objLink->RelDictId, $objLink->RelId);
			$RelLabel = $objRel->Label;
			
			$FromNodeId = 'subject_'.$objLink->SubjectId;
			$ToNodeId = 'subject_'.$objLink->ObjectId;
			
			switch ( $Style ){
				case 1:
				case 3:
					$Label = $RelLabel;
					break;
				case 2:
				case 4:
								
					$hasValues = false;													
					$Label = "<<table border='0' cellborder='1' cellspacing='0'>";
					$Label .= "<tr><td colspan='2' bgcolor='white'>$RelLabel</td></tr>";
								
					if (!is_null($objLink->EffectiveFrom)){
						$hasValues = true;
						$Label .= "<tr><td align='left' balign='left' valign='top'><b>Effective From</b></td><td  align='left' balign='left' valign='top'>".convertDate($objLink->EffectiveFrom)."</td></tr>";
					}
					if (!is_null($objLink->EffectiveTo)){
						$hasValues = true;
						$Label .= "<tr><td align='left' balign='left' valign='top'><b>Effective To</b></td><td  align='left' balign='left' valign='top'>".convertDate($objLink->EffectiveTo)."</td></tr>";
					}
					
					foreach ($objLink->Attributes as $DictAtts){
						foreach ($DictAtts as $PropAtts){
							foreach ($PropAtts as $objAtt){
								if (!IsEmptyString($objAtt->Value)){							
									$AttLabel = "<tr><td align='left' balign='left' valign='top'><b>".$objGraph->FormatDotCell($objAtt->Label,20)."</b></td><td  align='left' balign='left' valign='top'>".$objGraph->FormatDotCell(truncate($objAtt->Value),30)."</td></tr>";
									$Label .= $AttLabel;
									$hasValues = true;
								}
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
			$Links[$objLink->Id] = $objLink;
				
		}
		

		
		$Script = $objGraph->script;
		
		return $Script;		
		
	}
	
}


class clsDataClasses{
	
	private $Items = array();
	private $xml = null;
	
	private $OrgId = null;
	private $ContextId = null;
	private $LicenceTypeId = null;	
	private $ShapeId = null;
	
	private $System = null;
	
	private $Found = false;
	
	private $dom = null;
	private $xpath = null;
	private $DefaultNS = null;
	
	private $LdeNamespace = "http://schema.legsb.gov.uk/lde/";
	private $DictNamespace = "http://schema.legsb.gov.uk/lde/dictionary/";	
	private $MetaNamespace = "http://schema.legsb.gov.uk/lde/metadata/";
	
	public function __get($name){
		switch ($name){
			case 'Items':
				if (!$this->Found){
					$this->getClasses();					
				}
				break;

			case 'xml':
				return $this->getXML();
				break;

		}
		return $this->$name;
	}

	public function __set($name, $value){
		switch ($name){
			case 'OrgId':
				$this->OrgId = $value;
				$this->Found = false;
				break;
			case 'ContextId':
				$this->ContextId = $value;
				$this->Found = false;
				break;				
			case 'LicenceTypeId':
				$this->LicenceTypeId = $value;
				$this->Found = false;
				break;				
			case 'ShapeId':
				$this->ShapeId = $value;
				$this->Found = false;
				break;
		}
	}
	
	public function __construct(){
		global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}
	 	$this->System = $System;
	}
	
	private function getClasses(){

		global $Shapes;
		if (!isset($Shapes)){
			$Shapes = new clsShapes();
		}
		
		$Sets = new clsSets();
		$Sets->LicenceTypeId = $this->LicenceTypeId;
		$Sets->ContextId = $this->ContextId;
		$Sets->OrgId = $this->OrgId;
		$Sets->ShapeId = $this->ShapeId;
		
		$this->Items = array();
		
		foreach ($Sets->Items as $objSet){
			foreach ($objSet->SetShapes as $objSetShape){
				if (isset($Shapes->Items[$objSetShape->ShapeId])){
					$objShape = $Shapes->Items[$objSetShape->ShapeId];
					foreach ($objShape->ShapeClasses as $objShapeClass){
						if ($objShapeClass->Create === true){
							$this->Items[$objShapeClass->Class->DictId][$objShapeClass->Class->Id] = $objShapeClass->Class;
						}
					}
				}
			}
		}

		$this->Found = true;

	}
	
	private function getXML(){
	
		if (!$this->Found){
			$this->getClasses();
		}
		
		global $Dicts;
		if (!isset($Dicts)){
			$Dicts = new clsDicts();
		}

		$this->dom = new DOMDocument('1.0', 'utf-8');
		$this->dom->formatOutput = true;

		$DocumentElement = $this->dom->createElementNS($this->LdeNamespace, 'Classes');
		$this->dom->appendChild($DocumentElement);
		$DocumentElement->setAttribute("xmlns:meta", $this->MetaNamespace);
		$DocumentElement->setAttribute("xmlns:dict", $this->DictNamespace);
		
		foreach ($this->Items as $DictId=>$Classes){
			
			if (isset($Dicts->Dictionaries[$DictId])){			
				$xmlDict = $this->dom->importNode($Dicts->Dictionaries[$DictId]->xml, false);
				$DocumentElement->appendChild($xmlDict);
				
				foreach ($Classes as $objClass){
					if (is_object($objClass->xml)){
						$xmlDict->appendChild($this->dom->importNode($objClass->xml, true));
					}
				}
			}
		}
		
		return $this->dom->saveXML();
		
	}	
	
}

?>