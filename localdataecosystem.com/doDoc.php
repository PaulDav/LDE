<?php
	require_once('function/utils.inc');
	require_once('data/dataData.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');

	require_once('class/clsDict.php');
	require_once('class/clsShape.php');
	require_once('class/clsDocument.php');
		
	define('PAGE_NAME', 'document');
	
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
		$DocId = null;
		
		$xmlForm = null;
		$domForm = null;
		$xpathForm = null;
		$nsForm = null;

		$xmlLinkForm = null;
		$domLinkForm = null;
		$xpathLinkForm = null;
		
		
		$StartShapeClassId = null;
		
		$RelId = null;
		$RelStatId = null;
		$Seq = 0;
		
		$objShapeLink = null;

		$SubjectId = null;
		$objSubject = null;
		
		$ObjectId = null;
		$objDoc = null;		
		$objObject = null;

		$objSubjectForm = null;
		$objForm = null;
		$objLinkForm = null;
		
		$RelEffFrom = null;
		$RelEffTo = null;

		
		if (isset($_SESSION['forms'][PAGE_NAME]['docid'])){			
			$DocId = $_SESSION['forms'][PAGE_NAME]['docid'];
			$objDoc = new clsDocument($DocId);
			$SetId = $objDoc->SetId;
			if (!is_null($objDoc->objShape)){
				$ShapeId = $objDoc->ShapeId;
			}
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

		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
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
		

		switch ($Mode){
			case 'new':
			case 'edit':
				
				if (isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
					$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];			
				}
				
				if (is_null($ShapeId)){
					throw new exception ("Shape not specified");
				}
				
				if (!isset($Shapes->Items[$ShapeId])){
					throw new exception ("Unknown Shape");
				}
				$objShape = $Shapes->Items[$ShapeId];
		
				if (isset($_SESSION['forms'][PAGE_NAME]['xmlForm'])){			
					$xmlForm = $_SESSION['forms'][PAGE_NAME]['xmlForm'];
					$domForm = new DOMDocument;
					if (@$domForm->loadXML($xmlForm) === false){
						throw new exception("Invalid XML file for form");
					}
					$nsForm = $domForm->lookupNamespaceUri($domForm->namespaceURI);
					$xpathForm = new domxpath($domForm);
					$xpathForm->registerNamespace('form', $nsForm);	
					
		//			echo '<pre>'.htmlentities($xmlForm).'</pre>';
		//			exit;
					
				}
		
				
				if (isset($_SESSION['forms'][PAGE_NAME]['xmlLinkForm'])){			
					$xmlLinkForm = $_SESSION['forms'][PAGE_NAME]['xmlLinkForm'];
					$domLinkForm = new DOMDocument;
					if (@$domLinkForm->loadXML($xmlLinkForm) === false){
						throw new exception("Invalid XML file for link form");
					}
					$nsForm = $domLinkForm->lookupNamespaceUri($domLinkForm->namespaceURI);
					$xpathLinkForm = new domxpath($domLinkForm);
					$xpathLinkForm->registerNamespace('form', $nsForm);	
					
		//			echo '<pre>'.htmlentities($xmlLinkForm).'</pre>';
		//			exit;
					
				}
				
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
				break;
			case 'delete':
				dataDocDelete($DocId);
				break;
		}

		$ReturnUrl = "document.php?docid=$DocId";
		
		switch ( $Mode ){
			case "delete":
				$ReturnUrl = "set.php?setid=$SetId";
				if (!is_null($RelId)){
					$ReturnUrl = "document.php?docid=$DocId";				
				}				
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