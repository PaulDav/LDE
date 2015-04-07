<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'classviz');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	
	try {
	
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$Dicts = new clsDicts();
		
		$GroupId = null;
		$DictId = null;
		$ClassId = null;
		
		$VizTypeId = null;
		$Params = array();
	
		if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
			throw new exception("dictid not specified");
		}	
		$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
		
		if (!isset($_SESSION['forms'][PAGE_NAME]['classid'])){
			throw new exception("classid not specified");
		}
		$ClassId = $_SESSION['forms'][PAGE_NAME]['classid'];			
		
		if (!($objClass = $Dicts->getClass($DictId, $ClassId))){
			throw new exception("Unknown Class");
		}
			
		$objDict = $Dicts->Dictionaries[$DictId];
		
		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Class");
		}	
		
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'edit':
				
				if (!isset($_SESSION['forms'][PAGE_NAME]['viztypeid'])){
					throw new exception("viztypeid not specified");
				}
				$VizTypeId = $_SESSION['forms'][PAGE_NAME]['viztypeid'];			

				if (!isset($System->Config->Visualizers[$VizTypeId])){
					throw new exception("unknown visualiation type");
				}
				$objVizType = $System->Config->Visualizers[$VizTypeId];
				
				$ClassProperties = $Dicts->ClassProperties($objClass->DictId, $objClass->Id);
				
				
				$ParamNum = 0;
				foreach ($objVizType->Params as $objVizTypeParam){
					$ParamNum = $ParamNum + 1;
					$FieldName = "param$ParamNum";
					if (isset($_SESSION['forms'][PAGE_NAME][$FieldName])){
						$ClassPropNum = $_SESSION['forms'][PAGE_NAME][$FieldName];						
						if (isset($ClassProperties[$ClassPropNum])){
							$Params[$ParamNum]['propdictid'] = $ClassProperties[$ClassPropNum]->PropDictId;
							$Params[$ParamNum]['propid'] = $ClassProperties[$ClassPropNum]->PropId;
						}
					}			
				}
				
				break;				
				
			case 'delete':
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}

		

		switch ( $Mode ){
			case "edit":
				dataClassVizUpdate($DictId, $ClassId, $VizTypeId, $Params);
				break;
			case "delete":
				dataClassVizRemove($DictId, $ClassId);
				break;
		}

		$ReturnUrl = "class.php?dictid=$DictId&classid=$ClassId";
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>