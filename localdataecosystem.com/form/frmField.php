<?php

require_once(dirname(__FILE__).'/../function/utils.inc');

Function frmField( $objField){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	
	$Content = '';

	$DictId = $objField->DictId;
	$PropId = $objField->PropId;
	$PartId = $objField->PartId;	
	
	$Content .= "<form method='post' action='doField.php'>";
	
	if (!is_null( $DictId)){
		$Content .= "<input type='hidden' name='dictid' value='$DictId'/>";
	}
	if (!is_null($PropId)){
		$Content .= "<input type='hidden' name='propid' value='$PropId'/>";
	}
	if (!is_null($PartId)){
		$Content .= "<input type='hidden' name='partid' value='$PartId'/>";
	}
	
	$Content .= '<table class="sdbluebox">';
		
	$Content .= '<tr>';
		$Content .= '<th>';
		$Content .= 'Data Type';
		$Content .= '</th>';
		$Content .= '<td>';						
		$Content .= "<select name='datatype'>";
		foreach ($System->Config->DataTypes as $optDataType){
			$Content .= "<option";
			if ($objField->DataType == $optDataType){
				$Content .= " selected='true' ";
			}
			$Content .= ">$optDataType</option>";
		}
		$Content .= "</select>";						
		
		$Content .= '</td>';
	$Content .= '</tr>';					
	
	$Content .= '<tr>';
		$Content .= '<th>';
		$Content .= 'Length';
		$Content .= '</th>';
		$Content .= '<td>';						
			$Content .= "<input name='length' value='".$objField->Length."' size='4' maxlength = '4'>";
		$Content .= '</td>';
	$Content .= '</tr>';					
		
	
	$Content .= '<tr>';
		$Content .= '<td/>';
		$Content .= '<td>';
		
		$Content .= '<input type="submit" value="Update Field">';

		$Content .= '</td>';
	$Content .= '</tr>';

 	$Content .= '</table>';
	$Content .= '</form>';
	
	    
    return $Content;
}


?>