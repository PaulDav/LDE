<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
//	require_once("function/utils.inc");
	
//	require_once("panel/pnlSelection.php");	

	
	require_once("panel/pnlProfile.php");
	
	require_once("class/clsDict.php");	
	require_once("class/clsShape.php");	

	require_once("class/clsProfile.php");	
	
		
	define('PAGE_NAME', 'selection');

	session_start();
		
	$System = new clsSystem();
	
		
	$Page = new clsPage();
	
	if ($js = file_get_contents('java/jquery.js')){
		$Page->Script .= $js;
	}
		

	try {

		$Dicts = new clsDicts();
		$Shapes = new clsShapes();
				
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$SelId = null;
		$objSelection = null;
		
		$Type = 'profile';
		$TypeIdFieldName = null;
		$TypeId = null;
		$GroupId = null;
		$Types = null;
		$objType = null;
		$objShape = null;
		
		$StartShapeClass = null;
		
		if (isset($_REQUEST['selid'])){
			$SelId = $_REQUEST['selid'];
			
			if (!isset($Shapes->Selections[$SelId])){
				throw new Exception("Unknown Selection");
			}
			$objSelection = $Shapes->Selections[$SelId];	
		}
		

		if (isset($_REQUEST['profileid'])){
			$ProfileId = $_REQUEST['profileid'];
			$Type = 'profile';
			$TypeId = $_REQUEST['profileid'];
		}
		

		switch ($Type){
			case 'profile':
				$Types = new clsProfiles();
				break;
			default:
				throw new exception('invalid type');
				break;
		}
		
				
		if (!isset($Types->Items[$TypeId])){
			throw new Exception('Unknown Type Id');
		}		
		$objType = $Types->Items[$TypeId];		
		$ShapeId = $objType->ShapeId;
		if (!is_null($ShapeId)){
			$objShape = $Shapes->Items[$ShapeId];
		}

		$GroupId = $objType->GroupId;
						
		$selFields = array();
		if (!is_null($objSelection)){
			$selFields = MakeFields($objSelection);
		}
		
		$Group = new clsGroup($GroupId);
		if ($Group->canView === false){
			throw new exception("You cannot view this Group");
		}
		
		$Page->Title = $Mode." selection";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$ModeOk = false;
		switch ($Mode){
			case 'edit':
			case 'new':
				if ($objType->canEdit){
					$ModeOk = true;
				}
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}

		switch ($Type){
			case 'profile':
				$PanelB .= pnlProfile($TypeId);
				break;				
		}
		
		switch ($Mode){
			case 'edit':
			case 'new':
				
				$PanelB .= '<form method="post" action="doSelection.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				$PanelB .= "<input type='hidden' name='type' value='$Type'/>";
				$PanelB .= "<input type='hidden' name='typeid' value='$TypeId'/>";
				$PanelB .= "<input type='hidden' name='selectionof' value='$ShapeId'/>";
				
				$PanelB .= "<div class='sdbluebox'>";				
				$PanelB .= frmSelection($selFields);
				$PanelB .= "<input type='submit' value='Update Selection'/>";
				$PanelB .= "</div>";
				
								
				$PanelB .= '</form>';

				break;
				
		}
		
		
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;
	 	
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();
		
	
function frmSelection($Fields, $objShapeClass=null){

	global $System;
	global $Dicts;
	global $objShape;
	
	if (is_null($objShapeClass)){
		$objShapeClass = $objShape->Selection->ShapeClass;
	}

	if (is_null($objShapeClass)){
		return;
	}
	
	$Content = '';
	
	$Content .= "<table>";
	$Content .= "<tr><th>Class</th><td>".$objShapeClass->Class->Label."</td></tr>";
	$Content .= "</table>";
	
	$Content .= "<div class='tab'>";
	$Content .= "<h3>Properties</h3>";
	$Content .= "<table>";
	$Content .= "<thead><tr><th/><th>Selected?</th></tr></thead>";
	
	$PropNum = 0;
	foreach ($objShapeClass->ShapeProperties as $objShapeProperty){
		if ($objShapeProperty->Selected === true){
			$PropNum = $PropNum + 1;
			
			$Content .= "<tr><td>".$objShapeProperty->Property->Label."</td>";
			
			$Content .= "<td>";
			$FieldName = "class_".$objShapeClass->Id."_prop_".$PropNum."_sel";
			$Content .= "<input type='checkbox' name='$FieldName' value='selected' ";
	
			if (isset($Fields['class'][$objShapeClass->Id]['properties'][$PropNum]['sel'])){
				$Content .= " checked='checked' ";
			}
			
			$Content .= "/>";
			$Content .= "</td>";
			
			$Content .= "</tr>";

			if (count($objShapeProperty->ShapeProperties) > 0){
				$ComplexFields = array();
				if (isset($Fields['class'][$objShapeClass->Id]['properties'][$PropNum])){
					$ComplexFields = $Fields['class'][$objShapeClass->Id]['properties'][$PropNum];
				}
				$Content .= frmComplexSelection($ComplexFields, "class_".$objShapeClass->Id."_prop_".$PropNum, $objShapeProperty->ShapeProperties);
			}
			
		}
	}
	$Content .= "</table>";
	
	if (count($objShapeClass->ShapeLinks) > 0){
	
		$Content .= "<h3>Links</h3>";
			
		$Content .= "<div class='tab'>";
		foreach ($objShapeClass->ShapeLinks as $objShapeLink){
			
			if (is_null($objShapeLink->Relationship)){
				continue;
			}
			if (is_null($objShapeLink->ShapeClass)){
				continue;
			}
			
			$Content .= "<table>";
			$Content .= "<tr><th>Relationship</th><td>";
			switch ($objShapeLink->Inverse){
				case true;
					$Content .= $objShapeLink->Relationship->InverseLabel;
					break;
				default;
					$Content .= $objShapeLink->Relationship->Label;
					break;
			}
			$Content .= "</td></tr>";
			$Content .= "<tr><th>Cardinality</th><td>".$objShapeLink->Cardinality."</td></tr>";
			$Content .= "</table>";
			$Content .= frmLinkSelection($Fields, $objShapeLink);
			
			
			$Content .= "<div class='tab'>";
			$Content .= frmSelection($Fields, $objShapeLink->ShapeClass);
			$Content .= "</div>";
		}
		$Content .= "</div>";
	}
	
	$Content .= "</div>";
	
	return $Content;
}




function frmComplexSelection($Fields, $ParentFieldName = '', $objShapeProperties=array(), $Level = 1){
	
	global $System;
	global $Dicts;
	global $objShape;
		
	$Content = '';
		
	$PropNum = 0;
	foreach ($objShapeProperties as $objShapeProperty){
		if ($objShapeProperty->Selected === true){
			$PropNum = $PropNum + 1;
			
			$Padding = ($Level * 30).'px';			
			$Content .= "<tr><td style='padding-left:$Padding'>".$objShapeProperty->Property->Label."</td>";
			
			$Content .= "<td>";
			$FieldName = $ParentFieldName."_prop_".$PropNum."_sel";
			$Content .= "<input type='checkbox' name='$FieldName' value='selected' ";

			if (isset($Fields['properties'][$PropNum]['sel'])){
				$Content .= " checked='checked' ";
			}
			
			$Content .= "/>";
			$Content .= "</td>";
			
			$Content .= "</tr>";

			if (count($objShapeProperty->ShapeProperties) > 0){
				$ComplexFields = array();
				if (isset($Fields['properties'][$PropNum])){
					$ComplexFields = $Fields['properties'][$PropNum];
				}
				$Content .= frmComplexSelection($ComplexFields, $ParentFieldName."_prop_".$PropNum, $objShapeProperty->ShapeProperties, $Level + 1);
			}
			
		}
	}
	return $Content;
}


function frmLinkSelection($Fields, $objShapeLink=null){

	global $System;
	global $Dicts;
	global $objShape;
	
	if (is_null($objShapeLink)){
		return;
	}
	
	$Content = '';

	if (count($objShapeLink->ShapeProperties) > 0){
		
		$Content .= "<h3>Link Properties</h3>";
		$Content .= "<table>";
		$Content .= "<thead><tr><th/><th>Selected?</th></tr></thead>";
		
		$PropNum = 0;
		foreach ($objShapeLink->ShapeProperties as $objShapeProperty){
			if ($objShapeProperty->Selected === true){
				$PropNum = $PropNum + 1;
				
				$Content .= "<tr><td>".$objShapeProperty->Property->Label."</td>";
				
				$Content .= "<td>";
				$FieldName = "link_".$objShapeLink->Id."_prop_".$PropNum."_sel";
				$Content .= "<input type='checkbox' name='$FieldName' value='selected' ";
		
				if (isset($Fields['link'][$objShapeLink->Id]['properties'][$PropNum]['sel'])){
						$Content .= " checked='checked' ";
				}
				
				$Content .= "/>";
				$Content .= "</td>";
				
				$Content .= "</tr>";
			}
		}
		$Content .= "</table>";
	}	
	
	return $Content;
}



function MakeFields($objSel){

	global $System;
	global $Shapes;
	
	$Fields = array();
	$FieldNamePrefix = "";

	$objShapeClass = $objSel->Selection->ShapeClass;
	
	if (!is_null($objShapeClass)){	
		$objSuperShapeClass = $Shapes->SuperShapeClass($objShapeClass);
	
		if (!$objSuperShapeClass === false){
			$Fields = MakeSelFields($objShapeClass, $objSuperShapeClass);
		}
	}
		
	return $Fields;
	
}


function MakeSelFields($objShapeClass, $objSuperShapeClass, $arrSelFields = array()){
	
	$PropNum = 0;
	foreach ($objSuperShapeClass->ShapeProperties as $objSuperShapeProperty){
		if ($objSuperShapeProperty->Selected === true){
			$PropNum = $PropNum + 1;
//			$FieldName = 'class_'.$objSuperShapeClass->Id.'_prop_'.$PropNum;
			
			foreach ($objShapeClass->ShapeProperties as $objShapeProperty){
				if ($objShapeProperty->Property == $objSuperShapeProperty->Property){
					if ($objShapeProperty->Selected === true){
						$arrSelFields['class'][$objSuperShapeClass->Id]['properties'][$PropNum]['sel'] = true;
						
						$ComplexFields = MakeComplexFields($objShapeProperty->ShapeProperties, $objSuperShapeProperty);
						if (count($ComplexFields) > 0){
							$arrSelFields['class'][$objSuperShapeClass->Id]['properties'][$PropNum]['properties'] = $ComplexFields;
						}						
						
						
					}
				}
			}
		}
	}
		
	foreach ($objSuperShapeClass->ShapeLinks as $objSuperShapeLink){

		foreach ($objShapeClass->ShapeLinks as $objShapeLink){
		
			if (is_null($objSuperShapeLink->Relationship)){
				continue;
			}
			
			if (!$objSuperShapeLink->Relationship == $objShapeLink->Relationship){
				continue;
			}
			if (!$objSuperShapeLink->Inverse == $objShapeLink->Inverse){
				continue;
			}
			if (is_null($objSuperShapeLink->ShapeClass)){
				continue;
			}
			if (is_null($objSuperShapeLink->ShapeClass->Class)){
				continue;
			}
			if (!$objSuperShapeLink->ShapeClass->Class == $objShapeLink->ShapeClass->Class){
				continue;
			}
				
			$PropNum = 0;
			foreach ($objSuperShapeLink->ShapeProperties as $objSuperShapeProperty){
				if ($objSuperShapeProperty->Selected === true){
					$PropNum = $PropNum + 1;
					$FieldName = 'link_'.$objSuperShapeLink->Id.'_prop_'.$PropNum;
					
					foreach ($objShapeLink->ShapeProperties as $objShapeProperty){
						if ($objShapeProperty->Property == $objSuperShapeProperty->Property){
							if ($objShapeProperty->Selected === true){
								$arrSelFields['link'][$objSuperShapeLink->Id]['properties'][$PropNum]['sel'] = true;
							}
						}
					}
				}
			}
			
			
			$arrSelFields = MakeSelFields($objShapeLink->ShapeClass, $objSuperShapeLink->ShapeClass, $arrSelFields);
		}
		
	}
	
	return $arrSelFields;
	
}



function MakeComplexFields($objShapeProperties, $objSuperShapeProperty){

	$arrSelFields = array();
	
	$PropNum = 0;
	foreach ($objSuperShapeProperty->ShapeProperties as $objSuperShapeComplexProperty){
		if ($objSuperShapeComplexProperty->Selected === true){
			$PropNum = $PropNum + 1;
	
			foreach ($objShapeProperties as $objShapeProperty){
				if ($objShapeProperty->Property == $objSuperShapeComplexProperty->Property){
					if ($objShapeProperty->Selected === true){
						$arrSelFields[$PropNum]['sel'] = true;
						$ComplexFields = MakeComplexFields($objShapeProperty->ShapeProperties, $objSuperShapeComplexProperty);
						if (count($ComplexFields) > 0){
							$arrSelFields[$PropNum]['properties'] = $ComplexFields;
						}
					}
					
				}
			}
		}
	}
		
	return $arrSelFields;
	
}
	

?>