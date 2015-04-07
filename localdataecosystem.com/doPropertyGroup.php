<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'propertygroup');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	$DictId = '';
	$PropId = '';
	$GroupSeq = '';
	
	$Dicts = new clsDicts();

	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}
	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];
	
	if (isset($_SESSION['forms'][PAGE_NAME]['propid'])){
		$PropId = $_SESSION['forms'][PAGE_NAME]['propid'];
	}

	if (isset($_SESSION['forms'][PAGE_NAME]['groupseq'])){
		$GroupSeq = $_SESSION['forms'][PAGE_NAME]['groupseq'];
		if (!is_numeric($GroupSeq)){
			throw new Exception('Invalid Element Group Sequence');
		}
	}
	

	$objDict = $Dicts->Dictionaries[$DictId];
	
	if ( $PropId == ''){
		throw new exception("propid not specified");
	}
	$objProp = $objDict->Properties[$PropId];

	try {

		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
		}
				
		$Mode = 'new';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				break;
			case 'delete':
				if ( $GroupSeq == ''){
					throw new exception("groupseq not specified");
				}
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}

		switch ( $Mode ){
			case 'new':
				dataPropGroupAdd($DictId, $PropId);
				break;
			case 'delete':
				dataPropGroupDelete($DictId, $PropId, $GroupSeq);
				break;
		}

		$ReturnUrl = "property.php?dictid=$DictId&propid=$PropId#elements";
		
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>