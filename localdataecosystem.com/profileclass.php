<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlClass.php");
	require_once("panel/pnlProfile.php");
	
	require_once("panel/pnlProfileClass.php");

	require_once("class/clsModel.php");	
	require_once("class/clsDict.php");	
	require_once("class/clsProfile.php");
	
	define('PAGE_NAME', 'profileclass');

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
		$objSuperClass = null;
		
		$Select = true;
		$Create = false;
		
		$DictId = null;
		$ClassId = null;
		

		if (isset($_REQUEST['profileid'])){
			$ProfileId = $_REQUEST['profileid'];
		}
		if ($ProfileId =='') {
			throw new exception("ProfileId not specified");
		}
		
		$objProfile = new clsProfile($ProfileId);
		$GroupId = $objProfile->GroupId;
		
		if (isset($_REQUEST['profileclassid'])){
			$ProfileClassId = $_REQUEST['profileclassid'];
		}

		$objProfileRel = null;
		if (isset($_REQUEST['profilerelid'])){
			$ProfileRelId = $_REQUEST['profilerelid'];
			$objProfileRel = $objProfile->Relationships[$ProfileRelId];
			$objRel = $Dicts->Dictionaries[$objProfileRel->DictId]->Relationships[$objProfileRel->RelId];
			
			switch ($objProfileRel->Inverse){
				case false:
					$objSuperClass = $Dicts->Dictionaries[$objRel->ObjectDictId]->Classes[$objRel->ObjectId];
					break;
				default:
					$objSuperClass = $Dicts->Dictionaries[$objRel->SubjectDictId]->Classes[$objRel->SubjectId];
					break;
			}
					
		}
		
		
		switch ($Mode){
			case 'new':
				break;
			default:
				if ($ProfileClassId =='') {
					throw new exception("ProfileClassId not specified");
				}
				break;
		}

		if (!empty($ProfileClassId)){
			$objProfileClass = $objProfile->Classes[$ProfileClassId];
			$DictId = $objProfileClass->DictId;
			$ClassId = $objProfileClass->ClassId;
			$Select = $objProfileClass->Select;
			$Create = $objProfileClass->Create;
		}		

		if (isset($_REQUEST['profileclassid'])){
			$ProfileClassId = $_REQUEST['profileclassid'];
		}
		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if (isset($_REQUEST['classid'])){
			$ClassId = $_REQUEST['classid'];
		}		
		
		
		if ($objProfile->canView === false){
			throw new exception("You cannot view this Profile");
		}
		
		$Page->Title = $Mode." profile class";		
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
				$PanelB .= pnlProfileClass( $ProfileId, $ProfileClassId );

				$PanelB .= "<div class='hmenu'><ul>";
				if ($objProfile->canEdit === true){
					$PanelB .= "<li><a href='profileclass.php?profileid=$ProfileId&profileclassid=$ProfileClassId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objProfile->canControl === true){
					$PanelB .= "<li><a href='profileclass.php?profileid=$ProfileId&profileclassid=$ProfileClassId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$PanelB .= "</ul></div>";				

				$Tabs .= "<li><a href='#profile'>Profile";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='profile'>";
					$TabContent .= "<div id='dict'><h3>in Profile</h3></div>";	
					$TabContent .= pnlProfile($ProfileId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
			    
			    $Tabs .= "<li><a href='#relationships'>Relationships";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='relationships'>";
					$TabContent .= "<h3>Relationships</h3>";

					if (count($objProfileClass->ProfileRelationshipIds) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Label</th><th>Links to</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objProfileClass->ProfileRelationshipIds as $ProfileRelId){
							
							$objProfileRel = $objProfile->Relationships[$ProfileRelId];
							
							if (isset($Dicts->Dictionaries[$objProfileRel->DictId]->Relationships[$objProfileRel->RelId])){
								$objRel = $Dicts->Dictionaries[$objProfileRel->DictId]->Relationships[$objProfileRel->RelId];

								$num = $num + 1;

								$TabContent .= "<tr>";
								
								$TabContent .= "<td><a href='profilerelationship.php?profileid=".$objProfile->Id."&profilerelid=".$objProfileRel->Id."'>".$objProfileRel->Id."</a></td>";
								switch ($objProfileRel->Inverse){
									case true:
										$TabContent .= "<td>".$objRel->InverseLabel."</td>";
										break;
									default:
										$TabContent .= "<td>".$objRel->Label."</td>";
										break;										
								}
								
								$TabContent .= "<td>";
								if (!is_null($objProfileRel->ObjectProfileClassId)){
									$objObjectProfileClass = $objProfile->Classes[$objProfileRel->ObjectProfileClassId];
									if ($objObjectClass = $Dicts->getClass($objObjectProfileClass->DictId,$objObjectProfileClass->ClassId)){
										$TabContent .= $objObjectClass->Label;
									}
								}
								
								
								$TabContent .= "</td>";
								
							}
						}
				 		$TabContent .= '</table>';
					}

					if ($objProfile->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='profilerelationship.php?profileid=$ProfileId&profileclassid=$ProfileClassId&mode=new'>&bull; add</a></li> </ul></div>";
					}
	
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    			    
				break;
			case 'new':
			case 'edit':
				
				if (empty($ClassId)){
					
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#thisclasses'>Classes in this Group</a></li>";
					
					$optTabContent .= "<div class='tabContent' id='thisclasses'>";
					$optTabContent .= funSelectClass("this", $objSuperClass);				
					$optTabContent .= "</div>";
					
					$optTabs .= "<li><a href='#myclasses'>Classes in My Groups</a></li>";
					$optTabContent .= "<div class='tabContent' id='myclasses'>";
					$optTabContent .= funSelectClass("my", $objSuperClass);
					$optTabContent .= "</div>";
					
					$optTabs .= "<li><a href='#publishedclasses'>Classes in Published Dictionaries</a></li>";
					$optTabContent .= "<div class='tabContent' id='publishedclasses'>";						
					$optTabContent .= funSelectClass("published", $objSuperClass);
					$optTabContent .= "</div>";
					
					if (!empty($optTabs)){
						$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					
				}
				else
				{								
					$PanelB .= '<form method="post" action="doProfileClass.php">';
	
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
							$PanelB .= $ProfileClassId;
							$PanelB .= '</td>';
						$PanelB .= '</tr>';					
					}

					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Class';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= pnlClass($DictId, $ClassId);
						$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
						$PanelB .= "<input type='hidden' name='classid' value='$ClassId'/>";
												
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
					
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Create?';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= "<input type='checkbox' name='create' value='true' ";
						switch ($Create){
							case true:
								$PanelB .= " checked='true' ";
								break;
						}			
						$PanelB .= '/></td>';
					$PanelB .= '</tr>';					

					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Select?';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= "<input type='checkbox' name='select' value='true' ";
						switch ($Select){
							case true:
								$PanelB .= " checked='true' ";
								break;
						}			
						$PanelB .= '/></td>';
					$PanelB .= '</tr>';					
					
				
					$PanelB .= '<tr>';
						$PanelB .= '<td/>';
						$PanelB .= '<td>';
						
						switch ($Mode){
							case 'new':
								$PanelB .= '<input type="submit" value="Add Class to Profile">';
								break;
							default:
								$PanelB .= '<input type="submit" value="Update Profile Class">';
								break;
						}
						
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
				}

			 	$PanelB .= '</table>';
				$PanelB .= '</form>';
				
				break;
				
			case 'delete':
				
				$PanelB .= pnlProfileClass( $ProfileId, $ProfileClassId );

				$PanelB .= "<a href='doProfileClass.php?profileid=$ProfileId&profileclassid=$ProfileClassId&mode=delete'>confirm remove class from the profile?</a><br/>";
				
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

	
function funSelectClass($Selection='this', $objSuperClass = null){

	global $System;
	global $Mode;
	global $objProfile;
	
	global $Dicts;
	
	global $ReturnURL;

	$optClassList = array();
	if (!is_null($objSuperClass)){
		$optClassList = $Dicts->SubClasses($objSuperClass->DictId, $objSuperClass->Id);
		$optClassList[] = $objSuperClass;
	}	

	$Content = "";

	$TabId = "classes";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$DictFieldName = "dictid";
	$ClassFieldName = "classid";
	
	
	$opts = array();

		
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				if ($optDict->GroupId == $objProfile->GroupId){					
					foreach ($optDict->Classes as $optClass){
						$Select = true;
						if (!is_null($objSuperClass)){
							$Select = false;
							foreach ($optClassList as $ClassList){
								if ($ClassList->DictId == $optClass->DictId){
									if ($ClassList->Id == $optClass->Id){
										$Select = true;
										continue;
									}
								}
							}
						}
						if ($Select === true){
							$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
						}
					}
				}
			}
			break;
			
		case "my":
			
			foreach ($Dicts->Dictionaries as $optDict){
				
				$optGroup = new clsGroup($optDict->GroupId);
				if (!$optGroup->canEdit){
					continue;
				}				
				
				foreach ($optDict->Classes as $optClass){
					
					$Select = true;
					if (!is_null($objSuperClass)){
						$Select = false;
						foreach ($optClassList as $ClassList){
							if ($ClassList->DictId == $optClass->DictId){
								if ($ClassList->Id == $optClass->Id){
									$Select = true;
									continue;
								}
							}
						}
					}
					
					if ($Select === true){
						$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
					}
				}
			}
			break;
			
		case "published":
			
			foreach ($Dicts->Dictionaries as $optDict){

				if (!$optDict->Publish){
					continue;
				}				

				foreach ($optDict->Classes as $optClass){
					
					$Select = true;
					if (!is_null($objSuperClass)){
						$Select = false;
						foreach ($optClassList as $ClassList){
							if ($ClassList->DictId == $optClass->DictId){
								if ($ClassList->Id == $optClass->Id){
									$Select = true;
									continue;
								}
							}
						}
					}
					
					if ($Select === true){
						$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
					}
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