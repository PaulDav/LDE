<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'propertyelement');
	
	session_start();
	$System = new clsSystem();
	$Dicts = new clsDicts();
		
	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = null;
	$DictId = null;
	$PropId = null;
	$GroupSeq = null;
	$ElementDictId = null;
	$ElementPropId = null;
	
	$Cardinality = null;			
	

	try {	
	
		if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
			throw new exception("dictid not specified");
		}
		$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
		if (!isset($_SESSION['forms'][PAGE_NAME]['propid'])){
			throw new exception("propid not specified");
		}
		$PropId = $_SESSION['forms'][PAGE_NAME]['propid'];
		
		$objProp = $Dicts->getProperty($DictId, $PropId);
		if (!is_object($objProp)){
			throw new excpetion("Unknown Property");
		}
		$objDict = $Dicts->Dictionaries[$DictId];

		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
		}	

		if (!isset($_SESSION['forms'][PAGE_NAME]['groupseq'])){			
			throw new exception("groupseq not specified");
		}
		$GroupSeq = $_SESSION['forms'][PAGE_NAME]['groupseq']; 
		if (!isset($objProp->ElementGroups[$GroupSeq])){
			throw new exception("Unknown Group Seq");
		}
		$objElementGroup = $objProp->ElementGroups[$GroupSeq];
		
		
		if (!isset($_SESSION['forms'][PAGE_NAME]['elementdictid'])){
			throw new exception("elementdictid not specified");
		}
		$ElementDictId = $_SESSION['forms'][PAGE_NAME]['elementdictid'];			
		if (!isset($_SESSION['forms'][PAGE_NAME]['elementpropid'])){
			throw new exception("elementpropid not specified");
		}
		$ElementPropId = $_SESSION['forms'][PAGE_NAME]['elementpropid'];
		
		$objElementProperty = $Dicts->getProperty($ElementDictId, $ElementPropId);
		if (!is_object($objElementProperty)){
			throw new excpetion("Unknown Element Property");
		}
		$objElementDict = $Dicts->Dictionaries[$ElementDictId];

		if (!($objElementDict->canView)){
			throw new exception("You cannot view this Dictionary");
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
				
				$objPropertyElement = $objElementGroup->getElement($ElementDictId, $ElementPropId);
				if (!is_object($objPropertyElement)){
					throw new exception("Property Element not set for this Group");
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
				
				if (isset($_SESSION['forms'][PAGE_NAME]['cardinality'])){
					$Cardinality = $_SESSION['forms'][PAGE_NAME]['cardinality'];			
				}
				
				break;
		}
		
		switch ( $Mode ){
			case "new":
			case 'edit':
				dataPropElementUpdate($Mode, $DictId, $PropId, $GroupSeq, $ElementDictId, $ElementPropId, $Cardinality);
				break;
			case "delete":
				dataPropElementDelete($DictId, $PropId, $GroupSeq, $ElementDictId, $ElementPropId);
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