<?php



function frmShapeProperties($objParent,$HasProperties ){

	global $Dicts;
	global $System;
		
	$Content = '';
	
	$Content .= "<table>";
	$Content .= "<tr><th>Properties</th><td>";	
	
	$Content .= "<table>";
	
	$Content .= "<tr><th/><th>Selected?</th><th>Cardinality</th><th>Filters</th></tr>";
	
	$PropNum = 0;
	foreach ($HasProperties as $objHasProperty){
		
		$objShapeProp = null;
		if (!is_null($objParent)){
			foreach ($objParent->ShapeProperties as $optShapeProperty){
				if ($optShapeProperty->Property->DictId == $objHasProperty->PropDictId){
					if ($optShapeProperty->Property->Id == $objHasProperty->PropId){
						$objShapeProp = $optShapeProperty;
						continue;
					}
					
				}
			}
		}

		$PropNum = $PropNum + 1;
		$objProp = $Dicts->getProperty($objHasProperty->PropDictId,$objHasProperty->PropId);
		$Content .= "<tr>";
		$Content .= "<td>".$objProp->Label."</td>";
		$Content .= "<td>";
		$FieldName = "prop_".$PropNum."_sel";
		$Content .= "<input type='checkbox' name='$FieldName' value='selected' ";

		$FieldSelected = false;		
		
		if (!is_null($objShapeProp)){
			if ($objShapeProp->Selected === true){
				$Content .= " checked='checked' ";
			}
		}
								
		$Content .= "/>";
		$Content .= "</td>";
		
		$Content .= '<td>';
		
		
		if (isset($System->Config->SubCardinalities[$objHasProperty->Cardinality])){		
			$FieldName = "prop_".$PropNum."_cardinality";		
			$Content .= "<select name='$FieldName'>";
			foreach ($System->Config->SubCardinalities[$objHasProperty->Cardinality] as $optCardinality){
				$Content .= "<option";
				
				if (!is_null($objShapeProp)){
					if ($objShapeProp->Cardinality == $optCardinality){
						$Content .= " selected='true' ";
					}
				}
				
				$Content .= ">$optCardinality</option>";
			}
			$Content .= "</select>";
		}
		$Content .= '</td>';
		
		
		$Content .= "<td>";
		
		if ($objProp->Type == 'simple'){
		
			$EmptyFilter = false;
			
			$FilterNum = 0;
			if (!is_null($objShapeProp)){
				foreach ($objShapeProp->Filters as $objFilter){
					
					if (is_null($objFilter->Type)){
						if ($EmptyFilter){
							continue;
						}
					}
					
					$FilterNum = $FilterNum + 1;
					$FieldName = "prop_".$PropNum."_filter_".$FilterNum;
					$FilterType = $objFilter->Type;
					$FilterValue = $objFilter->Value;
								
					$Content .= "<select name='".$FieldName."_type'>";
					$Content .= "<option/>";
					foreach ($System->Config->FilterTypes as $optFilterType){
						$Content .= "<option";
						if ($FilterType == $optFilterType){
							$Content .= " selected='true' ";
						}
						$Content .= ">$optFilterType</option>";
					}				
					$Content .= "</select>";
	
				
					switch ($objProp->Field->DataType){
						case "date":
							$Content .= "<input type='date'  name='".$FieldName."_value' class='datepicker' id='".$FieldName."' size='10'";
							$Content .= " value='$FilterValue'>";
							$Content .= "</input>";								
							
							break;
					
						case "value":
							$Content .= "<select name='".$FieldName."_value' >";
							$Content .= "<option/>";
							foreach ($objHasProperty->Lists as $objHasPropList){
								$objList = $Dicts->Dictionaries[$objHasPropList->ListDictId]->Lists[$objHasPropList->ListId];
								foreach ($objList->Values as $objListValue){
									$optValue = $Dicts->Dictionaries[$objListValue->ValueDictId]->Values[$objListValue->ValueId];
									$Content .= "<option";
									
									if ( $optValue->Label  == $FilterValue ){
										$Content .= " selected='true' ";
									}						
									$Content .= ">".$optValue->Label."</option>";
								}
							}
							$Content .= "</select>";
							
							break;
													
						default: //line							
							$Content .= "<input  name='".$FieldName."_value' type='text'";
							if (!is_null($objProp->Field->Length)){
								$Content .= " size='".$objProp->Field->Length."' ";
							}
							$Content .= " maxlength='254' ";
							
							$Content .= " value='$FilterValue' >";
							$Content .= "</input>";								
							break;								
					}
							
					$Content .= "<br/>";
					
					if (is_null($objFilter->Type)){
						$EmptyFilter = true;
					}
					
				}
					
	
			}
	
			if (!$EmptyFilter){
				$FilterNum = $FilterNum + 1;
				$Content .= "<input type='submit' value='+' name='prop_".$PropNum."_filter_add'/>";
			}
		}

		$Content .= "</td>";
		$Content .= "</tr>";	

		
		if ($objProp->Type == 'complex'){
			$Content .= frmShapeComplexProperties($objProp, $objShapeProp, "prop_".$PropNum);
		}
		
	}


	$Content .= "</table>";
	
	$Content .= "</td></tr></table>";

	return $Content;

}


function frmShapeComplexProperties($objComplexProp, $objParentShapeProperty = null, $ComplexFieldName = null, $Level = 1 ){

	global $Dicts;
	global $System;
	
	$Content = '';
		
	$GroupNum = 0;
	foreach ($objComplexProp->ElementGroups as $objElementGroup){

		$GroupNum = $GroupNum + 1;

		$PropNum = 0;
		
		foreach ($objElementGroup->Elements as $objElement){
			$objProp = $Dicts->getProperty($objElement->DictId, $objElement->PropId);
			if (is_object($objProp)){

				$objShapeProp = null;
				if (!is_null($objParentShapeProperty)){
					foreach ($objParentShapeProperty->ShapeProperties as $optShapeProperty){
						if ($optShapeProperty->Property->DictId == $objProp->DictId){
							if ($optShapeProperty->Property->Id == $objProp->Id){
								$objShapeProp = $optShapeProperty;
								continue;
							}							
						}
					}
				}
								
				
				$PropNum = $PropNum + 1;
				$Content .= "<tr>";
				$Padding = ($Level * 30).'px';
				$Content .= "<td style='padding-left:$Padding'>".$objProp->Label."</td>";

				$Content .= "<td>";
				$FieldName = $ComplexFieldName."_group_".$GroupNum."_prop_".$PropNum."_sel";
				$Content .= "<input type='checkbox' name='$FieldName' value='selected' ";

				$FieldSelected = false;		
				if (!is_null($objShapeProp)){
					if ($objShapeProp->Selected === true){
						$Content .= " checked='checked' ";
					}
				}
				$Content .= "/>";

				$Content .= "</td>";

				$Content .= '<td>';
				
				
				if (isset($System->Config->SubCardinalities[$objElement->Cardinality])){		
					$FieldName = $ComplexFieldName."_group_".$GroupNum."_prop_".$PropNum."_cardinality";		
					$Content .= "<select name='$FieldName'>";
					foreach ($System->Config->SubCardinalities[$objElement->Cardinality] as $optCardinality){
						$Content .= "<option";
						
						if (!is_null($objShapeProp)){
							if ($objShapeProp->Cardinality == $optCardinality){
								$Content .= " selected='true' ";
							}
						}
						
						$Content .= ">$optCardinality</option>";
					}
					$Content .= "</select>";
				}


				$Content .= '</td>';
				
				$Content .= "</tr>";

			}

			if ($objProp->Type == 'complex'){
				$Content .= frmShapeComplexProperties($objProp, $objShapeProp, $ComplexFieldName."_group_".$GroupNum."_prop_".$PropNum, $Level + 1);
			}
		}
		
	}

	return $Content;

}


?>