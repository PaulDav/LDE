<?php

require_once("clsSystem.php");
require_once("clsGraph.php");
require_once("clsShape.php");
require_once("clsArchive.php");

require_once(dirname(__FILE__).'/../function/utils.inc');


class clsLicences {
	
	public $Items = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;

	private $folder = "rights";
	private $filename = "licences.xml";
    private $FilePath = null;

    private $xml = null;
	
	private $nsRights = null;
	

	public function __get($name){
		switch ($name){

			case 'xml':
				$this->getXML();
				break;

		}
		return $this->$name;
	}
	
	
	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		$this->nsRights = $System->Config->Namespaces['rights'];
		
		$this->FilePath = $System->path.$this->folder."//".$this->filename;
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		
		if (@$this->dom->load($this->FilePath) === false){
			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;

			$DocumentElement = $this->dom->createElementNS($this->nsRights, 'Licences');
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}

		$this->RefreshLicences();
		
		
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

	public function refreshLicences(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		foreach ($this->xpath->query("/rights:Licences/rights:Licence") as $xmlLicence){
			
			$objLicence = $this->makeLicence($xmlLicence);
			
			$objLicence->canView = $this->canView;
			$objLicence->canEdit = $this->canEdit;
			$objLicence->canControl = $this->canControl;
						
			$this->Items[$objLicence->Id] = $objLicence;
			
		}
	}

	
	private function makeLicence($xmlLicence){

		global $System;
		if (!isset($System)){
			$System = new clsSystem;
		}
		
		$xpath = new domxpath($xmlLicence->ownerDocument);
				
		foreach ($System->Config->Namespaces as $Alias=>$Namespace){
			$xpath->registerNamespace($Alias, $Namespace);
		}		
		
		$objLicence = new clsLicence();

		$objLicence->xml = $xmlLicence;
		
		$objLicence->Id = $xmlLicence->getAttribute("id");
		$objLicence->UserId = $xmlLicence->getAttribute("userid");
		
		$objLicence->Name = xmlelementvalue($xmlLicence, 'Name');
		$objLicence->Description = xmlelementvalue($xmlLicence, 'Description');
		
		foreach ($xpath->query("meta:Meta",$xmlLicence) as $xmlMeta){
			$objLicence->Version = $xmlMeta->getAttribute('version');
		}			
		
		foreach ($xpath->query("rights:Sets/rights:Set",$xmlLicence) as $xmlSet){
			$SetId = $xmlSet->getAttribute('id');
			if (!IsEmptyString($SetId)){
				$objLicence->SetIds[$SetId] = $SetId;
			}
		}

		foreach ($xpath->query("rights:Organisations/rights:Organisation",$xmlLicence) as $xmlOrg){
			$OrgId = $xmlOrg->getAttribute('id');
			if (!IsEmptyString($OrgId)){
				$objLicence->OrgIds[$OrgId] = $OrgId;
			}
		}

		foreach ($xpath->query("rights:Definitions/rights:Definition",$xmlLicence) as $xmlDef){
			$DefId = $xmlDef->getAttribute('id');
			if (!IsEmptyString($DefId)){
				$objLicence->DefIds[$DefId] = $DefId;
			}
		}
		
		return $objLicence;
		
	}
	

	public function getItem($Id, $Version=null){

		if (is_null($Version)){
			if (!isset($this->Items[$Id])){
				return false;
			}
			
			return $this->Items[$Id];
		}
		else
		{
			
			global $Archive;
			if (!isset($Archive)){
				$Archive = new clsArchive();
			}
			$objArchiveItem = $Archive->getItem('licence', $Id, $Version);
			if (!is_object($objArchiveItem)){
				return false;
			}

			$xmlLicence = $objArchiveItem->xml;
			
			$objLicence = $this->makeLicence($xmlLicence);
			
			$objLicence->canView = true;
			
			return $objLicence;
			
		}

	}
	
	
	private function getXML(){

		if (!is_null($this->xml)){
			return;
		}
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		
		$resdom = new DOMDocument('1.0', 'utf-8');
		$resdom->formatOutput = true;
		
		global $Orgs;
		if (!isset($Orgs)){
			$Orgs = new clsOrganisations();
		}
		
		global $Shapes;
		if (!isset($Shapes)){
			$Shapes = new clsShapes();
		}

		$nsLde = $System->Config->Namespaces['lde'];
		
		$DocumentElement = $resdom->createElementNS($nsLde, 'Results');
		$resdom->appendChild($DocumentElement);
				
		foreach ($this->Items as $objLicence){
			$xmlLicence = $resdom->importNode($objLicence->xml,true);
			$DocumentElement->appendChild($xmlLicence);
		}
					
		$this->xml = $resdom->saveXML();
		return $this->xml;
		
	}	
	
	public function Save(){
		$this->dom->save($this->FilePath);
	}
	

}

class clsLicence {
	
	public $xml = null;
	public $Id = null;
	public $Version = null;
	public $Name = null;
	public $Description = null;
		
	Public $SetIds = array();
	Public $OrgIds = array();
	Public $DefIds = array();
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
		
}


class clsOrganisations {
	
	public $Items = array();
	
	public $dom = null;
	public $xpath = null;
	public $DefaultNS = null;
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;

	private $folder = "rights";
	private $filename = "organisations.xml";
    private $FilePath = null;
	
	
	private $nsRights = null;
	

	public function __construct(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		$this->nsRights = $System->Config->Namespaces['rights'];
		
		$this->FilePath = $System->path.$this->folder."//".$this->filename;
		
		$this->dom = new DOMDocument('1.0', 'utf-8');
		
		if (@$this->dom->load($this->FilePath) === false){
			$this->dom = new DOMDocument('1.0', 'utf-8');
			$this->dom->formatOutput = true;

			$DocumentElement = $this->dom->createElementNS($this->nsRights, 'Organisations');
			$this->dom->appendChild($DocumentElement);
			
		}
		
		$this->DefaultNS = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
		$this->refreshXpath();
		
		
		$this->canView = true;
		if ($System->LoggedOn){
			$this->canEdit = true;
			$this->canControl = true;				
		}

		$this->RefreshOrganisations();
		
		
	}
	
	public function refreshXpath(){
		
		$this->xpath = new domxpath($this->dom);
		$this->xpath->registerNamespace('rights', $this->nsRights);
		
	}

	public function refreshOrganisations(){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		
		
		foreach ($this->xpath->query("/rights:Organisations/rights:Organisation") as $xmlOrg){
			
			$objOrg = new clsOrganisation;
			$objOrg->xml = $xmlOrg;
			$objOrg->Id = $xmlOrg->getAttribute("id");
			$objOrg->UserId = $xmlOrg->getAttribute("userid");
			
			$objOrg->Name = xmlelementvalue($xmlOrg, 'Name');
			$objOrg->Description = xmlelementvalue($xmlOrg, 'Description');
			$objOrg->URI = xmlelementvalue($xmlOrg, 'URI');
			$objOrg->WebSite = xmlelementvalue($xmlOrg, 'WebSite');

			$objOrg->canView = $this->canView;
			$objOrg->canEdit = $this->canEdit;
			$objOrg->canControl = $this->canControl;
			
			
			foreach ($this->xpath->query("rights:HasDefs/rights:HasDef",$xmlOrg) as $xmlHasDef){
				$objHasDef = new clsHasDef();
				$objHasDef->xml = $xmlHasDef;
				$objHasDef->Id = $xmlHasDef->getAttribute("id");
				
				$objHasDef->DefTypeId = $xmlHasDef->getAttribute("deftypeid");
				$objHasDef->DefId = $xmlHasDef->getAttribute("defid");
				
				if (!isemptystring($xmlHasDef->getAttribute("dateFrom"))){
					if ($inDate = DateTime::createFromFormat('Y-m-d', $xmlHasDef->getAttribute("dateFrom"))){
						$objHasDef->DateFrom = $inDate->format('d/m/Y');
					}
				}
				if (!isemptystring($xmlHasDef->getAttribute("dateTo"))){
					if ($inDate = DateTime::createFromFormat('Y-m-d', $xmlHasDef->getAttribute("dateTo"))){
						$objHasDef->DateTo = $inDate->format('d/m/Y');
					}
				}
				
				
				$objHasDef->Reference = xmlelementvalue($xmlHasDef, 'Reference');
				$objHasDef->URL = xmlelementvalue($xmlHasDef, 'URL');

				$objOrg->HasDefs[$objHasDef->Id] = $objHasDef;
				
			}

			$sql = "SELECT * FROM tbl_set WHERE setOrg = ".$objOrg->Id;
		 	$rst = $System->DbExecute($sql);
			
			while ($row = mysqli_fetch_array($rst, MYSQLI_ASSOC)) {
				$SetId = $row['setRecnum'];
			 	$objOrg->SetIds[] = $SetId;
			}		

			
			foreach ($this->xpath->query("rights:Roles/rights:Role",$xmlOrg) as $xmlRole){
				$objRole = new clsRole();
				$objRole->xml = $xmlRole;
				$objRole->Id = $xmlRole->getAttribute("id");
				
				$objRole->Name = xmlelementvalue($xmlRole, 'Name');
				$objRole->Description = xmlelementvalue($xmlRole, 'Description');
				
				$objRole->canView = $objOrg->canView;
				$objRole->canEdit = $objOrg->canEdit;
				$objRole->canControl = $objOrg->canControl;

				$objOrg->Roles[$objRole->Id] = $objRole;
				
				foreach ($this->xpath->query("rights:RolePurposes/rights:RolePurpose",$xmlRole) as $xmlRolePurpose){
					$objRolePurpose = new clsRolePurpose();
					
					$objRolePurpose->xml = $xmlRolePurpose;

					$objRolePurpose->Id = $xmlRolePurpose->getAttribute("id");
					
					$objRolePurpose->OrgId = $objOrg->Id;
					$objRolePurpose->RoleId = $objRole->Id;
					$objRolePurpose->PurposeId = $xmlRolePurpose->getAttribute('purposeid');
					
					$objRole->RolePurposes[$objRolePurpose->Id] = $objRolePurpose;				
				}

			}
			
			foreach ($this->xpath->query("rights:Users/rights:User",$xmlOrg) as $xmlUser){
				$objOrgUser = new clsOrgUser($xmlUser);
				$objOrg->Users[$objOrgUser->UserId] = $objOrgUser;
				$objOrg->UserRoles = $objOrg->UserRoles +$objOrgUser->UserRoles;
			}
			
			
			$this->Items[$objOrg->Id] = $objOrg;
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
	

}

class clsOrganisation {
	
	public $xml = null;
	public $Id = null;
	public $Name = null;
	public $Description = null;

	Public $URI = null;	
	Public $WebSite = null;

	Public $HasDefs = array();

	Public $SetIds = array();
	public $Roles = array();
	public $Users = array();
	public $UserRoles = array();
	Private $OrgLicences = null;

	public $canView = false;
	public $canEdit = false;
	public $canControl = false;	
	
	public function __get($name){
		switch ($name){
			case 'OrgLicences':
				if ($name == 'OrgLicences'){
					$this->getLicences();
				}
				break;
		}
		return $this->$name;	
	}

	private function getLicences(){
		
		global $Licences;
		if (!isset($Licences)){
			$Licences = new clsLicences;
		}
		
		$this->OrgLicences = array();
		
		foreach ($Licences->Items as $objLicence){
			foreach ($objLicence->OrgIds as $optOrgId){
				if ($optOrgId == $this->Id){
					$objOrgLicence = new clsOrgLicence();
					$objOrgLicence->Licence = $objLicence;
					$objOrgLicence->Organisation = $this;

					$this->OrgLicences[$objLicence->Id] = $objOrgLicence;
				}
			}
		}
	}
	
}

class clsRole {
	
	public $xml = null;
	public $Id = null;
	public $Name = null;
	public $Description = null;
	
	public $RolePurposes = array();
	
	public $canView = false;
	public $canEdit = false;
	public $canControl = false;
	
}


class clsRolePurpose{
	public $xml = null;
	
	public $Id = null;
	
	public $OrgId = null;
	public $RoleId = null;
	public $PurposeId = null;
}


class clsOrgLicence {
	
	public $Licence = null;
	public $Organisation = null;
	private $TermsMet = null;
	
	public function __get($name){
		switch ($name){
			case 'TermsMet':
				if (is_null($this->TermsMet)){
					$this->TermsMet = $this->getTermsMet();
				}				
				break;
		}
		if (isset($this->$name)){
			return $this->$name;
		}
	}
	
	private function getTermsMet(){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		
		global $Definitions;
		if (!isset($Definitions)){
			$Definitions = new clsDefinitions();
		}

		foreach ($this->Licence->DefIds as $DefId){
			if (isset($Definitions->Items[$DefId])){
				$objDef = $Definitions->Items[$DefId];
				if (isset($System->Config->OrgDefs[$objDef->TypeId])){
					
					$boolOrgHasDef = false;
					foreach ($this->Organisation->HasDefs as $objHasDef){
						if ($objHasDef->DefId == $DefId){							
							
							$Now = time();
							
							if (!is_null($objHasDef->DateFrom)){
								$DateFrom = str_replace('/', '-', $objHasDef->DateFrom);
								$DateFrom = strtotime($DateFrom);
								
								if ($DateFrom > $Now){
									continue;
								}
							}
								
							if (!is_null($objHasDef->DateTo)){
								$DateTo = str_replace('/', '-', $objHasDef->DateTo);
								$DateTo = strtotime($DateTo);
								
								if ($DateTo < $Now){
									continue;
								}
							}

							$boolOrgHasDef = true;
							
						}
					}
					
					if (!$boolOrgHasDef){
						return false;
					}
					
				}
			}

		}
		
		return true;

	}
	
}


class clsHasDef {
	
	public $xml = null;
	public $Id = null;
	public $DefTypeId = null;
	public $DefId = null;
	public $DateFrom = null;
	public $DateTo = null;
	Public $Reference = null;
	Public $URL = null;
	
}


class clsOrgUser{
	
	public $xml;
	public $UserId;
	public $UserRoles = array();
	
	private $dom = null;
	private $xpath = null;
	

	public function __construct($xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		$this->xml = $xml;

		if (!is_null($this->xml)){
		
			$this->dom = $this->xml->ownerDocument;
			
			$this->xpath = new domxpath($this->dom);
					
			foreach ($System->Config->Namespaces as $Alias=>$Namespace){
				$this->xpath->registerNamespace($Alias, $Namespace);
			}
			
			$this->UserId = $this->xml->getAttribute("userid");
			
			foreach ($this->xpath->query('rights:UserRoles/rights:UserRole', $this->xml) as $xmlUserRole){
				$objUserRole = new clsUserRole($this->UserId, $xmlUserRole);
				$this->UserRoles[$objUserRole->Id] = $objUserRole;
			}
		}
		
	}
	
	
}



class clsUserRole{
	
	public $xml;
	public $Id;
	public $RoleId = null;
	public $UserId = null;
	
	public $StartDate = null;
	public $EndDate = null;
	
	private $dom = null;
	private $xpath = null;
	

	public function __construct($UserId = null, $xml = null){

		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}		

		$this->UserId = $UserId;

		$this->xml = $xml;
		
		if (!is_null($this->xml)){
		
			$this->dom = $this->xml->ownerDocument;
			
			$this->xpath = new domxpath($this->dom);
					
			foreach ($System->Config->Namespaces as $Alias=>$Namespace){
				$this->xpath->registerNamespace($Alias, $Namespace);
			}

			$this->Id = $this->xml->getAttribute("id");
			$this->RoleId = $this->xml->getAttribute("roleid");
						
			if (!empty($this->xml->getAttribute('startdate'))){
				$this->StartDate = DateTime::createFromFormat('Y-m-d', $this->xml->getAttribute('startdate'));
			}
			if (!empty($this->xml->getAttribute('enddate'))){
				$this->EndDate = DateTime::createFromFormat('Y-m-d', $this->xml->getAttribute('enddate'));
			}
			
		}
		
	}
	
	
}


?>