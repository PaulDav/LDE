<?php

	require_once("class/clsSystem.php");
	
	require_once("class/clsPage.php");
	require_once("function/utils.inc");
			
	define('PAGE_NAME', 'account');
		
	session_start();
	
	$System = new clsSystem();
		
	$Page = new clsPage();
	
	$Mode = "";
	$Selection = "";
	
	$UserName = "";
	$Email = "";
	
	$PanelB = '';
	

	try {

		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$Fail = false;
				
		SaveUserInput(PAGE_NAME);
		$FormFields = getUserInput(PAGE_NAME);

		if ($System->Fail){
			if (isset($FormFields['Mode'])){
				$Mode = $FormFields['Mode'];
			}
			if (isset($FormFields['Selection'])){
				$Selection = $FormFields['Selection'];
			}
			
			if (isset($FormFields['UserName'])){
				$UserName = $FormFields['UserName'];
			}
			if (isset($FormFields['Email'])){
				$Email = $FormFields['Email'];
			}
		}		
						
		// check if the user has just uploaded a picture
	
		if (isset($_FILES['userImage'])){
		
			if ($_FILES['userImage']['error'] > 0) {
			    switch ($_FILES['userImage']['error']) {
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
			
			
			$image = $_FILES['userImage'];
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
			    throw new Exception('Could not load the image to the database');
		  	}
		  
		  	$imgId = $System->db->insert_id;
		  	
		  	$sql = 'update tbl_user set usrImage='.$imgId.' where usrRecnum='.$System->User->Id;
	
		  	$result = $System->db->query($sql);
		    if (!$result) {
		    	throw new Exception('Could not link user to image');
		  	}
		  	
		  	$System->User = new clsUser($System->User->Id);
		  	
		  	$System->Session->Message = "account picture set ok";
			
		}
				
		if (!$System->LoggedOn){
			$Mode = "new";
		}
		if ($System->Session->HashLogin){
			$Mode = "hash";
		}
		
		
		switch ($Mode){
			case "new":
			case "hash":
			case "view":
			case "edit":
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}
				
		
		if (isset($_REQUEST["selection"])){
			$Selection = $_REQUEST["selection"];
		}
		switch ($Mode){
			case "edit":
				if (empty($Selection)){
					throw new exception("No selection");
				}
				break;
		}
				
		switch ($Mode){
			case "view":				
				
				$Page->Title = "$Mode account";
				$PanelB .= "<h1>".$Page->Title."</h1>";
								
				$PanelB .= "<ul id='tabs'>";
				$PanelB .= "<li><a href='#settings'>Settings</a></li>";
				$PanelB .= "<li><a href='#picture'>Picture</a></li>";
				$PanelB .= "<li><a href='#email'>Email Accounts</a></li>";
				
				$PanelB .= "</ul>";
				

				$PanelB .= "<div class='tabContent hide' id='settings'>";
				$PanelB .= "<table>";
				$PanelB .= "<tr><th>Name</th><td>".$System->User->Name."</td><td><a href='account.php?mode=edit&selection=name'>&bull; edit</a></td></tr>";
				$PanelB .= "<tr><th>Password</th><td></td><td><a href='account.php?mode=edit&selection=password'>&bull; edit</a></td></tr>";				
				$PanelB .= "</table>";
				$PanelB .= "</div>";
				
				$PanelB .= "<div class='tabContent hide' id='picture'>";
				
				$PanelB .= '<form enctype="multipart/form-data" action="account.php" method=post>';
				$PanelB .= '<input type="hidden" name="MAX_FILE_SIZE" value="100000">';
				$PanelB .= 'Your Picture : <input name="userImage" type="file">';
				$PanelB .= '<input type="submit" value="Send Picture">';
				$PanelB .= '</form>';
				
				if (!$System->User->PictureOf == ""){
 					$PanelB .= '<img src="image.php?Id='.$System->User->PictureOf.'" /><br/>';
				}
				$PanelB .= "</div>";
				
				
				$PanelB .= "<div class='tabContent hide' id='email'>";
				$PanelB .= "<table><thead>";
				$PanelB .= "<tr><th>email address</th><th>Primary?</th><th/></tr>";

				foreach ($System->User->Emails as $Email){
					$PanelB .= "<tr><td>$Email</td><td/><td><a href='account.php?mode=edit&selection=email'>&bull; edit</a></td></tr>";
				}
				$PanelB .= "</table>";
				$PanelB .= "<a href='account.php?mode=new&selection=email'>&bull; add</a>";
				$PanelB .= "</div>";
								
				break;

			case 'new';
				switch ($Selection){
					case "email":

						$Page->Title = "Add Email Account";
						$PanelB .= "<h1>".$Page->Title."</h1>";
						
						$PanelB = "<div class='sdbluebox'>";
						
						$PanelB .= '<form method="post" action="doEmail.php">';
				
						$ReturnURL = 'account.php';
				
						$PanelB .= "<input type='hidden' name='ReturnURL' value='$ReturnURL'/>";
						$PanelB .= "<input type='hidden' name='Mode' value='$Mode'/>";
												
						$PanelB .= '<table class="bluebox">';
		
						$PanelB .= funAccountEmail();
						
						$PanelB .= "<tr><td colspan='2'><br/></td></tr>";
						$PanelB .= funAccountPassword();
						
						$PanelB .= "</table>";
						$PanelB .= '<input type="submit" value="Add">';
						
						$PanelB .= "</div>";
						
						break;
						
					default:
						
						$Page->Title = "$Mode account";
						$PanelB .= "<h1>".$Page->Title."</h1>";
						
						$PanelB = "<div class='sdbluebox'>";
						
						$PanelB .= '<form method="post" action="doAccount.php">';
				
						$ReturnURL = 'account.php';
				
						$PanelB .= "<input type='hidden' name='ReturnURL' value='$ReturnURL'/>";
						$PanelB .= "<input type='hidden' name='Mode' value='$Mode'/>";
												
						$PanelB .= '<table class="bluebox">';
		
						$PanelB .= funAccountName();
						$PanelB .= funAccountEmail();
						$PanelB .= funAccountNewPassword();
						
						$PanelB .= "</table>";
						$PanelB .= '<input type="submit" value="Register">';
						
						$PanelB .= "</div>";
						
						break;
				}
				
				break;

			case 'hash';
				
				$Page->Title = "Reset Password";
				$PanelB .= "<h1>".$Page->Title."</h1>";
			
				$PanelB .= '<form method="post" action="doAccount.php">';
		
				$ReturnURL = 'account.php';
		
				$PanelB .= "<input type='hidden' name='ReturnURL' value='$ReturnURL'/>";
				$PanelB .= "<input type='hidden' name='Mode' value='$Mode'/>";
										
				$PanelB .= '<table class="bluebox">';
				
				if (is_null($System->User->Name)){
					$PanelB .= funAccountName();
				}
				$PanelB .= funAccountNewPassword();
				
				$PanelB .= "</table>";
				$PanelB .= '<input type="submit" value="Update">';
				
				break;
				
				
			case 'edit';
			
				$Page->Title = "$Mode account";
				$PanelB .= "<h1>".$Page->Title."</h1>";
			
				$PanelB .= '<form method="post" action="doAccount.php">';
		
				$ReturnURL = "account.php";
		
				$PanelB .= "<input type='hidden' name='ReturnURL' value='$ReturnURL'/>";
				$PanelB .= "<input type='hidden' name='Mode' value='$Mode'/>";
				$PanelB .= "<input type='hidden' name='Selection' value='$Selection'/>";
				
				$PanelB .= '<table class="bluebox">';
				
				switch ($Selection){
					case "name":
						$PanelB .= funAccountName();
						break;
					case "email":
						$PanelB .= funAccountEmail();
						break;
					case "password":
						$PanelB .= funAccountNewPassword();
						break;
					default:
						throw new exception("Invalid Selection");
						break;						
				}
				
				$PanelB .= "<tr><td colspan='2'><br/></td></tr>";
				$PanelB .= funAccountPassword();
				
				$PanelB .= "</table>";
				$PanelB .= '<input type="submit" value="Update settings">';
				
				break;
			
		}
					
	 	$Page->ContentPanelB = $PanelB;
	 	
	}
	catch(Exception $e)  {
		$System->Session->ErrorMessage = $e->getMessage();
	}
	 	
	$Page->Display();	
	
function funAccountName(){
	global $System;
	global $Mode;
	global $UserName;

	if ($Mode == "edit"){
		if (!$System->Fail){
			$UserName = $System->User->Name;
		}
	}
	
	$Content = "";
	
	$Content .= '<tr>';
	$Content .= '<th>Your Name</th>';
	$Content .= "<td><input type='text' name='UserName' size='40' maxlength='100' value='$UserName'/></td>";
	$Content .= '</tr>';					
	
	return $Content;
	
}	


function funAccountEmail(){
	global $System;
	global $Mode;
	global $Email;

	if ($Mode == "edit"){
		if (!$System->Fail){
			$Email = $System->User->Email;
		}
	}
	
	$Content = "";
	
	$Content .= '<tr>';
	$Content .= '<th>Your Email address</th>';
	$Content .= "<td><input type='text' name='Email' size='80' maxlength='100' value='$Email'/></td>";
	$Content .= '</tr>';					
	
	return $Content;
	
}	

function funAccountNewPassword(){
	global $System;

	$Content = "";
	
	$Content .= '<tr>';
	$Content .= '<th>Choose a Password<br />(between 6 and 16 chars)</th>';
	$Content .= "<td><input type='password' name='NewPassword1' size='16' maxlength='16'/></td>";
	$Content .= '</tr>';					

	$Content .= '<tr>';
	$Content .= '<th>re type Password</th>';
	$Content .= "<td><input type='password' name='NewPassword2' size='16' maxlength='16'/></td>";
	$Content .= '</tr>';					
		
	return $Content;
	
}	

function funAccountPassword(){
	global $System;

	$Content = "";

	$Content .= '<tr>';
	$Content .= '<th>Current Password</th>';
	$Content .= "<td><input type='password' name='CurrentPassword' size='16' maxlength='16'/></td>";
	$Content .= '</tr>';					

	return $Content;
	
}	

?>