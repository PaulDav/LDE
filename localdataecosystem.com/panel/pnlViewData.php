<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsView.php');
require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsData.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlViewData( $ViewId, $arrFilterClasses = null){

	global $System;
	if (!isset($Sysytem)){
		$System = new clsSystem;
	}	
	
	global $Views;
	if (!isset($Views)){
		$Views = new clsViews;
	}
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}
	
	global $PassFilter;
	
	if (!isset($Views->Items[$ViewId])){
		throw new Exception("Unknown View");
	}
	
	$objView = $Views->Items[$ViewId];
			
	$Content = '';
	

	foreach ($objView->Selections as $objSel){

		$Content .= "<div class='sdgreybox'>";

		$objViewClass = $objSel->ViewClass;

		
		$objFilterClass = null;
		if (is_Array($arrFilterClasses)){
			foreach ($arrFilterClasses as $optFilterClass){
				if ($optFilterClass->Class == $objViewClass->Class ){
					$objFilterClass = $optFilterClass;
				}
			}
		}
		
		
		$objSubjects = new clsSubjects();
		$SubjectIds = $objSubjects->getClass($objViewClass->Class->DictId, $objViewClass->Class->Id);

		
		
		
		
		if (count($SubjectIds) > 0){

			$Cols = array();
			$Cols = setCols($objViewClass);
			$HeaderRows = setHeaderRows($Cols);
			
			
			$Content .= "<table class='list'><thead>";
			foreach ($HeaderRows as $Row){
				$Content .= "<tr>$Row</tr>";
			}		
			$Content .= "</thead>";
						
			
			$Content .= "<tbody>";

			foreach ($SubjectIds as $SubjectId){
				$PassFilter = true;
				$DataRow = getDataRow($objViewClass,$SubjectId, $objFilterClass);
				If ($PassFilter){
					$Content .= "<tr>";
					$Content .= $DataRow;
					$Content .= "</tr>";				
				}
			}
			
			$Content .= "</tbody>";
			
			$Content .= "</table>";
		}
		$Content .= "</div>";
		
	}
		
    
    return $Content;
}


class clsCol{
	
	public $Label = "";
	public $Property = null;
	public $Cols = array();
	
	public $ColSpan = 1;
	public $RowSpan = 1;
	
}



function setCols($objViewClass){
	
	$Cols = array();
	if (!is_object($objViewClass)){
		return $Cols;
	}

	if (!is_object($objViewClass->Class)){
		return $Cols;
	}
	
	
	$objClassCol = new clsCol;
	$objClassCol->Label = $objViewClass->Class->Label;
		
	foreach ($objViewClass->ViewProperties as $objViewProp){
		$objPropCol = new clsCol;
		$objPropCol->Label = $objViewProp->Property->Label;
		$objPropCol->Property = $objViewProp->Property;
		$objClassCol->Cols[] = $objPropCol;
	}
		
	foreach ($objViewClass->ViewLinks as $objViewLink){
		switch ( $objViewLink->Relationship->Cardinality ){
			case 'extend':
			case 'once':
				$objLinkCol = new clsCol;
				$objLinkCol->Label = $objViewLink->Relationship->Label;
				$objLinkCol->Cols = setCols($objViewLink->ViewObject);
				
				$objClassCol->Cols[] = $objLinkCol;
				
				break;
		}
	}

	$Cols[]  = $objClassCol;
	
	return $Cols;
}	


function getColSpan($objCol){

	$Span = 0;
	foreach ($objCol->Cols as $objSubCol){
		$Span = $Span + getColSpan($objSubCol);		
	}
	if ($Span == 0){
		$Span = 1;
	}

	return $Span;
}


function setHeaderRows($Cols, $RowNum = 1, $Rows=null){
	$MaxRows = getMaxRows($Cols) + 1;
	return setHeaderRow($Cols, $MaxRows);
}
	
Function setHeaderRow($Cols, $MaxRows = 1, $RowNum = 1, $Rows=null){	
		
	if (is_null($Rows)){
		$Rows = array();
	}
	
	foreach ($Cols as $Col){
		
		$ColSpan = getColSpan($Col);
		
		$RowCol = "<th";
		if ($ColSpan > 1){
			$RowCol .= " colspan = '$ColSpan' ";
		}
		
		if (count($Col->Cols) == 0){
			$RowSpan = $MaxRows - $RowNum;
			if ($RowSpan > 1){
				$RowCol .= " rowspan = '$RowSpan' ";
			}
		}
		
		
		$RowCol .= ">".$Col->Label."</th>";
		if (!isset($Rows[$RowNum])){
			$Rows[$RowNum] = "";
		}
		$Rows[$RowNum] .= $RowCol;

		$Rows = setHeaderRow($Col->Cols, $MaxRows, $RowNum + 1, $Rows);
		
	}	
	
	return $Rows;
	
}

function getMaxRows($Cols, $MaxRows=1, $ThisRow=1){
	
	if (count($Cols) > 0){
		if ($ThisRow > $MaxRows){
			$MaxRows = $ThisRow;
		}
		foreach ($Cols as $Col){		
			$MaxRows = getMaxRows($Col->Cols, $MaxRows, $ThisRow + 1);
		}	
		
	}
	return $MaxRows;
}


function getDataRow($objViewClass, $SubjectId, $objFilterClass = null){

	global $PassFilter;
	
	$Content = "";

	if (!is_object($objViewClass)){
		return;
	}
	if (!is_object($objViewClass->Class)){
		return;
	}

	$objSubject = new clsSubject($SubjectId);
	$useSubject = false;
	if ($objSubject->ClassDictId == $objViewClass->Class->DictId){
		if ($objSubject->ClassId == $objViewClass->Class->Id){
			$useSubject = true;		
		}		
	}
	if (!$useSubject){
		return;
	}

	$Attributes = $objSubject->getViewClassAttributes($objViewClass);
	foreach ($Attributes as $objAtts){	

		$AttDictId = null;
		$AttPropId = null;			
		
		
		$Content .= "<td>";
		$Value = array();
		
		foreach ($objAtts as $objAtt){
			$AttDictId = $objAtt->DictId;
			$AttPropId = $objAtt->PropId;			
			$Value[] = $objAtt->Value;
		}
			
		if (!is_null($objFilterClass)){
			foreach ($objFilterClass->Filters as $objFilter){
				if ($objFilter->Property->DictId == $AttDictId){
					if ($objFilter->Property->Id == $AttPropId){
						switch ($objFilter->Type){
							case 'is':
								if (count($Value) == 0){
									$PassFilter = false;
								}
								$Found = false;
								foreach ($Value as $ThisValue){
									if ($ThisValue == $objFilter->Value){
										$Found = true;
									}
								}
								if (!$Found){
									$PassFilter = false;
								}
								break;
							case 'is not':
								foreach ($Value as $ThisValue){
									if ($ThisValue == $objFilter->Value){
										$Pass = false;
									}
								}
								break;							
							case 'contains':
								if (count($Value) == 0){
									$PassFilter = false;
								}
								$Found = false;
								foreach ($Value as $ThisValue){
									if (strpos(strtolower($ThisValue), strtolower($objFilter->Value)) == true){
										$Found = true;
									}									
								}
								if (!$Found){
									$PassFilter = false;
								}
								
								
								break;
							case 'more than':
								if (count($Value) == 0){
									$PassFilter = false;
								}
								$Found = false;
								foreach ($Value as $ThisValue){
									if ($ThisValue > $objFilter->Value){
										$Found = true;
									}
								}
								if (!$Found){
									$PassFilter = false;
								}
								break;
							case 'less than';
								if (count($Value) == 0){
									$PassFilter = false;
								}
								$Found = false;
								foreach ($Value as $ThisValue){
									if ($ThisValue < $objFilter->Value){
										$Found = true;
									}
								}
								if (!$Found){
									$PassFilter = false;
								}
								break;						
						}
					}
					
				}
			}
		}
			
		foreach ($Value as $ThisValue){
			$Content .= truncate($objAtt->Value,300)."<br/>";
		}
		$Content .= "</td>";
	}

	foreach ($objViewClass->ViewLinks as $objViewLink){
		switch ( $objViewLink->Relationship->Cardinality ){
			case 'extend':
			case 'once':
				
			foreach ($objSubject->getStatements() as $objStatement){
				if ($objStatement->SubjectId == $SubjectId){
					if ($objStatement->TypeId == 300){
						if ($objStatement->LinkDictId == $objViewLink->Relationship->DictId){
							if ($objStatement->LinkId == $objViewLink->Relationship->Id){
								
								$objLinkFilterClass = null;
								if (!is_null($objFilterClass)){
									foreach ($objFilterClass->FilterLinks as $optFilterLink){
										if ($optFilterLink->Relationship == $objViewLink->Relationship){
											if ($optFilterLink->FilterClass->Class == $objViewLink->ViewObject->Class){
												$objLinkFilterClass = $optFilterLink->FilterClass;
												continue;
											}
										}
									}
								}
								
								$Content .= getDataRow($objViewLink->ViewObject, $objStatement->ObjectId, $objLinkFilterClass);
							}							
						}
					}
				}
			}				
				
		}
	}
	
	return $Content;

}

?>