<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');

// require_once(dirname(__FILE__).'/../class/clsView.php');
require_once(dirname(__FILE__).'/../class/clsShape.php');

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsData.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once('pnlClassSubjects.php');

Function pnlSelectionData( $objSelection, $objShapeClass = null){

	global $System;
	if (!isset($Sysytem)){
		$System = new clsSystem;
	}	
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}
		
	$Content = '';
	
//	$Content .= "<div class='sdgreybox'>";

	if (is_null($objShapeClass)){
		$objShapeClass = $objSelection->Selection->ShapeClass;
	}
		
	$objSubjects = new clsSubjects();
//	$objSubjects->Filter = $objFilterClass;
//	$objSubjects->ViewSelection = $objViewSelection;
//	$objSubjects->Class = $objShapeClass->Class;
	
	$SubjectIds = $objSubjects->getClass($objShapeClass->Class->DictId, $objShapeClass->Class->Id);
	if (count($SubjectIds) > 0){
		$ReturnUrl = UpdateUrl();
		$Content .= pnlClassSubjects( $objShapeClass->Class->DictId, $objShapeClass->Class->Id, $SubjectIds, $FieldName = 'subjectid', $ReturnUrl);
	}
	
	
	
	
	
//////////////////////////////////////	
/*
	if (count($SubjectIds) > 0){

		$Cols = array();
		global $Cols;
		$HeaderCells = array();
		$HeaderCells = setupTable($objShapeClass);
		$HeaderRows = setHeaderRows($HeaderCells);
		
		$Content .= "<table class='list'><thead>";
		foreach ($HeaderRows as $Row){
			$Content .= "<tr>$Row</tr>";
		}		
		$Content .= "</thead>";

		
		$Content .= "<tbody>";

		foreach ($SubjectIds as $SubjectId){
			$Row = getDataRow($objShapeClass,$SubjectId);			
			$Content .= "<tr>";
			foreach ($Cols as $ColName=>$Col){
				$Content .= "<td>";
				if (isset($Row->Cells[$ColName])){
					$RowCell = $Row->Cells[$ColName];
					if (!is_null($RowCell->href)){
						$Content .= "<a href='".$RowCell->href."'>";
					}					
					foreach ($Row->Cells[$ColName]->Values as $Value){
						$Content .= "$Value<br/>";
					}
					if (!is_null($RowCell->href)){
						$Content .= '</a>';
					}
					
				}
				
				$Content .= "</td>";
			}
			$Content .= "</tr>";				
		}
		
		$Content .= "</tbody>";
		
		$Content .= "</table>";
	}
	$Content .= "</div>";
	
*/
		    
    return $Content;
}

/*
class clsHeaderCell{
	
	public $Label = "";
	public $Cells = array();
	
	public $ColSpan = 1;
	public $RowSpan = 1;
	
}

class clsRow{
	public $Cells = array();
}

class clsRowCell{
	public $Values = array();
	public $href = null;
}


class clsCol{
	
	public $Property = null;
	
}


function setupTable($objShapeClass, $objFilterClass = null, $ParentIndex = ""){
	
	global $Cols;
	$HeaderCells = array();;
	
	if (!is_object($objShapeClass)){
		return $HeaderCells;
	}

	if (!is_object($objShapeClass->Class)){
		return $HeaderCells;
	}
	
	
	$objClassCell = new clsHeaderCell;
	$objClassCell->Label = $objShapeClass->Class->Label;

	$PropNum = 0;
	foreach ($objShapeClass->ShapeProperties as $objShapeProp){
		$PropNum = $PropNum + 1;
		$useProp = true;
		if (!is_null($objFilterClass)){
			$Found = false;
			foreach ($objFilterClass->FilterProperties as $objFilterProperty){
				if ($objFilterProperty->Property === $objShapeProp->Property){
					if ($objFilterProperty->Selected === true){
						$Found = true;
					}
				}
			}
			if (!$Found){
				$useProp = false;
			}
		}

		if ($useProp){
			$objPropCell = new clsHeaderCell;
			$objPropCell->Label = $objShapeProp->Property->Label;
			$objClassCell->Cells[] = $objPropCell;
			
			$objCol = new clsCol();
			$objCol->Property = $objShapeProp->Property;
			$Cols[$ParentIndex.'prop_'.$PropNum] = $objCol;
		}
	}

/*	
	$LinkNum = 0;
	foreach ($objShapeClass->ShapeLinks as $objShapeLink){
		$LinkNum = $LinkNum + 1;
		
		$useLink = true;
/*		$FilterLinkClass = null;
		if (!is_null($objFilterClass)){
			$Found = false;
			foreach ($objFilterClass->FilterLinks as $objFilterLink){
				if ($objFilterLink->Relationship === $objShapeLink->Relationship){
//					if ($objFilterLink->FilterClass->Class === $objShapeLink->ViewObject->Class){
//						$FilterLinkClass = $objFilterLink->FilterClass;
//						foreach ($FilterLinkClass->FilterProperties as $objFilterProperty){
//							if ($objFilterProperty->Selected === true){
								$Found = true;
//								break 2;
//							}
//						}
//					}
				}
			}
			if (!$Found){
				$useLink = false;
			}
		}
*/		
/*		
		if ($useLink){
			switch ( $objShapeLink->Relationship->Cardinality ){
				case 'extend':
				case 'once':
					$objLinkCell = new clsHeaderCell;
					$objLinkCell->Label = $objShapeLink->Relationship->Label;
//					$objLinkCell->Cells = setupTable($objShapeLink->ViewObject, $objFilterLink->FilterClass, $ParentIndex.'link_'.$LinkNum.'_');
					
					$objClassCell->Cells[] = $objLinkCell;
					
					break;
			}
		}
	}
*/

/*
	$HeaderCells[]  = $objClassCell;
	
	return $HeaderCells;
}	


function getColSpan($objCell){

	$Span = 0;
	foreach ($objCell->Cells as $objSubCell){
		$Span = $Span + getColSpan($objSubCell);		
	}
	if ($Span == 0){
		$Span = 1;
	}

	return $Span;
}


function setHeaderRows($Cells, $RowNum = 1, $Rows=null){
	$MaxRows = getMaxRows($Cells) + 1;
	return setHeaderRow($Cells, $MaxRows);
}
	
Function setHeaderRow($Cells, $MaxRows = 1, $RowNum = 1, $Rows=null){	
		
	if (is_null($Rows)){
		$Rows = array();
	}
	
	foreach ($Cells as $Cell){
		
		$ColSpan = getColSpan($Cell);
		
		$RowCol = "<th";
		if ($ColSpan > 1){
			$RowCol .= " colspan = '$ColSpan' ";
		}
		
		if (count($Cell->Cells) == 0){
			$RowSpan = $MaxRows - $RowNum;
			if ($RowSpan > 1){
				$RowCol .= " rowspan = '$RowSpan' ";
			}
		}
		
		
		$RowCol .= ">".$Cell->Label."</th>";
		if (!isset($Rows[$RowNum])){
			$Rows[$RowNum] = "";
		}
		$Rows[$RowNum] .= $RowCol;

		$Rows = setHeaderRow($Cell->Cells, $MaxRows, $RowNum + 1, $Rows);
		
	}	
	
	return $Rows;
	
}

function getMaxRows($Cells, $MaxRows=1, $ThisRow=1){
	
	if (count($Cells) > 0){
		if ($ThisRow > $MaxRows){
			$MaxRows = $ThisRow;
		}
		foreach ($Cells as $Cell){		
			$MaxRows = getMaxRows($Cell->Cells, $MaxRows, $ThisRow + 1);
		}	
		
	}
	return $MaxRows;
}


function getDataRow($objShapeClass, $SubjectId, $Row = null, $ParentIndex=''){

	global $Cols;
	if (is_null($Row)){
		$Row = new clsRow();
	}
	
	if (!is_object($objShapeClass)){
		return $Row;
	}
	if (!is_object($objShapeClass->Class)){
		return $Row;
	}

	$objSubject = new clsSubject($SubjectId);
	$useSubject = false;
	if ($objSubject->ClassDictId == $objShapeClass->Class->DictId){
		if ($objSubject->ClassId == $objShapeClass->Class->Id){
			$useSubject = true;		
		}		
	}
	if (!$useSubject){
		return $Row;
	}

//	$Attributes = $objSubject->getViewClassAttributes($objViewClass);
	$Attributes = $objSubject->Attributes;
	
	$FirstAtt = true;
	
	
	$PropNum = 0;
	foreach ($Attributes as $PropDictId=>$PropAtts){
		foreach ($PropAtts as $PropId=>$objAtts){	
	
			$PropNum = $PropNum + 1;
			if (isset($Cols[$ParentIndex.'prop_'.$PropNum])){
				if (!isset($Row->Cells[$ParentIndex.'prop_'.$PropNum])){
					$Row->Cells[$ParentIndex.'prop_'.$PropNum] = new clsRowCell;
				}
				$RowCell = $Row->Cells[$ParentIndex.'prop_'.$PropNum];
				foreach ($objAtts as $objAtt){
					if ($FirstAtt){
//						$RowCell->href = "subject.php?subjectid=$SubjectId";

						$RowCell->href = "dashboard1.php?subjectid=$SubjectId&selectionid=".$objShapeClass->Shape->Id;
						
						
						
					}
					$RowCell->Values[] = truncate($objAtt->Value,300);
				}
				$FirstAtt = false;
			}
		}
	}

/*	
	$LinkNum = 0;
	foreach ($objShapeClass->ShapeLinks as $objShapeLink){
		$LinkNum = $LinkNum + 1;
		switch ( $objShapeLink->Relationship->Cardinality ){
			case 'extend':
			case 'once':
				
				foreach ($objSubject->getStatements() as $objStatement){
					if ($objStatement->SubjectId == $SubjectId){
						if ($objStatement->TypeId == 300){
							if ($objStatement->LinkDictId == $objShapeLink->Relationship->DictId){
								if ($objStatement->LinkId == $objShapeLink->Relationship->Id){
//									$Row = getDataRow($objViewLink->ViewObject, $objStatement->ObjectId, $Row, $ParentIndex.'link_'.$LinkNum.'_');
								}							
							}
						}
					}
				}				
				break;
		}
	}
*/	

/*
	return $Row;

}

*/

?>