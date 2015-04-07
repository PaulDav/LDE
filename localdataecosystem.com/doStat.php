<?php
	require_once('function/utils.inc');
	require_once('data/dataData.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');

	require_once('class/clsDict.php');
	require_once('class/clsData.php');
	
	
	define('PAGE_NAME', 'stat');
	
	session_start();
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = null;
	$SetId = null;
	$StatId = null;
	$TypeId = null;
	$SubjectId = null;
	$ObjectId = null;
	$LinkDictId = null;
	$LinkId = null;
	$Value = null;
	
	$objLinkDict = null;
	

	if (isset($_SESSION['forms'][PAGE_NAME]['statid'])){
		$StatId = $_SESSION['forms'][PAGE_NAME]['statid'];			
		$objStat = new clsStat($StatId);
		if (!($objStat->canEdit)){
			throw new exception("You cannot update this Statement");
		}
		$TypeId = $objStat->TypeId;
		$SubjectId = $objStat->SubjectId;
		$ObjectId = $objStat->ObjectId;
		$LinkDictId = $objStat->LinkDictId;
		$LinkId = $objStat->LinkId;
		$Value = $objStat->Value;		
	}
		
	if (isset($_SESSION['forms'][PAGE_NAME]['setid'])){
		$SetId = $_SESSION['forms'][PAGE_NAME]['setid'];			
		$objSet = new clsSet($SetId);
		if (!($objSet->canEdit)){
			throw new exception("You cannot update this Set");
		}
	}

	
	if (isset($_SESSION['forms'][PAGE_NAME]['typeid'])){
		$TypeId = $_SESSION['forms'][PAGE_NAME]['typeid'];			
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['subjectid'])){
		$SubjectId = $_SESSION['forms'][PAGE_NAME]['subjectid'];			
	}
	if (isset($_SESSION['forms'][PAGE_NAME]['objectid'])){
		$ObjectId = $_SESSION['forms'][PAGE_NAME]['objectid'];			
	}
	if (isset($_SESSION['forms'][PAGE_NAME]['linkdictid'])){
		$LinkDictId = $_SESSION['forms'][PAGE_NAME]['linkdictid'];			
	}
	if (isset($_SESSION['forms'][PAGE_NAME]['linkid'])){
		$LinkId = $_SESSION['forms'][PAGE_NAME]['linkid'];			
	}

	if (isset($_SESSION['forms'][PAGE_NAME]['value'])){
		$Value = $_SESSION['forms'][PAGE_NAME]['value'];			
	}
	
		
	if (!isset($System->Config->StatementTypes[$TypeId])){
		throw new exception("Invalid Statement Type");
	}
	
	try {
			
		$Dicts = new clsDicts();
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				if ( IsEmptyString($SetId)){
					throw new exception("Set not specified");
				}

				switch ($TypeId){
					case 100:
					case 200:
						if (IsEmptyString($SubjectId)){
							throw new Exception("Subject not specified");
						}
						
						if (IsEmptyString($LinkDictId)){
							throw new Exception("Link Dict not specified");
						}
						if (IsEmptyString($LinkId)){
							throw new Exception("Link not specified");
						}
					case 300:
						if (IsEmptyString($ObjectId)){
							throw new Exception("Object not specified");
						}
						break;
				}
				
				break;
			case 'edit':
				
				if ( IsEmptyString($StatId == '')){
					throw new exception("Statement not specified");
				}
				
				break;
			default:
				throw new exception("Invalid Mode");
		}

		if (!IsEmptyString($LinkDictId)){
			if (!isset($Dicts->Dictionaries[$LinkDictId])){
				throw new Exception("Invalid Link Dictionary");
			}
			$objLinkDict = $Dicts->Dictionaries[$LinkDictId];
		}

		if (!IsEmptyString($LinkId)){
			if (is_null($objLinkDict)){
				throw new Exception("Link Dictionary not specified");
			}
			switch ($TypeId){
				case 100:
					if (!isset($objLinkDict->Classes[$LinkId])){
						throw new Exception("Unknown Link");
					}
					break;
				case 200:
					if (!isset($objLinkDict->Properties[$LinkId])){
						throw new Exception("Unknown Property");
					}
					break;
				case 300:
					if (!isset($objLinkDict->Relationships[$LinkId])){
						throw new Exception("Unknown Relationship");
					}
					break;
					
				default:
					throw new Exception("Unknown Link");
					break;					
			}
		}
		

		switch ( $Mode ){
			case "new":
			case "edit":				
				$StatId = dataStatUpdate($Mode, $StatId , $SetId, null, $TypeId, $LinkDictId, $LinkId,  $SubjectId, $ObjectId, $Value);
				break;
		}

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: statement.php?statid=$StatId");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>