<?php

require_once('clsSystem.php');
require_once('clsDict.php');
require_once('clsProfile.php');
require_once('clsShape.php');
require_once('clsRecordset.php');


class clsGroups{

private $Ids = array(); 
private $UserId = null;
private $OwnerId = null;
private $Published = null;

private $OrderBy = "grpName";

public $objRst = null;

	  public function __get($name){
	  	return $this->$name;
	  }

	  public function __set($name,$value){
	  	$this->$name = $value;
	  }

	 public function __construct(){
	 	
	 	$this->objRst = new clsRecordset();
	 	
	 }	 
	  
	 public function getIds(){

	 	global $System;
	 	if (!isset($System)){
	 		$System = new clsSystem();
	 	}

		$this->Ids = array();
		
		$boolWhere = false;
	
		$sql = "SELECT DISTINCT grpRecnum from tbl_group LEFT JOIN tbl_usergroup ON grpRecnum=usrgrpGroup ";

		if (!is_null($this->UserId)){
			if ($boolWhere === false){
				$sql = $sql . " WHERE ";
				$boolWhere = true;
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "( usrgrpUser = ".$this->UserId." OR grpOwner = ".$this->UserId.") ";
						
		}

		if (!is_null($this->OwnerId)){
			if ($boolWhere === false){
				$sql = $sql . " WHERE ";
				$boolWhere = true;
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "grpOwner = ".$this->OwnerId." ";
		}
		
		if (!is_null($this->Published)){
			if ($boolWhere === false){
				$sql = $sql . " WHERE ";
				$boolWhere = true;
			}
			else
			{
				$sql .= " AND ";
			}
			switch ($this->Published){
				case true:
					$sql .= "grpPublish = TRUE ";
					break;
				case false:
					$sql .= "grpPublish = FALSE ";
					break;
			}
		}

		if (!empty($this->OrderBy)){
			$sql .= "ORDER BY ".$this->OrderBy;
		}
		
		$this->objRst->sql = $sql;
				
		while ($row = mysqli_fetch_array($this->objRst->rst, MYSQLI_ASSOC)) {
			$this->Ids[] = $row['grpRecnum'];
		}
	
		return $this->Ids;
	 }
	
}


class clsGroup {

  private $Id;
  private $Name;
  private $Description;
  private $Picture;
  
  private $Publish = false;
    
  private $OwnerId;
  
  private $canView = false;
  private $canEdit = false;
  private $canControl = false;
    
  private $UserGroupIds = array();
  private $DictionaryIds = array();
  private $ShapeIds = array();
  private $ProfileIds = array();
  private $SpecIds = array();
  private $PartitionIds = array();
  private $ViewIds = array();
    
	public function __get($name){
		switch ($name){
			case "MyStatus":
	 			return $this->getMyStatus();
	 			break;			
	 		case "MyRights":
	 			return $this->getMyRights();
	 			break;
	 		default:
			  	return $this->$name;
			  	break;
	 	}
	}
  	
	 public function __construct ($Id){
	 	global $System;
	 	if (!isset($System)){
	 		$System = new clssystem();
	 	}
	 	
	 	 	
	 	$sql = "SELECT * FROM tbl_group WHERE grpRecnum = $Id";
	 	
	 	$rst = $System->DbExecute($sql);
		if (!$rst->num_rows > 0){
			return false;
		}
		$rstRow = $rst->fetch_assoc();	
	 	
		$this->Id = $rstRow['grpRecnum'];
		$this->Name = stripcslashes(Encode($rstRow['grpName']));
		$this->Description = stripcslashes(Encode($rstRow['grpDescription']));
		$this->Picture = $rstRow['grpImage'];
				
		if ($rstRow['grpPublish'] == true){
			$this->Publish = true;
		}
		
		
		$this->OwnerId = $rstRow['grpOwner'];
				
		$sql = "SELECT * FROM tbl_usergroup WHERE usrgrpGroup = $Id";
	 	$rst = $System->DbExecute($sql);
		
		while ($row = mysqli_fetch_array($rst, MYSQLI_ASSOC)) {
			$UserGroupId = $row['usrgrpRecnum'];
			$UserGroupUserId = $row['usrgrpUser'];
			if (!($UserGroupUserId == $this->OwnerId)){
		 		$this->UserGroupIds[$UserGroupUserId] = $UserGroupId;
			}
		}		
		
		if ($this->Publish === true){
			$this->canView = true;
		}
			
		if ($this->MyRights->Rights > 0){
			$this->canView = true;
		}
		if ($this->MyRights->Rights > 99){
			$this->canEdit = true;
		}
		if ($this->MyRights->Rights > 199){
			$this->canControl = true;
		}
		
		$dictspath = $System->path.'/dictionaries/';
		
		if ($handle = opendir($dictspath)) {
		    while (false !== ($entry = readdir($handle))) {
		        if ($entry != "." && $entry != "..") {

					$domDict = new DOMDocument();
					if (!(@$domDict->load($dictspath.$entry) === false)){
						if ($domDict->documentElement->getAttribute("groupid") == $this->Id){
							
							$DictId = explode(".",$entry);
							array_pop($DictId);
							$DictId = implode(".",$DictId);
							
							$this->DictionaryIds[] = $DictId;
						}
					}
					
		        }
		    }
		    closedir($handle);
		}

		$shapespath = $System->path."shapes//shapes.xml";
				
		$domShapes = new DOMDocument();
		if (!(@$domShapes->load($shapespath) === false)){
			$ShapeNS = $domShapes->lookupNamespaceUri($domShapes->namespaceURI);
			$xpathShapes = new domxpath($domShapes);
			$xpathShapes->registerNamespace('shape', $ShapeNS);
									
			foreach ($xpathShapes->query("/shape:Shapes/shape:Shape") as $xmlShape){
				if ($xmlShape->getAttribute("groupid") == $this->Id){							
					$this->ShapeIds[] = $xmlShape->getAttribute("id");
				}
			}
		}

		$profilespath = $System->path."profiles//profiles.xml";
		
		$domProfiles = new DOMDocument();
		if (!(@$domProfiles->load($profilespath) === false)){
			$ProfileNS = $domProfiles->lookupNamespaceUri($domProfiles->namespaceURI);
			$xpathProfiles = new domxpath($domProfiles);
			$xpathProfiles->registerNamespace('profile', $ProfileNS);
									
			foreach ($xpathProfiles->query("/profile:Profiles/profile:Profile") as $xmlProfile){
				if ($xmlProfile->getAttribute("groupid") == $this->Id){							
					$this->ProfileIds[] = $xmlProfile->getAttribute("id");
				}
			}
		}
		
		
		$specspath = $System->path."specs//specifications.xml";
		
		$domSpecs = new DOMDocument();
		if (!(@$domSpecs->load($specspath) === false)){
			$ProfileNS = $domSpecs->lookupNamespaceUri($domSpecs->namespaceURI);
			$xpathSpecs = new domxpath($domSpecs);
			$xpathSpecs->registerNamespace('spec', $ProfileNS);
									
			foreach ($xpathSpecs->query("/spec:Specs/spec:Spec") as $xmlSpec){
				if ($xmlSpec->getAttribute("groupid") == $this->Id){							
					$this->SpecIds[] = $xmlSpec->getAttribute("id");
				}
			}
		}
				
		$viewspath = $System->path."views/";
		
		if ($handle = @opendir($viewspath)) {
		    while (false !== ($entry = readdir($handle))) {
		        if ($entry != "." && $entry != "..") {
					$domView = new DOMDocument();
					if (!(@$domView->load($viewspath.$entry) === false)){
						if ($domView->documentElement->getAttribute("groupid") == $this->Id){
							
							$ViewId = explode(".",$entry);
							array_pop($ViewId);
							$ViewId = implode(".",$ViewId);
							
							$this->ViewIds[] = $ViewId;
						}
					}
					
		        }
		    }
		    closedir($handle);
		}

	}	
	
	private function getMyRights(){
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
				
		if (!$System->LoggedOn){
			return new clsUserGroupRights(0);
		}
		
		if ($this->OwnerId == $System->User->Id){
			return new clsUserGroupRights(200);
		}
		
		if (isset($this->UserGroupIds[$System->User->Id])){
			$MyUserGroupId = $this->UserGroupIds[$System->User->Id];
			$objMyUserGroup = new clsUserGroup($MyUserGroupId);
			if ($objMyUserGroup->Status == 100){
				return new clsUserGroupRights($objMyUserGroup->Rights);
			}
		}
		
		return new clsUserGroupRights(0);			
						
	}
		
	private function getMyStatus(){
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		
				
		if (!$System->LoggedOn){
			return new clsUserGroupStatus(0);			
		}
		
		if ($this->OwnerId == $System->User->Id){
			return new clsUserGroupStatus(100);
		}
		if (isset($this->UserGroupIds[$System->User->Id])){
			$MyUserGroupId = $this->UserGroupIds[$System->User->Id];
			$objMyUserGroup = new clsUserGroup($MyUserGroupId);
			return new clsUserGroupStatus($objMyUserGroup->Status);
		}
		
		return new clsUserGroupStatus(0);			
						
	}
	
	
}


class clsUserGroup {

  private $Id;
  private $GroupId;
  private $UserId;
  private $Rights;
  private $RightsName = null;
  private $Status;
  private $StatusName = null;
  
      
	 public function __get($name){
	  	return $this->$name;
	  }
  	
	 public function __construct ($Id){
	
	 	global $System;
	 	if (!isset($System)){
			$System = new clsSystem();
		}
	 	
	 		 	 	
	 	$sql = "SELECT * FROM tbl_usergroup WHERE usrgrpRecnum = $Id";
	 	
	 	$rst = $System->DbExecute($sql);
		if (!$rst->num_rows > 0){
			return false;
		}
		$rstRow = $rst->fetch_assoc();	
	 	
		$this->Id = $rstRow['usrgrpRecnum'];
		
		$this->GroupId = $rstRow['usrgrpGroup'];
		$this->UserId = $rstRow['usrgrpUser'];

		$this->Rights = $rstRow['usrgrpRights'];
		if (isset($System->Config->UserGroupRights[$this->Rights])){
			$this->RightsName = $System->Config->UserGroupRights[$this->Rights];
		}

		$this->Status = $rstRow['usrgrpStatus'];
		if (isset($System->Config->UserGroupStatus[$this->Status])){
			$this->StatusName = $System->Config->UserGroupStatus[$this->Status];
		}
				
	}	
	
}


class clsUserGroupStatus {
	private $Status;
	private $Name = null;
	
	public function __get($name){
		return $this->$name;
	}
	
	public function __construct($Status){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		
		
		$this->Status = $Status;
		if (isset($System->Config->UserGroupStatus[$this->Status])){
			$this->Name = $System->Config->UserGroupStatus[$this->Status];
		}
	}
		
}

class clsUserGroupRights {
	private $Rights;
	private $Name = null;
	
	public function __get($name){
		return $this->$name;
	}
	
	public function __construct($Rights){
		
		global $System;
		if (!isset($System)){
			$System = new clsSystem();
		}
		
		
		$this->Rights = $Rights;
		if (isset($System->Config->UserGroupRights[$this->Rights])){
			$this->Name = $System->Config->UserGroupRights[$this->Rights];
		}
	}			
}
?>