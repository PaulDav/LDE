<?php

require_once(dirname(__FILE__).'/../class/clsUser.php');


function pnlUser($UserId){

	$Content = '';
		
	try {
		$objUser = new clsUser($UserId);
		
		$Content .= '<table class="sdgreybox">';
		
		$Content .= "<tr><td>";
		if (!is_null($objUser->PictureOf)) {
			$Content .= "<img height = '30' src='image.php?Id=".$objUser->PictureOf."' /><br/>";
		}
		$Content .= $objUser->Name;
		$Content .= "</td>";
		
		$Content .= "</tr>";
		$Content .= "</table>";
	}
	catch (Exception $e) {
	    unset($e);
	}										
	
	return $Content;
}