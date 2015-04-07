<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDocument.php");
	require_once("class/clsRights.php");
	require_once("class/clsShape.php");	
	require_once("class/clsDict.php");

	require_once("panel/pnlOrg.php");
	require_once("panel/pnlSet.php");
	require_once("panel/pnlDocument.php");
	require_once("panel/pnlSelectSubject.php");
	
	require_once("panel/pnlClassSubjects.php");
	require_once("panel/pnlSubject.php");
	require_once("panel/pnlStatement.php");
	require_once("panel/pnlClassFilters.php");
		
	
	define('PAGE_NAME', 'documentlink');

	session_start();

	$System = new clsSystem();			
	$Page = new clsPage();	
	
	
	$Script = "\n";
	
	$Script .= "<link rel='stylesheet' href='//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css'>";	
  	$Script .= "<script src='jquery/jquery-1.11.1.min.js'></script>";
  	  	  	
  	$Script .= "<script src='//code.jquery.com/ui/1.10.4/jquery-ui.js'></script>";	
	$Script .= "<script type='text/javascript' src='java/datepicker.js'></script>";
	
	$Script .= "<script type='text/javascript' src='java/utils.js'></script>";
	
	$Script .= "<script type='text/javascript' src='java/ajax.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getClasses.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getClassSubjects.js'></script>";
	
		
	$Page->Script .= $Script;
	
	$InitScript = '<script>';	
	$InitScript .= "function init(){ \n";
	$InitScript .= "    setDatePicker();";	
	
	$AjaxDivNum = 0;
	global $AjaxDivNum;
	
	
	try {

		$Dicts = new clsDicts();
		$Shapes = new clsShapes();
		$Orgs = new clsOrganisations();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	

		$PanelB = '';
		$PanelC = '';

		$Tabs = "";
		$TabContent = "";

		$DocId = null;
		$SetId = null;
		$ShapeId = null;
		$ShapeLinkId = null;
		$LinkId = null;
		$FromId = null;
		$ToId = null;
				
		
		$objDoc = null;
		$objLinkForm = null;
		$objShape = null;
		$objShapeLink = null;
		
		$objFrom = null;
		$objTo = null;

		if (isset($_REQUEST['docid'])){
			$DocId = $_REQUEST['docid'];
		}

		if (isset($_REQUEST['linkid'])){
			$LinkId = $_REQUEST['linkid'];
		}
		
		
		if (isset($_REQUEST['setid'])){
			$SetId = $_REQUEST['setid'];
		}

		if (isset($_REQUEST['shapeid'])){
			$ShapeId = $_REQUEST['shapeid'];
		}
		
		if (isset($_REQUEST['shapelinkid'])){
			$ShapeLinkId = $_REQUEST['shapelinkid'];
		}
		
		if (!empty($DocId)){
			$objDoc = new clsDocument($DocId);
			$SetId = $objDoc->SetId;
			$ShapeId = $objDoc->ShapeId;
		}
		else
		{
			$objDoc = new clsDocument();
			if (isset($Shapes->Item[$ShapeId])){
				$objDoc->objShape = $Shapes->Item[$ShapeId];
			}
		}
		
		
		if (IsEmptyString($SetId)) {
			throw new exception("SetId not specified");
		}

		if ($System->Session->Error){
			$FormFields = GetUserInput(PAGE_NAME);
		}
		unset($_SESSION['forms'][PAGE_NAME]);
		$System->Session->Clear('Error');			
		

		switch ($Mode){
			case 'new':
				if (IsEmptyString($ShapeId)) {
					throw new exception("ShapeId not specified");
				}
				if (IsEmptyString($ShapeLinkId)) {
					throw new exception("ShapeLinkId not specified");
				}
				
				break;
			default:
				if (IsEmptyString($DocId)) {
					throw new exception("DocId not specified");
				}

				break;
		}


		$objSet = new clsSet($SetId);
		$objOrg = $Orgs->Items[$objSet->OrgId];

		if (!isset($Shapes->Items[$ShapeId])){
			throw new exception("Unknown Shape");
		}
			
		$objShape = $Shapes->Items[$ShapeId];
		
		
		switch ($Mode){
			case 'view':
			case 'edit':
			case 'delete':
				if (!isset($objDoc->LinkForms[$LinkId])){
					throw new Exception("Link not in the Document");
				}
				$objLinkForm = $objDoc->LinkForms[$LinkId];
				$FromId = $objLinkForm->FromId;
				$ToId = $objLinkForm->ToId;
				$ShapeLinkId = $objLinkForm->ShapeLink->Id;
				break;
			case 'new':
				
				if (!isset($objDoc->BlankLinkForms[$ShapeLinkId])){
					throw new Exception("Invalid Shape Link");
				}
				$objLinkForm = $objDoc->BlankLinkForms[$ShapeLinkId];
				
				break;
		}

		
		if (isset($_REQUEST['fromid'])){
			$FromId = $_REQUEST['fromid'];
			$objFrom = new clsSubject($FromId,$DocId);
		}
		if (isset($_REQUEST['toid'])){
			$ToId = $_REQUEST['toid'];
			$objTo = new clsSubject($ToId,$DocId);
		}
		
		$objShapeLink = null;
		if (!isset($objShape->ShapeLinks[$ShapeLinkId])){		
			throw new exception("Unknown Shape Link");
		}
		$objShapeLink = $objShape->ShapeLinks[$ShapeLinkId];
		
		
		if ($objOrg->canView === false){
			throw new exception("You cannot view this Organisation");
		}
		
		$Page->Title = $Mode." ".$objShape->Name." link in document";
		$PanelB .= "<h1>".$Page->Title."</h1>";

		
		$objFromShapeClass = $objShape->ShapeClasses[$objShapeLink->FromShapeClassId];
		$objToShapeClass = $objShape->ShapeClasses[$objShapeLink->ToShapeClassId];
		$RelLabel = $objShapeLink->Relationship->Label;
		if ($objShapeLink->Inverse === true){
			$RelLabel = $objShapeLink->Relationship->InverseLabel;
		}
		
		$PanelB .= "<h3>".$objFromShapeClass->Class->Label." $RelLabel ".$objToShapeClass->Class->Label."</h3>";
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objSet->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objSet->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objSet->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objSet->canEdit){
					$ModeOk = true;
				}
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}
		
		if (!is_null($DocId)){
			$Tabs .= "<li><a href='#document'>Document";
			$TabContent .= "<div class='tabContent hide' id='document'>";								
			$TabContent .= "<h3>Document</h3>";						
			$TabContent .= pnlDocument($DocId);
			$TabContent .= "</div>";
		    $Tabs .= "</a></li>";				
		}
		
		switch ($Mode){
			case 'view':
				
				$PanelB .= pnlLinkForm($objLinkForm);
				
				if ($objSet->canEdit === true){
					$PanelB .= "<li><a href='documentlink.php?docid=$DocId&linkid=$LinkId&mode=edit'>edit</a></li>";
					$PanelB .= "<li><a href='documentlink.php?docid=$DocId&linkid=$LinkId&mode=delete'>delete</a></li>";
				}				
											    

				break;
			case 'new':
			case 'edit':

				
				$PanelB .= '<form method="post" action="doDocLink.php">';					
						
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!IsEmptyString($ShapeId)){
					$PanelB .= "<input type='hidden' name='shapeid' value='$ShapeId'/>";
				}
					
				if (!IsEmptyString($ShapeLinkId)){
					$PanelB .= "<input type='hidden' name='shapelinkid' value='$ShapeLinkId'/>";
				}						
						
				if (!IsEmptyString($DocId)){
					$PanelB .= "<input type='hidden' name='docid' value='$DocId'/>";
				}
				if (!IsEmptyString($SetId)){
					$PanelB .= "<input type='hidden' name='setid' value='$SetId'/>";
				}				

				if (!IsEmptyString($LinkId)){
					$PanelB .= "<input type='hidden' name='linkid' value='$LinkId'/>";
				}
				
				
				$PanelB .= "<div class='sdgreybox'>";
				$PanelB .= "<table>";
				
				if (!is_null($FromId)){
					$PanelB .= "<tr><th>Link From</th><td>".pnlSubject($FromId, $DocId)."</td></tr>";
					$PanelB .= "<input type='hidden' name='fromid' value='$FromId'/>";
				}
				
				$RelLabel = $objShapeLink->Relationship->Label;
				if ($objShapeLink->Inverse){
					$RelLabel = $objShapeLink->Relationship->InverseLabel;					
				}
				$PanelB .= "<tr><th>Relationship</th><td>$RelLabel</td></tr>";				

				if (!($objLinkForm->CreateExtended === true)){								
					if (!is_null($ToId)){
						$PanelB .= "<tr><th>Link To</th><td>".pnlSubject($ToId, $DocId)."</td></tr>";
						$PanelB .= "<input type='hidden' name='toid' value='$ToId'/>";
					}
				}
				
				$PanelB .= "</table>";
				$PanelB .= "</div>";
				
				if (!($objLinkForm->CreateExtended === true)){
				
					$Tabs .= "<li><a href='#linkfrom'>Link From";
					$TabContent .= "<div class='tabContent hide' id='linkfrom'>";								
					$TabContent .= "<h3>Link From</h3>";
					
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#linkfromthis'>Select from this set</a></li>";
					$optTabContent .= "<div class='tabContent hide' id='linkfromthis'>";										
					$optTabContent .= pnlSelectSubject($objFromShapeClass->Class, 'this','fromid');
					$optTabContent .= "</div>";
					
					
					$optTabs .= "<li><a href='#linkfromreference'>Select from reference datasets</a></li>";
					$optTabContent .= "<div class='tabContent hide' id='linkfromreference'>";					
					$optTabContent .= pnlSelectSubject($objFromShapeClass->Class, 'reference','fromid');
					$optTabContent .= "</div>";
					
					if (!empty($optTabs)){
						$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					
										
					$TabContent .= "</div>";
				    $Tabs .= "</a></li>";				
					
					$Tabs .= "<li><a href='#linkto'>Link To";
					$TabContent .= "<div class='tabContent hide' id='linkto'>";								
					$TabContent .= "<h3>Link To</h3>";
					
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#linktothis'>Select from this set</a></li>";
					$optTabContent .= "<div class='tabContent hide' id='linktothis'>";										
					$optTabContent .= pnlSelectSubject($objToShapeClass->Class, 'this','toid');
					$optTabContent .= "</div>";
					
					
					$optTabs .= "<li><a href='#linktoreference'>Select from reference datasets</a></li>";
					$optTabContent .= "<div class='tabContent hide' id='linktoreference'>";					
					$optTabContent .= pnlSelectSubject($objToShapeClass->Class, 'reference','toid');
					$optTabContent .= "</div>";
					
					if (!empty($optTabs)){
						$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					
					$TabContent .= "</div>";
				    $Tabs .= "</a></li>";				
				}
			    
				
				if (!is_null($FromId)){
					
					if (!(is_null($ToId)) || $objLinkForm->CreateExtended === true  ){
										
						$InitScript .= "    loadForm(); ";
						$Script .= "    setDatePicker(); \n";				
										
						$PanelB .= frmLinkForm($objLinkForm);
						
						if (is_null($LinkId)){
							$PanelB .= "<input type='submit' value='Create a new Link'/>";
						}
						else
						{
							$PanelB .= "<input type='submit' value='Update Link'/>";
						}
					}
				}				
				
				$PanelB .= '</form>';
				
				break;
				
			case 'delete':
				
				$PanelB .= pnlLinkForm($objLinkForm);
												
				$PanelB .= "<a href='doDocLink.php?docid=$DocId&linkid=$LinkId&mode=delete'>confirm delete link?</a><br/>";
				
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
	 	
	
	$InitScript .= "} \n";
	$InitScript .= "</script>\n";
	$Page->Script .= $InitScript;
	
	$Page -> Display();

	
function xfunSelectSubject($objClass, $FieldName=null){
	
	$Content = "";
	$Content .= "<h3>Select a ".$objClass->Label." from one of these selections</h3>";
			
	$Selection = "this";
	
	$TabId = "";
	$TabId = "subjects";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$opts = array();

	switch ($Selection){
		case "this":
			$Content .= pnlClassSubjects($objClass->DictId, $objClass->Id, null, $FieldName, updateUrl());
			break;			
	}
				
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}

function frmLinkForm($objLinkForm){
		
	global $Page;
	
	global $Dicts;
	global $objProfile;
	
//	echo '<pre>'.htmlentities($objLinkForm->xml).'</pre>';
	
	$Content = "";
							
	$Content .= "<table class='sdbluebox'>";
	
	if ($objLinkForm->ShapeLink->EffDates){		
		$Content .= "<tr><th>Effective From</th><td>";
		$Content .= "<input name='relefffrom' size=20 type='date' value='".convertDate($objLinkForm->EffectiveFrom)."' class='datepicker' id='relefffrom'/>";
		$Content .= "</td></tr>";
		$Content .= "<tr><th>Effective To</th><td>";
		$Content .= "<input name='releffto' size=20 type='date' value='".convertDate($objLinkForm->EffectiveTo)."' class='datepicker' id='releffto'/>";
		$Content .= "</td></tr>";	
	}
	$Content .= '</table>';
	
	$Script = "";
	
	$Script .= "<script>\n";
	$Script .= "var xmlLinkForm = ".json_encode($objLinkForm->xml).";\n\n";
	$Script .= "</script>\n";
	
	
	$Page->Script = $Script .$Page->Script;
	
	$Content .= "<div id='divLinkForm'></div>";
	
	return $Content;
		
}


?>