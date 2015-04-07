<?php
	require_once('function/utils.inc');
	require_once('data/dataView.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsView.php');
	require_once('class/clsDict.php');
	
	define('PAGE_NAME', 'viewselection');
	
	session_start();
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);
		
	if (!$System->LoggedOn){
		throw new exception("You must be logged on");
	}
	
	$GroupId = null;
	$ViewId = null;
	$SelSeq = null;
	$Action = 'updateselection';

	$ClassDictId = null;
	$ClassId = null;
	

	$objView = null;
	$objGroup = null;
	$objClass = null;

	try {
	
		$Dicts = new clsDicts();
		
		if (!isset($_SESSION['forms'][PAGE_NAME]['viewid'])){
			throw new exception("View not specified");
		}
			
			
		$ViewId = $_SESSION['forms'][PAGE_NAME]['viewid'];			
		$objView = new clsView($ViewId);
		if (!($objView->canEdit)){
			throw new exception("You cannot update this View");
		}
		$objGroup = $objView->GroupId;
		
		foreach ($_SESSION['forms'][PAGE_NAME] as $key=>$val){
			$keyparts = explode('_',$key);
			
			if (in_array('addfilter',$keyparts)){
				$Action = 'addfilter';				
			}
			if (in_array('addlink',$keyparts)){
				$Action = 'addlink';				
			}			
		}
		

		if (isset($_SESSION['forms'][PAGE_NAME]['selseq'])){
			$SelSeq = $_SESSION['forms'][PAGE_NAME]['selseq'];						
		}
		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['classdictid'])){
			$ClassDictId = $_SESSION['forms'][PAGE_NAME]['classdictid'];						
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['classid'])){
			$ClassId = $_SESSION['forms'][PAGE_NAME]['classid'];						
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['action'])){		
			$Action = $_SESSION['forms'][PAGE_NAME]['action'];
		}
		
		$Fields = $_SESSION['forms'][PAGE_NAME];

		switch ($Action) {
			case 'addselection':				

				if ( is_null($ClassId)){
					throw new exception("ClassId not specified");
				}				
				
				if (!is_null($ClassId)){
					if (is_null($ClassDictId)){
						throw new exception("ClassDictId not specified");
					}
					
					if (!isset($Dicts->Dictionaries[$ClassDictId])){
						throw new exception("Unknown ClassDict");
						
					}
		
					if (!isset($Dicts->Dictionaries[$ClassDictId]->Classes[$ClassId])){
						throw new exception("Unknown Class");
					}
					$objClass = $Dicts->Dictionaries[$ClassDictId]->Classes[$ClassId];
				}
				
				dataViewAddSelection($ViewId , $objClass->DictId, $objClass->Id);
				break;
			case 'addfilter':
			case 'addlink':
				
				if (is_null($SelSeq)){
					throw new Exception("Sel Seq not specified");
				}
				
	    		header("Location: viewselection.php?viewid=$ViewId&selseq=$SelSeq");
    			exit;
							
			case 'updateselection':
								
				if (is_null($SelSeq)){
					throw new Exception("Sel Seq not specified");
				}
				if (!isset($objView->Selections[$SelSeq])){
					throw new exception("Unknown Selection");
				}
				$objSel = $objView->Selections[$SelSeq];

				
				$objViewClass = $objSel->ViewClass;
				
				funUpdateSelection($objViewClass);				
				
								
				$objView->Save();
				
				break;
				
			default:
				throw new exception("Invalid Action");
		}

		

		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: view.php?viewid=$ViewId#selections");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}
	
	
	
function funUpdateSelection($objViewClass, $FieldNamePrefix = ""){
	
	global $Dicts;
	global $Fields;
	
	dataViewClassRemoveProperties($objViewClass);	
	
	$arrProps = array();
	$arrLinks = array();

	$PropNum = 0;
	foreach ($Dicts->ClassProperties($objViewClass->Class->DictId, $objViewClass->Class->Id) as $objClassProperty){
		$PropNum = $PropNum + 1;					
		$arrProps[$PropNum]['objClassProp'] = $objClassProperty;
		$arrProps[$PropNum]['objProp'] = $Dicts->Dictionaries[$objClassProperty->PropDictId]->Properties[$objClassProperty->PropId];
	}
	
	foreach ($Fields as $key=>$val){
		
		if (!IsEmptyString($FieldNamePrefix)){
			if (!(0 === strpos($key, $FieldNamePrefix))){
				continue;
			}
			$key = str_replace($FieldNamePrefix,'',$key);
		}
				
		$keyparts = explode('_',$key);
		switch ($keyparts[0]){
			case 'prop':
				
				if (isset($keyparts[1])){
					if (is_numeric($keyparts[1])){
						$PropNum = $keyparts[1];
						if (isset($keyparts[2])){
							switch ($keyparts[2]){
								case "sel":
									if (isset($arrProps[$PropNum] )){
										$arrProps[$PropNum]['sel'] = true;
									}											
									break;
								case "filter":
									if (isset($keyparts[3])){
										if (is_numeric($keyparts[3])){
											$FilterNum = $keyparts[3];
											if (isset($keyparts[4])){
												switch ($keyparts[4]){
													case 'type':
														$arrProps[$PropNum]['filters'][$FilterNum]['type'] = $val;
														break;
													case 'value':
														$arrProps[$PropNum]['filters'][$FilterNum]['value'] = $val;
														break;																
												}
											}
										}
									}
									break;
									
							}
						}
					}
					
				}
				break;
			case 'link':
				if (isset($keyparts[1])){
					if (is_numeric($keyparts[1])){
						$LinkNum = $keyparts[1];
						if (isset($keyparts[2])){
							switch ($keyparts[2]){
								case "reldictid":
									$arrLinks[$LinkNum]['reldictid'] = $val;
									break;
								case "relid":
									$arrLinks[$LinkNum]['relid'] = $val;
									break;
								case "classdictid":
									$arrLinks[$LinkNum]['classdictid'] = $val;
									break;
								case "classid":
									$arrLinks[$LinkNum]['classid'] = $val;
									break;
							}
						}
					}
				}
				break;
				
		}
	}
	
	foreach ($arrProps as $PropNum=>$arrProp){
		if (isset($arrProp['sel'])){
			if ($arrProp['sel'] === true){
				dataViewClassSetProperty($objViewClass, $arrProp['objClassProp'], true);
			}
		}
		
		
		if (isset($arrProp['filters'])){
			foreach ($arrProp['filters'] as $arrFilter){
				
				$FilterType = "";
				$FilterValue = "";							
				if (isset($arrFilter['type'])){
					$FilterType = $arrFilter['type'];
				}
				if (isset($arrFilter['value'])){
					$FilterValue = $arrFilter['value'];
				}
					
				dataViewClassSetFilter($objViewClass, $arrProp['objClassProp'], $FilterType, $FilterValue);
			}
		}				

	}
	
	foreach ($arrLinks as $LinkNum=>$arrLink){

		if (!isset($arrLink['reldictid'])){
			continue;
		}
		if (!isset($arrLink['relid'])){
			continue;
		}
		if (!isset($arrLink['classdictid'])){
			continue;
		}
		if (!isset($arrLink['classid'])){
			continue;
		}
		
		$objLinkRel = $Dicts->Dictionaries[$arrLink['reldictid']]->Relationships[$arrLink['relid']];
		$objLinkObject = $Dicts->Dictionaries[$arrLink['classdictid']]->Classes[$arrLink['classid']];
		
		$objViewLink = dataViewClassSetLink($objViewClass, $objLinkRel, $objLinkObject);
		
		if (is_object($objViewLink)){
			$NextFieldName = $FieldNamePrefix.'link_'.$LinkNum.'_';		
			funUpdateSelection($objViewLink->ViewObject, $NextFieldName);
		}
				
	}
	

	return;
}

?>