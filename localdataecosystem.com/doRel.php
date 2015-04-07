<?php
	require_once('function/utils.inc');
	require_once('data/dataDict.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');
	require_once('class/clsDict.php');
	require_once('class/clsModel.php');
	
	define('PAGE_NAME', 'relationship');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	try {
	
	
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$GroupId = '';
		$DictId = '';
		$RelId = null;
	
		if (!isset($_SESSION['forms'][PAGE_NAME]['dictid'])){
			throw new exception("dictid not specified");
		}
	
		$DictId = $_SESSION['forms'][PAGE_NAME]['dictid'];			
		if (isset($_SESSION['forms'][PAGE_NAME]['relid'])){
			$RelId = $_SESSION['forms'][PAGE_NAME]['relid'];
		}
		
		$Dicts = new clsDicts();
		
	
		$objModel = new clsModel();
	

		$objDict = $Dicts->Dictionaries[$DictId];
		
		
		if (!($objDict->canEdit)){
			throw new exception("You cannot update this Dictionary");
		}	
		
		
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}
		switch ($Mode) {
			case 'new':				
				break;
			case 'edit':
			case 'delete':
				if ( is_null($RelId)){
					throw new exception("relid not specified");
				}
				break;
			default:
				throw new exception("Invalid Mode");
				break;
		}

		
		switch ($Mode){
			case "delete":
				break;
			default:
				
				$Label = "";
				$InverseLabel = "";
				$Cardinality = null;
				$Extending = false;
				$InverseExtending = false;
				$ConRel = '';
				$Description = '';
				$SubjectDictId = '';
				$SubjectId = '';

				$ObjectDictId = '';
				$ObjectId = '';

				if (isset($_SESSION['forms'][PAGE_NAME]['label'])){
					$Label = $_SESSION['forms'][PAGE_NAME]['label'];			
				}
				if ( $Label==''){
					throw new exception("Label not specified");
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['inverselabel'])){
					$InverseLabel = $_SESSION['forms'][PAGE_NAME]['inverselabel'];			
				}				

				if (isset($_SESSION['forms'][PAGE_NAME]['cardinality'])){
					$Cardinality = $_SESSION['forms'][PAGE_NAME]['cardinality'];			
				}				

				if (isset($_SESSION['forms'][PAGE_NAME]['extending'])){
					if ($_SESSION['forms'][PAGE_NAME]['extending'] == 'true'){
						$Extending = true;
					}
				}

				if (isset($_SESSION['forms'][PAGE_NAME]['inverseextending'])){
					if ($_SESSION['forms'][PAGE_NAME]['inverseextending'] == 'true'){
						$InverseExtending = true;
					}
				}
				
				
				
				if (isset($_SESSION['forms'][PAGE_NAME]['conrel'])){
					$ConRel = $_SESSION['forms'][PAGE_NAME]['conrel'];
					$found = false;
					foreach ($objModel->Relationships as $optConRel){
						if ($optConRel->Property == $ConRel){
							$found = true;
						}
						if ($optConRel->InverseProperty == $ConRel){
							$found = true;
						}						
					}
					if (!$found){
						throw new exception("Invalid Concept Relationship");
					}
				}
				if (isset($_SESSION['forms'][PAGE_NAME]['description'])){
					$Description = $_SESSION['forms'][PAGE_NAME]['description'];		
				}		

				$SubjectDictId = $DictId;
				$objSubjectDict = $objDict;
				if (isset($_SESSION['forms'][PAGE_NAME]['subjectdictid'])){
					$SubjectDictId = $_SESSION['forms'][PAGE_NAME]['subjectdictid'];		
					$objSubjectDict = $Dicts->Dictionaries[$SubjectDictId];					
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['subjectid'])){
					$SubjectId = $_SESSION['forms'][PAGE_NAME]['subjectid'];
				}
				if (empty($SubjectId)){
					throw new exception("SubjectId not specified");
				}
				if (!isset($objSubjectDict->Classes[$SubjectId])){
					throw new exception("Unknown SubjectId");
				}
				
				$ObjectDictId = $DictId;
				$objObjectDict = $objDict;
				
				if (isset($_SESSION['forms'][PAGE_NAME]['objectdictid'])){
					$ObjectDictId = $_SESSION['forms'][PAGE_NAME]['objectdictid'];
					$objObjectDict = $Dicts->Dictionaries[$ObjectDictId];					
				}		
				if (isset($_SESSION['forms'][PAGE_NAME]['objectid'])){
					$ObjectId = $_SESSION['forms'][PAGE_NAME]['objectid'];
				}		
				if (empty($ObjectId)){
					throw new exception("ObjectId not specified");
				}
				if (!isset($objObjectDict->Classes[$ObjectId])){
					throw new exception("Unknown ObjectId");
				}				
				
				break;
		}
		

		switch ( $Mode ){
			case "new":
			case "edit":				
				$RelId = dataRelUpdate($Mode, $RelId, $DictId, $SubjectDictId, $SubjectId, $ObjectDictId, $ObjectId, $ConRel, $Label, $Description, $InverseLabel, $Cardinality, $Extending, $InverseExtending);
				break;
			case "delete":
				dataRelDelete($RelId, $DictId);
				break;
		}

		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "dict.php?dictid=$DictId";
				break;
			default:
				$ReturnUrl = "relationship.php?dictid=$DictId&relid=$RelId";
				break;
		}
		
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}

?>