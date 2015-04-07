<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsProfile.php');
require_once(dirname(__FILE__).'/../class/clsLibrary.php');


function dataSetUpdate($Mode, $Id = null,  $OrgId = null, $Name = null, $Source = null, $Status = 1, $Context = 10, $LicenceType = 10) {

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a set');
	}
	
	$UserId = $System->User->Id;
	
	$prevStatus = 0;

	switch ($Mode) {
		case 'edit':
			
			$objSet = new clsSet($Id);
			if ($objSet === false){
				throw new exception("Set does not exist");
			}
			
			if (!$objSet->canEdit){
				throw new exception("You cannot update this Set");
			}
			
			if (is_null($OrgId)){
				$OrgId = $objSet->OrgId;
			}
			
			$prevStatus = $objSet->Status;
			
			break;
		case 'new':
			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}

	
	if (IsEmptyString($Name)){
		$Name = "NULL";
	} else {		
		$Name  = "'".$db->real_escape_string($Name)."'";
	}
	
	if (IsEmptyString($Source)){
		$Source = "NULL";
	} else {
		$Source  = "'".$db->real_escape_string($Source)."'";
	}
		
	switch ($Mode){
		case 'edit':
			$sql = "update tbl_set set setName=$Name, setSource = $Source, setContext = $Context, setLicenceType = $LicenceType WHERE setRecnum = $Id";
			$result = $System->DbExecute($sql);
			break;
		case 'new':

			$sql = "insert into tbl_set ( setOrg, setName, setSource, setContext, setLicenceType ) values
                        ($OrgId, $Name, $Source, $Context, $LicenceType) ";
			$result = $System->DbExecute($sql);
			$Id = $System->db->insert_id;

			break;
	}
	
	if (!($Status == $prevStatus)){
		$sql = "insert into tbl_log ( logSet, logBy, logStatus ) values
	                        ($Id, $UserId, $Status) ";
				$result = $System->DbExecute($sql);
	}
	
	  	
	return $Id;
	
}  	

function dataSetClear($Id = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to clear a set');
	}
		
	if (is_null($Id)){
		throw new exception("Id not specified");				
	}
	
	$objSet = new clsSet($Id);
	if ($objSet === false){
		throw new exception("Set does not exist");
	}
	
	if (!($objSet->canEdit)){
		throw new exception("You cannot update this Set");
	}
		
	$sql = "DELETE FROM tbl_document WHERE docSet = $Id";		
	$System->dbExecute($sql);
	
}

function dataSetDelete($Id = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete a set');
	}
		
	if (is_null($Id)){
		throw new exception("Id not specified");				
	}
	
	$objSet = new clsSet($Id);
	if ($objSet === false){
		throw new exception("Set does not exist");
	}
	
	if (!($objSet->canEdit)){
		throw new exception("You cannot update this Set");
	}
		
	$sql = "DELETE FROM tbl_set WHERE setRecnum = $Id";		
	$System->dbExecute($sql);
	
}



function dataStatUpdate($Mode, $Id = null, $SetId = null, $DocId = null, $TypeId = null, $LinkDictId = null, $LinkId = null, $SubjectId = null, $ObjectId = null, $Value = null, $EffectiveFrom = null, $EffectiveTo = null, $AboutId=null) {

	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a statement');
	}
	
	$UserId = $System->User->Id;
	
	switch ($Mode) {
		case 'edit':
			
			$objStat = new clsStatement($Id);
			if ($objStat === false){
				throw new exception("Statement does not exist");
			}
			
//			if (!$objStat->canEdit){
//				throw new exception("You cannot update this Statement");
//			}
			
			if (is_null($SetId)){
				$SetId = $objStat->SetId;
			}
			
			break;
		case 'new':
			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}
	
	$objSet = new clsSet($SetId);
	if (!$objSet->canEdit){
		throw new exception("You cannot update this Set");
	}
	
	if (IsEmptyString($DocId)){
		$DocId = "NULL";
	}

	if (IsEmptyString($LinkId)){
		$LinkId = "NULL";
		$LinkDictId = "NULL";
	}
	else
	{
		if (IsEmptyString($LinkDictId)){
			$LinkDictId = "NULL";
		} else {		
			$objDictItem = new clsDictItem($LinkDictId, $LinkId, $TypeId);
			$LinkDictId  = "'".$db->real_escape_string($LinkDictId)."'";
		}
	}

	
	if (IsEmptyString($SubjectId)){
		$SubjectId = "NULL";
	}
	
	if (IsEmptyString($ObjectId)){
		$ObjectId = "NULL";
	}

	if (IsEmptyString($AboutId)){
		$AboutId = "NULL";
	}
	
	
	if (is_null($EffectiveFrom)){
		$EffectiveFrom = 'NULL';
	}
	else
	{
		$date = DateTime::createFromFormat('d/m/Y|', $EffectiveFrom);
		if ($date === false){
			throw new Exception("Invalid From Date");
		}
		$EffectiveFrom = "'".$date->format('Y-m-d')."'";
	}
	
	if (is_null($EffectiveTo)){
		$EffectiveTo = 'NULL';
	}
	else
	{
		$date = DateTime::createFromFormat('d/m/Y|', $EffectiveTo);
		if ($date === false){
			throw new Exception("Invalid To Date");
		}
		
		$EffectiveTo = "'".$date->format('Y-m-d')."'";
	}
	

	switch ($Mode){
		case 'edit':
			$sql = "update tbl_statement set stmSubject=$SubjectId, stmLinkDict = $LinkDictId, stmLink = $LinkId, stmObject = $ObjectId, stmEffFrom = $EffectiveFrom, stmEffTo = $EffectiveTo, stmAboutStatement = $AboutId WHERE stmRecnum = $Id";
			$result = $System->DbExecute($sql);
			break;
		case 'new':
			
			if ($TypeId == 100){
				$sql = "insert into tbl_subject values() ";
				$result = $System->DbExecute($sql);
				$SubjectId = $System->db->insert_id;				
			}
			
			$sql = "insert into tbl_statement ( stmSet, stmDocument, stmType, stmSubject, stmLinkDict, stmLink, stmObject, stmEffFrom, stmEffTo, stmAboutStatement ) values
                        ($SetId, $DocId, $TypeId, $SubjectId, $LinkDictId, $LinkId, $ObjectId, $EffectiveFrom, $EffectiveTo, $AboutId) ";
			$result = $System->DbExecute($sql);
			$Id = $System->db->insert_id;

			break;
	}	

	if ($TypeId == 200){
				
		$objProperty = $objDictItem->Object;
		
		switch ($objProperty->Type){
			case 'simple':
		
				$DataType = 100;
		
				
				if (!($DataType = array_search($objProperty->Field->DataType, $System->Config->DataTypes))){
					$DataType = 100;
				}
		
				$ValId = null;
				switch ($Mode){
					case 'edit':
						$sql = "SELECT * FROM tbl_value WHERE valStatement = $Id";
		
						$rstVal = $System->DbExecute($sql);
						if ($rstVal->num_rows > 0){
							$rowVal = $rstVal->fetch_assoc();
							$ValId = $rowVal['valRecnum'];
						}
						break;
				}
						
				if (is_null($ValId)){
					$sql = "insert into tbl_value ( valStatement, valDataType) values
					       ($Id, $DataType) ";
					$result = $System->DbExecute($sql);
					$ValId = $System->db->insert_id;
				}
						
				$ValTableName = "tbl_value_string";
				if (isset($System->Config->ValueTables[$DataType])){
					$ValTableName = $System->Config->ValueTables[$DataType];
				}
				
		
				if (IsEmptyString($Value)){
					$Value = "NULL";
				} else
				{
					switch($DataType){
						case 300: // date
							$date = DateTime::createFromFormat('d/m/Y', $Value);
							if (!is_object($date)){
								echo "invalid date $Value";
								exit;
								throw new exception("invalid date");
							}
							$Value = "'".$date->format('Y-m-d')."'";
							break;
						case 400: //time		
							$Value  = "'$Value'";
							break;
						case 500: //integer
						case 700: // number
							break;
						case 600: //currency			
							$Value  = str_replace('.','',$Value);
							break;
						default:
							$Value  = "'".$db->real_escape_string($Value)."'";
							break;					
					}
				}
				
				
				$ValExists = false;
				switch ($Mode){
					case 'edit':
						$sql = "SELECT * FROM $ValTableName WHERE valRecnum = $ValId";
						$rstVal = $System->DbExecute($sql);
						if ($rstVal->num_rows > 0){
							$ValExists = true;
						}
						break;
				}
				if ($ValExists){
					$sql = "update $ValTableName set valValue=$Value WHERE valRecnum = $ValId";
					$result = $System->DbExecute($sql);			
				}	
				else
				{			
					$sql = "insert into $ValTableName ( valRecnum, valValue) values
		    	                    ($ValId, $Value) ";
					$result = $System->DbExecute($sql);
				}
				break;
		}
	}
	
	return $Id;
	
}  	



function dataStatDelete($Id = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete a statement');
	}
		
	if (is_null($Id)){
		throw new exception("Id not specified");				
	}
	
	$objStatement = new clsStatement($Id);
	if ($objStatement === false){
		throw new exception("Statement does not exist");
	}
	
/*	
	if (!($objStatement->canEdit)){
		throw new exception("You cannot update this Statement");
	}
*/
			
	$sql = "DELETE FROM tbl_statement WHERE stmRecnum = $Id";		
	$System->dbExecute($sql);
	
}





function dataSetProfileUpdate($Mode, $Id = null, $SetId = null, $ProfileId = null) {

	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	global $Profiles;
	if (!isset($Profiles)){
		$Profiles = new clsProfiles();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a set profile');
	}
	
	$UserId = $System->User->Id;
	
	switch ($Mode) {
		case 'edit':
			
			$objSetProfile = new clsSetProfile($Id);
			if ($objSetProfile === false){
				throw new exception("Set Profile does not exist");
			}
			
			if (is_null($SetId)){
				$SetId = $objSetProfile->SetId;
			}
			
			break;
		case 'new':
			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}
	
	$objSet = new clsSet($SetId);
	if (!$objSet->canEdit){
		throw new exception("You cannot update this Set");
	}
	
	if (is_null($ProfileId)){
		throw new exception("ProfileId not specified");
	}
			
	$ProfileId  = "'".$db->real_escape_string($ProfileId)."'";	
	
	switch ($Mode){
		case 'edit':
			$sql = "update tbl_set_profile set setprfSet=$SetId, setprfProfile = $ProfileId WHERE setprfRecnum = $Id";
			$result = $System->DbExecute($sql);
			break;
		case 'new':
						
			$sql = "insert into tbl_set_profile ( setprfSet, setprfProfile ) values
                        ($SetId, $ProfileId) ";
			$result = $System->DbExecute($sql);
			$Id = $System->db->insert_id;

			break;
	}	
	
	return $Id;
	
}  	


function dataSetProfileDelete($SetId = null, $SetProfileId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete a set');
	}
		

	if (is_null($SetId)){
		throw new exception("SetId not specified");				
	}
	
	if (is_null($SetProfileId)){
		throw new exception("Id not specified");				
	}
	
	
	$objSet = new clsSet($SetId);
	if ($objSet === false){
		throw new exception("Set does not exist");
	}
	
	if (!($objSet->canEdit)){
		throw new exception("You cannot update this Set");
	}

	if (!isset($objSet->SetProfiles[$SetProfileId])){
		throw new exception("Profile not in Set");
	}
	
	$sql = "DELETE FROM tbl_set_profile WHERE setprfRecnum = $SetProfileId";
	$System->dbExecute($sql);
	
}


function dataSetShapeUpdate($Mode, $Id = null, $SetId = null, $ShapeId = null) {

	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	global $Shapes;
	if (!isset($Shapes)){
		$Shapes = new clsShapes();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a set shape');
	}
	
	$UserId = $System->User->Id;
	
	switch ($Mode) {
		case 'edit':
			
			$objSetShape = new clsSetShape($Id);
			if ($objSetShape === false){
				throw new exception("Set Shape does not exist");
			}
			
			if (is_null($SetId)){
				$SetId = $objSetShape->SetId;
			}
			
			break;
		case 'new':
			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}
	
	$objSet = new clsSet($SetId);
	if (!$objSet->canEdit){
		throw new exception("You cannot update this Set");
	}
	
	if (is_null($ShapeId)){
		throw new exception("ShapeId not specified");
	}
			
	$ShapeId  = "'".$db->real_escape_string($ShapeId)."'";	
	
	switch ($Mode){
		case 'edit':
			$sql = "update tbl_set_shape set setshpSet=$SetId, setshpShape = $ShapeId WHERE setshpRecnum = $Id";
			$result = $System->DbExecute($sql);
			break;
		case 'new':
						
			$sql = "insert into tbl_set_shape ( setshpSet, setshpShape ) values
                        ($SetId, $ShapeId) ";
			$result = $System->DbExecute($sql);
			$Id = $System->db->insert_id;

			break;
	}	
	
	return $Id;
	
}  	


function dataSetShapeDelete($SetId = null, $SetShapeId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete a set');
	}
		

	if (is_null($SetId)){
		throw new exception("SetId not specified");				
	}
	
	if (is_null($SetShapeId)){
		throw new exception("Id not specified");				
	}
	
	
	$objSet = new clsSet($SetId);
	if ($objSet === false){
		throw new exception("Set does not exist");
	}
	
	if (!($objSet->canEdit)){
		throw new exception("You cannot update this Set");
	}

	if (!isset($objSet->SetShapes[$SetShapeId])){
		throw new exception("Shape not in Set");
	}
	
	$sql = "DELETE FROM tbl_set_shape WHERE setshpRecnum = $SetShapeId";
	$System->dbExecute($sql);
	
}


function dataSetPurposeUpdate($Mode, $Id = null, $SetId = null, $PurposeId = null) {

	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	global $Defs;
	if (!isset($Defs)){
		$Defs = new clsDefs();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a set');
	}
	
	$UserId = $System->User->Id;
	
	switch ($Mode) {
		case 'edit':
			
			$objSetPurpose = new clsSetPurpose($Id);
			if ($objSetPurpose === false){
				throw new exception("Set Purpose does not exist");
			}
			
			if (is_null($SetId)){
				$SetId = $objSetPurpose->SetId;
			}
			
			break;
		case 'new':
			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}
	
	$objSet = new clsSet($SetId);
	if (!$objSet->canEdit){
		throw new exception("You cannot update this Set");
	}
	
	if (is_null($PurposeId)){
		throw new exception("PurposeId not specified");
	}
			
	$PurposeId  = "'".$db->real_escape_string($PurposeId)."'";	
	
	switch ($Mode){
		case 'edit':
			$sql = "update tbl_set_purpose set setprpSet=$SetId, setprpPurpose = $PurposeId WHERE setprpRecnum = $Id";
			$result = $System->DbExecute($sql);
			break;
		case 'new':

			$sql = "insert into tbl_set_purpose ( setprpSet, setprpPurpose ) values
                        ($SetId, $PurposeId) ";
			$result = $System->DbExecute($sql);
			$Id = $System->db->insert_id;

			break;
	}	
	
	return $Id;
	
}  	


function dataSetPurposeDelete($SetId = null, $SetPurposeId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete a set');
	}
		

	if (is_null($SetId)){
		throw new exception("SetId not specified");				
	}
	
	if (is_null($SetPurposeId)){
		throw new exception("Id not specified");				
	}
	
	
	$objSet = new clsSet($SetId);
	if ($objSet === false){
		throw new exception("Set does not exist");
	}
	
	if (!($objSet->canEdit)){
		throw new exception("You cannot update this Set");
	}

	if (!isset($objSet->SetPurposes[$SetPurposeId])){
		throw new exception("Purpose not in Set");
	}
	
	$sql = "DELETE FROM tbl_set_purpose WHERE setprpRecnum = $SetPurposeId";
	$System->dbExecute($sql);
	
}


function dataDocUpdate($Mode, $Id = null,  $SetId = null, $ShapeId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a set');
	}
	
	$UserId = $System->User->Id;

	switch ($Mode) {
		case 'edit':
			
			$objDoc = new clsDocument($Id);
			if ($objDoc === false){
				throw new exception("Document does not exist");
			}
						
			if (is_null($SetId)){
				$SetId = $objDoc->SetId;
			}
			
			break;
		case 'new':
			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}
	
	$ShapeId  = "'".$db->real_escape_string($ShapeId)."'";	
	
	switch ($Mode){
		case 'edit':
			$sql = "update tbl_document set docSet=$SetId, docShape = $ShapeId WHERE docRecnum = $Id";
			$result = $System->DbExecute($sql);
			break;
		case 'new':

			$sql = "insert into tbl_document ( docSet, docShape ) values
                        ($SetId, $ShapeId) ";
			
			$result = $System->DbExecute($sql);
			$Id = $System->db->insert_id;

			break;
	}	
	  	
	return $Id;
	
}  	


function dataDocSubject( $Id = null,  $SubjectId = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a set');
	}
	
	$UserId = $System->User->Id;
			
	$objDoc = new clsDocument($Id);
	if ($objDoc === false){
		throw new exception("Document does not exist");
	}
			
	$sql = "update tbl_document set docSubject=$SubjectId WHERE docRecnum = $Id";
	$result = $System->DbExecute($sql);
	  	
	return true;
	
}  	


function dataDocDelete($Id = null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to delete a document');
	}
		
	if (is_null($Id)){
		throw new exception("Id not specified");				
	}
	
	$objDoc = new clsDocument($Id);
	if ($objDoc === false){
		throw new exception("Document does not exist");
	}
			
	$sql = "DELETE FROM tbl_document WHERE docRecnum = $Id";		
	$System->dbExecute($sql);
	
}

?>