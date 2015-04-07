<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsData.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once('pnlComplexValue.php');


Function pnlClassSubjects( $DictId, $ClassId, $SubjectIds = null, $FieldName = 'subjectid', $ReturnUrl = null, $Selection = null){

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}
	
	if (!isset($Dicts->Dictionaries[$DictId])){
		throw new Exception("Unknown Dictionary");
	}
	
	$Dict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($Dict->Classes[$ClassId])){
		throw new Exception("Unknown Class");
	}
	$Class = $Dict->Classes[$ClassId];
	
	
	if (is_null($ReturnUrl)){
		$ReturnUrl = 'subject.php';
	}
	
	if (is_null($Selection)){
		$Selection = 'all';
	}
	
	$Content = '';
	
	$Subjects = new clsSubjects();
	switch ($Selection){
		case 'this':
			global $SetId;
			if (isset($SetId)){
				$Subjects->SetId = $SetId;
			}
			break;
		case 'reference':
			$Subjects->ContextId = 1;
			break;
	}
	
	if (is_null($SubjectIds)){
		$SubjectIds = $Subjects->getClass($DictId, $ClassId);
	}
	$Content .= '<div class="sdgreybox">';
	
		
	$arrCols = array();
	pnlGetClassHeadings($DictId, $ClassId, $arrCols);
	$NumHeadingRows = pnlGetNumHeadingRows($arrCols) + 1;
	
	
	$Content .= '<table class="list"><thead>';
//	$Content .= "<tr><th  rowspan = '$NumHeadingRows'>dictionary</th><th rowspan = '$NumHeadingRows'>class</th><th rowspan = '$NumHeadingRows'>id</th></tr>";
	$Content .= "<tr><th rowspan = '$NumHeadingRows'>id</th></tr>";
	
	$Content .= pnlClassHeadings($arrCols);
		
	$Content .= '</tr></thead>';

	foreach ($SubjectIds as $SubjectId){

		$objSubject = new clsSubject($SubjectId);		
		$objSubjectClass = $Dicts->getClass($objSubject->ClassDictId,$objSubject->ClassId);
		$Content .= "<tr>";

//		$Content .= "<td rowspan = '2'>$objSubjectClass->DictId</td>";
//		$Content .= "<td rowspan = '2'>$objSubjectClass->Label</td>";

		
		
		$UrlParams = array();
		$UrlParams[$FieldName] = $SubjectId;
		$ReturnUrl = UpdateUrl($UrlParams,$ReturnUrl);

		$Content .= "<td rowspan = '2'><a href='$ReturnUrl'>$SubjectId</a></td>";

		$Content .= "</tr>";	
				
		reset($arrCols);
		
		$Content .= pnlSubjectRow($arrCols, $objSubject);
		
	}	
	
	
    $Content .= '</table>';
    $Content .= '</div>';
    
    return $Content;
}


function pnlGetClassHeadings($DictId, $ClassId, &$arrCols){

	global $Dicts;
	
	foreach ($Dicts->ClassProperties($DictId, $ClassId) as $ClassProp){
		
		$objProperty = $Dicts->getProperty($ClassProp->PropDictId, $ClassProp->PropId );
		
		$arrCol = array();
		$arrCol['property'] = $objProperty;
		
		$arrCols[] = $arrCol;
		
	}
	
	pnlGetRelHeadings($DictId, $ClassId, $arrCols);

}

function pnlGetRelHeadings($DictId, $ClassId, &$arrCols){

	global $Dicts;
	
	foreach($Dicts->RelationshipsFor($DictId, $ClassId) as $objRel){
		if ($objRel->Cardinality == 'extend'){			
			$arrCol = array();
			$arrCol['relationship'] = $objRel;
			$arrCol['property'] = $Dicts->getClass($objRel->ObjectDictId, $objRel->ObjectId);
			
			$arrCol['cols'] = array();
			
			pnlGetClassHeadings($objRel->ObjectDictId, $objRel->Id, $arrCol['cols']);			
		
			$arrCols[] = $arrCol;

		}		
	}
}

function pnlClassHeadings($arrCols){

	$NumberOfRows = pnlGetNumHeadingRows($arrCols);
	
	$Content = '';
	for ($Level=1; $Level < ($NumberOfRows+1); $Level++){
		$Content .= "<tr>";
		$Content .= pnlClassHeadingRow($arrCols, $Level, $NumberOfRows);
		$Content .= "</tr>";
	}
	
	return $Content;
	
}


function pnlGetNumHeadingRows($arrCols){

	$StartLevel = 1;	
	$NextLevel = 0;
	
	foreach ($arrCols as $arrCol){
		if (isset($arrCol['cols'])){			
			$ThisNextLevel = pnlGetNumHeadingRows($arrCol['cols']);
			if ($ThisNextLevel > $NextLevel){
				$NextLevel = $ThisNextLevel;				
			}
		}
	}
	
	return ( $StartLevel + $NextLevel);

}


function pnlGetNumHeadingCols($arrCol){

	$NumCols = 0;

	if (isset($arrCol['cols'])){
		foreach ($arrCol['cols'] as $arrSubCol){
			$NumCols = $NumCols + pnlGetNumHeadingCols($arrSubCol);
		}
	}
	
	if ($NumCols == 0){
		$NumCols = 1;
	}
	
	return $NumCols;

}


function pnlClassHeadingRow($arrCols, $Level, $MaxLevels = 1){
	
	$Content = '';

	reset ($arrCols);

	$arrCols = pnlGetColsForLevel($arrCols, $Level);
	
	foreach ($arrCols as $arrCol){

		$RowSpan = 1;
		if (!isset($arrCol['cols'])){
			$RowSpan = $MaxLevels - $Level + 1;
		}

		$ColSpan = pnlGetNumHeadingCols($arrCol);
		
		$Content .= "<th";
		
		if ($RowSpan > 1){
			$Content .= " rowspan = '$RowSpan' ";
		}		

		if ($ColSpan > 1){
			$Content .= " colspan = '$ColSpan' ";
		}		
			
		$Content .= ">";

		if (isset($arrCol['relationship'])){
			$Content .= $arrCol['relationship']->Label.' ';
		}
		
		if (isset($arrCol['property'])){
			$Content .= $arrCol['property']->Label;
		}
		$Content .= "</th>";
		
	}	

	return $Content;
}

function pnlGetColsForLevel($arrCols, $Level = 1, $ThisLevel = 1){

	$NewArrCols = array();

	if ($ThisLevel == $Level){	
		return $arrCols;
	}
	
	foreach ($arrCols as $arrCol){
		if (isset($arrCol['cols'])){
			$NewArrCols = array_merge($NewArrCols,pnlGetColsForLevel($arrCol['cols'],$Level,$ThisLevel + 1));
		}
	}
	
	return $NewArrCols;
	
}


function pnlSubjectRow($arrCols, $objSubject){

	global $Dicts;
	
	$Content = '';
	$Content .= "<tr>";
	
	$Content .= pnlSubjectCols($arrCols, $objSubject);	

	$Content .= "</tr>";

	return $Content;
}

function pnlSubjectCols($arrCols, $objSubject, $LinkSeq = 1){

// does not yet cope with many extended relationships , or , 'one' links - but could do.	
	
	
	global $Dicts;
	
	$Content = '';
	foreach ($arrCols as $arrCol){
		
		
		if (isset($arrCol['relationship'])){
			
			$objLink = null;
			$optSeq = 0;
			foreach ($objSubject->Links as $optLink){
				$optSeq = $optSeq + 1;
				if (!is_null($optLink->ObjectId)){
					if ($optSeq == $LinkSeq){
						$objLink = $optLink;
					}
				}
			}
			if (!is_null($objLink)){
				$objObject = new clsSubject($objLink->ObjectId);
				$objObject->AsAtDocumentId = $objSubject->AsAtDocumentId;
				$Content .= pnlSubjectCols($arrCol['cols'], $objObject);
			}
			else
			{
				$ColSpan = pnlGetNumHeadingCols($arrCol);
				$Content .= "<td colspan='$ColSpan'/>";				
			}
			
		}
		elseif (isset($arrCol['property'])){
			
			$Content .= "<td>";			
			
			$objProperty = $arrCol['property'];
		
			foreach ($objSubject->Attributes as $PropDictId=>$PropAtts){
				foreach ($PropAtts as $PropId=>$Atts){
				
					if (isset($Atts[0])){
						
						$useAtt = false;
						
						if ($Atts[0]->DictId == $objProperty->DictId){
							if ($Atts[0]->PropId == $objProperty->Id){
								$useAtt = true;
							}
						}
						if (!$useAtt){
							foreach ( $Dicts->SubProperties($objProperty->DictId, $objProperty->Id) as $SubProp){
								if ($Atts[0]->DictId == $SubProp->DictId){
									if ($Atts[0]->PropId == $SubProp->Id){
										$useAtt = true;
									}
								}
							}
						}
						
						if ($useAtt){
							foreach ($Atts as $objAtt){	
								
								switch ($objProperty->Type){							
									case 'simple':
										$Content .= truncate(make_links($objAtt->Value))."<br/>";
										break;
									case 'complex':
										$Content .= pnlComplexValue($objAtt)."<br/>";
										break;
								}
									
							}
						}
						
					}
				}
			}
			$Content .= "</td>";			
		}
					
	}
	
	return $Content;
	
}
?>