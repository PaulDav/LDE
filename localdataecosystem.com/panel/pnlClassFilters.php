<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlClassFilters( $DictId, $ClassId, $ParentId = 'filter'){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objClass = $Dicts->getClass($DictId, $ClassId);
	if (!is_object($objClass)){
		return;
	}

	$Content = '';
	$Content .= '<table class="sdbluebox"><tbody>';
	
	$PropNum = -1;
		
	foreach ($Dicts->ClassProperties($DictId, $ClassId) as $objClassProperty){
		$PropNum = $PropNum + 1;
		
		$objProperty = $Dicts->getProperty($objClassProperty->PropDictId, $objClassProperty->PropId );
		if (!is_object($objProperty)){
			continue;
		}
		
		$FieldId = $ParentId.'_prop_'.$PropNum;		
		$Content .= pnlPropertyFilters( $objProperty, $FieldId );
		
	}
	
	
	$Content .= '</tbody></table>';

	// extended Classes
	
	$RelNum = -1;	
	foreach ($Dicts->RelationshipsFor($DictId, $ClassId) as $objRel){
		$RelNum = $RelNum + 1;
		
		if ($objRel->Extending == true){
			$objExtendedClass = $Dicts->getClass($objRel->ObjectDictId, $objRel->ObjectId);			
			$Content .= '<h4>'.$objRel->Label .' '.$objExtendedClass->Label.'</h4>';
			
			$Content .= pnlClassFilters( $objRel->ObjectDictId, $objRel->ObjectId, $ParentId.'_rel_'.$RelNum);
			
		}
	}
	
	
    return $Content;
}

Function pnlPropertyFilters( $objProperty, $FieldId ){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}

	$Content = '';
	$Content .= "<tr>";
	$Content .= "<th>".$objProperty->Label."</th>";
	
	$Content .= "<td>";
		
	switch ($objProperty->Type){
		case 'complex':
			
			$Content .= '<table class="sdbluebox"><tbody>';
	
			$PropNum = -1;
		
			foreach ($objProperty->ElementGroups as $objElementGroup){
				foreach ($objElementGroup->Elements as $objElement){
					$PropNum = $PropNum + 1;
				
					$objComplexProperty = $Dicts->getProperty($objElement->DictId, $objElement->PropId );
					if (!is_object($objComplexProperty)){
						continue;
					}

					$ComplexFieldId = $FieldId.'_prop_'.$PropNum;
					$Content .= pnlPropertyFilters( $objComplexProperty, $ComplexFieldId );
				}				
			}

			$Content .= '</tbody></table>';
			
			
			break;
		default:
			
			$DataType = null;
			if (!is_null($objProperty->Field)){
				switch ($objProperty->Field->DataType){
					case 'line':
					case 'text':
						$Content .= " contains ";
						$Content .= "<input id='$FieldId'/>";
						break;
					case 'date':
						$Content .= " is ";
						$Content .= "<input type='date' class='datepicker' id='$FieldId' onChange='validateDate(this)', size='10'/>";
						break;
					case 'number':
						$Content .= " is ";
						$Content .= "<input id='$FieldId' align='right'/>";
						break;
					case 'value':
						$Content .= " is ";
						$Content .= "<select id='$FieldId'/>";
						$Content .= "<option/>";
						if (is_array($objProperty->Lists)){
							foreach ($objProperty->Lists as $objPropertyList){
								$objList = $Dicts->getList($objPropertyList->ListDictId,$objPropertyList->ListId);
								if (is_object($objList)){
									if (is_array($objList->Values)){
										foreach ($objList->Values as $objListValue){
											$objList = $Dicts->getValue($objListValue->ValueDictId,$objListValue->ValueId);
											if (is_object($objList)){
												$Content .= "<option>";
												$Content .= $objList->Label;
												$Content .= "</option>";
											}
										}
									}
								}
							}
							
						}					
						
						$Content .= "</select>";
						break;
						
				}
			}
	}

	$Content .= "</td>";		
	$Content .= "</tr>";

    return $Content;
}

?>