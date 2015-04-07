<?php
	require_once('function/utils.inc');
	require_once('data/dataData.php');
	require_once('class/clsSystem.php');
	require_once('class/clsGroup.php');

	require_once('class/clsDict.php');
	require_once('class/clsShape.php');
	require_once('class/clsDocument.php');
		
	define('PAGE_NAME', 'documentlink');
	
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
		$ShapeLinkId = null;
		$DocId = null;
		$LinkId = null;
		$FromId = null;
		$ToId = null;
		$RelEffFrom = null;
		$RelEffTo = null;
		
		$xmlForm = null;
		$domForm = null;
		$xpathLinkForm = null;
		$nsForm = null;

		$objDoc = null;
		$objLink = null;
		$objFrom = null;
		$objTo = null;	
		
		if (isset($_SESSION['forms'][PAGE_NAME]['docid'])){			
			$DocId = $_SESSION['forms'][PAGE_NAME]['docid'];
			$objDoc = new clsDocument($DocId);
			$SetId = $objDoc->SetId;
			$ShapeId = $objDoc->objShape->Id;
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

		
		if (isset($_SESSION['forms'][PAGE_NAME]['shapelinkid'])){
			$ShapeLinkId = $_SESSION['forms'][PAGE_NAME]['shapelinkid'];			
			if (!isset($objShape->ShapeLinks[$ShapeLinkId])){
				throw new exception("Unknown Shape Link");
			}
		}
		

		if (is_null($objDoc)){
			$objDoc = new clsDocument();
			$objDoc->ShapeId = $ShapeId;
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['xmlLinkForm'])){			
			$xmlLinkForm = $_SESSION['forms'][PAGE_NAME]['xmlLinkForm'];
			$domLinkForm = new DOMDocument;
			if (@$domLinkForm->loadXML($xmlLinkForm) === false){
				throw new exception("Invalid XML file for link form");
			}
			$nsLinkForm = $domLinkForm->lookupNamespaceUri($domLinkForm->namespaceURI);
			$xpathLinkForm = new domxpath($domLinkForm);
			$xpathLinkForm->registerNamespace('form', $nsLinkForm);	
			
//			echo '<pre>'.htmlentities($xmlLinkForm).'</pre>';
//			exit;		
		}
		
		if (isset($_SESSION['forms'][PAGE_NAME]['linkid'])){
			$LinkId = $_SESSION['forms'][PAGE_NAME]['linkid'];
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['fromid'])){
			$FromId = $_SESSION['forms'][PAGE_NAME]['fromid'];
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['toid'])){
			$ToId = $_SESSION['forms'][PAGE_NAME]['toid'];
		}

		if (isset($_SESSION['forms'][PAGE_NAME]['relefffrom'])){
			$RelEffFrom = $_SESSION['forms'][PAGE_NAME]['relefffrom'];
		}
		if (isset($_SESSION['forms'][PAGE_NAME]['releffto'])){
			$RelEffFrom = $_SESSION['forms'][PAGE_NAME]['releffto'];
		}

		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];			
		}

		if (!is_null($LinkId)){
			if (!isset($objDoc->LinkForms[$LinkId])){
				throw new Exception("Link not in the Document");
			}
			$objLinkForm = $objDoc->LinkForms[$LinkId];
		}
		else
		{
			if (!isset($objDoc->BlankLinkForms[$ShapeLinkId])){
				throw new Exception("Invalid Shape Link");
			}
			$objLinkForm = $objDoc->BlankLinkForms[$ShapeLinkId];
		}
		
		if (!is_null($LinkId)){
			$objLink = new clsLink($LinkId);
		}
		if (!is_null($FromId)){
			$objFrom = new clsSubject($FromId);
		}
		if (!is_null($ToId)){
			$objTo = new clsSubject($ToId);
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
			case 'edit':
			case 'new':
				
				$FromOk = false;
				if (is_object($objFrom)){
					$FromShapeClassId = $objLinkForm->ShapeLink->FromShapeClassId;
					if (isset($objShape->ShapeClasses[$FromShapeClassId])){
						$objFromShapeClass = $objShape->ShapeClasses[$FromShapeClassId];
						if ($objFrom->ClassDictId == $objFromShapeClass->Class->DictId){
							if ($objFrom->ClassId == $objFromShapeClass->Class->Id){
								$FromOk = true;
							}					
						}				
						if (!$FromOk){
							foreach ($Dicts->SubClasses($objFromShapeClass->Class->DictId, $objFromShapeClass->Class->Id) as $SubClass){
								if ($objFrom->ClassDictId == $SubClass->DictId){
									if ($objFrom->ClassId == $SubClass->Id){
										$FromOk = true;
									}
								}
							}					
						}
						
						
						
					}
				}
				if (!$FromOk){
					throw new exception("Invalid Link From");
				}
				
				
				$ToOk = false;
				if (is_object($objTo)){
					$ToShapeClassId = $objLinkForm->ShapeLink->ToShapeClassId;
					if (isset($objShape->ShapeClasses[$ToShapeClassId])){
						$objToShapeClass = $objShape->ShapeClasses[$ToShapeClassId];
						if ($objTo->ClassDictId == $objToShapeClass->Class->DictId){
							if ($objTo->ClassId == $objToShapeClass->Class->Id){
								$ToOk = true;
							}
						}
						if (!$ToOk){
							foreach ($Dicts->SubClasses($objToShapeClass->Class->DictId, $objToShapeClass->Class->Id) as $SubClass){
								if ($objTo->ClassDictId == $SubClass->DictId){
									if ($objTo->ClassId == $SubClass->Id){
										$ToOk = true;
									}
								}
							}					
						}
					}
				}
				

				switch ($objLinkForm->CreateExtended){
					case true:
						break;
					default:
						if (!$ToOk){
							throw new exception("Invalid Link To");
						}
				}						
				
				
				$newDoc = false;
				if (is_null($DocId)){
					$newDoc = true;
					$DocId = dataDocUpdate($Mode, $DocId , $SetId, $ShapeId);
				}

				
				if ($objLinkForm->CreateExtended){

					$objExtendedForm = null;					
					$ToShapeClassId = $objLinkForm->ShapeLink->ToShapeClassId;
					if (isset($objDoc->BlankSubjectForms[$ToShapeClassId])){
						$objExtendedForm = $objDoc->BlankSubjectForms[$ToShapeClassId];						
					}
					if (!is_null($objLinkForm->ToId)){
						$ToId = $objLinkForm->ToId;
						if (isset($objDoc->SubjectForms[$ToId])){
							$objExtendedForm = $objDoc->SubjectForms[$ToId];
						}
					}
					
					$ToId = updateForm($xpathLinkForm, $objExtendedForm);
				}
				
				
				switch ($objLinkForm->ShapeLink->Inverse){
					case true:
						$LinkId = dataStatUpdate($Mode, $LinkId , $SetId, $DocId, 300, $objLinkForm->ShapeLink->Relationship->DictId, $objLinkForm->ShapeLink->Relationship->Id, $ToId, $FromId, null, $RelEffFrom, $RelEffTo);
						break;						
					default:
						$LinkId = dataStatUpdate($Mode, $LinkId , $SetId, $DocId, 300, $objLinkForm->ShapeLink->Relationship->DictId, $objLinkForm->ShapeLink->Relationship->Id, $FromId, $ToId, null, $RelEffFrom, $RelEffTo);
						break;						
				}

								
				$FormLinkId = updateLinkForm($LinkId, $xpathLinkForm, $objLinkForm);
								
				break;
			case 'delete':

				dataStatDelete($LinkId);
				
				foreach ($objLinkForm->Statements as $objStatement){
					$deleteStatement = false;
					if ($objStatement->AboutId == $LinkId){
						$deleteStatement = true;
					}
					if ($deleteStatement){
						dataStatDelete($objStatement->Id);
					}
				}
				
				break;
			default:
				throw new exception("Invalid Mode");
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