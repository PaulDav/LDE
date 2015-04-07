<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'dict');
	
	session_start();
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = null;
	$DictId = null;

	if (isset($_SESSION['forms'][PAGE_NAME]['groupid'])){
		$GroupId = $_SESSION['forms'][PAGE_NAME]['groupid'];
		$objGroup = new clsGroup($GroupId);
		if (!($objGroup->canEdit)){
			throw new exception("You cannot update this Group");
		}		
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
		$objDict = new clsDict($DictId);
		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
		}
	}
	
	try {
			
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				if ( $GroupId == ''){
					throw new exception("Group not specified");
				}
				
				break;
			case 'edit':
			case 'delete':
				
				if ( is_null($DictId)){
					throw new exception("Dict not specified");
				}
				
				if (is_null($GroupId)){
					$GroupId = $objDict->GroupId;
				}
				
					break;
			default:
				throw new exception("Invalid Mode");
		}

		switch ($Mode) {
			case 'new':
			case 'edit':
				
				$Name = "";
				if (isset($_SESSION['forms'][PAGE_NAME]['name'])){
					$Name = $_SESSION['forms'][PAGE_NAME]['name'];			
				}
				if ( $Name==''){
					throw new exception("Name not specified");
				}
				
				$Description = "";
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		
				
				$Publish = false;
				if (isset($_SESSION['forms'][PAGE_NAME]['publish'])){
					if ($_SESSION['forms'][PAGE_NAME]['publish'] == "Yes"){
						$Publish = true;
					}
				}		
				break;
		}

		$ReturnUrl = '.';
		
		switch ( $Mode ){
			case 'new':
			case 'edit':
				if ( is_null($DictId)){
					$DictId = clean(strtolower($Name));
				}
				$DictId = dataDictUpdate($Mode, $DictId , $GroupId, $Name, $Description, $Publish);
				$ReturnUrl = "dict.php?dictid=$DictId";
				break;
			case "delete":
				dataDictDelete($DictId);
				$ReturnUrl = "group.php?groupid=$GroupId";
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