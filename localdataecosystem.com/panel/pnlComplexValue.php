<?php

function pnlComplexValue($objAtt){

	$Content = '';
	foreach ($objAtt->ComplexAttributes as $objComplexAttribute){
		
		switch ($objComplexAttribute->Property->Type){
			case 'simple':
				$Content .= make_links($objComplexAttribute->Value)." ";
				break;
			case 'complex':
				$Content .= pnlComplexValue($objComplexAttribute).'<br/>';
				break;
		}
		
	}
	
	return $Content;
	
}
