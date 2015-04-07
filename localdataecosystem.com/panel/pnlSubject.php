<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsData.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlSubject( $SubjectId, $AsAtDocumentId = null){

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}

	$Subject = new clsSubject($SubjectId);
	if ($Subject === false){
		return;
	}
	if (!is_object($Subject)){
		return;
	}
	
	$Subject->AsAtDocumentId = $AsAtDocumentId;
		
	$SubjectDictId = $Subject->ClassDictId;
	$SubjectClassId = $Subject->ClassId;

	if (is_null($SubjectClassId)){
		return;
	}
	
	if (!isset($Dicts->Dictionaries[$SubjectDictId])){
		throw new exception("Unknown Dictionary");
	}
	$SubjectDict = $Dicts->Dictionaries[$SubjectDictId];
	if (!isset($Dicts->Dictionaries[$SubjectDictId]->Classes[$SubjectClassId])){
		throw new exception("Unknown Class");
	}
	$SubjectClass = $Dicts->Dictionaries[$SubjectDictId]->Classes[$SubjectClassId];
	
	$Content = '';
	$Content .= '<table>';
	$Content .= "<tr><th>id</th><td><a href='subject.php?subjectid=$SubjectId'>$SubjectId</a></td><th>dictionary</th><td><a href='dict.php?dictid=".$SubjectDict->Id."'>".$SubjectDict->Name."</a></td><th>class</th><td><a href='class.php?dictid=".$SubjectClass->DictId."&classid=".$SubjectClass->Id."'>".$SubjectClass->Label."</a></td></tr>";
	$Content .= "</table>";

	$Content .= '<div class="sdgreybox">';	
	
	$Content .= '<h3>'.$SubjectClass->Label.'</h3>';
	
	$Content .= '<table class="form">';
	
	foreach ($Subject->Attributes as $PropDictId=>$DictAtts){
		foreach ($DictAtts as $PropId=>$Atts){
		
			if (isset($Atts[0])){
				$Content .= "<tr>";		
				$Content .= "<th>".$Atts[0]->Label."</th>";
	
				$Content .= "<td>";		
				foreach ($Atts as $objAtt){
					switch ($objAtt->Property->Type){
						case 'simple':
							$Content .= nl2br(make_links($objAtt->Value))."<br/>";
							break;
						case 'complex':
							$Content .= pnlSubjectComplexAttribute( $objAtt).'<br/>';						
							break;
					}
				}
				$Content .= "</td>";			
				$Content .= "</tr>";			
			}
		}
	}
	
	foreach ($Subject->Links as $objLink){
		$objRelationship = $Dicts->getRelationship($objLink->RelDictId, $objLink->RelId);
		if (is_object($objRelationship)){
			if ($objRelationship->Cardinality == 'extend'){
				if ($objLink->SubjectId == $SubjectId){
					$Content .= "<tr>";		
					$Content .= "<th>".$objRelationship->Label."</th>";
					$Content .= '<td>'.pnlSubject( $objLink->ObjectId, $AsAtDocumentId ).'</td>';
					$Content .= "</tr>";					
				}
			}			
		}
	}
	
			
    $Content .= '</table>';
    $Content .= '</div>';
    
    return $Content;
}


Function pnlSubjectComplexAttribute( $Attribute){
	
	$Content = "";
	
	$Content .= '<table class="sdgreybox">';
	
	if (is_array($Attribute->ComplexAttributes)){
		foreach ($Attribute->ComplexAttributes as $objComplexAttribute){
			
			$Content .= "<tr>";		
			$Content .= "<th>".$objComplexAttribute->Label."</th>";

			$Content .= "<td>";		
			switch ($objComplexAttribute->Property->Type){
				case 'simple':
					$Content .= make_links($objComplexAttribute->Value)."<br/>";
					break;
				case 'complex':
					$Content .= pnlSubjectComplexAttribute( $objComplexAttribute).'<br/>';						
					break;
			}
			$Content .= "</td>";			
			$Content .= "</tr>";	
		}
	}
	
    $Content .= '</table>';
	
    return $Content;
    
}



?>