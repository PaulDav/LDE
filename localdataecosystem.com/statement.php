<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrg.php");
	require_once("panel/pnlSet.php");
	require_once("panel/pnlDocument.php");
	require_once("panel/pnlStatement.php");
	require_once("panel/pnlPredicate.php");
	require_once("panel/pnlClassSubjects.php");
	require_once("panel/pnlSubject.php");
	require_once("panel/pnlRel.php");
	
	require_once("class/clsRights.php");
	require_once("class/clsData.php");
	require_once("class/clsDict.php");
	
	define('PAGE_NAME', 'statement');

	session_start();
		
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();

			
	try {

		$Dicts = new clsDicts();
		$Orgs = new clsOrganisations();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$StatId = null;
		$SetId = null;
		$TypeId = null;
		$SubjectId = null;
		$ObjectId = null;
		$LinkDictId = null;
		$LinkId = null;
		$Value = null;

		if (isset($_REQUEST['statid'])){
			$StatId = $_REQUEST['statid'];
		}
		
		if (isset($_REQUEST['setid'])){
			$SetId = $_REQUEST['setid'];
		}

		if (isset($_REQUEST['typeid'])){
			$TypeId = $_REQUEST['typeid'];
		}

		if (isset($_REQUEST['subjectid'])){
			$SubjectId = $_REQUEST['subjectid'];
		}
		
		if (isset($_REQUEST['objectid'])){
			$ObjectId = $_REQUEST['objectid'];
		}
		
		if (isset($_REQUEST['linkdictid'])){
			$LinkDictId = $_REQUEST['linkdictid'];
			if (isset($_REQUEST['linkid'])){
				$LinkId = $_REQUEST['linkid'];
			}
		}
		
		
		
		switch ($Mode){
			case 'new':
				if (IsEmptyString($SetId)) {
					throw new exception("SetId not specified");
				}
				if (IsEmptyString($TypeId)) {
					throw new exception("TypeId not specified");
				}
				
				break;
			default:
				if (IsEmptyString($StatId)) {
					throw new exception("StatId not specified");
				}

				break;
		}

		if (!empty($StatId)){
			$objStat = new clsStatement($StatId);
			$SetId = $objStat->SetId;
			$SubjectId = $objStat->SubjectId;
			$LinkDictId = $objStat->LinkDictId;
			$LinkId = $objStat->LinkId;
			$ObjectId = $objStat->ObjectId;
			$Value = $objStat->Value;
		}		


		if ($System->Session->Error){
			if (isset($FormFields['subjectid'])){
				$SubjectId = $FormFields['subjectid'];
			}
			if (isset($FormFields['linkdictid'])){
				$LinkDictId = $FormFields['linkdictid'];
			}
			if (isset($FormFields['linkid'])){
				$LinkId = $FormFields['linkid'];
			}				
			if (isset($FormFields['objectid'])){
				$ObjectId = $FormFields['objectid'];
			}				
			if (isset($FormFields['value'])){
				$Value = $FormFields['value'];
			}				

			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}

		$objSet = new clsSet($SetId);
		$objOrg = $Orgs->Items[$objSet->OrgId];
		
		if ($objOrg->canView === false){
			throw new exception("You cannot view this Organisation");
		}
		
		$Page->Title = $Mode." statement";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
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
								
				
		switch ($Mode){
			case 'view':
				$PanelB .= pnlStatement( $StatId );
												
				$PanelB .= "</ul></div>";				

				
				$Tabs .= "<li><a href='#org'>Organisation";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='org'>";
					$TabContent .= "<h3>by Organisation</h3>";	
					$TabContent .= pnlOrg($objSet->OrgId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
							    
				$TabContent .= "</div>";

				$Tabs .= "<li><a href='#set'>Set";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='set'>";
					$TabContent .= "<h3>in Set</h3>";	
					$TabContent .= pnlSet($objSet);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
							    
				$TabContent .= "</div>";

				if (!is_null($objStat->DocId)){
					$Tabs .= "<li><a href='#document'>Document";
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='document'>";
						$TabContent .= "<h3>in Document</h3>";	
						$TabContent .= pnlDocument($objStat->DocId);	
					$TabContent .= "</div>";
				    $Tabs .= "</a></li>";
								    
					$TabContent .= "</div>";
				}
				
				break;
			case 'new':
			case 'edit':
				if (($TypeId == 100) && (is_null($LinkId))){
					// get Link
					$PanelB .= "<h3>Set the Class from any of these selections</h3>";	
			
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#myclasses'>Classes in My Groups</a></li>";
					$optTabContent .= funSelectClass("my");
										
					$optTabs .= "<li><a href='#publishedclasses'>Classes in Published Dictionaries</a></li>";			    
					$optTabContent .= funSelectClass("published");
					
					if (!empty($optTabs)){
						$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					
				}
				elseif (($TypeId == 200) ){
					
					if (is_null($SubjectId)){
						throw new exception("SubjectId Not Specified");
					}

					if (is_null($LinkId)){
						throw new exception("LinkId Not Specidied");
					}
					
					
					$objProp = $Dicts->Dictionaries[$LinkDictId]->Properties[$LinkId];
					
					
// build field
					$PanelB .= '<form method="post" action="doStat.php">';
			
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					if (!IsEmptyString($TypeId)){
						$PanelB .= "<input type='hidden' name='typeid' value='$TypeId'/>";
					}
					
					if (!IsEmptyString($StatId)){
						$PanelB .= "<input type='hidden' name='statid' value='$StatId'/>";
					}
					if (!IsEmptyString($SetId)){
						$PanelB .= "<input type='hidden' name='setid' value='$SetId'/>";
					}				

					if (!IsEmptyString($SubjectId)){
						$PanelB .= "<input type='hidden' name='subjectid' value='$SubjectId'/>";
					}				
					
					$PanelB .= '<input type="hidden" name="linkdictid" value="'.$LinkDictId.'">';
					$PanelB .= '<input type="hidden" name="linkid" value="'.$LinkId.'">';							
					
					
					$PanelB .= '<table class="sdbluebox">';
					
					if ($Mode == "edit"){
						$PanelB .= '<tr>';
							$PanelB .= '<th>';
							$PanelB .= 'Id';
							$PanelB .= '</th>';
							$PanelB .= '<td>';
							$PanelB .= $StatId;
							$PanelB .= '</td>';
						$PanelB .= '</tr>';					
					}
					$PanelB .= "<tr><th>".$objProp->Label."</th>";
					if ($objProp->Type == 'simple'){
						$PanelB .= "<td>";
						switch ($objProp->Field->DataType){
							case 'line':
								$PanelB .= "<input name='value' size='".$objProp->Field->Length."'/>";
								break;
						}
						
						$PanelB .= "</td>";						
					}
					$PanelB .= "</tr>";
					
					
					$PanelB .= '<tr>';
						$PanelB .= '<td/>';
						$PanelB .= '<td>';
						
						switch ( $Mode ){
							case "new":
								$PanelB .= '<input type="submit" value="Create New Statement">';
								break;
							case "edit":
								$PanelB .= '<input type="submit" value="Update Statement">';
								break;
						}
	
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
			
				 	$PanelB .= '</table>';
					$PanelB .= '</form>';
					
					
				}
				elseif (($TypeId == 300) ){
					
					if (is_null($SubjectId)){
						$PanelB .= "<h3>Select the Subject</h3>";
						$PanelB .= funSelectSubject();
					}
					else
					{
						$PanelB .= "<h3>Subject</h3>";
						$PanelB .= pnlSubject($SubjectId);
					
						if (is_null($ObjectId)){
							$PanelB .= "<h3>Select the Object</h3>";
							$PanelB .= funSelectSubject('objectid');
						}
						else
						{
							$PanelB .= "<h3>Object</h3>";
							$PanelB .= pnlSubject($ObjectId);
							
							if (is_null($LinkId)){
								$PanelB .= "<h3>Select the Relationship</h3>";
								$PanelB .= funSelectRelationship($SubjectId, $ObjectId);								
							}
							else
							{
								$PanelB .= "<h3>Relationship</h3>";
								$PanelB .= pnlRel($LinkDictId, $LinkId);
								$PanelB .= "<br/>";
								
								$PanelB .= '<form method="post" action="doStat.php">';
			
								$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
								if (!IsEmptyString($TypeId)){
									$PanelB .= "<input type='hidden' name='typeid' value='$TypeId'/>";
								}
								
								if (!IsEmptyString($StatId)){
									$PanelB .= "<input type='hidden' name='statid' value='$StatId'/>";
								}
								if (!IsEmptyString($SetId)){
									$PanelB .= "<input type='hidden' name='setid' value='$SetId'/>";
								}
			
								$PanelB .= "<input type='hidden' name='subjectid' value='$SubjectId'/>";
								$PanelB .= "<input type='hidden' name='objectid' value='$ObjectId'/>";
								
								$PanelB .= '<input type="hidden" name="linkdictid" value="'.$LinkDictId.'">';
								$PanelB .= '<input type="hidden" name="linkid" value="'.$LinkId.'">';							
																
								switch ( $Mode ){
									case "new":
										$PanelB .= '<input type="submit" value="Create New Statement">';
										break;
									case "edit":
										$PanelB .= '<input type="submit" value="Update Statement">';
										break;
								}
				
								$PanelB .= '</form>';
								
							}
						}
					}					
				}
				else
				{
				
					$PanelB .= '<form method="post" action="doStat.php">';
			
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					if (!IsEmptyString($TypeId)){
						$PanelB .= "<input type='hidden' name='typeid' value='$TypeId'/>";
					}
					
					if (!IsEmptyString($StatId)){
						$PanelB .= "<input type='hidden' name='statid' value='$StatId'/>";
					}
					if (!IsEmptyString($SetId)){
						$PanelB .= "<input type='hidden' name='setid' value='$SetId'/>";
					}				
											
					$PanelB .= '<table class="sdbluebox">';
					
					if ($Mode == "edit"){
						$PanelB .= '<tr>';
							$PanelB .= '<th>';
							$PanelB .= 'Id';
							$PanelB .= '</th>';
							$PanelB .= '<td>';
							$PanelB .= $StatId;
							$PanelB .= '</td>';
						$PanelB .= '</tr>';					
					}
					
					switch ($TypeId){
						case 100: // class
							
							$PanelB .= '<tr>';
								$PanelB .= '<th>';
								$PanelB .= 'Link';
								$PanelB .= '</th>';
								$PanelB .= '<td>';
								$PanelB .= pnlPredicate($LinkDictId, $LinkId, $TypeId);
								$PanelB .= '<input type="hidden" name="linkdictid" value="'.$LinkDictId.'">';
								$PanelB .= '<input type="hidden" name="linkid" value="'.$LinkId.'">';							
								$PanelB .= '</td>';
							$PanelB .= '</tr>';
							
							break;
						default:
							$PanelB .= '<tr>';
								$PanelB .= '<th>';
								$PanelB .= 'Subject';
								$PanelB .= '</th>';
								$PanelB .= '<td>';
								$PanelB .= '<input type="text" name="name" size="30" maxlength="100" value="'.$Name.'">';
								$PanelB .= '</td>';
							$PanelB .= '</tr>';
							break;
					}
					
									
					$PanelB .= '<tr>';
						$PanelB .= '<td/>';
						$PanelB .= '<td>';
						
						switch ( $Mode ){
							case "new":
								$PanelB .= '<input type="submit" value="Create New Statement">';
								break;
							case "edit":
								$PanelB .= '<input type="submit" value="Update Statement">';
								break;
						}
	
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
			
				 	$PanelB .= '</table>';
					$PanelB .= '</form>';
				}

				break;
				
			case 'delete':
				
				$PanelB .= pnlSet( $objSet );
				
				$PanelB .= "<a href='doSet.php?setid=$SetId&Mode=delete'>confirm delete?</a><br/>";
				
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


function funSelectClass($Selection){

	global $System;
	
	global $Dicts;
	
	$Content = "";
		
	$ReturnURL = $_SERVER['SCRIPT_NAME'];
	$QueryString = $_SERVER['QUERY_STRING'];
	$ReturnURL = $ReturnURL.'?'.$QueryString;
	
	
	$arrDictIds = array();

	$TabId = "";
	$TabId = $Selection."classes";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$DictFieldName = "linkdictid";
	$ClassFieldName = "linkid";

	$opts = array();

	switch ($Selection){
			
		case "my":
			
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
	
		foreach ($opts as $optDictId=>$optConcepts){
		
			$Content .= "<form method='post' action='$ReturnURL'>";
							
			$Content .= "<input type='hidden' name='ReturnURL' value='$ReturnURL'/>";
			$Content .= "<input type='hidden' name='$DictFieldName' value='".$optDictId."'/>";
			
			$Content .= "<table class='list'>";

			$Content .= "<thead><tr><th>Dictionary</th><th>Concept</th><th>Class</th><th>Description</th></tr></thead>";
			$Content .= "<tbody>";
						
			$Content .= "<tr>";
			
			$numRows = 1;
			foreach ($optConcepts as $optClasses){
				$numRows = $numRows + 1;
				foreach ($optClasses as $optClass){
					$numRows = $numRows + 1;
				}
			}
					
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
					$Content .= "<tr><td><input type='radio' name='$ClassFieldName' value='".$optClass->Id."'/>".$optClass->Label."</td>";
					$Content .= "<td>".nl2br($optClass->Description)."</td>";
					$Content .= "</tr>";
				}
			}
			$Content .= "</tbody>";
			$Content .= "</table>";
	
			$Content .= '<table><tr>';
				$Content .= '<td/>';
				$Content .= '<td>';
				
				$Content .= '<input type="submit" value="Select Class">';
		
				$Content .= '</td>';
			$Content .= '</tr></table>';
				
		 	$Content .= "</form>";
		}
						
	}
		
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}

	
function funSelectSubject($FieldName='subjectid'){

	global $System;
	global $objSet;
	
	global $Dicts;
	
	$DictId = $System->Config->Vars['browse']['startdict'];
	$Dict = $Dicts->Dictionaries[$DictId];
	
	$Content = "";
		
	$ReturnURL = $_SERVER['SCRIPT_NAME'];
	$QueryString = $_SERVER['QUERY_STRING'];
	$ReturnURL = $ReturnURL.'?'.$QueryString;
	
	$Selection = "this";
	
	$TabId = "";
	$TabId = "subjects";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$opts = array();

	switch ($Selection){
		case "this":
			foreach ($Dict->Classes as $Class){
				$Content .= "<h3>".$Class->Label."<h3>";
				$Content .= pnlClassSubjects($Dict->Id, $Class->Id, null, $FieldName, $ReturnURL );
			}
			break;			
	}
				
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}


function funSelectRelationship($SubjectId = null, $ObjectId = null){

	global $System;
	global $objSet;

	global $Dicts;

	$SubjectDictId = null;
	$SubjectClassId = null;

	$ObjectDictId = null;
	$ObjectClassId = null;
	
	if (!is_null($SubjectId)){
		$Subject = new clsSubject($SubjectId);
		$SubjectDictId = $Subject->ClassDictId;
		$SubjectClassId = $Subject->ClassId;
	}

	$Rels = $Dicts->RelationshipsFor($SubjectDictId, $SubjectClassId, $ObjectDictId, $ObjectClassId);
	
	$Content = "";
		
	$ReturnURL = $_SERVER['SCRIPT_NAME'];
	$QueryString = $_SERVER['QUERY_STRING'];
	$ReturnURL = $ReturnURL.'?'.$QueryString;
	
	$Selection = "this";
	
	
	$TabId = "";
	$TabId = "relationships";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$opts = array();

	switch ($Selection){
		case "this":
			foreach ($Rels as $Rel){
				
				$UrlParams = array();
				$UrlParams['linkdictid'] = $Rel->DictId;				
				$UrlParams['linkid'] = $Rel->Id;				
				$ReturnUrl = UpdateUrl($UrlParams);
				$Content .= "<a href='$ReturnUrl'>".$Rel->Label."</a><br/>";
			}
			break;			
	}
				
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}

	
?>