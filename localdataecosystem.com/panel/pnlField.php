<?php

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlField( $objField ){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	
	$Content = '';
	
	$Content .= '<table class="sdgreybox">';
	
	$Content .= "<tr><th>Data Type</th><td>".$objField->DataType."</a></td></tr>";
	
	
	switch ( $objField->DataType){
		case 'line':
			$Length = $objField->Length;
			if (empty($Length)){
				$Length = $System->Config->Defaults->LineLength;
			}
			$Content .= "<tr><th>Length</th><td align='right'>".$Length."</a></td></tr>";
			break;
	}
	
    $Content .= '</table>';
	    
    return $Content;
}


?>