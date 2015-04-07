<?php

function frmSelectClass($Selection=null, $GroupId = null){

	global $System;
	global $Dicts;
	
	$Content = "";

	
	$Content .= "<div class='sdbluebox'>";

	$DictFieldName = "classdictid";
	$ClassFieldName = "classid";
	
	
	$opts = array();

	if (is_null($Selection)){
		$Selection = 'published';
	}
		
	switch ($Selection){
		case "this":
			if (is_null($GroupId)){
				throw new exception("Group not specified");
			}
			foreach ($Dicts->Dictionaries as $optDict){
				if ($optDict->GroupId == $GroupId){					
					foreach ($optDict->Classes as $optClass){					
						$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
					}
				}
			}
			break;
			
		case "my":
			if (!$System->LoggedOn){
				throw new exception("not logged on");
			}
			foreach ($Dicts->Dictionaries as $optDict){
				
				$optGroup = new clsGroup($optDict->GroupId);
				if (!$optGroup->canEdit){
					continue;
				}				
				
				foreach ($optDict->Classes as $optClass){
					$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
				}
			}
			break;
			
		case "published":
			
			foreach ($Dicts->Dictionaries as $optDict){

				if (!$optDict->Publish){
					continue;
				}				

				foreach ($optDict->Classes as $optClass){
					$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
				}
			}

			break;
			
	}
		
		
	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Dictionary</th><th>Concept</th><th>Class</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
		
		foreach ($opts as $optDictId=>$optConcepts){
					
			$numRows = 1;
			foreach ($optConcepts as $optClasses){
				$numRows = $numRows + 1;
				foreach ($optClasses as $optClass){
					$numRows = $numRows + 1;
				}
			}
			
			$Content .= "<tr>";			
			$Content .= "<td rowspan='$numRows'>";
				$optDict = $Dicts->Dictionaries[$optDictId];
				$Content .= $optDict->Name;
			$Content .= "</td>";
			$Content .= "</tr>";
		
		
			foreach ($optConcepts as $optConcept=>$optClasses){

				$Content .= "<td rowspan='".(count($optClasses) + 1)."'>";
					$Content .= $optConcept;
				$Content .= "</td>";
				$Content .= "</tr>";
					
				foreach ($optClasses as $optClass){
					
					$UrlParams = array();
					$UrlParams[$DictFieldName] = $optClass->DictId;
					$UrlParams[$ClassFieldName] = $optClass->Id;
					$ReturnUrl = UpdateUrl($UrlParams);

					$Content .= "<tr><td><a href='$ReturnUrl'>".$optClass->Label."<a></td>";
					$Content .= "<td>".nl2br($optClass->Description)."</td>";
					$Content .= "</tr>";
				}
			}
	
		}
		$Content .= "</tbody>";
		$Content .= "</table>";
		
	}
	
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}

?>