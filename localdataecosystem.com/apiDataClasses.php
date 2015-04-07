<?php

require_once("class/clsSystem.php");
require_once("class/clsData.php");
require_once("class/clsDict.php");
	
	define('PAGE_NAME', 'apiDataClasses');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
		
	$ContextId = null;
	$LicenceTypeId = null;
	$OrgId = null;
	$ShapeId = null;

	if (isset($_SESSION['forms'][PAGE_NAME]['contextid'])){
		$ContextId = $_SESSION['forms'][PAGE_NAME]['contextid'];		
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['licencetypeid'])){
		$LicenceTypeId = $_SESSION['forms'][PAGE_NAME]['licencetypeid'];		
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['orgid'])){
		$OrgId = $_SESSION['forms'][PAGE_NAME]['orgid'];		
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
		$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];		
	}
	
	$Classes = new clsDataClasses();
	$Classes->LicenceTypeId = $LicenceTypeId;
	$Classes->ContextId = $ContextId;
	$Classes->OrgId = $OrgId;
	$Classes->ShapeId = $ShapeId;
	
	unset($_SESSION['forms'][PAGE_NAME]);
	
	header ('Content-type: text/xml');
	echo $Classes->xml;
	exit;
	
?>