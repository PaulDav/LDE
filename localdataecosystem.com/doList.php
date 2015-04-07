<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');

	define('PAGE_NAME', 'list');

	session_start();
	$System = new clsSystem();

	SaveUserInput(PAGE_NAME);

	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = '';
	$DictId = '';
	$ListId = null;
	
	$Dicts = new clsDicts();

	if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
		throw new exception("dictid not specified");
	}

	$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
	if (isset($_SESSION['forms'][PAGE_NAME]['listid'])){
		$ListId = $_SESSION['forms'][PAGE_NAME]['listid'];
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];

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
				if (empty($ListId)){
					throw new exception("listid not specified");
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
				
				$Label = "";
				$Description = '';
				$Source = '';
				$DescribedAt = '';
				
				if (isset($_SESSION['forms'][PAGE_NAME]['label'])){
					$Label = $_SESSION['forms'][PAGE_NAME]['label'];			
				}
				if ( $Label==''){
					throw new exception("Label not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['source'])){
					$Source = $_SESSION['forms'][PAGE_NAME]['source'];			
				}
				
				if (isset($_SESSION['forms'][PAGE_NAME]['describedat'])){
					$DescribedAt = $_SESSION['forms'][PAGE_NAME]['describedat'];			
				}
				
				
				break;
		}
		

		switch ( $Mode ){
			case "new":
			case "edit":
				$ListId = dataListUpdate($Mode, $ListId , $DictId, $Label, $Description, $Source, $DescribedAt);
				break;
			case "delete":
				dataListDelete($ListId, $DictId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "dict.php?dictid=$DictId";
				break;
			default:
				$ReturnUrl = "list.php?dictid=$DictId&listid=$ListId";
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