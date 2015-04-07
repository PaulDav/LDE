<?php

require_once('class/clsDict.php');
require_once('class/clsData.php');
require_once('class/clsProfile.php');

	$Type = null;

	$Style = null;
	$DictId = null;
	$ProfileId = null;
	$SubjectId = null;
	
	$objDict = null;
	$objProfile = null;
	$objSubject = null;
	
	if (isset($_REQUEST['style'])){
		$Style = $_REQUEST['style'];
	}

	$Dicts = new clsDicts();
	
	if (isset($_REQUEST['dictid'])){
		$DictId = $_REQUEST['dictid'];
		$objDict = $Dicts->Dictionaries[$DictId];
		$Type = 'dict';
	}

	if (isset($_REQUEST['profileid'])){
		$ProfileId = $_REQUEST['profileid'];
		$Profiles = new clsProfiles();
		$objProfile = $Profiles->Items[$ProfileId];
		$Type = 'profile';
	}

	if (isset($_REQUEST['subjectid'])){
		$SubjectId = $_REQUEST['subjectid'];
		$objSubject = new clsSubject($SubjectId);
		$Type = 'subject';
	}
	
	
	
	$Script = "";

	switch ($Type){
		case 'dict':
			if (!is_null($objDict)){	
				$Script = $objDict->getDot($Style);
			}
			break;
		case 'profile':
			if (!is_null($objProfile)){	
				$Script = $objProfile->getDot($Style);
			}
			break;
		case 'subject':
			if (!is_null($objSubject)){	
				$Script = $objSubject->getDot($Style);
			}
			break;			
	}

	header('content-type: image/png');
	
  	$url = 'https://chart.googleapis.com/chart';

	$fields = array('cht' => urlencode('gv'),'chl' => urlencode($Script));
				
	$fields_string = "";
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');

	$ch = curl_init();
	
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
//	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
	$result = curl_exec($ch);
	
	curl_close($ch);

	
?>