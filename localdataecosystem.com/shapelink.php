<?php
	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");

	require_once("panel/pnlShape.php");
	
	require_once("panel/pnlGroup.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	require_once("class/clsShape.php");

	require_once("form/frmShapeProperties.php");
	
	define('PAGE_NAME', 'shapelink');

	session_start();
		
	$System = new clsSystem();
	
		
	$Page = new clsPage();
	

	try {
		
		global $Dicts;
		$Dicts = new clsDicts();

		global $Shapes;
		$Shapes = new clsShapes();
				
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
				
		$ShapeId = null;
		$ShapeLinkId = null;
		$GroupId = null;
		
		$RelDictId = null;
		$RelId = null;
		$Inverse = null;
		
		$FromShapeClassId = null;
		$ToShapeClassId = null;
		
		$objFromShapeClass = null;
		$objToShapeClass = null;
		
		$objRel = null;
		$objShape = null;
		$objShapeLink = null;
		
		$LinkEffDates = false;
		$Cardinality = null;
		
		if (isset($_REQUEST['shapeid'])){
			$ShapeId = $_REQUEST['shapeid'];
		}

		if (isset($_REQUEST['shapelinkid'])){
			$ShapeLinkId = $_REQUEST['shapelinkid'];
		}

		if (is_null($ShapeId)) {
			throw new exception("ShapeId not specified");
		}

		if (!isset($Shapes->Items[$ShapeId])){
			throw new exception("Unknown Shape Id");
		}
		$objShape = $Shapes->Items[$ShapeId];
		
		
		if (!is_null($ShapeLinkId)){
			if (!isset($objShape->ShapeLinks[$ShapeLinkId])){
				throw new Exception("Unknown Link");
			}
			$objShapeLink = $objShape->ShapeLinks[$ShapeLinkId];
			$FromShapeClassId = $objShapeLink->FromShapeClassId;
			$ToShapeClassId = $objShapeLink->ToShapeClassId;
		}
		
		
		if (isset($_REQUEST['fromshapeclassid'])){
			$FromShapeClassId = $_REQUEST['fromshapeclassid'];
		}
		if (isset($_REQUEST['toshapeclassid'])){
			$ToShapeClassId = $_REQUEST['toshapeclassid'];
		}
		
		
		if (isset($_REQUEST['reldictid'])){
			$RelDictId = $_REQUEST['reldictid'];
		}
		if (isset($_REQUEST['relid'])){
			$RelId = $_REQUEST['relid'];
		}
		if (isset($_REQUEST['inverse'])){
			if ($_REQUEST['inverse'] == 'true'){
				$Inverse = true;
			}
		}
		


		if (!is_null($FromShapeClassId)){
			if (isset($objShape->ShapeClasses[$FromShapeClassId])){
				$objFromShapeClass = $objShape->ShapeClasses[$FromShapeClassId];
			}
		}
		if (!is_null($ToShapeClassId)){
			if (isset($objShape->ShapeClasses[$ToShapeClassId])){
				$objToShapeClass = $objShape->ShapeClasses[$ToShapeClassId];
			}
		}
		
				
		
				
		$GroupId = $objShape->GroupId;

		
		
		if (!is_null($objShapeLink)){
			$objRel = $objShapeLink->Relationship;
			if (!is_null($objRel)){
				$RelDictId = $objRel->DictId;
				$RelId = $objRel->Id;
			}
			$Inverse = $objShapeLink->Inverse;
			$Cardinality = $objShapeLink->Cardinality;
			$LinkEffDates = $objShapeLink->EffDates;
		}
		

		if (!is_null($RelId)){
			if (!isset($Dicts->Dictionaries[$RelDictId]->Relationships[$RelId])){
				throw new exception('Unknown Relationship');
			}
			$objRel = $Dicts->Dictionaries[$RelDictId]->Relationships[$RelId];
		}
		
		if ($System->Session->Error){			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
					
		$Page->Title = $Mode." shape link";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objShape->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
			case 'edit':
			case 'delete':				
				if ($objShape->canEdit){
					$ModeOk = true;
				}
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}

		$Tabs .= "<li><a href='#shape'>in Shape";			    
		$TabContent .= "<div class='tabContent hide' id='shape'>";
		$TabContent .= "<h3>in Shape</h3>";				
		$TabContent .= pnlShape($ShapeId);						
		$TabContent .= "</div>";
		$Tabs .= "</a></li>";
				
		switch ($Mode){
			case 'view':
				$PanelB .= '<h3>Link</h3>';
				$PanelB .= pnlShapeLink($objShapeLink);
				if ($objShape->canEdit){
					$PanelB .= "<li><a href='shapelink.php?shapeid=$ShapeId&shapelinkid=$ShapeLinkId&mode=edit'>edit link</a></li> ";					
					$PanelB .= "<li><a href='shapelink.php?shapeid=$ShapeId&shapelinkid=$ShapeLinkId&mode=delete'>delete link</a></li> ";					
				}
				
				break;
			
			case 'new':
			case 'edit':
				
				$Tabs .= "<li><a href='#linkfrom'>Link From";			    
				$TabContent .= "<div class='tabContent hide' id='linkfrom'>";
				$TabContent .= "<h3>Link From</h3>";				
				$TabContent .= funSelectShapeClass('fromshapeclassid');								
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";

				$Tabs .= "<li><a href='#rel'>Relationship";			    
				$TabContent .= "<div class='tabContent hide' id='rel'>";
				$TabContent .= "<h3>Relationship</h3>";	

				
				$optTabs = "";
				$optTabContent = "";
					
				$optTabs .= "<li><a href='#thisrels'>Relationships in this Group</a></li>";					
				$optTabContent .= "<div class='tabContent' id='thisrels'>";
				$optTabContent .= funSelectRelationship("this");
				$optTabContent .= "</div>";
					
				$optTabs .= "<li><a href='#myrels'>Relationships in My Groups</a></li>";
				$optTabContent .= "<div class='tabContent' id='myrels'>";
				$optTabContent .= funSelectRelationship("my");
				$optTabContent .= "</div>";
					
				$optTabs .= "<li><a href='#publishedrels'>Relationships in Published Dictionaries</a></li>";
				$optTabContent .= "<div class='tabContent' id='publishedrels'>";						
				$optTabContent .= funSelectRelationship("published");
				$optTabContent .= "</div>";
					
				if (!empty($optTabs)){
					$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
				}					
					
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";
								
				$Tabs .= "<li><a href='#linkto'>Link To";			    
				$TabContent .= "<div class='tabContent hide' id='linkto'>";
				$TabContent .= "<h3>Link To</h3>";				
				$TabContent .= funSelectShapeClass('toshapeclassid');								
				$TabContent .= "</div>";
				$Tabs .= "</a></li>";

					
					
				$PanelB .= "<div class='sdbluebox'>";				
				
				$PanelB .= '<form method="post" action="doShapeLink.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				$PanelB .= "<input type='hidden' name='shapeid' value='$ShapeId'/>";
				if (!is_null($ShapeLinkId)){
					$PanelB .= "<input type='hidden' name='shapelinkid' value='$ShapeLinkId'/>";
				}

				
				$PanelB .= "<table>";
				
				$LinkSet = true;
				
				if (!is_null($objFromShapeClass)){
					$PanelB .= "<input type='hidden' name='fromshapeclassid' value='".$FromShapeClassId."'/>";
					$PanelB .= "<tr><th>Link From</th><td>".$objFromShapeClass->Class->Label.'</td></tr>';	
				}
				else
				{
					$LinkSet = false;
				} 
				
				if (is_object($objRel)){
									
					$PanelB .= "<input type='hidden' name='reldictid' value='$RelDictId'/>";
					$PanelB .= "<input type='hidden' name='relid' value='$RelId'/>";
					
					if ($Inverse === true){
						$PanelB .= "<input type='hidden' name='inverse' value='true'/>";
					}
					
					switch ($Inverse){
						case true:
							$PanelB .= "<tr><th>Relationship</th><td>".$objRel->InverseLabel.'</td></tr>';	
							$objRelClass = $Dicts->getClass($objRel->SubjectDictId, $objRel->SubjectId);
							
							break;
						default:
							$PanelB .= "<tr><th>Relationship</th><td>".$objRel->Label.'</td></tr>';	
							$objRelClass = $Dicts->getClass($objRel->ObjectDictId, $objRel->ObjectId);
							break;
					}
				}
				else
				{
					$LinkSet = false;
				} 
				
				if (!is_null($objToShapeClass)){
					$PanelB .= "<input type='hidden' name='toshapeclassid' value='".$ToShapeClassId."'/>";
					$PanelB .= "<tr><th>Link To</th><td>".$objToShapeClass->Class->Label.'</td></tr>';							
				}					
				else
				{
					$LinkSet = false;
				} 
				

				if ($LinkSet){
				
					$PanelB .= "<tr><th>Cardinality</th><td>";
					$PanelB .= "<select name='cardinality'>";
					if (isset($System->Config->RelSubCardinalities[$objRel->Cardinality])){
						foreach ($System->Config->RelSubCardinalities[$objRel->Cardinality] as $SubCardinality){
							$PanelB .= "<option";
							if ($SubCardinality == $Cardinality ){
								$PanelB .= " selected='true' ";
							}
							$PanelB .= ">".$SubCardinality."</option>";
						}						
					}
					$PanelB .= "</select>";
					
					
					$PanelB .= "<tr><th>Effective Dates?</th><td>";
					$PanelB .= "<input type='checkbox' name='linkeffdates' value='true'";
					
					if ($LinkEffDates === true){
						$PanelB .= " checked='true' ";
					}
					
					$PanelB .= '/>';
					
					
					$PanelB .= '</td></tr>';	
				}
				
				
				$PanelB .= "</table>";

				if (is_object($objRel)){
					$RelProperties = $Dicts->RelProperties($objRel->DictId, $objRel->Id);
					if (count($RelProperties) > 0){
						$PanelB .= "<div class='tab'>";					
						$PanelB .= frmShapeProperties($objShapeLink, $RelProperties);
						$PanelB .= "</div>";
					}
				}

				if ($LinkSet){
					$PanelB .= "<input type='submit' value='Update Link'/>";
				}
				
				$PanelB .= '</form>';
				$PanelB .= "</div>";
					
				break;
				
			case 'delete':
				
				$PanelB .= pnlShapeLink($objShapeLink);	
				
				$PanelB .= "<a href='doShapeLink.php?shapeid=$ShapeId&shapelinkid=$ShapeLinkId&mode=delete'>confirm delete?</a><br/>";
				
				break;
				
			
				
		}
		
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;
	 	
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();
		
	

function funSelectRelationship($Selection='this'){

	global $System;
	global $Mode;
	global $objShape;
	
	global $objFromShapeClass;
	global $objToShapeClass;
	
	global $Dicts;

	$FromClassDictId = null;
	$FromClassId = null;
	if (is_object($objFromShapeClass)){
		$FromClassDictId = $objFromShapeClass->Class->DictId;
		$FromClassId = $objFromShapeClass->Class->Id;
	}
	
	global $ReturnURL;

	$optRelList = $Dicts->RelationshipsFor($FromClassDictId, $FromClassId, null, null);
	$optInverseRelList = $Dicts->RelationshipsFor(null,null,$FromClassDictId, $FromClassId);
	

	$Content = "";

	$TabId = "rels";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$DictFieldName = "reldictid";
	$RelFieldName = "relid";
	$InverseFieldName = "inverse";
	
	
	$opts = array();
		
	switch ($Selection){
		case "this":
			foreach ($optRelList as $optRel){				
				$optDict = $Dicts->Dictionaries[$optRel->DictId];
				if (is_null($optDict->EcoSystem)){
					if ($optDict->GroupId == $objShape->GroupId){
						$opts[false][] = $optRel;
					}
				}
			}
			foreach ($optInverseRelList as $optRel){
				$optDict = $Dicts->Dictionaries[$optRel->DictId];				
				if (is_null($optDict->EcoSystem)){				
					if ($Dicts->Dictionaries[$optRel->DictId]->GroupId == $objShape->GroupId){
						$opts[true][] = $optRel;
					}
				}
			}
			
			break;
			
		case "my":

			foreach ($optRelList as $optRel){
				
				$optDict = $Dicts->Dictionaries[$optRel->DictId];
				if (is_null($optDict->EcoSystem)){				
					$optGroup = new clsGroup($optDict->GroupId);				
					if (!$optGroup->canEdit){
						continue;
					}
					$opts[false][] = $optRel;
				}
			}

			foreach ($optInverseRelList as $optRel){
				
				$optDict = $Dicts->Dictionaries[$optRel->DictId];
				if (is_null($optDict->EcoSystem)){				
					$optGroup = new clsGroup($optDict->GroupId);								
					if (!$optGroup->canEdit){
						continue;
					}
					$opts[true][] = $optRel;
				}
			}
			
			
			break;
			
		case "published":
			
			foreach ($optRelList as $optRel){
				$optDict = $Dicts->Dictionaries[$optRel->DictId];
				
				if (!$optDict->Publish === true){
					continue;
				}
				$opts[false][] = $optRel;
			}

			foreach ($optInverseRelList as $optRel){
				$optDict = $Dicts->Dictionaries[$optRel->DictId];
				
				if (!$optDict->Publish === true){
					continue;
				}
				$opts[true][] = $optRel;
			}
			
			break;
			
	}	
		
	$rels = array();	
	
	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Dictionary</th><th>Relationship</th><th>to Class</th></tr></thead>";
		$Content .= "<tbody>";
		
		foreach ($opts as $Inverse=>$optRels){
					
			foreach ($optRels as $optRel){
				
				if (isset($rels[$optRel->DictId][$optRel->Id])){
					continue;
				}
				$rels[$optRel->DictId][$optRel->Id] = $optRel;
								
				switch ($Inverse){
					case false;
				
						$UrlParams = array();
						$UrlParams[$DictFieldName] = $optRel->DictId;
						$UrlParams[$RelFieldName] = $optRel->Id;
						$ReturnUrl = UpdateUrl($UrlParams);
										
						$Content .= "<tr><td>".$optRel->DictId."</td><td><a href='$ReturnUrl'>".$optRel->Label."<a></td>";
						$Content .= "<td>";
						$objRelClass = $Dicts->getClass($optRel->ObjectDictId, $optRel->ObjectId);
						$Content .= $objRelClass->Label.' ('.$objRelClass->Concept.')';
						
						$Content .= "</td>";
						$Content .= "</tr>";
						break;
						
					case true;
				
						$UrlParams = array();
						$UrlParams[$DictFieldName] = $optRel->DictId;
						$UrlParams[$RelFieldName] = $optRel->Id;
						$UrlParams[$InverseFieldName] = 'true';
						
						$ReturnUrl = UpdateUrl($UrlParams);
										
						$Content .= "<tr><td>".$optRel->DictId."</td><td><a href='$ReturnUrl'>".$optRel->InverseLabel."<a></td>";
						$Content .= "<td>";
						$objRelClass = $Dicts->getClass($optRel->SubjectDictId, $optRel->SubjectId);
						$Content .= $objRelClass->Label.' ('.$objRelClass->Concept.')';
						
						$Content .= "</td>";
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


function funSelectShapeClass($ShapeClassFieldName = 'fromshapeclassid'){

	global $System;
	global $Mode;
	global $objShape;
	
	global $objRel;
	global $Inverse;
	
	global $Dicts;
	
	global $ReturnURL;

//	$optClassList = array();

	$Content = "";
	
	$Content .= "<div class='sdbluebox'>";

	
	
	$opts = array();

	if (is_null($objRel)){		
		foreach ($objShape->ShapeClasses as $optShapeClass){
			$opts[$optShapeClass->Id] = $optShapeClass;
		}	
	}
	else
	{
		$SuperClass = null;
		switch ( $ShapeClassFieldName){
			case 'fromshapeclassid':
				switch ($Inverse){
					case true:
						$SuperClass = $Dicts->getClass($objRel->ObjectDictId, $objRel->ObjectId);
						break;
					default:
						$SuperClass = $Dicts->getClass($objRel->SubjectDictId, $objRel->SubjectId);
						break;
				}				
				break;
			default:
				switch ($Inverse){
					case true:
						$SuperClass = $Dicts->getClass($objRel->SubjectDictId, $objRel->SubjectId);
						break;
					default:
						$SuperClass = $Dicts->getClass($objRel->ObjectDictId, $objRel->ObjectId);
						break;
				}				
				break;
		}
		if (is_object($SuperClass)){
			$SubClasses = $Dicts->SubClasses($SuperClass->DictId, $SuperClass->Id);
			foreach ($objShape->ShapeClasses as $optShapeClass){
				$useShapeClass = false;
				if ($optShapeClass->Class === $SuperClass){
					$useShapeClass = true;
				}
				else
				{
					foreach ($SubClasses as $optClass){
						if ($optShapeClass->Class === $optClass){
							$useShapeClass = true;
						}
					}
				}
				if ($useShapeClass){		
					$opts[$optShapeClass->Id] = $optShapeClass;
				}
				
			}	
			
			
		}
	}
		
	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Class</th></tr></thead>";
		$Content .= "<tbody>";
		
		foreach ($opts as $optShapeClassId=>$optShapeClass){

			$UrlParams = array();
			$UrlParams[$ShapeClassFieldName] = $optShapeClass->Id;
			$ReturnUrl = UpdateUrl($UrlParams);
										
			$Content .= "<tr><td><a href='$ReturnUrl'>".$optShapeClass->Class->Label."<a></td></tr>";
	
		}
		$Content .= "</tbody>";
		$Content .= "</table>";
		
	}
		
 	$Content .= "</div>";
	
	return $Content;
}


?>