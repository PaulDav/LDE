<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsProfile.php');

require_once(dirname(__FILE__).'/../class/clsDocument.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once('pnlClassSubjects.php');

Function pnlDocLinks($LinkForms = array()){

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}
	global $Profiles;
	if (!isset($Profiles)){
		$Profiles = new clsProfiles;
	}
	global $Shapes;
	if (!isset($Shapes)){
		$Shapes = new clsShapes;
	}
		
	if (!isset($LinkForms[1])){
		return;
	}
	
	$Content = '';

	$objLinkForm = $LinkForms[0];
	$DocId = $objLinkForm->ParentForm->DocumentId;
	$SubjectId = $objLinkForm->ParentForm->SubjectId;
	$RelId = $objLinkForm->ShapeLinkId;
	
	$objClass = $objLinkForm->ObjectForm->Class;
	$objRel = $objLinkForm->Relationship;
	
		
	$Content .= '<table class="sdgreybox"><thead><tr>';
	
	$Content .= "<th>Link Id</th>";

	$Content .= "<th>Eff From</th><th>Eff To</th>";

	
	foreach ($Dicts->RelProperties($objRel->DictId, $objRel->Id) as $HasProp){
		if (!isset($Dicts->Dictionaries[$HasProp->PropDictId])){
			throw new Exception("Unknown Property Dictionary");
		}
		$PropDict = $Dicts->Dictionaries[$HasProp->PropDictId];
		if (!isset($PropDict->Properties[$HasProp->PropId])){
			throw new Exception("Unknown Property");
		}
		$Property = $PropDict->Properties[$HasProp->PropId];
		
		$Content .= "<th>".$Property->Label."</th>";
		
	}

	$Content .= "<th>Link to</th>";
	
	foreach ($Dicts->ClassProperties($objClass->DictId, $objClass->Id) as $ClassProp){
		
		if (!isset($Dicts->Dictionaries[$ClassProp->PropDictId])){
			throw new Exception("Unknown Property Dictionary");
		}
		$PropDict = $Dicts->Dictionaries[$ClassProp->PropDictId];
		if (!isset($PropDict->Properties[$ClassProp->PropId])){
			throw new Exception("Unknown Property");
		}
		$Property = $PropDict->Properties[$ClassProp->PropId];
		
		$Content .= "<th>".$Property->Label."</th>";
		
	}

	$Content .= '</tr></thead>';
	
	$Content .= "<tbody>";

	$Seq = 0;

	
	foreach ($LinkForms as $objNextLinkForm){
		if (!is_null($objNextLinkForm->ObjectForm->SubjectId)){
			$Seq = $Seq + 1;
			
//			$Statements = $objNextLinkForm->Statements;
			
			$Content .= "<tr>";
			
			$ReturnUrl = "document.php?docid=".$DocId."&subjectid=$SubjectId&relid=$RelId&seq=$Seq";
			$Content .= "<td><a href='$ReturnUrl'>".$objNextLinkForm->Statement->Id."</a></td>";

			$objLink = new clsLink($objNextLinkForm->Statement->Id);
			$objLink->AsAtDocumentId = $objNextLinkForm->ParentForm->DocumentId;
			
			$Content .= "<td>";			
			if (!is_null($objLink->EffectiveFrom)){
				$Content .= convertDate($objLink->EffectiveFrom);
			}
			$Content .= "</td>";

			$Content .= "<td>";			
			if (!is_null($objLink->EffectiveTo)){
				$Content .= convertDate($objLink->EffectiveTo);
			}
			$Content .= "</td>";
						
			foreach ($Dicts->RelProperties($objRel->DictId, $objRel->Id) as $HasProp){
				$Content .= "<td>";

				
				foreach ($objLink->Attributes as $DictAtts){
					foreach ($DictAtts as $PropAtts){
						foreach ($PropAtts as $objAtt){
				
							$useProp = false;
							if ($HasProp->PropDictId == $objAtt->DictId){
								if ($HasProp->PropId == $objAtt->PropId){
									$useProp = true;
								}								
							}
							if (!$useProp){
								foreach ($Dicts->SubProperties($HasProp->PropDictId, $HasProp->PropId) as $objSubProp){
									if ($objSubProp->DictId = $objAtt->DictId){
										if ($objSubProp->Id = $objAtt->PropId){
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
										$Content .= pnlSubjectComplexAttribute( $objAtt).'<br/>';						
										break;
								}								
							}
						}
					}
				}
								
				$Content .= "</td>";			
			}
			
			
			$objSubject = new clsSubject($objNextLinkForm->ObjectForm->SubjectId);
			$objSubject->AsAtDocumentId = $DocId;
			
			$Content .= "<td>".$objSubject->Id."</td>";
			
			foreach ($Dicts->ClassProperties($objClass->DictId, $objClass->Id) as $ClassProp){
				$Content .= "<td>";
				
				
				
				foreach ($objSubject->Attributes as $DictAtts){
					foreach ($DictAtts as $PropAtts){
						foreach ($PropAtts as $objAtt){
				
							$useProp = false;
							if ($ClassProp->PropDictId == $objAtt->DictId){
								if ($ClassProp->PropId == $objAtt->PropId){
									$useProp = true;
								}								
							}
							if (!$useProp){
								foreach ($Dicts->SubProperties($ClassProp->PropDictId, $ClassProp->PropId) as $objSubProp){
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
			$Content .= "</tr>";		
			
		}
	}
	
	$Content .= "</tbody>";
	
    $Content .= '</table>';
	    
    return $Content;
}

?>