<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');
require_once(dirname(__FILE__).'/../class/clsDict.php');


require_once(dirname(__FILE__).'/../viz/vizMap.php');


function funVisualize($SubjectIds = array(), $Page){
	
	$Content = "";

	global $System;
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}

	$Classes = funVisualizeClasses($SubjectIds);	
	
	foreach ($Classes as $ClassDictId=>$ClassIds){
		foreach ($ClassIds as $ClassId=>$Subjects){
			
			if ($objClass = $Dicts->getClass($ClassDictId, $ClassId)){
				if (!is_null($objClass->Viz)){					
					if (isset($System->Config->Visualizers[$objClass->Viz->TypeId])){
						$objVizType = $System->Config->Visualizers[$objClass->Viz->TypeId];
						
						$VizClass = $objVizType->Class;
						if (class_exists($VizClass)){
							$objViz = new $VizClass;						
							
							foreach ($Subjects as $VizSubjectId=>$Subject){
								$arrParamValues = array();
								$Subject->getAttributes();
								if (is_array($Subject->Attributes)){
									foreach ($Subject->Attributes as $PropDictId=>$DictAtts){
										foreach ($DictAtts as $PropId=>$PropAtts){
											foreach ($PropAtts as $objAtt){
												foreach ($objClass->Viz->Params as $ParamNum=>$objVizParam){
													if ($objVizParam->PropDictId == $objAtt->DictId){
														if ($objVizParam->PropId == $objAtt->PropId){
															$arrParamValues[$ParamNum] = $objAtt->Value;
														}
													}
												}
											}
										}
									}
								}
								$ParamsSet = true;
								$ParamNum = 0;
								foreach ($objVizType->Params as $objParamType){
									$ParamNum = $ParamNum + 1;
									if (!isset($arrParamValues[$ParamNum])){
										$ParamsSet = false;
									}
								}
								if ($ParamsSet){
									$objViz->addSubject($VizSubjectId, $arrParamValues);
								}
							}
						}
						
						$objViz->Generate();
						
						$Page->Script .= $objViz->Script;
						$Content .= $objViz->Html;
						
					}
					
				}
			}
			
		}
	}
	
	return $Content;

}



function funVisualizeClasses($SubjectIds = array(), $Classes=null, $VizSubjectId=null){

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}

	if (is_null($Classes)){
		$Classes = array();
	}

	foreach ($SubjectIds as $SubjectId){
		
		if (is_null($VizSubjectId)){
			$ThisVizSubjectId = $SubjectId;
		}
		else
		{
			$ThisVizSubjectId = $VizSubjectId;
		}
		
		$objSubject = new clsSubject($SubjectId);
		if ($objClass = $Dicts->getClass($objSubject->ClassDictId, $objSubject->ClassId)){
			if (!is_null($objClass->Viz)){
				$Classes[$objClass->DictId][$objClass->Id][$ThisVizSubjectId]=$objSubject;
			}
		}				
		
		$arrLinkSubjectIds = array();
		foreach ($objSubject->getStatements() as $Statement){
			if ($Statement->TypeId == '300'){
				if ($Statement->SubjectId == $SubjectId){
					$RelDictId =  $Statement->LinkDictId;
					$RelId = $Statement->LinkId;
					if ($objRel = $Dicts->getRelationship($RelDictId, $RelId)){
						if ($objRel->Cardinality == 'extend'){
							$arrLinkSubjectIds[$Statement->ObjectId] = $Statement->ObjectId;
						}
					}
				}
			}
		}
		$Classes = funVisualizeClasses($arrLinkSubjectIds, $Classes, $ThisVizSubjectId);
	}
	
	return $Classes;
	
}
