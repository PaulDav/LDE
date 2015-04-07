<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlClass.php");
	require_once("panel/pnlRel.php");
	
	require_once("panel/pnlProfile.php");
	
	require_once("panel/pnlProfileClass.php");
	require_once("panel/pnlProfileRel.php");
	
	require_once("class/clsModel.php");	
	require_once("class/clsDict.php");	
	require_once("class/clsProfile.php");
	
	define('PAGE_NAME', 'profilerelationship');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

	$Model = new clsModel();

	try {

		$Dicts = new clsDicts();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	

		$PanelB = '';
		$PanelC = '';

		$Tabs = "";
		$TabContent = "";

		$ProfileId = null;
		$GroupId = null;
		$ProfileClassId = null;
		$ProfileRelId = null;
		$DictId = null;
		$RelId = null;
		
		
		$Inverse = false;

		if (isset($_REQUEST['profileid'])){
			$ProfileId = $_REQUEST['profileid'];
		}
		if ($ProfileId =='') {
			throw new exception("ProfileId not specified");
		}
		
		$objProfile = new clsProfile($ProfileId);
		$objGroupId = $objProfile->GroupId;

		if (isset($_REQUEST['profileclassid'])){
			$ProfileClassId = $_REQUEST['profileclassid'];
		}
		
		
		if (isset($_REQUEST['profilerelid'])){
			$ProfileRelId = $_REQUEST['profilerelid'];
		}

		switch ($Mode){
			case 'new':
				break;
			default:
				if ($ProfileRelId =='') {
					throw new exception("ProfileRelId not specified");
				}
				break;
		}

		
		if (!empty($ProfileRelId)){
			$objProfileRel = $objProfile->Relationships[$ProfileRelId];
			$DictId = $objProfileRel->DictId;
			$RelId = $objProfileRel->RelId;
			

			if (isset($objProfileRel->SubjectProfileClassIds[0])){
				$ProfileClassId = $objProfileRel->SubjectProfileClassIds[0];
			}
			
		}		

		if (isset($_REQUEST['profileclassid'])){
			$ProfileClassId = $_REQUEST['profileclassid'];
		}
		if (!empty($ProfileClassId)){
			$objProfileClass = $objProfile->Classes[$ProfileClassId];
		}		
		
		
		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if (isset($_REQUEST['relid'])){
			$RelId = $_REQUEST['relid'];
		}		
		if (isset($_REQUEST['inverse'])){
			$Inverse = $_REQUEST['inverse'];
		}
		
		
		if ($objProfile->canView === false){
			throw new exception("You cannot view this Profile");
		}
		
		$Page->Title = $Mode." profile relationship";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objProfile->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objProfile->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objProfile->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objProfile->canEdit){
					$ModeOk = true;
				}
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}

				
		switch ($Mode){
			case 'view':
				$PanelB .= pnlProfileRel( $ProfileId, $ProfileRelId );

				$PanelB .= "<div class='hmenu'><ul>";
				if ($objProfile->canEdit === true){
					$PanelB .= "<li><a href='profileclass.php?profileid=$ProfileId&profilerelid=$ProfileRelId&mode=new'>&bull; set object class</a></li><br/> ";
					$PanelB .= "<li><a href='profilerelationship.php?profileid=$ProfileId&profilerelid=$ProfileRelId&mode=edit'>&bull; edit</a></li><br/> ";
				}
				
				if ($objProfile->canControl === true){
					$PanelB .= "<li><a href='profilerelationship.php?profileid=$ProfileId&profilerelid=$ProfileRelId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$PanelB .= "</ul></div>";				

				$Tabs .= "<li><a href='#profile'>Profile";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='profile'>";
					$TabContent .= "<div id='dict'><h3>in Profile</h3></div>";	
					$TabContent .= pnlProfile($ProfileId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
				break;
			case 'new':
			case 'edit':
				
				if (empty($RelId)){
					$objSubjectProfileClass = null;
					$objObjectProfileClass = null;
					
					if (isset($objProfileClass)){
						$objSubjectProfileClass = $objProfileClass;
					}
					
					$PanelB .= funSelectRelationship($objSubjectProfileClass, $objObjectProfileClass);
				}
				else
				{
					
					$PanelB .= '<form method="post" action="doProfileRelationship.php">';
	
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					if (!( $ProfileId == '')){
						$PanelB .= "<input type='hidden' name='profileid' value='$ProfileId'/>";
					}
					if (!( $ProfileClassId == '')){
						$PanelB .= "<input type='hidden' name='profileclassid' value='$ProfileClassId'/>";
					}
					if (!( $ProfileRelId == '')){
						$PanelB .= "<input type='hidden' name='profilerelid' value='$ProfileRelId'/>";
					}
					
					$PanelB .= '<table class="sdbluebox">';
				
								
					if ($Mode == "edit"){
						$PanelB .= '<tr>';
							$PanelB .= '<th>';
							$PanelB .= 'Id';
							$PanelB .= '</th>';
							$PanelB .= '<td>';
							$PanelB .= $ProfileRelId;
							$PanelB .= '</td>';
						$PanelB .= '</tr>';					
					}

					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Relationship';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= pnlRel($DictId, $RelId);
						$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
						$PanelB .= "<input type='hidden' name='relid' value='$RelId'/>";
						
						if (isset($Inverse)){
							if ($Inverse == 'true'){
								$PanelB .= "<input type='hidden' name='inverse' value='$Inverse'/>";
							}
						}
												
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
					
					$PanelB .= '<tr>';
						$PanelB .= '<td/>';
						$PanelB .= '<td>';
						
						switch ($Mode){
							case 'new':
								$PanelB .= '<input type="submit" value="Add Relationship for Profile">';
								break;	
							case 'edit':
								$PanelB .= '<input type="submit" value="Update Relationship for Profile">';
								break;
						}
								
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
					
					
				}

			 	$PanelB .= '</table>';
				$PanelB .= '</form>';
				
				break;
				
			case 'delete':
				
				$PanelB .= pnlProfileRel( $ProfileId, $ProfileRelId );

				$PanelB .= "<a href='doProfileRelationship.php?profileid=$ProfileId&profilerelid=$ProfileRelId&mode=delete'>confirm remove relationship from the profile?</a><br/>";
				
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

	
function funSelectRelationship($objSubjectProfileClass = null, $objObjectProfileClass = null){

	global $System;
	global $objGroup;
	global $objSet;

	global $Dicts;

	$SubjectDictId = null;
	$SubjectClassId = null;

	$ObjectDictId = null;
	$ObjectClassId = null;
	
	if (!is_null($objSubjectProfileClass)){
		$SubjectDictId = $objSubjectProfileClass->DictId;
		$SubjectClassId = $objSubjectProfileClass->ClassId;
	}

	if (!is_null($objObjectProfileClass)){
		$ObjectDictId = $objObjectProfileClass->DictId;
		$ObjectClassId = $objObjectProfileClass->ClassId;
	}
	
	$Rels = $Dicts->RelationshipsFor($SubjectDictId, $SubjectClassId, $ObjectDictId, $ObjectClassId);
	$InverseRels = $Dicts->RelationshipsFor($ObjectDictId, $ObjectClassId, $SubjectDictId, $SubjectClassId);
	
	$Content = "";
			
	$Selection = "this";
	
	$TabId = "";
	$TabId = "relationships";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$opts = array();

	$Content .= "<table>";
	
	switch ($Selection){
		case "this":
			foreach ($Rels as $Rel){
				$Content .= "<tr>";				
				$UrlParams = array();
				$UrlParams['dictid'] = $Rel->DictId;				
				$UrlParams['relid'] = $Rel->Id;				
				$ReturnUrl = UpdateUrl($UrlParams);
				
				$objSubjectClass = $Dicts->Dictionaries[$Rel->SubjectDictId]->Classes[$Rel->SubjectId];				
				$Content .= "<td>".$objSubjectClass->Label."</a></td>";
				
				$Content .= "<td><a href='$ReturnUrl'>".$Rel->Label."</a></td>";
				
				$objObjectClass = $Dicts->Dictionaries[$Rel->ObjectDictId]->Classes[$Rel->ObjectId];				
				$Content .= "<td>".$objObjectClass->Label."</a></td>";
				
				$Content .= "</tr>";				
			}
			
			foreach ($InverseRels as $Rel){
				
				$Content .= "<tr>";				
				$UrlParams = array();
				$UrlParams['dictid'] = $Rel->DictId;				
				$UrlParams['relid'] = $Rel->Id;
				$UrlParams['inverse']='true';
				$ReturnUrl = UpdateUrl($UrlParams);
				
				$objObjectClass = $Dicts->Dictionaries[$Rel->ObjectDictId]->Classes[$Rel->ObjectId];				
				$Content .= "<td>".$objObjectClass->Label."</a></td>";
				
				$Content .= "<td><a href='$ReturnUrl'>".$Rel->InverseLabel."</a></td>";

				$objSubjectClass = $Dicts->Dictionaries[$Rel->SubjectDictId]->Classes[$Rel->SubjectId];				
				$Content .= "<td>".$objSubjectClass->Label."</a></td>";
				
				
				$Content .= "</tr>";				
			}
			break;			
			
			
			break;			
	}

	$Content .= "</table>";
	
	
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}
	

?>