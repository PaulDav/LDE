<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrg.php");
	require_once("panel/pnlSet.php");
	
	require_once("class/clsData.php");
	
	require_once("class/clsRights.php");
	require_once("class/clsLibrary.php");
	require_once("class/clsShape.php");
	
	
	define('PAGE_NAME', 'set');

	session_start();
	
	$System = new clsSystem();
	
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();
	
	$Script = '';
	$Script .= "<script type='text/javascript' src='java/ajax.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getSet.js'></script>";
	$Page->Script .= $Script;	
	
	try {

		$Shapes = new clsShapes();
		$Specs = new clsSpecs();
		$Orgs = new clsOrganisations();
		$Defs = new clsDefinitions();
		$Licences = new clsLicences();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
				
		$SetId = '';
		$OrgId = '';
		$Name = '';
		$Source = '';
		$Description = '';
		$Status = 1;
		$Context = 1;
		$LicenceType = 1;

		if (isset($_REQUEST['setid'])){
			$SetId = $_REQUEST['setid'];
		}
		
		if (isset($_REQUEST['orgid'])){
			$OrgId = $_REQUEST['orgid'];
		}
		

		switch ($Mode){
			case 'new':
				if ($OrgId =='') {
					throw new exception("OrgId not specified");
				}
				
				break;
			default:
				if ($SetId =='') {
					throw new exception("SetId not specified");
				}

				break;
		}

		if (!empty($SetId)){
			$objSet = new clsSet($SetId);
			$OrgId = $objSet->OrgId;
			$Name = $objSet->Name;
			$Source = $objSet->Source;			
			$Status = $objSet->Status;			
			$Context = $objSet->ContextId;			
			$LicenceType = $objSet->LicenceType;
			
		}		
		

		if ($System->Session->Error){
			if (isset($FormFields['orgid'])){
				$OrgId = $FormFields['orgid'];
			}
			if (isset($FormFields['name'])){
				$Name = $FormFields['name'];
			}
			if (isset($FormFields['source'])){
				$Source = $FormFields['source'];
			}				
			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if (!empty($OrgId)){
			if (!isset($Orgs->Items[$OrgId])){
				throw new Exception("Invalid Organisation");
			}			
			$objOrg = $Orgs->Items[$OrgId];
			if ($objOrg->canView === false){
				throw new exception("You cannot view this Organisation");
			}
		}
		
		$Page->Title = $Mode." set";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
				
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objSet->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objOrg->canEdit){
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
			case 'clear':
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

				$Script = '';
				$Script .= "<script>\n";				
				$Script .= "function init(){ \n";
				$Script .= "    getSet($SetId); ";
				$Script .= "} \n";
				$Script .= "</script>\n";
				$Page->Script .= $Script;				
				
				$PanelB .= pnlSet( $objSet );
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objSet->canEdit === true){
					$PanelB .= "<li><a href='set.php?setid=$SetId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objSet->canControl === true){
					$PanelB .= "<li><a href='set.php?setid=$SetId&mode=clear'>&bull; clear</a></li> ";
				}
				
				if ($objSet->canControl === true){
					$PanelB .= "<li><a href='set.php?setid=$SetId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$PanelB .= "</ul></div>";				

				$Tabs .= "<li><a href='#org'>Organisation";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='org'>";
					$TabContent .= "<h3>by Organisation</h3>";	
					$TabContent .= pnlOrg($OrgId);
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";				

			    $Tabs .= "<li><a href='#log'>Log";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='log'>";
					$TabContent .= "<h3>Log</h3>";
					$TabContent .= "<table><thead><tr><th>Date/Time</th><th>Status</th><th>By</th></tr></thead><tbody>";
					foreach ($objSet->Logs as $objLog){
						$TabContent .= "<tr>";
						
						$TabContent .= "<td>".$objLog->DateTime."</td>";
						$TabContent .= "<td>".$objLog->StatusText."</td>";
						$TabContent .= "<td>";
						$objUser = new clsUser($objLog->UserId);
						if (!is_null($objUser->PictureOf)) {
							$TabContent .= '<img height = "30" src="image.php?Id='.$objUser->PictureOf.'" /><br/>';
						}
						$TabContent .= $objUser->Name;
						$TabContent .= "</td>";
						
						$TabContent .= "</tr>";
					}
					$TabContent .= "</tbody></table>";
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
			    
			    $Tabs .= "<li><a href='#purposes'>Purposes";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='purposes'>";
					$TabContent .= "<h3>Purposes</h3>";	

					if (count($objSet->SetPurposes) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Name</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objSet->SetPurposes as $objSetPurpose){
							$PurposeId = $objSetPurpose->PurposeId;
							if (isset($Defs->Items[$PurposeId])){
								$objDef = $Defs->Items[$PurposeId];
								$num = $num + 1;
								
								$TabContent .= "<tr>";
								
								$TabContent .= "<td><a href='setpurpose.php?setid=$SetId&setpurposeid=".$objSetPurpose->Id."'>".$objSetPurpose->Id."</a></td>";
								
								$TabContent .= "<td>".$objDef->Name."</td>";							
								$TabContent .= "<td>".nl2br(Truncate($objDef->Description))."</td>";
								
								$TabContent .= "</tr>";
							}
							
						}
						$TabContent .= '</table>';
					}
					
					
					if ($objSet->canEdit === true){
						$TabContent .= "<br/><a href='setpurpose.php?setid=$SetId&mode=new'>&bull; add Purpose</a>";
					}

				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";

			    $Tabs .= "<li><a href='#licences'>Licences";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='licences'>";
					$TabContent .= "<h3>Licences</h3>";	

					$TabContent .= "<table class='list'>";
					$TabContent .= "<thead><tr><th>Id</th><th>Name</th><th>Description</th></tr></thead><body>";
					
					foreach ($Licences->Items as $objLicence){
						if (isset($objLicence->SetIds[$SetId])){
							$num = $num + 1;
							$TabContent .= "<tr><td><a href='licence.php?licenceid=".$objLicence->Id."'>".$objLicence->Id."</a></td><td>".$objLicence->Name."</td><td>".truncate(nl2br($objLicence->Id))."</td></tr>";						
						}
					}
	
					$TabContent .= "</tbody></table>";
										
					if ($objSet->canEdit === true){
						$TabContent .= "<br/><a href='licence.php?setid=$SetId&mode=new'>&bull; add Licence</a>";
					}

				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    $Tabs .= "<li><a href='#shapes'>uses Shapes";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='shapes'>";
					$TabContent .= "<h3>uses Shapes</h3>";	

					if (count($objSet->SetShapes) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Name</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objSet->SetShapes as $objSetShape){
							$ShapeId = $objSetShape->ShapeId;
							if (isset($Shapes->Items[$ShapeId])){
								$objShape = $Shapes->Items[$ShapeId];
								$num = $num + 1;
								
								$TabContent .= "<tr>";
								
								$TabContent .= "<td><a href='setshape.php?setid=$SetId&setshapeid=".$objSetShape->Id."'>".$objSetShape->ShapeId."</a></td>";
								
								$TabContent .= "<td>".$objShape->Name."</td>";							
								$TabContent .= "<td>".nl2br(Truncate($objShape->Description))."</td>";
								
								$TabContent .= "</tr>";
							}
							
						}
				 		$TabContent .= '</table>';
					}
										
					
					if ($objSet->canEdit === true){
						$TabContent .= "<br/><a href='setshape.php?setid=$SetId&mode=new'>&bull; add Shape</a>";
					}

				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    
/*			    
			    $Tabs .= "<li><a href='#profiles'>uses Profiles";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='profiles'>";
					$TabContent .= "<h3>uses Profiles</h3>";	

					if (count($objSet->SetProfiles) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Name</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objSet->SetProfiles as $objSetProfile){
							$ProfileId = $objSetProfile->ProfileId;
							if (isset($Profiles->Items[$ProfileId])){
								$objProfile = $Profiles->Items[$ProfileId];
								$num = $num + 1;
								
								$TabContent .= "<tr>";
								
								$TabContent .= "<td><a href='setprofile.php?setid=$SetId&setprofileid=".$objSetProfile->Id."'>".$objSetProfile->Id."</a></td>";
								
								$TabContent .= "<td>".$objProfile->Name."</td>";							
								$TabContent .= "<td>".nl2br(Truncate($objProfile->Description))."</td>";
								
								$TabContent .= "</tr>";
							}
							
						}
				 		$TabContent .= '</table>';
					}
										
					
					if ($objSet->canEdit === true){
						$TabContent .= "<br/><a href='setprofile.php?setid=$SetId&mode=new'>&bull; add Profile</a>";
					}

				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
*/			    

/*			    
			    if ($objSet->canEdit){
			    	if (count($objSet->SetProfiles) > 0){
			    		$Tabs .= "<li><a href='#import'>Import</a></li>";
						$TabContent .= "<div class='tabContent hide' id='import'>";
						$TabContent .= "<h3>Import</h3>";

						$TabContent .= "<div class='sdbluebox'>";
						
						$TabContent .= "<form method='post' action='doImport.php'>";
						$TabContent .= "<input type='hidden' name='setid' value='$SetId'/>";
						
						$TabContent .= "<table>";
						$TabContent .= "<tr><th>Import Spec</th><td>";
						$TabContent .= "<select name='specid'>";
						$TabContent .= "<option/>";
						foreach ($Specs->Items as $optSpec){
							$useSpec = false;
							foreach ($objSet->SetProfiles as $objSetProfile){
								if ($objSetProfile->ProfileId == $optSpec->ProfileId){
									$useSpec = true;
								}
							}
							if ($useSpec){
								$TabContent .= "<option value='".$optSpec->Id."'>".$optSpec->Name."</option>";
							}
						}
						$TabContent .= "</select>";
						$TabContent .= "</td></tr>";
						
						
						if ($handle = opendir('./import')) {
							
							$TabContent .= "<tr><th>File</th><td>";
							$TabContent .= "<select name='filepath'>";
							$TabContent .= "<option/>";
							
						    while (false !== ($entry = readdir($handle))) {
						        if ($entry != "." && $entry != "..") {
						        	
						        	$temp = explode( '.', $entry );
									$ext = array_pop( $temp );
									$Id = implode( '.', $temp );
									
									$TabContent .= "<option>$entry</option>";
									
						        }
						    }
						    closedir($handle);
						}
						
						$TabContent .= "</select></td></tr>";						
						
						$TabContent .= "</table>";
						
						
						$TabContent .= "<input type='submit' value='Import'/>";
						$TabContent .= "</form>";						
						$TabContent .= "</div>";
						
						$TabContent .= "</div>";
			    	}
			    }
*/
			    
				$Tabs .= "<li><a href='#documents'>Documents";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='documents'>";
					$TabContent .= "<h3>Documents</h3>";

					$TabContent .= "<div id='setdocuments'>";
					$TabContent .= "</div>";
/*
					if (count($objSet->DocumentIds) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Shape</th><th>Subject Identifier</th><th>Subject Name</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objSet->DocumentIds as $DocId){
							
							$objDoc = new clsDocument($DocId);
							
							$objShape = null;
							if (isset($Shapes->Items[$objDoc->ShapeId])){
								$objShape = $Shapes->Items[$objDoc->ShapeId];
							}
							$num = $num + 1;
							
							$TabContent .= "<tr>";
							
							$TabContent .= "<td><a href='document.php?docid=".$objDoc->Id."'>".$objDoc->Id."</a></td>";
							$TabContent .= "<td>";
							if (!is_null($objShape)){
								$TabContent .= $objShape->Name;
							}
							$TabContent .= "</td>";
							
							
							$objSubject = null;
							$SubjectForms = $objDoc->SubjectForms;
							if (count($SubjectForms) > 0){
								$objSubjectForm = current($SubjectForms);								
								$objSubject = new clsSubject($objSubjectForm->SubjectId);
							}
							
							$TabContent .= "<td>";
							if (!is_null($objSubject)){
								$TabContent .= $objSubject->Identifier;
							}
							$TabContent .= "</td>";
							$TabContent .= "<td>";
							if (!is_null($objSubject)){
								$TabContent .= $objSubject->Name;
							}
							$TabContent .= "</td>";
							
							$TabContent .= "</tr>";
							
						}
				 		$TabContent .= '</table>';
					}
*/										
					if ($objSet->canEdit === true){
/*						
						foreach ($objSet->SetProfiles as $objSetProfile){
							if (isset($Profiles->Items[$objSetProfile->ProfileId])){
								$optProfile = $Profiles->Items[$objSetProfile->ProfileId];
								$TabContent .= "<br/><a href='document.php?setid=$SetId&profileid=$optProfile->Id&mode=new'>&bull; add new document to create a ".$optProfile->Name."</a>";
								$TabContent .= "<br/><a href='document.php?setid=$SetId&profileid=$optProfile->Id&mode=new&method=amend'>&bull; add new document to amend a ".$optProfile->Name."</a>";
							}
						}
*/
						foreach ($objSet->SetShapes as $objSetShape){
							if (isset($Shapes->Items[$objSetShape->ShapeId])){
								$optShape = $Shapes->Items[$objSetShape->ShapeId];
								$TabContent .= "<br/><a href='document.php?setid=$SetId&shapeid=$optShape->Id&mode=new'>&bull; add new document to create a ".$optShape->Name."</a>";
							}
						}
						
						
					}

				$TabContent .= "</div>";
			    $Tabs .= "<span id='setdocumentscount'></span></a></li>";
			    
				$Tabs .= "<li><a href='#statements'>Statements";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='statements'>";
					$TabContent .= "<h3>Statements</h3>";	
					
					$TabContent .= "<div id='setstatements'>";
					$TabContent .= "</div>";
					
/*
					if (count($objSet->StatementIds) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<td>Doc</td><th>Id</th><th>Type</th><th>About</th><th>Subject</th><th>Link</th><th>Value</th><th>Eff From</th><th>Eff To</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objSet->StatementIds as $StatId){
							$objStat = new clsStatement($StatId);
							$num = $num + 1;
							
							$TabContent .= "<tr>";

							$TabContent .= "<td>".$objStat->DocId."</td>";
							
							$TabContent .= "<td><a href='statement.php?statid=".$objStat->Id."'>".$objStat->Id."</a></td>";
							
							$TabContent .= "<td>".$objStat->TypeLabel."</td>";
							
							$TabContent .= "<td>".$objStat->AboutId."</td>";
							
							$TabContent .= "<td>".$objStat->SubjectId."</td>";
							
							$TabContent .= "<td>";
							if (!is_null($objStat->LinkId)){
								$objDictItem = new clsDictItem($objStat->LinkDictId, $objStat->LinkId, $objStat->TypeId);
								$TabContent .= $objDictItem->Label;
							}
							$TabContent .= "</td>";
							
							$TabContent .= "<td>";
							if (!is_null($objStat->ObjectId)){
								$TabContent .= $objStat->ObjectId;
							}
							elseif (!is_null($objStat->Value)){
								$TabContent .= nl2br(Truncate($objStat->Value));								
							}
							$TabContent .= "</td>";
							
							$TabContent .= "<td>".$objStat->EffectiveFrom."</td>";
							$TabContent .= "<td>".$objStat->EffectiveTo."</td>";
														
							$TabContent .= "</tr>";
							
						}
				 		$TabContent .= '</table>';
				 		
					}
*/										
					
				$TabContent .= "</div>";
				$Tabs .= "<span id='setstatementscount'></div></a></li>";
				
			    			    
			    $Tabs .= "<li><a href='#export'>Export</a></li>";
			    $TabContent .= "<div class='tabContent hide' id='export'>";
			    
				$TabContent .= "</div>";
				
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doSet.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $SetId == '')){
					$PanelB .= "<input type='hidden' name='setid' value='$SetId'/>";
				}
				if (!( $OrgId == '')){
					$PanelB .= "<input type='hidden' name='orgid' value='$OrgId'/>";
				}				
										
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $SetId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Name';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="name" size="30" maxlength="100" value="'.$Name.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Source';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="source" size="30" maxlength="100" value="'.$Source.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
/*				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Effective';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<table><thead><tr><th>On</th><td>or</td><th>Between</th></thead></tr>";
					$PanelB .= "<tr><td><input name='effat' type='date' class='datepicker' id='effat' size='10'/></td><td/><td><input name='efffrom' type='date'  class='datepicker' id='efffrom' size='10'/><br/><input name='effto' type='date'  class='datepicker' id='effto' size='10'/></td></tr>";
					$PanelB .= "</table>";
					$PanelB .= "</td>";
				$PanelB .= '</tr>';
*/					

				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Status';
					$PanelB .= '</th>';
					$PanelB .= '<td>';					
					$PanelB .= "<select name='status'>";
					foreach ($System->Config->SetStatusTypes as $optStatus=>$optStatusText){
						$PanelB .= "<option value='$optStatus'";
						if ($optStatus == $Status){
							$PanelB .= " selected='true'";
						}
						$PanelB .= ">$optStatusText</option>";
					}
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Context';
					$PanelB .= '</th>';
					$PanelB .= '<td>';					
					$PanelB .= "<select name='context'>";
					$PanelB .= '<option/>';
					foreach ($System->Config->SetContextTypes as $optContextId=>$optContext){
						$PanelB .= "<option value='$optContextId'";
						if ($optContextId == $Context){
							$PanelB .= " selected='true'";
						}
						$PanelB .= ">$optContext->Name</option>";
					}
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Licence Type';
					$PanelB .= '</th>';
					$PanelB .= '<td>';					
					$PanelB .= "<select name='licencetype'>";
					$PanelB .= '<option/>';
					foreach ($System->Config->SetLicenceTypeTypes as $optLicenceType=>$optLicenceTypeText){
						$PanelB .= "<option value='$optLicenceType'";
						if ($optLicenceType == $LicenceType){
							$PanelB .= " selected='true'";
						}
						$PanelB .= ">$optLicenceTypeText</option>";
					}
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Set">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Set">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				$PanelB .= pnlSet( $objSet );
				
				$PanelB .= "<a href='doSet.php?setid=$SetId&mode=delete'>confirm delete?</a><br/>";
				
				break;

			case 'clear':
				
				$PanelB .= pnlSet( $objSet );
				
				$PanelB .= "<a href='doSet.php?setid=$SetId&mode=clear'>confirm clear?</a><br/>";
				
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
	$Page->Display();
	$System->db->close();
	exit;
	
?>