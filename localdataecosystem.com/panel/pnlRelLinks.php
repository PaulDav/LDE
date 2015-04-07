<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsData.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlRelLinks( $Subject, $Rel, $Inverse = false){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem;
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}

	$Content = '';

	switch ($Inverse){
		case true:
			$Class = $Dicts->getClass($Rel->SubjectDictId, $Rel->SubjectId);
			break;
		default:
			$Class = $Dicts->getClass($Rel->ObjectDictId, $Rel->ObjectId);
			break;		
	}
	
	$ClassDict = $Dicts->Dictionaries[$Class->DictId];

	$Content .= '<div class="sdgreybox">';
	$Content .= '<table class="list"><thead><tr>';
	
	$Content .= "<th>Eff From</th><th>Eff To</th>";

	$arrPropertyCols = array();
	
	foreach ($Dicts->RelProperties($Rel->DictId, $Rel->Id) as $HasProp){
		$objProperty = $Dicts->getProperty($HasProp->PropDictId, $HasProp->PropId);		
		$Content .= "<th>".$objProperty->Label."</th>";
		$arrPropertyCols[] = $objProperty;		
	}

	
//	$Content .= "<th>dictionary</th><th>class</th><th>id</th>";	
	$Content .= "<th>id</th>";	
	
	foreach ($Dicts->ClassProperties($Class->DictId, $Class->Id) as $ClassProp){		
		$objProperty = $Dicts->getProperty($ClassProp->PropDictId, $ClassProp->PropId);		
		$Content .= "<th>".$objProperty->Label."</th>";		
		$arrPropertyCols[] = $objProperty;		
	}
		
	
	$Content .= '</tr></thead>';
	
	foreach ($Subject->Links as $objLink){
		if ($objLink->RelDictId == $Rel->DictId){
			if ($objLink->RelId == $Rel->Id){

				switch ($Inverse){
					case true:
						$ObjectId = $objLink->SubjectId;
						break;
					default:
						$ObjectId = $objLink->ObjectId;
						break;
				}
				
				$objObject = new clsSubject($ObjectId);
				
				$ObjectClassDictId = $objObject->ClassDictId;
				$objObjectClassDict = $Dicts->Dictionaries[$ObjectClassDictId];				
				$ObjectClassId = $objObject->ClassId;
				$objObjectClass = $Dicts->getClass($ObjectClassDictId, $ObjectClassId);
				
				$Content .= "<tr>";

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
				
				reset($arrPropertyCols);
				
				foreach ($Dicts->RelProperties($Rel->DictId, $Rel->Id) as $HasProp){
					
					$objProperty = current($arrPropertyCols);
					
					$Content .= "<td>";
					
										
					foreach ($objLink->Attributes as $PropDictId=>$PropAtts){
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
												$Content .= make_links($objAtt->Value)."<br/>";
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
					
					next($arrPropertyCols);
					
				}
				
								
				$UrlParams = array();
				$UrlParams['subjectid'] = $objObject->Id;
				$ReturnUrl = UpdateUrl($UrlParams);

				$Content .= "<td><a href='$ReturnUrl'>".$objObject->Id."</a></td>";
				
				foreach ($Dicts->ClassProperties($Class->DictId, $Class->Id) as $ClassProp){
					
					$objProperty = current($arrPropertyCols);
					
					$Content .= "<td>";
					
					if (is_object($objProperty)){
					
						foreach ($objObject->Attributes as $PropDictId=>$PropAtts){
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
													$Content .= make_links($objAtt->Value)."<br/>";
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
					}
					
					$Content .= "</td>";
					next($arrPropertyCols);
					
				}
				$Content .= "</tr>";
				
			}
		}
	}
	
		
    $Content .= '</table>';
    $Content .= '</div>';
    
    
    return $Content;
}


?>