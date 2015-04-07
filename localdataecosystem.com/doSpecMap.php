<?php
	require_once('function/utils.inc');
	require_once('data/dataProfile.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');

	require_once('class/clsDict.php');
	require_once('class/clsProfile.php');
	require_once('class/clsData.php');
		
	define('PAGE_NAME', 'specmap');
	
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
		
		$RelId = null;
		$Seq = 0;
		
		$objForm = null;
		$objLinkForm = null;
				
		if (isset($_SESSION['forms'][PAGE_NAME]['specid'])){			
			$SpecId = $_SESSION['forms'][PAGE_NAME]['specid'];
			
			
			
			if (!isset($Specs->Items[$SpecId])){
				throw new exception("Unknown Specification");
			}
			$objSpec = $Specs->Items[$SpecId];
			if (!($objSpec->canEdit)){
				throw new exception("You cannot update this Specification");
			}
			
			$ProfileId = $objSpec->ProfileId;
		}
		
		if (is_null($SpecId)){
			throw new exception ("Spec Id not specified");
		}
		
		
		if (is_null($ProfileId)){
			throw new exception ("Profile not specified");
		}
		
		$objProfile = $Profiles->Items[$ProfileId];

		
		if (isset($_SESSION['forms'][PAGE_NAME]['relid'])){
			$RelId = $_SESSION['forms'][PAGE_NAME]['relid'];
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['seq'])){
			$Seq = $_SESSION['forms'][PAGE_NAME]['seq'];			
		}
		
		
		$Dicts = new clsDicts();

		if (!is_null($RelId)){
			$objProfileRel = $objProfile->Relationships[$RelId];
			$objRel = $Dicts->Dictionaries[$objProfileRel->DictId]->Relationships[$objProfileRel->RelId];
		}		
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		
		$objDocForm = new clsForm($ProfileId);
		$objForm = $objDocForm;
		
		if (!is_null($RelId)){
			if (!isset($objDocForm->LinkForms[$RelId][$Seq])){
				throw new exception("Unknown Link Form");
			}
			$objLinkForm = $objDocForm->LinkForms[$RelId][$Seq];
			$objForm = $objLinkForm->ObjectForm;			
		}

		
		switch ($Mode){
			case 'edit':
				break;
			default:
				throw new exception("Invalid Mode");
				break;				
		}
		
		switch ( $Mode ){
			case "edit":
				updateSpecFields($objForm);
				$objSpec->Specs->Save();
				break;
		}

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: spec.php?specid=$SpecId");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}
	

function updateSpecFields($objForm){
	
	global $objSpec;
	
	foreach ($objForm->FormFields as $FieldNum=>$arrFields){
		foreach ($arrFields as $occ=>$objFormField){
			
			dataSpecMapRemoveField($objSpec, $objFormField);
			
			$ColNo = null;
			$Default = null;
			$TransId = null;
			
			if (isset($_SESSION['forms'][PAGE_NAME][$objFormField->FieldName.'_colnum'])){
				$ColNo = $_SESSION['forms'][PAGE_NAME][$objFormField->FieldName.'_colnum'];
			}
			if (isset($_SESSION['forms'][PAGE_NAME][$objFormField->FieldName.'_default'])){
				$Default = $_SESSION['forms'][PAGE_NAME][$objFormField->FieldName.'_default'];
			}
			if (isset($_SESSION['forms'][PAGE_NAME][$objFormField->FieldName.'_translation'])){
				$TransId = $_SESSION['forms'][PAGE_NAME][$objFormField->FieldName.'_translation'];
			}

			dataSpecMapSetField($objSpec, $objFormField ,$ColNo, $Default, $TransId);
						
		}
	}
	
	if (get_class($objForm) == "clsForm"){
		foreach ($objForm->LinkForms as $ProfileRelId=>$arrLinks){
			foreach ($arrLinks as $seq=>$objLinkForm){
				updateSpecFields($objLinkForm->ObjectForm);
			}
		}
	}

}



?>