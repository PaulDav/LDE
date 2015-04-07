<?php

require_once(dirname(__FILE__).'/../class/clsDocument.php');
require_once(dirname(__FILE__).'/../class/clsShape.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once('pnlComplexValue.php');


//require_once('pnlProfile.php');


Function pnlDocument( $DocId){
	
	global $Shapes;
	if (!isset($Shapes)){
		$Shapes = new clsShapes;
	}
	
	$objDoc = new clsDocument($DocId);
	$objShape = $objDoc->objShape;
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Document Id</th><td><a href='document.php?docid=".$objDoc->Id."'>".$objDoc->Id."</a></td></tr>";
	$Content .= "<tr><th>Set Id</th><td><a href='set.php?setid=".$objDoc->SetId."'>".$objDoc->SetId."</a></td></tr>";
	if (!is_null($objShape)){
		$Content .= "<tr><th>Shape</th><td><a href='shape.php?shapeid=".$objDoc->ShapeId."'>".$objShape->Name."</a></td></tr>";
	}

	$Content .= "</table>";

	
    return $Content;
	
}

function pnlForm($objForm){
	
	$Content = "";
	
	$Content .= "<table class='sdgreybox'>";
	foreach ($objForm->FormFields as $FieldNum=>$arrFields){

		if (isset($arrFields[1])){
			
			$FormField = $arrFields[1];
			switch ($FormField->Property->Type){
				case 'simple':
				
					$hasValue = false;
					foreach ($arrFields as $objFormField){
						if (!IsEmptyString($objFormField->Value)){
							$hasValue = true;
						}
					}
					if ($hasValue){
						$Content .= "<tr><th>".$objFormField->Property->Label."</th><td>";
							
						foreach ($arrFields as $objFormField){
							if (!IsEmptyString($objFormField->Value)){
								$Content .= nl2br($objFormField->Value)."<br/>";
							}
						}
						$Content .= "</td></tr>";
					}
					break;
				case 'complex':
					foreach ($arrFields as $objFormField){
						if (!is_null($objFormField->Statement)){
							$Content .= "<tr><th>".$objFormField->Property->Label."</th><td>";
							$Content .= pnlFormComplexAttribute($objFormField);
							$Content .= "</td></tr>";
						}
					}
					break;				
			}
		}
	}
/*	
	foreach ($objForm->LinkForms as $ProfileRelId=>$arrLinkForms){
		foreach ($arrLinkForms as $objLinkForm){	
			if ($objLinkForm->Cardinality == 'extend'){
				if (!is_null($objLinkForm->Statement)){
					$Content .= "<tr><th>";
					switch ($objLinkForm->Inverse){
						case true:
							$Content .= $objLinkForm->Relationship->InverseLabel;
							break;
						default:
							$Content .= $objLinkForm->Relationship->Label;
							break;
					}
					$Content .= "</th>";
					$Content .= "<td>".pnlForm($objLinkForm->ObjectForm)."</td></tr>";
				}
			}
		}
	}
*/	
	
	$Content .= "</table>";

	return $Content;
	
}

function pnlFormComplexAttribute($objFormField){
	
	$Content = '';
	$Content .= "<table class='sdgreybox'>";
		
	foreach ($objFormField->FormFields as $arrComplexFields){
		$objComplexField = current($arrComplexFields);
		
		switch ($objComplexField->Property->Type){
			case 'simple':
				$Content .= "<tr><th>".$objComplexField->Property->Label."</th>";
				$Content .= "<td>";
				foreach ($arrComplexFields as $objComplexField){
					if (!is_null($objComplexField->Statement)){
						if (!IsEmptyString($objComplexField->Value)){
							$Content .= nl2br($objComplexField->Value)."<br/>";
						}
					}
				}
				$Content .= "</td></tr>";	
				break;
			case 'complex':
				foreach ($arrComplexFields as $objComplexField){
					if (!is_null($objComplexField->Statement)){
						$Content .= "<tr><th>".$objComplexField->Property->Label."</th>";
						$Content .= "<td>".pnlFormComplexAttribute($objComplexField)."</td></tr>";
					}
				}
				break;
		}
		
	}
	
	$Content .= '</table>';
	
	return $Content;
	
}


function pnlLinkForm($objLinkForm){

	$Content = "";
	$Content .= "<table class='sdgreybox'>";
	
	if (!is_null($objLinkForm->FromId)){
		$Content .= "<tr><th>Link From</th><td>".pnlSubject($objLinkForm->FromId, $objLinkForm->DocumentId)."</td></tr>";
	}
				
	$RelLabel = $objLinkForm->ShapeLink->Relationship->Label;
	if ($objLinkForm->ShapeLink->Inverse){
		$RelLabel = $objLinkForm->ShapeLink->Relationship->InverseLabel;					
	}
	
	$Content .= "<tr><th>Relationship</th><td>$RelLabel</td></tr>";				

	if (!is_null($objLinkForm->ToId)){
		$Content .= "<tr><th>Link To</th><td>".pnlSubject($objLinkForm->ToId, $objLinkForm->DocumentId)."</td></tr>";
	}
	
	
	
	
	if (!is_null($objLinkForm->EffectiveFrom)){
		$Content .= "<tr><th>Effective From</th><td>".convertDate($objLinkForm->EffectiveFrom)."</td>";		
	}
	if (!is_null($objLinkForm->EffectiveTo)){
		$Content .= "<tr><th>Effective To</th><td>".convertDate($objLinkForm->EffectiveTo)."</td>";		
	}
	
	foreach ($objLinkForm->FormFields as $FieldNum=>$arrFields){

		$hasValue = false;
		foreach ($arrFields as $objFormField){
			if (!IsEmptyString($objFormField->Value)){
				$hasValue = true;
			}
		}
		if ($hasValue){
			if (isset($arrFields[1])){
				$objFormField = $arrFields[1];
				$Content .= "<tr><th>".$objFormField->Property->Label."</th><td>";
				
				foreach ($arrFields as $objFormField){
					if (!IsEmptyString($objFormField->Value)){
						$Content .= nl2br($objFormField->Value)."<br/>";
					}
				}
				$Content .= "</td></tr>";
			}
		}
	}
	$Content .= "</table>";

	return $Content;
	
}


Function pnlDocShapeClassForms($objDoc, $objShapeClass){

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}

	$DocId = null;
	if (!is_null($objDoc)){
		$DocId = $objDoc->Id;
	}
	$ShapeClassId = $objShapeClass->Id;
	$objClass = $objShapeClass->Class;
	$objShape = $objShapeClass->Shape;

	if (is_null($objDoc)){
		return;
	}
	
	
	if (is_null($objDoc->SubjectForms)){
		return;
	}
	
	$SubjectForms = array();
	foreach ($objDoc->SubjectForms as $objSubjectForm){
		if ($objSubjectForm->ShapeClass == $objShapeClass){
			$SubjectForms[$objSubjectForm->SubjectId] = $objSubjectForm;
		}
	}
		
	
	if (count($SubjectForms) == 0){
		return;
	}
	
	$Content = '';

		
	$Content .= '<table class="sdgreybox"><thead><tr>';

	$Content .= "<th>Subject Id</th>";
	
	
	foreach ($objShapeClass->ShapeProperties as $objShapeProp){
		if ($objShapeProp->Selected){
			$Content .= "<th>".$objShapeProp->Property->Label."</th>";
		}		
	}

	$Content .= '</tr></thead>';
	
	$Content .= "<tbody>";

	$Seq = 0;

	foreach ($SubjectForms as $objSubjectForm){
		
		$Content .= "<tr>";
			
		$SubjectId = $objSubjectForm->SubjectId;
		
		$ReturnUrl = "documentsubject.php?docid=".$DocId."&subjectid=$SubjectId&shapeid=$objShape->Id&shapeclassid=".$objShapeClass->Id;
		$Content .= "<td><a href='$ReturnUrl'>$SubjectId</a></td>";

		$objSubject = new clsSubject($SubjectId);
		$objSubject->AsAtDocumentId = $DocId;
					
		foreach ($objShapeClass->ShapeProperties as $objShapeProp){
			if ($objShapeProp->Selected){
		
				$Content .= "<td>";
								
				foreach ($objSubject->Attributes as $DictAtts){
					foreach ($DictAtts as $PropAtts){
						foreach ($PropAtts as $objAtt){
				
							$useProp = false;
							if ($objShapeProp->Property->DictId == $objAtt->DictId){
								if ($objShapeProp->Property->Id == $objAtt->PropId){
									$useProp = true;
								}								
							}
							if (!$useProp){
								foreach ($Dicts->SubProperties($objShapeProp->Property->DictId, $objShapeProp->Property->Id) as $objSubProp){
									if ($objSubProp->DictId == $objAtt->DictId){
										if ($objSubProp->Id == $objAtt->PropId){
											$useProp = true;
											break;
										}										
									}
								}
							}
							if ($useProp){
								switch ($objAtt->Property->Type){
									case 'simple':
										$Content .= make_links($objAtt->Value)."<br/>";
										break;
									case 'complex':
										$Content .= pnlComplexValue( $objAtt).'<br/>';						
										break;
								}								
							}
						}
					}
				}
				
				$Content .= "</td>";
			}
							
		}
		$Content .= "</tr>";		
	}
	
	$Content .= "</tbody>";
	
    $Content .= '</table>';
	    
    return $Content;
}



Function pnlDocShapeLinkForms($objDoc, $objShapeLink, $SubjectId = null){

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}

	$DocId = null;
	if (!is_null($objDoc)){
		$DocId = $objDoc->Id;
	}
	$ShapeLinkId = $objShapeLink->Id;
	$objRel = $objShapeLink->Relationship;
	$objShape = $objShapeLink->Shape;

	if (is_null($objDoc)){
		return;
	}
		
	if (is_null($objDoc->LinkForms)){
		return;
	}
	$LinkForms = $objDoc->getSubjectLinkForms($SubjectId, $objShapeLink);
		
	
	if (count($LinkForms) == 0){
		return;
	}
	
	$Content = '';

		
	$Content .= '<table class="sdgreybox"><thead><tr>';

	$Content .= "<th>Link Id</th>";

	if ($objShapeLink->EffDates === true){
		$Content .= "<th>Effective From</th>";
		$Content .= "<th>Effective To</th>";
	}	

	$Content .= "<th>Link From</th>";
	$Content .= "<th>Link To</th>";
	
	
	foreach ($objShapeLink->ShapeProperties as $objShapeProp){
		if ($objShapeProp->Selected){
			$Content .= "<th>".$objShapeProp->Property->Label."</th>";
		}		
	}
	
	
	$Content .= '</tr></thead>';
	
	$Content .= "<tbody>";

	foreach ($LinkForms as $objLinkForm){
		
		$Content .= "<tr>";
			
		$LinkId = $objLinkForm->LinkId;
		
		$ReturnUrl = "documentlink.php?docid=".$DocId."&linkid=$LinkId&shapeid=$objShape->Id&shapelinkid=".$objShapeLink->Id;
		$Content .= "<td><a href='$ReturnUrl'>$LinkId</a></td>";
		
		if ($objShapeLink->EffDates === true){
			$Content .= '<td>'.convertDate($objLinkForm->EffectiveFrom).'</td>';
			$Content .= '<td>'.convertDate($objLinkForm->EffectiveTo).'</td>';
		}

			
		$objLink = new clsLink($LinkId);
		$objLink->AsAtDocumentId = $DocId;
				
		$objFromSubject = new clsSubject($objLinkForm->FromId);
		$objFromSubject->AsAtDocumentId = $DocId;
		
		$objToSubject = new clsSubject($objLinkForm->ToId);
		$objToSubject->AsAtDocumentId = $DocId;
		
		$Content .= "<td>".$objFromSubject->Label."</td>";
		$Content .= "<td>".$objToSubject->Label."</td>";
				
  		foreach ($objShapeLink->ShapeProperties as $objShapeProp){
			if ($objShapeProp->Selected){

				$Content .= "<td>";
									
				foreach ($objLink->Attributes as $DictAtts){
					foreach ($DictAtts as $PropAtts){
						foreach ($PropAtts as $objAtt){
				
							$useProp = false;
							if ($objShapeProp->Property->DictId == $objAtt->DictId){
								if ($objShapeProp->Property->Id == $objAtt->PropId){
									$useProp = true;
								}								
							}
							if (!$useProp){
								foreach ($Dicts->SubProperties($objShapeProp->Property->DictId, $objShapeProp->Property->Id) as $objSubProp){
									if ($objSubProp->DictId == $objAtt->DictId){
										if ($objSubProp->Id == $objAtt->PropId){
											$useProp = true;
											break;
										}										
									}
								}
															}
							if ($useProp){
								switch ($objAtt->Property->Type){
									case 'simple':
										$Content .= make_links($objAtt->Value)."<br/>";
										break;
									case 'complex':
										$Content .= pnlComplexValue( $objAtt).'<br/>';						
										break;
								}								
							}
						}
					}
				}
					
				$Content .= "</td>";
			}
							
		}
		$Content .= "</tr>";	
		
	}
	
	
	$Content .= "</tbody>";
	
    $Content .= '</table>';
	    
    return $Content;
}

?>