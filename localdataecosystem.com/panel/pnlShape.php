<?php

require_once(dirname(__FILE__).'/../class/clsShape.php');

require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlShape( $ShapeId){
	
	global $Shapes;
	if (!isset($Shapes)){
		$Shapes = new clsShapes();
	}

	if (!isset($Shapes->Items[$ShapeId])){		
		throw new exception("Unknown Shape");
	}
	
	$objShape = $Shapes->Items[$ShapeId];
	
	$User = new clsUser($objShape->OwnerId);
	
	$Content = '';

	$Content .= "<div  class='sdgreybox'>";
	$Content .= '<table>';
	if (!is_null($objShape->EcoSystem)){
		$Content .= "<tr><th>EcoSystem</th><td>".$objShape->EcoSystem."</td></tr>";
	}
	$Content .= "<tr><th>Id</th><td><a href='shape.php?shapeid=".$objShape->Id."'>".$objShape->Id."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objShape->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objShape->Description)."</td></tr>";

	/*
	if (!is_null($objShape->ParentId)){
		$Content .= "<tr><th>Parent Shape</th><td>";
		$Content .= pnlShape($objShape->ParentId);		
		$Content .= "</td></tr>";		
	}
	*/
	
	$Content .= "<tr><th>Owned by</th><td>";
	if (!is_null($User->PictureOf)) {
		$Content .= '<img height = "30" src="image.php?Id='.$User->PictureOf.'" /><br/>';
	}
	$Content .= $User->Name."</td></tr>";

	$Content .= "<tr><th>Publish?</th><td>";
	switch ($objShape->Publish){
		case true:
			$Content .= "Yes";
			break;
		default:
			$Content .= "No";
			break;			
	}
	$Content .= "</td></tr>";
	
    $Content .= '</table>';
    $Content .= '</div>';
    
    return $Content;
}


function pnlShapeSelection($objShape, $objParent=null){
	
	if (is_null($objParent)){
		$objParent = $objShape->Selection;
	}
	$objShapeClass = $objParent->ShapeClass;
	
	$Content = "";
	$Content .= pnlShapeClass($objShapeClass);
	
	if (!is_null($objShapeClass)){
		if (!is_null($objShapeClass->Class)){
			$Content .= "<div class='tab'>";
			$Content .= "<h3>Links</h3>";
		
			$Content .= "<div class='tab'>";
			foreach ($objShapeClass->ShapeLinks as $objShapeLink){
				$Content .= pnlShapeLink($objShapeLink);
				
				$Content .= "<div class='tab'>";
				$Content .= pnlShapeSelection($objShape, $objShapeLink);
				$Content .= "</div>";
			}
			$Content .= "</div>";
						
			$Content .= "</div>";
		}
	}
		
	return $Content;
		
}




Function pnlShapeClass( $objShapeClass){

	$Content = "";
	
	if (!is_object($objShapeClass)){
		return;
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
		
	$objClass = $objShapeClass->Class;
	if (!is_object($objClass)){
		return;
	}
	
	$Content .= "<div  class='sdgreybox'>";
	
	$Content .= "<table>";
	$Content .= "<tr><th>Class</th><td>".$objClass->Label."</td></tr>";	
	if ($objShapeClass->Create === true){
		$Content .= "<tr><th>Create?</th><td>";
		$Content .= "&#10003";
		$Content .= "</td></tr>";	
	}
	if ($objShapeClass->Select === true){
		$Content .= "<tr><th>Select?</th><td>";
		$Content .= "&#10003";
		$Content .= "</td></tr>";	
	}
	if ($objShapeClass->Match === true){
		$Content .= "<tr><th>Match?</th><td>";
		$Content .= "&#10003";
		$Content .= "</td></tr>";	
	}
	
	
	$Content .= "</table>";

	
	$ClassProperties = $Dicts->ClassProperties($objClass->DictId,$objClass->Id);
	if (count($ClassProperties) > 0){
		$Content .= "<div class='tab'>";
		$Content .= "<h4>Properties</h4>";	
		$Content .= "<div class='tab'>";
		$Content .= pnlShapeProperties($objShapeClass, $ClassProperties);
		$Content .= "</div>";
		$Content .= "</div>";
	}
	
	$Content .= "</div>";
	
	return $Content;
}



Function pnlShapeLink( $objShapeLink){

	$Content = "";
	
	if (!is_object($objShapeLink)){
		return;
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}

	$objShape = $objShapeLink->Shape;

	
	
	
	$objRel = $objShapeLink->Relationship;
	if (!is_object($objRel)){
		return;
	}

	$Content .= "<table class='sdgreybox'>";
	
	
	if (!is_null($objShapeLink->FromShapeClassId)){
		if (isset($objShape->ShapeClasses[$objShapeLink->FromShapeClassId])){
			$objFromShapeClass = $objShape->ShapeClasses[$objShapeLink->FromShapeClassId];			
			$Content .= "<tr><th>Link From</th><td>".$objFromShapeClass->Class->Label."</td></tr>";
		}
	}
		
	switch ($objShapeLink->Inverse){
		case true:
			$Content .= "<tr><th>Relationship</th><td>".$objRel->InverseLabel."</td></tr>";
			break;
		default:
			$Content .= "<tr><th>Relationship</th><td>".$objRel->Label."</td></tr>";
			break;
	}
	
	if (!is_null($objShapeLink->ToShapeClassId)){
		if (isset($objShape->ShapeClasses[$objShapeLink->ToShapeClassId])){
			$objToShapeClass = $objShape->ShapeClasses[$objShapeLink->ToShapeClassId];			
			$Content .= "<tr><th>Link To</th><td>".$objToShapeClass->Class->Label."</td></tr>";
		}
	}

	$Content .= "<tr><th>Cardinality</th><td>".$objShapeLink->Cardinality."</td></tr>";
		
	if ($objShapeLink->EffDates === true){
		$Content .= "<tr><th>Eff Dates</th><td>&#10003</td></tr>";
	}
	
	
	$Content .= "</table>";

	$RelProperties = $Dicts->RelProperties($objRel->DictId,$objRel->Id);

	if (count($RelProperties) > 0){
		$Content .= "<div class='tab'>";	
		$Content .= "<h4>Properties</h4>";		
		$Content .= "<div class='tab'>";			
		$Content .= pnlShapeProperties($objShapeLink, $RelProperties);
		$Content .= "</div>";
		$Content .= "</div>";
	}
	
	
	return $Content;
}

function pnlShapeProperties($objParent, $HasProperties=array()){
	
	global $Dicts;
	
	$Content = '';
	
	$Content .= "<table>";
	$Content .= "<tr><th/><th>Selected?</th><th>Cardinality</th><th>Filters</th></tr>";
	
	foreach ($HasProperties as $objHasProperty){
		
		$objShapeProp = null;
		foreach ($objParent->ShapeProperties as $optShapeProperty){
			if ($optShapeProperty->Property->DictId == $objHasProperty->PropDictId){
				if ($optShapeProperty->Property->Id == $objHasProperty->PropId){
					$objShapeProp = $optShapeProperty;
					continue;
				}				
			}
		}

		$objProp = $Dicts->getProperty($objHasProperty->PropDictId,$objHasProperty->PropId);
		if (is_object($objProp)){
			$Content .= "<tr>";
			$Content .= "<td>".$objProp->Label."</td>";
			
			$Content .= "<td>";		
			if (!is_null($objShapeProp)){
				if ($objShapeProp->Selected === true){
					$Content .= "&#10003";
				}
			}		
			$Content .= "</td>";

			
			$Content .= "<td>";
			if (is_object($objShapeProp)){
				$Content .= $objShapeProp->Cardinality;
			}
			$Content .= "</td>";
			
			
			$Content .= "<td>";		
			
			if (!is_null($objShapeProp)){
				foreach ($objShapeProp->Filters as $objFilter){
					$Content .= $objFilter->Type.' '.$objFilter->Value.' <br/>';
				}
			}		

			$Content .= "</td>";
			
			$Content .= "</tr>";
			
			if (!is_null($objShapeProp)){
				$Content .= pnlShapeComplexProperties($objShapeProp->ShapeProperties);
			}		
			
		}
	}
	$Content .= "</table>";
	
	return $Content;
}



function pnlShapeComplexProperties($ShapeProperties=array(), $Level = 1){
	
	global $Dicts;
	
	$Content = '';
	
	
	foreach ($ShapeProperties as $objShapeProp){
		
		$objProp = $objShapeProp->Property;
		if (is_object($objProp)){
			$Content .= "<tr>";
			
			$Padding = ($Level * 30).'px';
			$Content .= "<td style='padding-left:$Padding'>".$objProp->Label."</td>";
			
			$Content .= "<td>";		
			if ($objShapeProp->Selected === true){
				$Content .= "&#10003";
			}
			$Content .= "</td>";
			
			$Content .= "<td>";		
			$Content .= $objShapeProp->Cardinality;
			$Content .= "</td>";
			
				
			$Content .= "</tr>";
			
			$Content .= pnlShapeComplexProperties($objShapeProp->ShapeProperties, ($Level + 1));
			
		}
	}
	
	return $Content;
}


?>