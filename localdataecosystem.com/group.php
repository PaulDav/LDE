<?php
 
	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlGroup.php");
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");
	require_once("class/clsProfile.php");
	require_once("class/clsView.php");
	require_once("class/clsData.php");
	require_once("class/clsShape.php");
	
	
	define('PAGE_NAME', 'group');

	session_start();
	
	$System = new clsSystem();
	
	$Page = new clsPage();	

	$Mode = 'view';
	$GroupId = '';
	$Name = '';
	$Description = '';
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";

	if (isset($_REQUEST['mode'])){
		$Mode = $_REQUEST['mode'];
	}
	
	try {

		$Dicts = new clsDicts();
		$Shapes = new clsShapes();
		
		$Page->Title = $Mode." group";
		$PanelB .= "<h1>".$Page->Title."</h1>";
			
		if (isset($_REQUEST['groupid'])){
			$GroupId = $_REQUEST['groupid'];
		}
		
		switch ($Mode){
			case 'new':
				break;
			default:
				if ($GroupId =='') {
					throw new exception("GroupId not specified");
				}

				break;
		}


		if (!empty($GroupId)){
			$objGroup = new clsGroup($GroupId);
			if ($objGroup->canView === false){
				throw new exception("You cannot view this Group");
			}
			$Name = $objGroup->Name;
			$Description = $objGroup->Description;
		}		
		
		if ($System->Session->Error){
			$FormFields = GetUserInput(PAGE_NAME);
			if (isset($FormFields['groupid'])){
				$GroupId = $FormFields['groupid'];
			}
			if (isset($FormFields['name'])){
				$Name = $FormFields['name'];
			}
			if (isset($FormFields['description'])){
				$Description = $FormFields['description'];
			}				
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');
			
		}
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objGroup->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if (!$System->LoggedOn){
					throw new Exception("Please log on");
				}
				break;
			case 'edit':
				if ($objGroup->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objGroup->canControl){
					$ModeOk = true;
				}
				break;
		}
		
				
		switch ($Mode){
			case 'view':
				$PanelB .= pnlGroup( $GroupId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objGroup->canControl === true){
					$PanelB .= "<li><a href='group.php?groupid=$GroupId&mode=edit'>&bull; edit</a></li> ";
				}
				$PanelB .= "</ul></div>";
				
				if ($objGroup->canControl === true){
				    $Tabs .= "<li><a href='#picture'>Picture";
				    $num = 0;
				    
					$TabContent .= "<div class='tabContent hide' id='picture'>";

					if (isset($_FILES['grpImage'])){
		
						if ($_FILES['grpImage']['error'] > 0) {
						    switch ($_FILES['grpImage']['error']) {
						      case 1:
						      	throw new exception('File exceeded upload_max_filesize');
							  	break;
						      case 2:
						      	throw new exception('File exceeded max_file_size');
							  	break;
						      case 3:
						      	throw new exception('File only partially uploaded');
							  	break;
						      case 4:
						      	throw new exception('No file uploaded');
							  case 6:
							  	throw new exception('Cannot upload file: No temp directory specified.');
					  			break;
							  case 7:
							  	throw new exception('Upload failed: Cannot write to disk.');
							  	break;
						    }
						}
			
			
						$image = $_FILES['grpImage'];
						if (!is_uploaded_file($image['tmp_name'])) {
				            throw new Exception('File is not an uploaded file');
				        }
				        $info = getImageSize($image['tmp_name']);
				        if (!$info) {
				            throw new Exception('File is not an image');
				        }
						        
						$sql = sprintf(
				            "insert into tbl_image (imgFilename, imgMimeType, imgSize, imgData, imgUser)
				                values ('%s', '%s', %d, '%s', %d)",
				            $System->db->real_escape_string($image['name']),
				            $System->db->real_escape_string($info['mime']),
				            $image['size'],
				            $System->db->real_escape_string(file_get_contents($image['tmp_name'])),
				            $System->User->Id	            
				            );
	        
						$result = $System->db->query($sql);
						if (!$result) {
						    throw new Exception('Could not load the image to the database. '.$System->db->error);
					  	}
					  
					  	$imgId = $System->db->insert_id;
					  	
					  	$sql = "update tbl_group set grpImage=$imgId where grpRecnum=$GroupId";
				
					  	$result = $System->db->query($sql);
					    if (!$result) {
					    	throw new Exception('Could not link group to image');
					  	}
					  	
					  	$objGroup = new clsGroup($GroupId);
					  	
					  	$System->Session->Message = "group picture set ok";
							
					}

					$TabContent .= "<form enctype='multipart/form-data' action='group.php?groupid=$GroupId#picture' method='post'>";
					$TabContent .= '<input type="hidden" name="MAX_FILE_SIZE" value="100000">';
					$TabContent .= 'Group Picture : <input name="grpImage" type="file">';
					$TabContent .= '<input type="submit" value="Send Picture">';
					$TabContent .= '</form>';
				
					if (!$objGroup->Picture == ""){
	 					$TabContent .= '<img src="image.php?Id='.$objGroup->Picture.'" /><br/>';
					}
										
					$TabContent .= "</div>";
					$Tabs .= "</a></li>";
				}
				
				$Tabs .= "<li><a href='#dictionaries'>Dictionaries";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='dictionaries'>";

					$TabContent .= "<div><h3>Dictionaries</h3></div>";
					
					if ($objGroup->canEdit === true){					
						$TabContent .= "<a href='dict.php?mode=new&groupid=$GroupId'>&bull; add</a><br/>";
					}

					if (count($objGroup->DictionaryIds) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>by</th>";
							$TabContent .= "<th>Id</th><th>Name</th><th>Description</th><th>Published?</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objGroup->DictionaryIds as $DictId){
							
							$num = $num + 1;
							
							$objDict = $Dicts->Dictionaries[$DictId];
							$TabContent .= "<tr>";
							
							$objDictUser = new clsUser($objDict->OwnerId);
							
							$TabContent .= "<td>";
							if (!is_null($objDictUser->PictureOf)) {
								$TabContent .= '<img height = "30" src="image.php?Id='.$objDictUser->PictureOf.'" /><br/>';
							}
							$TabContent .= $objDictUser->Name."</td>";
							
							$TabContent .= "<td><a href='dict.php?dictid=".$objDict->Id."'>".$objDict->Id."</a></td>";
							$TabContent .= "<td>".$objDict->Name."</td>";
							
							$TabContent .= "<td>".nl2br(Truncate($objDict->Description))."</td>";
							$TabContent .= "<td>";
							if ($objDict->Publish === true){
								$TabContent .= "yes";
							}
							else
							{
								$TabContent .= "no";
							}
							$TabContent .= "</td>";
							$TabContent .= "</tr>";
							
						}
				 		$TabContent .= '</table>';
					}
				$TabContent .= "</div>";

				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";

			    $Tabs .= "<li><a href='#shapes'>Shapes";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='shapes'>";

					$TabContent .= "<div><h3>Shapes</h3></div>";
					
					if ($objGroup->canEdit === true){					
						$TabContent .= "<a href='shape.php?mode=new&groupid=$GroupId'>&bull; add</a><br/>";
					}

					if (count($objGroup->ShapeIds) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>by</th>";
							$TabContent .= "<th>Id</th><th>Name</th><th>Parent</th><th>Description</th><th>Published?</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objGroup->ShapeIds as $ShapeId){
							
							$num = $num + 1;
							
							if (!isset($Shapes->Items[$ShapeId])){
								continue;
							}
							$objShape = $Shapes->Items[$ShapeId];
							$TabContent .= "<tr>";

							$ShapeUser = new clsUser($objShape->OwnerId);
							$TabContent .= "<td>";
							if (!is_null($ShapeUser->PictureOf)) {
								$TabContent .= '<img height = "30" src="image.php?Id='.$ShapeUser->PictureOf.'" /><br/>';
							}
							$TabContent .= $ShapeUser->Name."</td>";
							
							$TabContent .= "<td><a href='shape.php?shapeid=".$objShape->Id."'>".$objShape->Id."</a></td>";
							
							$TabContent .= "<td>".$objShape->Name."</td>";
														
							$TabContent .= "<td>";
							if (!is_null($objShape->ParentId)){
								$objParentShape  = $Shapes->Items[$objShape->ParentId];
								$TabContent .= "$objParentShape->Name";
							}
							$TabContent .= "</td>";
							
							$TabContent .= "<td>".nl2br(Truncate($objShape->Description))."</td>";
							$TabContent .= "<td>";
							if ($objShape->Publish === true){
								$TabContent .= "yes";
							}
							else
							{
								$TabContent .= "no";
							}
							$TabContent .= "</td>";
						}
				 		$TabContent .= '</table>';
					}
				$TabContent .= "</div>";

				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";
/*			    
				$Tabs .= "<li><a href='#profiles'>Profiles";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='profiles'>";

					$TabContent .= "<div><h3>Profiles</h3></div>";
					
					if ($objGroup->canEdit === true){					
						$TabContent .= "<a href='profile.php?mode=new&groupid=$GroupId'>&bull; add</a><br/>";
					}

					if (count($objGroup->ProfileIds) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>by</th>";
							$TabContent .= "<th>Id</th><th>Name</th><th>Description</th><th>Published?</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objGroup->ProfileIds as $ProfileId){
							
							$num = $num + 1;
							
							if (!isset($Profiles->Items[$ProfileId])){
								continue;
							}
							$Profile = $Profiles->Items[$ProfileId];
							
							$TabContent .= "<tr>";
							
							$ProfileUser = new clsUser($Profile->OwnerId);
							$TabContent .= "<td>";
							if (!is_null($ProfileUser->PictureOf)) {
								$TabContent .= '<img height = "30" src="image.php?Id='.$ProfileUser->PictureOf.'" /><br/>';
							}
							$TabContent .= $ProfileUser->Name."</td>";
							
							$TabContent .= "<td><a href='profile.php?profileid=".$Profile->Id."'>".$Profile->Id."</a></td>";
							$TabContent .= "<td>".$Profile->Name."</td>";
							
							$TabContent .= "<td>".nl2br(Truncate($Profile->Description))."</td>";
							$TabContent .= "<td>";
							if ($Profile->Publish === true){
								$TabContent .= "yes";
							}
							else
							{
								$TabContent .= "no";
							}
							$TabContent .= "</td>";
							$TabContent .= "</tr>";
							
						}
				 		$TabContent .= '</table>';
					}
				$TabContent .= "</div>";

				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";

			    $Tabs .= "<li><a href='#specs'>Import Specifications";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='specs'>";

					$TabContent .= "<div><h3>Import Specifications</h3></div>";
					
					if ($objGroup->canEdit === true){					
						$TabContent .= "<a href='spec.php?mode=new&groupid=$GroupId'>&bull; add</a><br/>";
					}

					if (count($objGroup->SpecIds) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>by</th>";
							$TabContent .= "<th>Id</th><th>Name</th><th>Profile</th><th>Description</th><th>Published?</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objGroup->SpecIds as $SpecId){
							if (isset($Specs->Items[$SpecId])){
							
								$num = $num + 1;						
							
								$objSpec = $Specs->Items[$SpecId];
								$TabContent .= "<tr>";
	
								$SpecUser = new clsUser($objSpec->OwnerId);
								$TabContent .= "<td>";
								if (!is_null($SpecUser->PictureOf)) {
									$TabContent .= '<img height = "30" src="image.php?Id='.$SpecUser->PictureOf.'" /><br/>';
								}
								$TabContent .= $SpecUser->Name."</td>";
								
								$TabContent .= "<td><a href='spec.php?specid=".$objSpec->Id."'>".$objSpec->Id."</a></td>";
								
								$TabContent .= "<td>".$objSpec->Name."</td>";
															
								$TabContent .= "<td>";
								if (!is_null($objSpec->ProfileId)){
									$objSpecProfile  = $Profiles->Items[$objSpec->ProfileId];
									$TabContent .= "$objSpecProfile->Name";
								}
								$TabContent .= "</td>";
								
								$TabContent .= "<td>".nl2br(Truncate($objSpec->Description))."</td>";
								$TabContent .= "<td>";
								if ($objSpec->Publish === true){
									$TabContent .= "yes";
								}
								else
								{
									$TabContent .= "no";
								}
								$TabContent .= "</td>";
								
								$TabContent .= "</tr>";
							}
						}
						
				 		$TabContent .= '</table>';
				 		
					}
				$TabContent .= "</div>";

				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";

			    
				$Tabs .= "<li><a href='#views'>Views";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='views'>";

					$TabContent .= "<div><h3>Views</h3></div>";
					
					if ($objGroup->canEdit === true){					
						$TabContent .= "<a href='view.php?mode=new&groupid=$GroupId'>&bull; add</a><br/>";
					}

					if (count($objGroup->ViewIds) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>by</th>";
							$TabContent .= "<th>Id</th><th>Name</th><th>Description</th><th>Published?</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objGroup->ViewIds as $ViewId){
							
							$num = $num + 1;
							
							$objView = new clsView($ViewId);
							$TabContent .= "<tr>";
							
							$ViewUser = new clsUser($objView->OwnerId);
							$TabContent .= "<td>";
							if (!is_null($ViewUser->PictureOf)) {
								$TabContent .= '<img height = "30" src="image.php?Id='.$ViewUser->PictureOf.'" /><br/>';
							}
							$TabContent .= $ViewUser->Name."</td>";
							
							$TabContent .= "<td><a href='view.php?viewid=".$objView->Id."'>".$objView->Id."</a></td>";
							$TabContent .= "<td>".$objView->Name."</td>";
							
							$TabContent .= "<td>".nl2br(Truncate($objView->Description))."</td>";
							$TabContent .= "<td>";
							if ($objView->Publish === true){
								$TabContent .= "yes";
							}
							else
							{
								$TabContent .= "no";
							}
							$TabContent .= "</td>";
						}
				 		$TabContent .= '</table>';
					}
				$TabContent .= "</div>";

				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";
*/			    			    
				$Tabs .= "<li><a href='#users'>Users";
				$num = 0;
				
				$TabContent .= "<div class='tabContent hide' id='users'>";

					$TabContent .= "<div><h3>Users</h3></div>";
					
					if ($objGroup->canControl === true){					
						$TabContent .= "<a href='usergroup.php?mode=new&groupid=$GroupId'>&bull;  invite a new user to join</a><br/>";
					}
									
					if (count($objGroup->UserGroupIds) > 0){
						
						$TabContent .= "<table><thead><tr><th>User</th><th>Rights</th></tr></thead>";
						$TabContent .= "<tbody>";
						foreach ($objGroup->UserGroupIds as $UserGroupId){
							
							$objUserGroup = new clsUserGroup($UserGroupId);
							
							if ($objUserGroup->Status == 100){ // member
								$num = $num + 1;
															
								$objGroupUser = new clsUser($objUserGroup->UserId);
								
								$TabContent .= "<tr><td>";
								
									if (!is_null($objGroupUser->PictureOf)) {
										$TabContent .= "<img height = '30' src='image.php?Id=".$objGroupUser->PictureOf."' /><br/>";
									}
									if ($objGroup->canControl){
										$TabContent .= "<a href='usergroup.php?usergroupid=$UserGroupId'>".$objGroupUser->Name;
									}
									else
									{
										$TabContent .= $objGroupUser->Name;
									}
									$TabContent .= "</td>";
								
								$TabContent .= "<td>".$objUserGroup->RightsName."</td>";
								
								$TabContent .= "</tr>";
							}
						}
						$TabContent .= "</tbody></table>";
						
					}
				$TabContent .= "</div>";
			    				
				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";

				
				if ($objGroup->canControl === true){
				    $Tabs .= "<li><a href='#invites'>Invites and Requests";
				    $num = 0;
				    
					$TabContent .= "<div class='tabContent hide' id='invites'>";

						$TabContent .= "<div><h3>Invited and Requested Users</h3></div>";
										
						if (count($objGroup->UserGroupIds) > 0){
							
							$TabContent .= "<table><thead><tr><th>User</th><th>Status</th><th>Rights</th></tr></thead>";
							$TabContent .= "<tbody>";
							foreach ($objGroup->UserGroupIds as $UserGroupId){
								
								$objUserGroup = new clsUserGroup($UserGroupId);
								
								if (!($objUserGroup->Status == 100)){ // member
								
									$objGroupUser = new clsUser($objUserGroup->UserId);
									
									$TabContent .= "<tr><td>";
									
										if (!is_null($objGroupUser->PictureOf)) {
											$TabContent .= "<img height = '30' src='image.php?Id=".$objGroupUser->PictureOf."' /><br/>";
										}
										if (!is_null($objGroupUser->Name)){
											$TabContent .= "<a href='usergroup.php?usergroupid=$UserGroupId'>".$objGroupUser->Name;
										}
										else
										{
											$TabContent .= "<a href='usergroup.php?usergroupid=$UserGroupId'>".$objGroupUser->Email;
										}
										$TabContent .= "</td>";
									
									$TabContent .= "<td>".$objUserGroup->StatusName."</td><td>".$objUserGroup->RightsName."</td>";
									
									$TabContent .= "</tr>";
								}
							}
							$TabContent .= "</tbody></table>";
							
						}
					$TabContent .= "</div>";
				    
				    
					if ($num > 0 ){
		    			$Tabs .= "($num)";							
					}
				    $Tabs .= "</a></li>";				    
				}
			    
				
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doGroup.php">';

				$onErrorURL = $_SERVER['SCRIPT_NAME'];
				$QueryString = $_SERVER['QUERY_STRING'];
				$onErrorURL .= '?'.$QueryString;
				$PanelB .= "<input type='hidden' name='onErrorURL' value='$onErrorURL'/>";

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $GroupId == '')){
					$PanelB .= "<input type='hidden' name='groupid' value='$GroupId'/>";
				}

				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $GroupId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Name';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="name" size="100" maxlength="100" value="'.$Name.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Description';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<textarea rows = "5" cols = "80" name="description" >';
					$PanelB .= $Description;
					$PanelB .= '</textarea>';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';		

				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Publish?';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					
					$PanelB .= "<select name='publish'>";
					$PanelB .= "<option>Yes</option>";
					$PanelB .= "<option";
					if (isset($objGroup)){
						if ($objGroup->Publish === false){
							$PanelB .= " selected='true' ";
						}
					}
					else
					{
						$PanelB .= " selected='true' ";
					}
					$PanelB .= ">No</option>";
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				

				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Group">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Group">';
							break;
					}
		
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';
			 	
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
		
?>