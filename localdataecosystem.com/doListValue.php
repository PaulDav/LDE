<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'listvalue');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	$DictId = '';
	$ListId = '';
	$ValueId = '';
	$ValueDictId = '';
	$ListPropId = '';
	
	$Label = '';
	$Description = '';		
	$URI = '';
	$Code = '';
	
	$Dicts = new clsDicts();
	
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}
	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($_SESSION['forms'][PAGE_NAME]['listid'])){
		throw new exception("listid not specified");
	}
		
	$ListId = $_SESSION['forms'][PAGE_NAME]['listid'];
	$objList = $objDict->Lists[$ListId];

	if (isset($_SESSION['forms'][PAGE_NAME]['valueid'])){
		$ValueId = $_SESSION['forms'][PAGE_NAME]['valueid'];
		$VaueDictId = $DictId;
	}
		
	if (isset($_SESSION['forms'][PAGE_NAME]['listvalueid'])){
		$ListValueId = $_SESSION['forms'][PAGE_NAME]['listvalueid'];
		$objListValue = $objList->Values[$ListValueId];
		if ($ValueId == ""){
			$ValueId = $objListValue->ValueId;
			$ValueDictId = $objListValue->ValueDictId;
		}
	}


	if (isset($_SESSION['forms'][PAGE_NAME]['label'])){
		$Label = $_SESSION['forms'][PAGE_NAME]['label'];
	}
	if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
		$Description = $_SESSION['forms'][PAGE_NAME]['description'];
	}
	if (isset($_SESSION['forms'][PAGE_NAME]['code'])){
		$Code = $_SESSION['forms'][PAGE_NAME]['code'];
	}
	if (isset($_SESSION['forms'][PAGE_NAME]['uri'])){
		$URI = $_SESSION['forms'][PAGE_NAME]['uri'];
	}
	
	try {

		
		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
		}
		
		
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				break;
			case 'edit':
			case 'delete':
				if ( $ListValueId == ''){
					throw new exception("valuelistid not specified");
				}
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}

		
		switch ($Mode){
			case "delete":
				break;
			default:				
				break;
		}
		

		switch ( $Mode ){
			case "new":
				$ValueId = dataValueUpdate($Mode, null , $DictId, $Label, $Description, $Code, $URI);				
				$ValueDictId = $DictId;
				$ListValueId = dataListValueUpdate($Mode, null , $DictId, $ListId, $ValueDictId, $ValueId);
				break;
			case "edit":
				$ValueId = dataValueUpdate($Mode, $ValueId, $DictId, $Label, $Description, $Code, $URI);
				break;
			case "delete":
				dataListValueDelete($DictId, $ListId, $ListValueId);
				break;
		}

//		$ReturnUrl = "listvalue.php?dictid=$DictId&listid=$ListId&listvalueid=$ListValueId";
		$ReturnUrl = "list.php?dictid=$DictId&listid=$ListId#values";
		
		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "list.php?dictid=$DictId&listid=$ListId#values";
				break;
		}
				
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>