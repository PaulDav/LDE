<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');
require_once(dirname(__FILE__).'/../class/clsData.php');

Function pnlStandardMenu(){

 	global $System;
 	if (!isset($System)){
 		$System = new clsSystem();
 	}

		
	$Content = "";
		
	$Content .= "<div class='menu'>";
		
	$Content .= "<ul>";
	$Content .= "<li><a href='.'>Home</a></li>";
	$Content .= "<hr/>";
	$Content .= "<li><a href='browse.php'>Browse</a></li>";
	$Content .= "<li><a href='dashboards.php'>Dashboards</a></li>";
	$Content .= "<hr/>";
	$Content .= "<li><a href='sets.php'>Datasets</a></li>";
	$Content .= "<li><a href='organisations.php'>Organisations</a></li>";
	$Content .= "<li><a href='licences.php'>Licences</a></li>";
	$Content .= "<hr/>";
	$Content .= "<li><a href='designs.php'>Designs</a></li>";	
	$Content .= "<li><a href='groups.php'>Design Groups</a></li>";
	$Content .= "<li><a href='library.php'>Library</a></li>";
	
	$Content .= "</ul>";

	$Content .= "</div>";
	
	
    return $Content;
}

Function pnlLoggedOnMenu(){
	
	global $System;
 	if (!isset($System)){
 		$System = new clsSystem();
 	}
		
	
	$Content = "";
				
	$Content .= "<div class='menu'>";	
	$Content .= "<ul>";
	
	$Content .= "</ul>";
	
    return $Content;
}

?>