<?php
	require_once('function/utils.inc');
	require_once('data/dataData.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');

	require_once('class/clsDict.php');
	require_once('class/clsShape.php');
	require_once('class/clsDocument.php');
		
	define('PAGE_NAME', 'documentsubject');
	
	session_start();
	$System = new clsSystem();
		
	
	try {
			
		SaveUserInput(PAGE_NAME);
		
		if (!$System->LoggedOn){
			throw new exception("You must be logged on");
		}
		
		$Dicts = new clsDicts();
		$Shapes = new clsShapes();
		
		$GroupId = null;
		$SetId = null;
		$ShapeId = null;
		$ShapeClassId = null;
		$DocId = null;
		
		$xmlForm = null;
		$domForm = null;
		$xpathForm = null;
		$nsForm = null;


		$SubjectId = null;
		$objDoc = null;		
		$objSubject = null;
		$objShapeClass = null;

		$objForm = null;		

		if (isset($_SESSION['forms'][PAGE_NAME]['docid'])){			
			$DocId = $_SESSION['forms'][PAGE_NAME]['docid'];
			$objDoc = new clsDocument($DocId);
			$SetId = $objDoc->SetId;
			$ShapeId = $objDoc->ShapeId;
		}		
		
		if (isset($_SESSION['forms'][PAGE_NAME]['setid'])){
			$SetId = $_SESSION['forms'][PAGE_NAME]['setid'];			
		}
		
		if (is_null($SetId)){
			throw new exception ("Set not specified");
		}
		
		$objSet = new clsSet($SetId);
		if (!($objSet->canEdit)){
			throw new exception("You cannot update this Set");
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
			$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];
		}
		
		if (is_null($ShapeId)){
			throw new exception ("Shape not specified");
		}

		if (!isset($Shapes->Items[$ShapeId])){
			throw new exception("Unknown Shape $ShapeId");
		}
		$objShape = $Shapes->Items[$ShapeId];
		
		if (isset($_SESSION['forms'][PAGE_NAME]['shapeclassid'])){
			$ShapeClassId = $_SESSION['forms'][PAGE_NAME]['shapeclassid'];			
			if (!isset($objShape->ShapeClasses[$ShapeClassId])){
				throw new exception("Unknown Shape Class");
			}
			$objShapeClass = $objShape->ShapeClasses[$ShapeClassId];
		}

		
		if (is_null($objDoc)){
			$objDoc = new clsDocument();
			$objDoc->ShapeId = $ShapeId;
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['xmlForm'])){			
			$xmlForm = $_SESSION['forms'][PAGE_NAME]['xmlForm'];
			
//			echo '<pre>'.htmlentities($xmlForm).'</pre>';
						
			$domForm = new DOMDocument;
			if ($domForm->loadXML($xmlForm) === false){
				
//				echo "xml form is $xmlForm <br/>";
//				exit;
				
				throw new exception("Invalid XML file for form");
			}
			$nsForm = $domForm->lookupNamespaceUri($domForm->namespaceURI);
			$xpathForm = new domxpath($domForm);
			$xpathForm->registerNamespace('form', $nsForm);	
			
			
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['subjectid'])){
			$SubjectId = $_SESSION['forms'][PAGE_NAME]['subjectid'];
		}
		
				
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}

				
		if (!is_null($SubjectId)){
			if (isset($objDoc->SubjectForms[$SubjectId])){
				$objForm = $objDoc->SubjectForms[$SubjectId];
			}
			else
			{
				$objForm = new clsForm($objShapeClass, $SubjectId);
			}
		}
		else
		{
			if (!isset($objDoc->BlankSubjectForms[$ShapeClassId])){
				throw new Exception("Invalid Shape Class");
			}
			$objForm = $objDoc->BlankSubjectForms[$ShapeClassId];
		}
		
		if (!is_null($SubjectId)){
			$objSubject = new clsSubject($SubjectId);
		}
		

		if (is_object($objSubject)){
			$SubjectClass = $objForm->ShapeClass->Class;
			
			$SubjectOk = false;
			if ($objSubject->ClassDictId == $SubjectClass->DictId){
				if ($objSubject->ClassId == $SubjectClass->Id){
					$SubjectOk = true;
				}			
			}
			
			// check for sameAs Classes
			if (!$SubjectOk){
				foreach ($Dicts->SameAsClasses($SubjectClass->DictId, $SubjectClass->Id) as $SameAsClass){
					if ($objSubject->ClassDictId == $SameAsClass->DictId){
						if ($objSubject->ClassId == $SameAsClass->Id){
							$SubjectOk = true;
						}			
					}					
				}
			}
			
						
			if (!$SubjectOk){
				throw new Exception("Invalid Subject");
			}
		}
				
		switch ($Mode){
			case 'edit':
			case 'delete':
				if (!isset($DocId)){
					throw new exception("DocId not specified");
				}
				break;
			case 'new':
				break;
			default:
				throw new exception("Invalid Mode");
				break;				
		}
		
		switch ( $Mode ){
			case "new":
			case "edit":
				$newDoc = false;
				if (is_null($DocId)){
					$newDoc = true;
					$DocId = dataDocUpdate($Mode, $DocId , $SetId, $ShapeId);
				}
				
				$FormSubjectId = updateForm($xpathForm, $objForm);
				
				if (is_null($SubjectId)){
					$SubjectId = $FormSubjectId;
				}
				

				break;
			case 'delete':
				
				
				foreach ($objForm->Statements as $objStatement){
					$deleteStatement = false;
					if ($objStatement->SubjectId == $SubjectId){
						$deleteStatement = true;
					}
					if ($objStatement->ObjectId == $SubjectId){
						$deleteStatement = true;
					}
					if ($deleteStatement){
						dataStatDelete($objStatement->Id);
					}
				}

				break;
		}

		$ReturnUrl = "document.php?docid=$DocId";
		
		unset($_SESSION['forms'][PAGE_NAME]);
	    header("Location: $ReturnUrl");
    	exit;
				
	}
	catch(Exception $e)  {
		$System->doError($e->getMessage());
		exit;
	}




?>