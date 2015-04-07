<?php
	require_once('function/utils.inc');
	require_once('data/dataData.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');

	require_once('class/clsDict.php');
	require_once('class/clsProfile.php');
	require_once('class/clsData.php');
		
	define('PAGE_NAME', 'import');
	
	session_start();
	$System = new clsSystem();
	
		
	try {
	
		SaveUserInput(PAGE_NAME);
			
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$Profiles = new clsProfiles();
		$Specs = new clsSpecs();
		
		$GroupId = null;
		$SetId = null;
		$SpecId = null;
		$ProfileId = null;
		
		$FilePath = null;
		$FileHandle = null;

		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['filepath'])){
			$FilePath = './import/'. $_SESSION['forms'][PAGE_NAME]['filepath'];			
		}
		if (is_null($FilePath)){
			throw new exception ("File Path not specified");
		}
		
		if (($FileHandle = fopen($FilePath, "r")) == FALSE) {
			throw new exception ("Can't open file");
		}
		

		if (isset($_SESSION['forms'][PAGE_NAME]['setid'])){
			$SetId = $_SESSION['forms'][PAGE_NAME]['setid'];			
		}
		
		if (is_null($SetId)){
			throw new exception ("Set not specified");
		}
		
		$objSet = new clsSet($SetId);
		if (!($objSet->canEdit)){
			throw new exception("You cannot update this Set");
		}

		
		if (isset($_SESSION['forms'][PAGE_NAME]['specid'])){
			$SpecId = $_SESSION['forms'][PAGE_NAME]['specid'];			
		}
		if (is_null($SpecId)){
			throw new exception ("Spec not specified");
		}		
		
		if (!isset($Specs->Items[$SpecId])){
			throw new exception("Unknown Specification");
		}
		
		$objSpec = $Specs->Items[$SpecId];
		
		$ProfileId = $objSpec->ProfileId;		
		if (is_null($ProfileId)){
			throw new exception ("Profile not specified");
		}		
		$objProfile = $Profiles->Items[$ProfileId];

				
		$Dicts = new clsDicts();

		$RowNum = 0;
		$Values = array();
		while (($Values = fgetcsv($FileHandle, 0, ",")) !== FALSE) {
			$RowNum++;
			
			if ($RowNum < 2){
				continue;
			}
			
			$DocId = null;
			
			$RelId = null;
			$RelStatId = null;
			$Seq = 0;
	
			$SubjectId = null;
			$objSubject = null;
			
			$ObjectId = null;
			$objDoc = null;		
			$objObject = null;
			
			$objForm = null;
			$objLinkForm = null;
			
			$RelEffFrom = null;
			$RelEffTo = null;
			
			
			$objForm = new clsForm($ProfileId);
			$objdocForm = $objForm;
		
			$Mode = 'new';
			$DocId = dataDocUpdate($Mode, $DocId , $SetId, $ProfileId);
			
			getValues($objForm);
			$ObjectId = updateValues($objForm);
			
			dataDocSubject( $DocId,  $ObjectId);
			
			
		}
								
		
		fclose($FileHandle);
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: set.php?setid=$SetId");
    	exit;
				
	}
	catch(Exception $e)  {		
		$System->doError($e->getMessage());
		exit;
	}
	
	
function getValues($objForm){
	
	global $objSpec;
	global $Values;
		
	foreach ($objForm->FormFields as $FieldNum=>$arrFields){
		foreach ($arrFields as $occ=>$objFormField){
			
			if (isset($objSpec->Fields[$objFormField->FieldName])){

				$SpecField = $objSpec->Fields[$objFormField->FieldName];
				$Col = $SpecField->Col;
				if (!is_null($SpecField->Col - 1)){
					if (isset($Values[$Col - 1])){
						
						if (!isemptystring($Values[$Col - 1])){

// apply translation						
							if (!is_null($SpecField->TransId)){
								if (isset($objSpec->Translations[$SpecField->TransId])){
									$objTrans = $objSpec->Translations[$SpecField->TransId];
									foreach ($objTrans->Items as $optTransItem){
										if ($optTransItem->FromValue == $Values[$Col -1]){
											$Values[$Col - 1] = $optTransItem->ToValue;
										}
									}
								}
							}
							
							$objFormField->Value = $Values[$Col - 1];
							
							$objForm->hasValues = true;
						}
					}
				}
			}			
		}
	}
	
	if (get_class($objForm) == "clsForm"){
		foreach ($objForm->LinkForms as $ProfileRelId=>$arrLinks){
			foreach ($arrLinks as $seq=>$objLinkForm){
				getValues($objLinkForm->ObjectForm);
			}
		}
	}

}

function updateValues($objForm){
	
	global $SetId;
	global $DocId;
	global $Mode;

	$ThisMode = $Mode;
	
	$SubjectId = $objForm->SubjectId;
	if (is_null($SubjectId)){
		$SubjectStatId = null;
		$ThisMode = 'new';
		$SubjectStatId = dataStatUpdate($ThisMode, $SubjectStatId , $SetId, $DocId, 100, $objForm->Class->DictId, $objForm->Class->Id);
		$objSubjectStatement = new clsStatement($SubjectStatId);
		$SubjectId = $objSubjectStatement->SubjectId;
	}
	
	foreach ($objForm->FormFields as $FieldNum=>$arrFields){
		foreach ($arrFields as $occ=>$objFormField){
			$FieldStatId = null;
			$FieldMode = 'new';
			switch ($ThisMode){
				case 'edit':
					if (!is_null($objFormField->Statement)){
						$FieldStatId = $objFormField->Statement->Id;
						$SubjectId = $objFormField->Statement->SubjectId;
						$FieldMode = 'edit';
					}					
					break;
			}
			
			if (!is_null($objFormField->Value)){
				$FieldStatId = dataStatUpdate($FieldMode, $FieldStatId , $SetId, $DocId, 200, $objFormField->Property->DictId, $objFormField->Property->Id, $SubjectId, null, $objFormField->Value);
			}
		}
	}
	
	foreach ($objForm->LinkForms as $ProfileRelId=>$arrLinkForms){
		foreach ($arrLinkForms as $seq=>$objLinkForm){			
			if ($objLinkForm->ObjectForm->hasValues){
				$ObjectId = updateValues($objLinkForm->ObjectForm);
				$RelStatId = null;
				$RelMode = 'new';
				if (!is_null($objLinkForm->Statement)){
					$RelStatId = $objLinkForm->Statement->Id;
					$RelMode = 'edit';
				}		
				$RelStatId = dataStatUpdate($RelMode, $RelStatId , $SetId, $DocId, 300, $objLinkForm->Relationship->DictId, $objLinkForm->Relationship->Id, $SubjectId, $ObjectId);
			}
		}
	}
	
	return $SubjectId;

}



function updateLinkValues($objLinkForm, $RelStatementId){
	
	global $SetId;
	global $DocId;
	global $Mode;
	
	$ThisMode = $Mode;
		
	foreach ($objLinkForm->FormFields as $FieldNum=>$arrFields){
		foreach ($arrFields as $occ=>$objFormField){

			$FieldStatId = null;
			$FieldMode = 'new';
			
			switch ($ThisMode){
				case 'edit':
					if (!is_null($objFormField->Statement)){
						$FieldStatId = $objFormField->Statement->Id;
						$FieldMode = 'edit';
					}					
					break;
			}
			
			if (!is_null($objFormField->Value)){
				$FieldStatId = dataStatUpdate($FieldMode, $FieldStatId , $SetId, $DocId, 200, $objFormField->Property->DictId, $objFormField->Property->Id, null, null, $objFormField->Value, null, null, $RelStatementId);
			}
		}
	}
		
	return true;

}


?>