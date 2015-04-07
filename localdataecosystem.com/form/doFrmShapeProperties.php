<?php

function doFrmShapeProperties($objShapeParent, $HasProperties=array(), $Fields=array()){

	global $Dicts;
	
	$arrProps = array();

	$PropNum = 0;
	
	foreach ($HasProperties as $objHasProperty){
		$PropNum = $PropNum + 1;
		$arrProps[$PropNum]['objHasProp'] = $objHasProperty;
		$arrProps[$PropNum]['objProp'] = $Dicts->getProperty($objHasProperty->PropDictId, $objHasProperty->PropId);
	}
	
	$objShape = $objShapeParent->Shape;
	$xmlParent = $objShapeParent->xml;
		
	doFrmShapeProperties1($Fields, $arrProps, $objShape, $xmlParent);
	
	return;

}
		

function doFrmComplexProperties($objShape, $arrParentProp, $GroupNum, $Fields=array()){

	global $Dicts;
	
	$arrProps = array();

	$objParentProp = $arrParentProp['objProp'];
	$xmlShapeParent = $arrParentProp['xml'];
	
	if (isset($objParentProp->ElementGroups[$GroupNum])){
		
		$ElementGroup = $objParentProp->ElementGroups[$GroupNum];
		
		$PropNum = 0;		
		foreach ($ElementGroup->Elements as $objElement){
			$PropNum = $PropNum + 1;					
			$arrProps[$PropNum]['objElement'] = $objElement;
			$arrProps[$PropNum]['objProp'] = $Dicts->getProperty($objElement->DictId, $objElement->PropId);
		}
		
		doFrmShapeProperties1($Fields, $arrProps, $objShape, $xmlShapeParent);
		
	}


	return;

}


function doFrmShapeProperties1($Fields, $arrProps, $objShape, $xmlShapeParent) {
	
	global $System;
	
	foreach ($Fields as $key=>$val){
							
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
										if ($keyparts[3] == 'add'){										
											$arrProps[$PropNum]['filters']['add'] = null;
										}
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
								
								case 'cardinality';
									if (isset($arrProps[$PropNum] )){
										$arrProps[$PropNum]['cardinality'] = $val;
									}											
									break;
								
								case 'group':
									
									if (isset($keyparts[3])){
										if (is_numeric($keyparts[3])){
											$ComplexGroupNum = $keyparts[3];
											
											$SubGroups = array();
											foreach ($Fields as $subFieldKey=>$subField){
												
												$subFieldKeyParts = explode('_',$subFieldKey);
												
												if (isset($subFieldKeyParts[0])){
													if ($subFieldKeyParts[0] == 'prop'){
														if (isset($subFieldKeyParts[1])){
															if ($subFieldKeyParts[1] == $PropNum){
																if (isset($subFieldKeyParts[2])){
																	if ($subFieldKeyParts[2] == 'group'){
																		if (isset($subFieldKeyParts[3])){
																			if ($ComplexGroupNum = $subFieldKeyParts[3]){
																				unset($subFieldKeyParts[0]);
																				unset($subFieldKeyParts[1]);
																				unset($subFieldKeyParts[2]);
																				unset($subFieldKeyParts[3]);
																			
																				$subFieldKey = implode('_',$subFieldKeyParts);
																				
																				$SubGroups[$ComplexGroupNum][$subFieldKey] = $subField;
																			}																			
																		}
																	}
																}
															}
														}
													}
												}
													
											}
											
											$arrProps[$PropNum]['complexgroups'] = $SubGroups;
											
										}
									}
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

			$Cardinality = null;
			if (isset($arrProp['cardinality'])){
				if (in_array($arrProp['cardinality'],$System->Config->Cardinalities)){
					$Cardinality = $arrProp['cardinality'];
				}
			}
			$xmlShapeProperty = dataShapeProperty($objShape, $xmlShapeParent, $arrProp['objProp'], $arrProp['sel'], $Cardinality );
			
			$arrProp['xml'] = $xmlShapeProperty;
					
			if (isset($arrProp['complexgroups'])){
				
				foreach ($arrProp['complexgroups'] as $GroupNum=>$GroupFields){					
					doFrmComplexProperties($objShape, $arrProp, $GroupNum, $GroupFields);
				}			
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
				dataShapeSetFilter($objShapeParent, $arrProp['objHasProp'], $FilterType, $FilterValue);
			}
		}				
			
	}
}


?>